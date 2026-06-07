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
    page.waitForURL(/deskapp\//, { timeout: 20000 }),
    page.locator('#submitBtn').click(),
  ]);

  return true;
}

async function ensureAuthenticatedPage(page, baseUrl, path) {
  const response = await page.goto(baseUrl + path, { waitUntil: 'domcontentloaded' });
  const loggedIn = await maybeLogin(page);

  if (loggedIn && !page.url().includes(path)) {
    await page.goto(baseUrl + path, { waitUntil: 'domcontentloaded' });
  }

  return response;
}

async function assertNoFatal(page) {
  await expect(page.locator('#loginForm')).toHaveCount(0);

  const bodyText = await page.locator('body').innerText();
  expect(bodyText).not.toMatch(/Fatal error|Uncaught|TypeError|ErrorException|Whoops|A PHP Error was encountered/i);
}

async function assertRouteHealthy(page, baseUrl, route, marker) {
  const response = await ensureAuthenticatedPage(page, baseUrl, route);
  expect(response).not.toBeNull();
  expect(response.status()).toBeLessThan(500);

  if (marker) {
    await expect(page.locator(marker).first()).toBeVisible();
  }

  await assertNoFatal(page);
}

async function maybeOpenAndCloseModal(page, triggerSelector, modalSelector) {
  const trigger = page.locator(triggerSelector).first();
  if (!(await trigger.count())) {
    return;
  }

  await trigger.click();
  const modal = page.locator(modalSelector).first();
  await expect(modal).toBeVisible();
  await modal.locator('[data-dismiss="modal"]').last().click();
  await expect(modal).toBeHidden();
}

async function maybeExpandCollapse(page, toggleSelector, panelSelector) {
  const toggle = page.locator(toggleSelector).first();
  if (!(await toggle.count())) {
    return;
  }

  await toggle.click();
  await expect(page.locator(panelSelector).first()).toHaveClass(/show/);
}

