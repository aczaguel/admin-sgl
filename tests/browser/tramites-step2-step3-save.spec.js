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

async function openPrototypeStep(page, baseUrl, step, tramiteId) {
  const path = `/deskapp/tramitesn/prototipo-layout/paso-${step}?tramite_id=${tramiteId}`;
  const response = await ensureAuthenticatedPage(page, baseUrl, path);
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(500);
  return path;
}

async function getEmbeddedConfig(page, configName) {
  return page.evaluate((name) => {
    const marker = `const ${name} = `;
    for (const script of Array.from(document.scripts)) {
      const content = script.textContent || '';
      const start = content.indexOf(marker);
      if (start === -1) {
        continue;
      }
      const raw = content.slice(start + marker.length);
      const end = raw.indexOf(';');
      if (end === -1) {
        continue;
      }
      const objectLiteral = raw.slice(0, end).trim();
      try {
        return Function(`return (${objectLiteral});`)();
      } catch (error) {
      }
    }
    return null;
  }, configName);
}

async function readStep2State(page) {
  return page.evaluate(() => {
    const valueOf = (name) => (document.querySelector(`[data-step2-input="${name}"]`)?.value || '').trim();
    const textOf = (selector) => (document.querySelector(selector)?.textContent || '').trim();
    const approvalActions = document.querySelector('[data-step2-approval-actions]');

    return {
      canEdit:
        !!document.querySelector('[data-step2-save]')
        && !document.querySelector('[data-step2-input="derechos_refer_banc"]')?.hasAttribute('disabled'),
      values: {
        empresa_gestora_id: valueOf('empresa_gestora_id'),
        gestor_id: valueOf('gestor_id'),
        derechos_tramite: valueOf('derechos_tramite'),
        derechos_pago_sitio: valueOf('derechos_pago_sitio'),
        derechos_vigencia: valueOf('derechos_vigencia'),
        derechos_revol_cliente: valueOf('derechos_revol_cliente'),
        derechos_refer_banc: valueOf('derechos_refer_banc'),
      },
      feedbackText: textOf('[data-step2-feedback]'),
      approvalTitle: textOf('[data-operational-step2-title]'),
      approvalCopy: textOf('[data-operational-step2-copy]'),
      approvalMissing: Array.from(document.querySelectorAll('.tp-approval-missing li')).map((node) => (node.textContent || '').trim()),
      hasApproveAction: !!approvalActions?.querySelector('[data-operational-approve="1"]'),
      cards: {
        asignacion: textOf('[data-step2-card-value="asignacion"]'),
        pago_derechos: textOf('[data-step2-card-value="pago_derechos"]'),
        vigencia_referencia: textOf('[data-step2-card-value="vigencia_referencia"]'),
      },
    };
  });
}

async function readStep3State(page) {
  return page.evaluate(() => ({
    canUpload:
      !!document.querySelector('[data-step3-upload]')
      && !document.querySelector('[data-step3-input="comprobante_final"]')?.hasAttribute('disabled'),
    feedbackText: (document.querySelector('[data-step3-feedback]')?.textContent || '').trim(),
    gateTitle: (document.querySelector('[data-step3-gate-title]')?.textContent || '').trim(),
    gateCopy: (document.querySelector('[data-step3-gate-copy]')?.textContent || '').trim(),
    note: (document.querySelector('[data-step3-evidence-note]')?.textContent || '').trim(),
    tail: (document.querySelector('[data-operational-step3-tail]')?.textContent || '').trim(),
    chips: Array.from(document.querySelectorAll('[data-step3-chip]')).map((node) => ({
      key: node.getAttribute('data-step3-chip') || '',
      label: (node.textContent || '').trim(),
      success: node.classList.contains('is-success'),
    })),
    gallery: Array.from(document.querySelectorAll('[data-step3-gallery] a, [data-step3-gallery] .tp-gallery-item')).map((node) => ({
      text: (node.textContent || '').trim(),
      href: node.getAttribute('href') || '',
    })),
  }));
}

