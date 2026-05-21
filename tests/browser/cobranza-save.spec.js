const { test, expect } = require('@playwright/test');

function getEnv(name) {
  return (process.env[name] || '').trim();
}

async function maybeLogin(page) {
  const loginForm = page.locator('#loginForm');
  if (!(await loginForm.isVisible().catch(() => false))) {
    return;
  }

  const username = getEnv('SGL_USERNAME');
  const password = getEnv('SGL_PASSWORD');

  if (!username || !password) {
    throw new Error('No hay sesion activa. Define SGL_USERNAME y SGL_PASSWORD o inicia sesion manualmente antes de correr la spec.');
  }

  await page.locator('input[name="username"]').fill(username);
  await page.locator('input[name="password"]').fill(password);

  await Promise.all([
    page.waitForURL(/deskapp\/(dashboard|cobranza)/, { timeout: 20000 }),
    page.locator('#submitBtn').click(),
  ]);
}

async function openEditableCobranzaCase(page, baseUrl) {
  await page.goto(baseUrl + '/deskapp/cobranza', { waitUntil: 'domcontentloaded' });
  await maybeLogin(page);

  await expect(page.locator('.cobranza-list')).toBeVisible();

  const caseLinks = page.locator('[data-cobranza-detail-link]');
  const totalCases = await caseLinks.count();
  if (totalCases === 0) {
    test.skip(true, 'No hay expedientes visibles en Cobranza para esta sesion.');
  }

  for (let index = 0; index < totalCases; index += 1) {
    const caseLink = caseLinks.nth(index);

    await Promise.all([
      page.waitForResponse((response) => {
        return response.request().method() === 'GET'
          && response.url().includes('/deskapp/cobranza/expediente/');
      }, { timeout: 30000 }).catch(() => null),
      caseLink.click(),
    ]);

    const form = page.locator('form[data-cobranza-ajax-form]').filter({
      has: page.locator('button[type="submit"]:has-text("Guardar ajustes")'),
    }).first();

    if (await form.isVisible().catch(() => false)) {
      return form;
    }
  }

  test.skip(true, 'Ningun expediente visible expone el formulario editable de Cobro Cliente.');
}

async function captureCurrentValues(form) {
  return {
    idGiveCliente: await form.locator('#id-give-cliente').inputValue(),
    numeroFactura: await form.locator('#numero-factura').inputValue(),
    numeroRefactura: await form.locator('#numero-refactura').inputValue(),
    evidenciaCobroTxt: await form.locator('#evidencia-cobro-txt').inputValue(),
  };
}

async function submitAndAssert(page, form, values) {
  await form.locator('#id-give-cliente').fill(values.idGiveCliente);
  await form.locator('#numero-factura').fill(values.numeroFactura);
  await form.locator('#numero-refactura').fill(values.numeroRefactura);
  await form.locator('#evidencia-cobro-txt').fill(values.evidenciaCobroTxt);

  await Promise.all([
    page.waitForResponse((response) => {
      return response.request().method() === 'POST'
        && response.url().includes('/deskapp/tramitesn/update_final_save/');
    }, { timeout: 30000 }),
    form.locator('button[type="submit"]:has-text("Guardar ajustes")').click(),
  ]);

  const feedback = page.locator('[data-cobranza-detail-feedback]');
  await expect(feedback).toHaveClass(/is-success/);
  await expect(feedback).toContainText(/guardado|cambio/i);
  await expect(page.locator('#id-give-cliente')).toHaveValue(values.idGiveCliente);
  await expect(page.locator('#numero-factura')).toHaveValue(values.numeroFactura);
  await expect(page.locator('#numero-refactura')).toHaveValue(values.numeroRefactura);
  await expect(page.locator('#evidencia-cobro-txt')).toHaveValue(values.evidenciaCobroTxt);
}

test('guarda ajustes de cobro cliente desde la UI y restaura el estado original', async ({ page, baseURL }) => {
  const baseUrl = (getEnv('SGL_BASE_URL') || baseURL || 'http://admin-sgl').replace(/\/$/, '');
  const form = await openEditableCobranzaCase(page, baseUrl);

  await expect(page.locator('#cobranza-detalle')).toBeVisible();
  await expect(page.locator('h3:has-text("Ajustes de cobro cliente")')).toBeVisible();

  const original = await captureCurrentValues(form);
  const suffix = getEnv('SGL_BROWSER_TEST_SUFFIX') || ('PW' + Date.now());
  const updated = {
    idGiveCliente: (original.idGiveCliente || 'AUTO-GIVE') + '-' + suffix,
    numeroFactura: (original.numeroFactura || 'AUTO-FAC') + '-' + suffix,
    numeroRefactura: (original.numeroRefactura || 'AUTO-REF') + '-' + suffix,
    evidenciaCobroTxt: 'Smoke browser save ' + suffix,
  };

  try {
    await submitAndAssert(page, form, updated);
  } finally {
    if (getEnv('SGL_RESTORE_ORIGINAL') !== '0') {
      const refreshedForm = page.locator('form[data-cobranza-ajax-form]').filter({
        has: page.locator('button[type="submit"]:has-text("Guardar ajustes")'),
      }).first();
      await submitAndAssert(page, refreshedForm, original);
    }
  }
});