# Pruebas de navegador

Smoke test actual:

- `tests/browser/cobranza-save.spec.js`: abre Cobranza, autentica si se proporcionan credenciales, localiza el primer expediente con formulario editable de Cobro Cliente, guarda ajustes desde la UI y luego restaura los valores originales.

Variables esperadas:

- `SGL_BASE_URL`: URL base local. Por defecto usa `http://admin-sgl`.
- `SGL_USERNAME`: usuario para login visual si no hay sesion activa.
- `SGL_PASSWORD`: password para login visual si no hay sesion activa.
- `SGL_BROWSER_TEST_SUFFIX`: sufijo opcional para distinguir la captura de prueba.
- `SGL_RESTORE_ORIGINAL`: si vale `0`, no restaura el estado original al terminar.

Comandos:

- `npm install`
- `npx playwright install chromium`
- `npm run test:browser:cobranza`
- `SGL_BASE_URL=http://admin-sgl SGL_USERNAME=tu_usuario SGL_PASSWORD=tu_password npm run test:browser:cobranza`