function normalizeDocs(docs) {
  return (docs || [])
    .map((doc) => ({
      file: String(doc.file || ''),
      comprobante_final: String(doc.comprobante_final || ''),
    }))
    .sort((left, right) => {
      const leftKey = `${left.comprobante_final}:${left.file}`;
      const rightKey = `${right.comprobante_final}:${right.file}`;
      return leftKey.localeCompare(rightKey);
    });
}

async function saveStep2(page) {
  const gestorResponsePromise = page.waitForResponse((response) => {
    return response.request().method() === 'POST' && response.url().includes('/deskapp/tramitesn/update_gestor_save/');
  }, { timeout: 30000 });

  const derechosResponsePromise = page.waitForResponse((response) => {
    return response.request().method() === 'POST' && response.url().includes('/deskapp/tramitesn/update_derechos_save/');
  }, { timeout: 30000 });

  await page.locator('[data-step2-save]').click();

  const gestorResponse = await gestorResponsePromise;
  const derechosResponse = await derechosResponsePromise;

  return {
    gestor: {
      status: gestorResponse.status(),
      json: await gestorResponse.json().catch(() => null),
    },
    derechos: {
      status: derechosResponse.status(),
      json: await derechosResponse.json().catch(() => null),
    },
  };
}

async function uploadStep3Evidence(page, type, fileName, content) {
  await page.locator('[data-step3-input="comprobante_final"]').selectOption(type);
  await page.locator('[data-step3-file]').setInputFiles({
    name: fileName,
    mimeType: 'text/plain',
    buffer: Buffer.from(content, 'utf8'),
  });

  const responsePromise = page.waitForResponse((response) => {
    return response.request().method() === 'POST' && response.url().includes('/deskapp/tramitesn/upload_pago_gestor/');
  }, { timeout: 30000 });

  await page.locator('[data-step3-upload]').click();
  const response = await responsePromise;
  await expect(page.locator('[data-step3-feedback]')).toContainText(/subida|correctamente/i);

  return {
    status: response.status(),
    json: await response.json().catch(() => null),
  };
}

async function deletePagoGestorFile(page, baseUrl, step3Config, fileName) {
  return page.evaluate(async ({ deleteUrl, csrfName, csrfHash, tramiteId, file }) => {
    const payload = new URLSearchParams();
    payload.set('tramite_id', String(tramiteId));
    payload.set('file', String(file));
    if (csrfName && csrfHash) {
      payload.set(String(csrfName), String(csrfHash));
    }

    const response = await fetch(deleteUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: payload.toString(),
    });

    let json = null;
    try {
      json = await response.json();
    } catch (error) {
      json = null;
    }

    return {
      status: response.status,
      json,
    };
  }, {
    deleteUrl: `${baseUrl}/deskapp/tramitesn/delete_pago_gestor`,
    csrfName: step3Config?.csrfName || '',
    csrfHash: step3Config?.csrfHash || '',
    tramiteId: step3Config?.tramiteId || 0,
    file: fileName,
  });
}

async function readFreshStep2State(context, baseUrl, tramiteId) {
  const freshPage = await context.newPage();
  try {
    await openPrototypeStep(freshPage, baseUrl, 2, tramiteId);
    return await readStep2State(freshPage);
  } finally {
    await freshPage.close();
  }
}

async function readFreshStep3Snapshot(context, baseUrl, tramiteId) {
  const freshPage = await context.newPage();
  try {
    await openPrototypeStep(freshPage, baseUrl, 3, tramiteId);
    return {
      state: await readStep3State(freshPage),
      config: await getEmbeddedConfig(freshPage, 'step3FormConfig'),
    };
  } finally {
    await freshPage.close();
  }
}

