# Pruebas de navegador

Smoke test actual:

- `tests/browser/php82-smoke.spec.js`: inicia sesion visual si hace falta y valida que carguen sin fatales Dashboard, Buscar Trámite, Wizard y Cobranza en el runtime web.
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
- `npm run test:browser:php82-smoke`
- `npm run test:browser:cobranza`
- `SGL_BASE_URL=http://admin-sgl SGL_USERNAME=tu_usuario SGL_PASSWORD=tu_password npm run test:browser:cobranza`

Atajo sugerido para el smoke de PHP 8.2 con el usuario demo del repo:

- `DOCKER_APP_PORT=18080 docker compose up -d app`
- `docker compose exec -T app php spark sgl:demo-data --count=4`
- `SGL_BASE_URL=http://localhost:18080 SGL_USERNAME=luisa.flores SGL_PASSWORD=Demo1234! npm run test:browser:php82-smoke`