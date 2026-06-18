# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: tramites-step1-save.spec.js >> Paso 1 guarda cambios reales, confirma persistencia y restaura el estado original
- Location: tests/browser/tramites-step1-save.spec.js:196:1

# Error details

```
Error: No hay sesion activa. Define SGL_USERNAME y SGL_PASSWORD o inicia sesion manualmente antes de correr la spec.
```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - link "SASGL" [ref=e5] [cursor=pointer]:
    - /url: login
    - img "SASGL" [ref=e6]
  - generic [ref=e9]:
    - img "Login Illustration" [ref=e11]
    - generic [ref=e12]:
      - generic [ref=e13]:
        - generic [ref=e14]:
          - generic [ref=e15]:
            - generic [ref=e16]: 
            - text: Plataforma SGL
          - heading "Bienvenido" [level=2] [ref=e17]
          - paragraph [ref=e18]: Ingresa tus credenciales para continuar
        - generic [ref=e19]:
          - generic [ref=e20]:
            - textbox "Usuario" [ref=e21]
            - generic:
              - generic:
                - generic: 
          - generic [ref=e22]:
            - textbox "Contraseña" [ref=e23]
            - generic:
              - generic:
                - generic: 
            - button "" [ref=e24] [cursor=pointer]:
              - generic [ref=e25]: 
          - button " Iniciar Sesión" [ref=e29] [cursor=pointer]:
            - generic [ref=e30]: 
            - text: Iniciar Sesión
      - paragraph [ref=e32]: © 2026 SGL - Sistema de Gestión de Trámites
```

# Test source

```ts
  1   | const { test, expect } = require('@playwright/test');
  2   | 
  3   | function getEnv(name) {
  4   |   return (process.env[name] || '').trim();
  5   | }
  6   | 
  7   | async function maybeLogin(page) {
  8   |   const loginForm = page.locator('#loginForm');
  9   |   if (!(await loginForm.isVisible().catch(() => false))) {
  10  |     return false;
  11  |   }
  12  | 
  13  |   const username = getEnv('SGL_USERNAME');
  14  |   const password = getEnv('SGL_PASSWORD');
  15  | 
  16  |   if (!username || !password) {
> 17  |     throw new Error('No hay sesion activa. Define SGL_USERNAME y SGL_PASSWORD o inicia sesion manualmente antes de correr la spec.');
      |           ^ Error: No hay sesion activa. Define SGL_USERNAME y SGL_PASSWORD o inicia sesion manualmente antes de correr la spec.
  18  |   }
  19  | 
  20  |   await page.locator('input[name="username"]').fill(username);
  21  |   await page.locator('input[name="password"]').fill(password);
  22  | 
  23  |   await Promise.all([
  24  |     page.waitForURL(/deskapp\//, { timeout: 20000, waitUntil: 'domcontentloaded' }),
  25  |     page.locator('#submitBtn').click(),
  26  |   ]);
  27  | 
  28  |   return true;
  29  | }
  30  | 
  31  | async function ensureAuthenticatedPage(page, baseUrl, path) {
  32  |   const response = await page.goto(baseUrl + path, { waitUntil: 'domcontentloaded' });
  33  |   const loggedIn = await maybeLogin(page);
  34  | 
  35  |   if (loggedIn && !page.url().includes(path)) {
  36  |     return page.goto(baseUrl + path, { waitUntil: 'domcontentloaded' });
  37  |   }
  38  | 
  39  |   return response;
  40  | }
  41  | 
  42  | async function openStep1Page(page, baseUrl, tramiteId) {
  43  |   const path = `/deskapp/tramitesn/prototipo-layout/paso-1?tramite_id=${tramiteId}`;
  44  |   const response = await ensureAuthenticatedPage(page, baseUrl, path);
  45  |   expect(response).not.toBeNull();
  46  |   expect(response.status()).toBeLessThan(500);
  47  | 
  48  |   await expect(page.locator('[data-step1-service-input="principal_tipo_id"]').first()).toBeVisible();
  49  |   await expect(page.locator('[data-step1-services-list]').first()).toBeVisible();
  50  | 
  51  |   return path;
  52  | }
  53  | 
  54  | async function readStep1State(page) {
  55  |   return page.evaluate(() => {
  56  |     const rows = Array.from(document.querySelectorAll('[data-step1-services-list] .tp-assoc-item')).map((node) => ({
  57  |       asociadoId: Number(node.getAttribute('data-step1-asociado-id') || '0'),
  58  |       label: (node.querySelector('strong')?.textContent || '').trim(),
  59  |       note: (node.querySelector('small')?.textContent || '').trim(),
  60  |       selectValue: (node.querySelector('[data-step1-associated-select]')?.value || '').trim(),
  61  |     }));
  62  | 
  63  |     const addOptions = Array.from(document.querySelectorAll('[data-step1-service-input="add_tipo_id"] option'))
  64  |       .map((option) => ({
  65  |         value: String(option.value || '').trim(),
  66  |         label: (option.textContent || '').trim(),
  67  |       }))
  68  |       .filter((option) => option.value !== '');
  69  | 
  70  |     const feedbackNode = document.querySelector('[data-step1-services-feedback]');
  71  | 
  72  |     return {
  73  |       badgeText: (document.querySelector('.tp-badge')?.textContent || '').trim(),
  74  |       sourceCopy: Array.from(document.querySelectorAll('.tp-mini-copy'))
  75  |         .map((node) => (node.textContent || '').trim())
  76  |         .find((text) => text.includes('Este prototipo toma datos')) || '',
  77  |       canEdit:
  78  |         !!document.querySelector('[data-step1-principal-save]')
  79  |         && !!document.querySelector('[data-step1-associated-add]')
  80  |         && !document.querySelector('[data-step1-service-input="principal_tipo_id"]')?.hasAttribute('disabled'),
  81  |       principalValue: (document.querySelector('[data-step1-service-input="principal_tipo_id"]')?.value || '').trim(),
  82  |       principalLabel: (document.querySelector('[data-step1-detail-value="principal"]')?.textContent || '').trim(),
  83  |       ligadosLabel: (document.querySelector('[data-step1-detail-value="ligados"]')?.textContent || '').trim(),
  84  |       observacionesValue: (document.querySelector('[data-step1-input="observaciones"]')?.value || '').trim(),
  85  |       feedbackText: (feedbackNode?.textContent || '').trim(),
  86  |       rows,
  87  |       addOptions,
  88  |     };
  89  |   });
  90  | }
  91  | 
  92  | function comparableState(state) {
  93  |   return {
  94  |     principalValue: String(state.principalValue || ''),
  95  |     rows: state.rows.map((row) => ({
  96  |       asociadoId: Number(row.asociadoId || 0),
  97  |       label: String(row.label || ''),
  98  |       note: String(row.note || ''),
  99  |       selectValue: String(row.selectValue || ''),
  100 |     })),
  101 |   };
  102 | }
  103 | 
  104 | function findAssociatedRow(state, asociadoId) {
  105 |   return state.rows.find((row) => Number(row.asociadoId || 0) === Number(asociadoId || 0));
  106 | }
  107 | 
  108 | function pickCandidateType(options, excludedValues, preferredValues) {
  109 |   const optionValues = options.map((option) => Number(option.value || 0)).filter((value) => value > 0);
  110 |   const preferred = preferredValues.map((value) => Number(value || 0)).filter((value) => value > 0);
  111 | 
  112 |   for (const value of preferred) {
  113 |     if (optionValues.includes(value) && !excludedValues.has(value)) {
  114 |       return value;
  115 |     }
  116 |   }
  117 | 
```