test('Paso 2 y Paso 3 guardan, rehidratan, habilitan gate y restauran el estado original', async ({ page, context, baseURL }) => {
  test.setTimeout(300000);
  const baseUrl = (getEnv('SGL_BASE_URL') || baseURL || 'http://admin-sgl').replace(/\/$/, '');
  const tramiteId = getEnv('SGL_TRAMITE_STEP23_ID') || '12460';
  const suffix = String(Date.now());

  await openPrototypeStep(page, baseUrl, 2, tramiteId);
  const initialStep2 = await readStep2State(page);

  if (!initialStep2.canEdit) {
    test.skip(true, `La sesion actual no puede editar el Paso 2 del tramite ${tramiteId}.`);
  }

  const originalValues = { ...initialStep2.values };
  const originalFreshStep3 = await readFreshStep3Snapshot(context, baseUrl, tramiteId);
  const originalDocs = normalizeDocs(originalFreshStep3.config?.docs || []);
  const originalHasAcuse = originalDocs.some((doc) => doc.comprobante_final === 'acuse_recibo_cliente');

  let step2ReferenceRestored = true;
  const uploadedFiles = [];

  try {
    await page.locator('[data-step2-input="derechos_refer_banc"]').fill('');
    step2ReferenceRestored = false;
    const blankSave = await saveStep2(page);
    expect(blankSave.gestor.status).toBe(200);
    expect(blankSave.derechos.status).toBe(200);
    expect(initialStep2.values.empresa_gestora_id === '' || blankSave.gestor.json?.status === 'success' || blankSave.gestor.json?.success !== false).toBeTruthy();
    expect(blankSave.derechos.json?.status === 'success' || blankSave.derechos.json?.success !== false).toBeTruthy();

    const afterBlank = await readStep2State(page);
    expect(afterBlank.values.derechos_refer_banc).toBe('');
    expect(afterBlank.cards.pago_derechos).toMatch(/faltan obligatorios/i);
    expect(afterBlank.approvalTitle).toMatch(/falta aprobacion/i);

    const freshAfterBlank = await readFreshStep2State(context, baseUrl, tramiteId);
    expect(freshAfterBlank.values.derechos_refer_banc).toBe('');
    expect(freshAfterBlank.approvalTitle).toMatch(/falta aprobacion/i);

    await page.locator('[data-step2-input="derechos_refer_banc"]').fill(originalValues.derechos_refer_banc || ('AUTO-REF-' + suffix));
    const restoreSave = await saveStep2(page);
    expect(restoreSave.gestor.status).toBe(200);
    expect(restoreSave.derechos.status).toBe(200);
    expect(restoreSave.derechos.json?.status === 'success' || restoreSave.derechos.json?.success !== false).toBeTruthy();
    step2ReferenceRestored = true;

    const afterRestore = await readStep2State(page);
    expect(afterRestore.values.derechos_refer_banc).toBe(originalValues.derechos_refer_banc || ('AUTO-REF-' + suffix));
    expect(afterRestore.cards.pago_derechos).toMatch(/campos completos/i);

    const freshAfterRestore = await readFreshStep2State(context, baseUrl, tramiteId);
    expect(freshAfterRestore.values.derechos_refer_banc).toBe(originalValues.derechos_refer_banc || ('AUTO-REF-' + suffix));

    if (afterRestore.hasApproveAction) {
      await page.locator('[data-operational-approve="1"]').click();
      await expect(page).toHaveURL(new RegExp(`/deskapp/tramitesn/prototipo-layout/paso-3\?tramite_id=${tramiteId}$`));
      await expect(page.locator('[data-operational-step3-tail]')).toContainText(/Paso 2 aprobado en esta sesion/i);
    } else {
      await openPrototypeStep(page, baseUrl, 3, tramiteId);
    }

    const step3Config = await getEmbeddedConfig(page, 'step3FormConfig');
    const initialStep3 = await readStep3State(page);
    if (!initialStep3.canUpload || !step3Config) {
      test.skip(true, `La sesion actual no puede subir evidencias finales para el tramite ${tramiteId}.`);
    }

    const firstUpload = await uploadStep3Evidence(
      page,
      'tramite_recibido',
      `tramite-recibido-${suffix}.txt`,
      `tramite recibido ${suffix}`
    );
    expect(firstUpload.status).toBe(200);
    expect(firstUpload.json?.success).toBe(true);
    uploadedFiles.push(firstUpload.json?.fileName || '');

    const afterFirstUpload = await readStep3State(page);
    expect(afterFirstUpload.chips.find((chip) => chip.key === 'tramite_recibido')?.success).toBe(true);
    expect(afterFirstUpload.gateTitle).toMatch(originalHasAcuse ? /ya puede abrirse/i : /sigue bloqueado/i);

    const secondUpload = await uploadStep3Evidence(
      page,
      'acuse_recibo_cliente',
      `acuse-recibo-${suffix}.txt`,
      `acuse recibo ${suffix}`
    );
    expect(secondUpload.status).toBe(200);
    expect(secondUpload.json?.success).toBe(true);
    uploadedFiles.push(secondUpload.json?.fileName || '');

    const afterSecondUpload = await readStep3State(page);
    expect(afterSecondUpload.chips.find((chip) => chip.key === 'tramite_recibido')?.success).toBe(true);
    expect(afterSecondUpload.chips.find((chip) => chip.key === 'acuse_recibo_cliente')?.success).toBe(true);
    expect(afterSecondUpload.gateTitle).toMatch(/ya puede abrirse/i);

    const freshAfterUploads = await readFreshStep3Snapshot(context, baseUrl, tramiteId);
    expect(freshAfterUploads.state.gateTitle).toMatch(/ya puede abrirse/i);
    expect(normalizeDocs(freshAfterUploads.config?.docs || [])).toEqual(normalizeDocs([
      ...originalDocs,
      { file: firstUpload.json?.fileName || '', comprobante_final: 'tramite_recibido' },
      { file: secondUpload.json?.fileName || '', comprobante_final: 'acuse_recibo_cliente' },
    ]));

    const resetApprovalLink = page.locator('[data-operational-reset-approval="1"]').first();
    if (await resetApprovalLink.isVisible().catch(() => false)) {
      await resetApprovalLink.click();
      await expect(page.locator('[data-operational-step3-tail]')).toContainText(/Esperando aprobacion del Paso 2/i);
    }
  } finally {
    const currentStep3Config = await getEmbeddedConfig(page, 'step3FormConfig').catch(() => null);
    while (uploadedFiles.length > 0) {
      const fileName = uploadedFiles.pop();
      if (!fileName) {
        continue;
      }
      const deleteResult = await deletePagoGestorFile(page, baseUrl, currentStep3Config, fileName).catch(() => null);
      if (deleteResult && deleteResult.status >= 400) {
        throw new Error(`No se pudo eliminar el archivo temporal ${fileName}.`);
      }
    }

    if (!step2ReferenceRestored) {
      await openPrototypeStep(page, baseUrl, 2, tramiteId).catch(() => null);
      const currentStep2 = await readStep2State(page).catch(() => null);
      if (currentStep2 && currentStep2.canEdit) {
        await page.locator('[data-step2-input="derechos_refer_banc"]').fill(originalValues.derechos_refer_banc || '');
        await saveStep2(page).catch(() => null);
      }
      step2ReferenceRestored = true;
    }
  }

  const finalFreshStep2 = await readFreshStep2State(context, baseUrl, tramiteId);
  expect(finalFreshStep2.values.derechos_refer_banc).toBe(originalValues.derechos_refer_banc || '');

  const finalFreshStep3 = await readFreshStep3Snapshot(context, baseUrl, tramiteId);
  expect(normalizeDocs(finalFreshStep3.config?.docs || [])).toEqual(originalDocs);
});