test('carga rutas core validadas en PHP 8.2', async ({ page, baseURL }) => {
  const baseUrl = (getEnv('SGL_BASE_URL') || baseURL || 'http://admin-sgl').replace(/\/$/, '');
  const tramiteOperativoId = getEnv('SGL_TRAMITE_OPERATIVO_ID') || '12426';

  page.on('dialog', async (dialog) => {
    await dialog.dismiss();
  });

  await assertRouteHealthy(
    page,
    baseUrl,
    '/deskapp/dashboard',
    '.select2-selection__rendered[title="Todos los clientes"]'
  );

  await assertRouteHealthy(
    page,
    baseUrl,
    '/deskapp/dashboardadmin',
    'text=Dashboard Admin'
  );

  await assertRouteHealthy(
    page,
    baseUrl,
    '/deskapp/dashboardadmin/financiero',
    'h4:has-text("Análisis Financiero")'
  );

  await assertRouteHealthy(
    page,
    baseUrl,
    '/deskapp/dashboardadmin/reportes',
    'h4:has-text("Reportes y Estadísticas")'
  );

  await assertRouteHealthy(
    page,
    baseUrl,
    '/deskapp/dashboardadmin/por_cliente',
    'h4:has-text("Trámites por Cliente")'
  );

  await assertRouteHealthy(
    page,
    baseUrl,
    '/deskapp/tramitesn/search',
    'h4:has-text("Buscar Trámite"), h5:has-text("Buscar Trámite")'
  );

  await assertRouteHealthy(
    page,
    baseUrl,
    '/deskapp/tramitesn/tramite',
    'h2:has-text("Trámites en Flujo Normal")'
  );

  const tramiteUpdateHref = await page.locator('a[href*="/deskapp/tramitesn/update/"]').first().getAttribute('href');
  expect(tramiteUpdateHref).toBeTruthy();
  const discoveredTramiteId = /\/update\/(\d+)/.exec(tramiteUpdateHref)?.[1] || tramiteOperativoId;

  await assertRouteHealthy(
    page,
    baseUrl,
    '/deskapp/tramitewizard',
    '#wizardForm'
  );
  await expect(page.locator('h2:has-text("Crear Nuevo Trámite")')).toBeVisible();

  await assertRouteHealthy(
    page,
    baseUrl,
    '/deskapp/cobranza',
    '.cobranza-shell'
  );

  const cobranzaDetailHref = await page.locator('[data-cobranza-detail-link]').first().getAttribute('href');
  expect(cobranzaDetailHref).toBeTruthy();
  const cobranzaTramiteId = await page.locator('[data-cobranza-detail-link]').first().getAttribute('data-tramite-id');
  expect(cobranzaTramiteId).toBeTruthy();

  const cobranzaDetailResponse = await page.goto(new URL(cobranzaDetailHref, baseUrl).toString(), {
    waitUntil: 'domcontentloaded',
  });
  expect(cobranzaDetailResponse).not.toBeNull();
  expect(cobranzaDetailResponse.status()).toBeLessThan(500);
  await expect(page.locator('.cobranza-detail-header')).toBeVisible();
  const cobranzaAuditLink = page.locator('a[href*="/deskapp/tramites/audit_timeline/"]').first();
  const hasCobranzaAuditLink = await cobranzaAuditLink.count();
  const cobranzaAuditHref = hasCobranzaAuditLink ? await cobranzaAuditLink.getAttribute('href') : null;
  await assertNoFatal(page);

  const tramiteUpdateResponse = await page.goto(new URL(tramiteUpdateHref, baseUrl).toString(), {
    waitUntil: 'domcontentloaded',
  });
  expect(tramiteUpdateResponse).not.toBeNull();
  expect(tramiteUpdateResponse.status()).toBeLessThan(500);
  expect(page.url()).toContain(`/deskapp/tramitesn/update/${discoveredTramiteId}`);
  await expect(page.locator('text=Detalle rápido').first()).toBeVisible();
  await maybeOpenAndCloseModal(page, 'button[data-target="#modal-documentos"]', '#modal-documentos');
  await assertNoFatal(page);

  const pagoGestorResponse = await ensureAuthenticatedPage(
    page,
    baseUrl,
    `/deskapp/tramitesn/ver_seccion_pago_gestor/${cobranzaTramiteId}`
  );
  expect(pagoGestorResponse).not.toBeNull();
  expect(pagoGestorResponse.status()).toBeLessThan(500);
  expect(page.url()).toMatch(new RegExp(`/deskapp/tramitesn/(ver_seccion_pago_gestor|update)/${cobranzaTramiteId}`));
  await expect(page.locator('text=Detalle rápido').first()).toBeVisible();
  await assertNoFatal(page);

  await assertRouteHealthy(
    page,
    baseUrl,
    `/deskapp/tramitesn/ver_seccion_cobro_cliente/${cobranzaTramiteId}`,
    '#miDropzoneCliente'
  );
  await maybeExpandCollapse(page, 'button[data-target="#collapsePaso5"]', '#collapsePaso5');
  await assertNoFatal(page);

  if (cobranzaAuditHref) {
    const auditTimelineResponse = await page.goto(new URL(cobranzaAuditHref, baseUrl).toString(), {
      waitUntil: 'domcontentloaded',
    });
    expect(auditTimelineResponse).not.toBeNull();
    expect(auditTimelineResponse.status()).toBeLessThan(500);
    expect(page.url()).toContain('/deskapp/tramites/audit_timeline/');
    await expect(page.locator('h5:has-text("Historial Completo de Cambios")').first()).toBeVisible();
    await assertNoFatal(page);
  }

  const menuRoutes = [
    '/deskapp/concluido/final',
    '/deskapp/proceso/final',
    '/deskapp/clientes/cliente',
    '/deskapp/clidirecto/clidirecto',
    '/deskapp/clidirecto/ejecutivo',
    '/deskapp/gestores/gestores',
    '/deskapp/roles/roles',
    '/deskapp/permisos/permisos',
    '/deskapp/users/users',
    '/bitacora/search',
  ];

  for (const route of menuRoutes) {
    await assertRouteHealthy(page, baseUrl, route);
  }
});