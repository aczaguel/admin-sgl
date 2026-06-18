# Pruebas de navegador

Smoke test actual:

- `tests/browser/php82-smoke.spec.js`: inicia sesion visual si hace falta y valida que carguen sin fatales Dashboard, Buscar Trámite, Wizard y Cobranza en el runtime web.
- `tests/browser/cobranza-save.spec.js`: abre Cobranza, autentica si se proporcionan credenciales, localiza el primer expediente con formulario editable de Cobro Cliente, guarda ajustes desde la UI y luego restaura los valores originales.
- `tests/browser/tramites-step1-save.spec.js`: abre el prototipo del Paso 1 para un trámite operativo, valida edicion de tipo ligado, alta y baja de ligado, cambio de tipo principal, confirma persistencia en una pagina fresca y restaura el estado original al terminar.
- `tests/browser/tramites-step2-step3-save.spec.js`: abre los pasos 2 y 3 del prototipo para un trámite operativo, valida guardado real de gestor y derechos, recalculo local del panel de aprobacion, upload real de evidencias finales, apertura del gate a Pago a gestor y restauracion final del estado original.
- `npm run test:browser:tramites-operativo`: corre en una sola pasada la regresion operativa completa de Paso 1, Paso 2 y Paso 3 usando las dos specs anteriores.

Variables esperadas:

- `SGL_BASE_URL`: URL base local. Por defecto usa `http://admin-sgl`.
- `SGL_USERNAME`: usuario para login visual si no hay sesion activa.
- `SGL_PASSWORD`: password para login visual si no hay sesion activa.
- `SGL_TRAMITE_STEP1_ID`: trámite objetivo para la spec de Paso 1. Por defecto usa `12460`.
- `SGL_STEP1_ASSOCIATED_ALT_ID`: tipo alterno preferido para editar el ligado existente. Por defecto usa `13`.
- `SGL_STEP1_ADD_TYPE_ID`: tipo preferido para probar el alta de ligado. Por defecto usa `18`.
- `SGL_STEP1_PRINCIPAL_ALT_ID`: tipo alterno preferido para probar el cambio de principal. Por defecto usa `13`.
- `SGL_TRAMITE_STEP23_ID`: trámite objetivo para la spec combinada de Paso 2 y Paso 3. Por defecto usa `12460`.
- `SGL_BROWSER_TEST_SUFFIX`: sufijo opcional para distinguir la captura de prueba.
- `SGL_RESTORE_ORIGINAL`: si vale `0`, no restaura el estado original al terminar.

Comandos:

- `npm install`
- `npx playwright install chromium`
- `npm run test:browser:php82-smoke`
- `npm run test:browser:cobranza`
- `npm run test:browser:tramites-step1`
- `npm run test:browser:tramites-step2-step3`
- `npm run test:browser:tramites-operativo`
- `SGL_BASE_URL=http://admin-sgl SGL_USERNAME=tu_usuario SGL_PASSWORD=tu_password npm run test:browser:cobranza`
- `SGL_BASE_URL=http://localhost:18080 SGL_USERNAME=tu_usuario_con_permiso SGL_PASSWORD=tu_password SGL_TRAMITE_STEP1_ID=12460 npm run test:browser:tramites-step1`
- `SGL_BASE_URL=http://localhost:18080 SGL_USERNAME=tu_usuario_con_permiso SGL_PASSWORD=tu_password SGL_TRAMITE_STEP23_ID=12460 npm run test:browser:tramites-step2-step3`
- `SGL_BASE_URL=http://localhost:18080 SGL_USERNAME=tu_usuario_con_permiso SGL_PASSWORD=tu_password npm run test:browser:tramites-operativo`

Notas para Paso 1:

- La spec se omite si la sesion activa o el usuario configurado no puede editar el Paso 1 del trámite objetivo. Eso evita falsos negativos por ACL o tenant.
- Para ejercer la bateria completa, usa un usuario que vea el badge como `Editable` y que tenga permisos reales sobre el expediente elegido.

Notas para Paso 2 y Paso 3:

- La spec combinada usa lecturas frescas para confirmar persistencia real despues del guardado de derechos y despues del upload de evidencias finales.
- Tambien elimina los archivos temporales que sube al endpoint real de `upload_pago_gestor`, para dejar el expediente tal como estaba al inicio.

Atajo sugerido para el smoke de PHP 8.2 con el usuario demo del repo:

- `DOCKER_APP_PORT=18080 docker compose up -d app`
- `docker compose exec -T app php spark sgl:demo-data --count=4`
- `SGL_BASE_URL=http://localhost:18080 SGL_USERNAME=luisa.flores SGL_PASSWORD=Demo1234! npm run test:browser:php82-smoke`