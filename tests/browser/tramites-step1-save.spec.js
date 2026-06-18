const { test, expect } = require('@playwright/test');

function getEnv(name) {
  return (process.env[name] || '').trim();
}

async function maybeLogin(page) {
  const loginForm = page.locator('#loginForm');
  if (!(await loginForm.isVisible().catch(() => false))) {
    return false;
  }

  const username = getEnv('SGL_USERNAME');
  const password = getEnv('SGL_PASSWORD');

  if (!username || !password) {
    throw new Error('No hay sesion activa. Define SGL_USERNAME y SGL_PASSWORD o inicia sesion manualmente antes de correr la spec.');
  }

  await page.locator('input[name="username"]').fill(username);
  await page.locator('input[name="password"]').fill(password);

  await Promise.all([
    page.waitForURL(/deskapp\//, { timeout: 20000, waitUntil: 'domcontentloaded' }),
    page.locator('#submitBtn').click(),
  ]);

  return true;
}

async function ensureAuthenticatedPage(page, baseUrl, path) {
  const response = await page.goto(baseUrl + path, { waitUntil: 'domcontentloaded' });
  const loggedIn = await maybeLogin(page);

  if (loggedIn && !page.url().includes(path)) {
    return page.goto(baseUrl + path, { waitUntil: 'domcontentloaded' });
  }

  return response;
}

async function openStep1Page(page, baseUrl, tramiteId) {
  const path = `/deskapp/tramitesn/prototipo-layout/paso-1?tramite_id=${tramiteId}`;
  const response = await ensureAuthenticatedPage(page, baseUrl, path);
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(500);

  await expect(page.locator('[data-step1-service-input="principal_tipo_id"]').first()).toBeVisible();
  await expect(page.locator('[data-step1-services-list]').first()).toBeVisible();

  return path;
}

async function readStep1State(page) {
  return page.evaluate(() => {
    const rows = Array.from(document.querySelectorAll('[data-step1-services-list] .tp-assoc-item')).map((node) => ({
      asociadoId: Number(node.getAttribute('data-step1-asociado-id') || '0'),
      label: (node.querySelector('strong')?.textContent || '').trim(),
      note: (node.querySelector('small')?.textContent || '').trim(),
      selectValue: (node.querySelector('[data-step1-associated-select]')?.value || '').trim(),
    }));

    const addOptions = Array.from(document.querySelectorAll('[data-step1-service-input="add_tipo_id"] option'))
      .map((option) => ({
        value: String(option.value || '').trim(),
        label: (option.textContent || '').trim(),
      }))
      .filter((option) => option.value !== '');

    const feedbackNode = document.querySelector('[data-step1-services-feedback]');

    return {
      badgeText: (document.querySelector('.tp-badge')?.textContent || '').trim(),
      sourceCopy: Array.from(document.querySelectorAll('.tp-mini-copy'))
        .map((node) => (node.textContent || '').trim())
        .find((text) => text.includes('Este prototipo toma datos')) || '',
      canEdit:
        !!document.querySelector('[data-step1-principal-save]')
        && !!document.querySelector('[data-step1-associated-add]')
        && !document.querySelector('[data-step1-service-input="principal_tipo_id"]')?.hasAttribute('disabled'),
      principalValue: (document.querySelector('[data-step1-service-input="principal_tipo_id"]')?.value || '').trim(),
      principalLabel: (document.querySelector('[data-step1-detail-value="principal"]')?.textContent || '').trim(),
      ligadosLabel: (document.querySelector('[data-step1-detail-value="ligados"]')?.textContent || '').trim(),
      observacionesValue: (document.querySelector('[data-step1-input="observaciones"]')?.value || '').trim(),
      feedbackText: (feedbackNode?.textContent || '').trim(),
      rows,
      addOptions,
    };
  });
}

function comparableState(state) {
  return {
    principalValue: String(state.principalValue || ''),
    rows: state.rows.map((row) => ({
      asociadoId: Number(row.asociadoId || 0),
      label: String(row.label || ''),
      note: String(row.note || ''),
      selectValue: String(row.selectValue || ''),
    })),
  };
}

function findAssociatedRow(state, asociadoId) {
  return state.rows.find((row) => Number(row.asociadoId || 0) === Number(asociadoId || 0));
}

function pickCandidateType(options, excludedValues, preferredValues) {
  const optionValues = options.map((option) => Number(option.value || 0)).filter((value) => value > 0);
  const preferred = preferredValues.map((value) => Number(value || 0)).filter((value) => value > 0);

  for (const value of preferred) {
    if (optionValues.includes(value) && !excludedValues.has(value)) {
      return value;
    }
  }

  return optionValues.find((value) => !excludedValues.has(value)) || 0;
}

async function runJsonAction(page, urlPart, action) {
  const responsePromise = page.waitForResponse((response) => {
    return response.request().method() === 'POST' && response.url().includes(urlPart);
  }, { timeout: 30000 });

  await action();

  const response = await responsePromise;
  let json = null;
  try {
    json = await response.json();
  } catch (error) {
    json = null;
  }

  return {
    status: response.status(),
    json,
  };
}

async function updateAssociated(page, asociadoId, tipoId) {
  await page.locator(`[data-step1-associated-select="${asociadoId}"]`).selectOption(String(tipoId));
  const result = await runJsonAction(page, '/deskapp/tramitesn/services/update', async () => {
    await page.locator(`[data-step1-associated-save="${asociadoId}"]`).click();
  });
  await expect(page.locator('[data-step1-services-feedback]')).toContainText('Tipo asociado actualizado.');
  return result;
}

async function addAssociated(page, tipoId) {
  await page.locator('[data-step1-service-input="add_tipo_id"]').selectOption(String(tipoId));
  const result = await runJsonAction(page, '/deskapp/tramitesn/services/add', async () => {
    await page.locator('[data-step1-associated-add]').click();
  });
  await expect(page.locator('[data-step1-services-feedback]')).toContainText('Tipo ligado agregado.');
  return result;
}

async function deleteAssociated(page, asociadoId) {
  const result = await runJsonAction(page, '/deskapp/tramitesn/services/delete', async () => {
    await page.locator(`[data-step1-associated-delete="${asociadoId}"]`).click();
  });
  await expect(page.locator('[data-step1-services-feedback]')).toContainText('Tipo asociado eliminado.');
  return result;
}

async function savePrincipal(page, tipoId) {
  await page.locator('[data-step1-service-input="principal_tipo_id"]').selectOption(String(tipoId));
  const result = await runJsonAction(page, '/deskapp/tramitesn/principal/update_tipo', async () => {
    await page.locator('[data-step1-principal-save]').click();
  });
  await expect(page.locator('[data-step1-services-feedback]')).toContainText('Tipo principal actualizado.');
  return result;
}

async function saveBaseData(page, observacionesValue) {
  await page.locator('[data-step1-input="observaciones"]').fill(observacionesValue);
  const result = await runJsonAction(page, '/deskapp/tramitesn/update_save/', async () => {
    await page.locator('[data-step1-save]').click();
  });
  await expect(page.locator('[data-step1-feedback]')).toContainText('Guardado real completado.');
  return result;
}

async function readFreshState(context, baseUrl, tramiteId) {
  const freshPage = await context.newPage();
  try {
    await openStep1Page(freshPage, baseUrl, tramiteId);
    return await readStep1State(freshPage);
  } finally {
    await freshPage.close();
  }
}

test('Paso 1 guarda cambios reales, confirma persistencia y restaura el estado original', async ({ page, context, baseURL }) => {
  const baseUrl = (getEnv('SGL_BASE_URL') || baseURL || 'http://admin-sgl').replace(/\/$/, '');
  const tramiteId = getEnv('SGL_TRAMITE_STEP1_ID') || '12460';

  await openStep1Page(page, baseUrl, tramiteId);
  const initialState = await readStep1State(page);
  const originalState = comparableState(initialState);

  if (!initialState.canEdit || /solo lectura/i.test(initialState.badgeText)) {
    test.skip(true, `La sesion actual no puede editar el Paso 1 del tramite ${tramiteId}. Define SGL_USERNAME y SGL_PASSWORD con permisos de escritura para este expediente.`);
  }

  expect(initialState.badgeText).toContain(`Tramite ${tramiteId}`);
  expect(initialState.badgeText).toContain('Editable');
  expect(initialState.sourceCopy).toContain(`tramite ${tramiteId}`);
  expect(initialState.sourceCopy.toLowerCase()).toContain('editable');

  const editableAssociated = initialState.rows.find((row) => row.note === 'Asociado editable' && row.selectValue !== '');
  if (!editableAssociated) {
    test.skip(true, 'El tramite no expone tipos ligados editables para validar Paso 1.');
  }

  const initialUsedTypes = new Set(
    initialState.rows
      .map((row) => Number(row.selectValue || 0))
      .filter((value) => value > 0)
      .concat(Number(initialState.principalValue || 0))
  );

  const associatedAltType = pickCandidateType(
    initialState.addOptions,
    new Set([Number(initialState.principalValue || 0), Number(editableAssociated.selectValue || 0)]),
    [getEnv('SGL_STEP1_ASSOCIATED_ALT_ID') || '13', '7', '18']
  );

  if (!associatedAltType) {
    test.skip(true, 'No hay tipo alterno disponible para validar la edicion de ligado en Paso 1.');
  }

  const addType = pickCandidateType(
    initialState.addOptions,
    new Set([...initialUsedTypes, associatedAltType]),
    [getEnv('SGL_STEP1_ADD_TYPE_ID') || '18', '13', '7']
  );

  if (!addType) {
    test.skip(true, 'No hay tipo disponible para validar el alta de ligado en Paso 1.');
  }

  const principalAltType = pickCandidateType(
    initialState.addOptions,
    new Set([Number(initialState.principalValue || 0)]),
    [getEnv('SGL_STEP1_PRINCIPAL_ALT_ID') || '13', '10']
  );

  if (!principalAltType) {
    test.skip(true, 'No hay tipo alterno disponible para validar el cambio de principal en Paso 1.');
  }

  let currentAssociatedType = Number(editableAssociated.selectValue || 0);
  let currentPrincipalType = Number(initialState.principalValue || 0);
  let currentObservaciones = String(initialState.observacionesValue || '');
  let addedAssociatedId = 0;

  try {
    const updatedObservaciones = [currentObservaciones, '[pw-step1-csrf-check]'].filter(Boolean).join(' ').trim();
    const baseSave = await saveBaseData(page, updatedObservaciones);
    expect(baseSave.status).toBe(200);
    expect(baseSave.json?.success).toBe(true);
    currentObservaciones = updatedObservaciones;

    const afterBaseSave = await readStep1State(page);
    expect(afterBaseSave.observacionesValue).toBe(updatedObservaciones);

    const freshAfterBaseSave = await readFreshState(context, baseUrl, tramiteId);
    expect(freshAfterBaseSave.observacionesValue).toBe(updatedObservaciones);

    const associatedUpdate = await updateAssociated(page, editableAssociated.asociadoId, associatedAltType);
    expect(associatedUpdate.status).toBe(200);
    expect(associatedUpdate.json?.status).toBe('success');
    currentAssociatedType = associatedAltType;

    const afterAssociatedUpdate = await readStep1State(page);
    expect(findAssociatedRow(afterAssociatedUpdate, editableAssociated.asociadoId)?.selectValue).toBe(String(associatedAltType));

    const freshAfterAssociatedUpdate = await readFreshState(context, baseUrl, tramiteId);
    expect(findAssociatedRow(freshAfterAssociatedUpdate, editableAssociated.asociadoId)?.selectValue).toBe(String(associatedAltType));

    const associatedRestore = await updateAssociated(page, editableAssociated.asociadoId, Number(editableAssociated.selectValue || 0));
    expect(associatedRestore.status).toBe(200);
    expect(associatedRestore.json?.status).toBe('success');
    currentAssociatedType = Number(editableAssociated.selectValue || 0);

    const freshAfterAssociatedRestore = await readFreshState(context, baseUrl, tramiteId);
    expect(findAssociatedRow(freshAfterAssociatedRestore, editableAssociated.asociadoId)?.selectValue).toBe(String(editableAssociated.selectValue));

    const added = await addAssociated(page, addType);
    expect(added.status).toBe(200);
    expect(added.json?.status).toBe('success');
    addedAssociatedId = Number(added.json?.asociado_id || 0);
    expect(addedAssociatedId).toBeGreaterThan(0);

    const afterAdd = await readStep1State(page);
    expect(findAssociatedRow(afterAdd, addedAssociatedId)?.selectValue).toBe(String(addType));

    const freshAfterAdd = await readFreshState(context, baseUrl, tramiteId);
    expect(findAssociatedRow(freshAfterAdd, addedAssociatedId)?.selectValue).toBe(String(addType));

    const deleted = await deleteAssociated(page, addedAssociatedId);
    expect(deleted.status).toBe(200);
    expect(deleted.json?.status).toBe('success');
    addedAssociatedId = 0;

    const afterDelete = await readStep1State(page);
    expect(findAssociatedRow(afterDelete, Number(added.json?.asociado_id || 0))).toBeUndefined();

    const freshAfterDelete = await readFreshState(context, baseUrl, tramiteId);
    expect(findAssociatedRow(freshAfterDelete, Number(added.json?.asociado_id || 0))).toBeUndefined();

    const principalUpdate = await savePrincipal(page, principalAltType);
    expect(principalUpdate.status).toBe(200);
    expect(principalUpdate.json?.status).toBe('success');
    currentPrincipalType = principalAltType;

    const afterPrincipalUpdate = await readStep1State(page);
    expect(afterPrincipalUpdate.principalValue).toBe(String(principalAltType));

    const freshAfterPrincipalUpdate = await readFreshState(context, baseUrl, tramiteId);
    expect(freshAfterPrincipalUpdate.principalValue).toBe(String(principalAltType));

    const principalRestore = await savePrincipal(page, Number(initialState.principalValue || 0));
    expect(principalRestore.status).toBe(200);
    expect(principalRestore.json?.status).toBe('success');
    currentPrincipalType = Number(initialState.principalValue || 0);
  } finally {
    if (currentObservaciones !== String(initialState.observacionesValue || '')) {
      await saveBaseData(page, String(initialState.observacionesValue || '')).catch(() => null);
      currentObservaciones = String(initialState.observacionesValue || '');
    }

    if (addedAssociatedId > 0) {
      const currentState = await readStep1State(page).catch(() => null);
      if (currentState && findAssociatedRow(currentState, addedAssociatedId)) {
        await deleteAssociated(page, addedAssociatedId).catch(() => null);
      }
      addedAssociatedId = 0;
    }

    if (currentAssociatedType !== Number(editableAssociated.selectValue || 0)) {
      const currentState = await readStep1State(page).catch(() => null);
      if (currentState && findAssociatedRow(currentState, editableAssociated.asociadoId)) {
        await updateAssociated(page, editableAssociated.asociadoId, Number(editableAssociated.selectValue || 0)).catch(() => null);
      }
      currentAssociatedType = Number(editableAssociated.selectValue || 0);
    }

    if (currentPrincipalType !== Number(initialState.principalValue || 0)) {
      await savePrincipal(page, Number(initialState.principalValue || 0)).catch(() => null);
      currentPrincipalType = Number(initialState.principalValue || 0);
    }
  }

  const finalFreshState = await readFreshState(context, baseUrl, tramiteId);
  expect(comparableState(finalFreshState)).toEqual(originalState);
  expect(finalFreshState.badgeText).toContain(`Tramite ${tramiteId}`);
  expect(finalFreshState.badgeText).toContain('Editable');
  expect(finalFreshState.sourceCopy).toContain(`tramite ${tramiteId}`);
  expect(finalFreshState.sourceCopy.toLowerCase()).toContain('editable');
});