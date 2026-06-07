# Planeacion De Migracion A PHP 8.2

## Objetivo

Migrar la aplicacion a PHP 8.2 con el menor riesgo posible, manteniendo funcionales los flujos criticos de tramites, dashboard, notificaciones, cargas de archivos, Grocery CRUD y exportaciones.

## Estado Actual Detectado

- El proyecto corre actualmente con PHP 7.4 en este entorno de trabajo.
- El repo declara compatibilidad amplia en composer, pero el framework embebido sigue siendo CodeIgniter 4.0.4.
- El framework no esta consumido como dependencia moderna externa; se usa el directorio local system/.
- Grocery CRUD embebido ya incluye piezas orientadas a PHP 8.1+, pero arrastra una dependencia antigua de PHPExcel dentro de app/Libraries/GroceryCrudEnterprise/.
- Ya se corrigio un uso propio de strftime en el dashboard para evitar una deprecacion directa al pasar a PHP 8.2.

## Diagnostico Verificado 2026-05-26

### Runtime Local Confirmado

- PHP CLI local: 7.4.33.
- Composer local: 2.8.3.
- El host local sigue sin un binario nativo de PHP 8.2, pero ya existe validacion real del proyecto sobre PHP 8.2 via Docker en este workspace.

### Inconsistencia de Compatibilidad Declarada

- [composer.json](composer.json) declara `"php": "^7.2 || ^8.0"`.
- Pero Grocery CRUD embebido exige PHP >= 8.1 en [app/Libraries/GroceryCrudEnterprise/composer/platform_check.php](app/Libraries/GroceryCrudEnterprise/composer/platform_check.php).
- Implicacion: el arbol principal y la libreria embebida no cuentan la misma historia de compatibilidad. No conviene confiar solo en composer.json como fuente de verdad para esta migracion.

### Bloqueadores Concretos Verificados En Framework Base

- El framework embebido sigue en CodeIgniter 4.0.4, confirmado en [system/CodeIgniter.php](system/CodeIgniter.php#L39).
- Se verificaron deprecations concretas de PHP 8.1/8.2 en `system/` y ya se corrigieron localmente en esta rama:
  - [system/Router/RouteCollection.php](system/Router/RouteCollection.php)
  - [system/HTTP/CLIRequest.php](system/HTTP/CLIRequest.php)
  - [system/HTTP/CURLRequest.php](system/HTTP/CURLRequest.php)
  - [system/Helpers/cookie_helper.php](system/Helpers/cookie_helper.php)
  - [system/Log/Handlers/ChromeLoggerHandler.php](system/Log/Handlers/ChromeLoggerHandler.php)
- El reemplazo fue deliberado y compatible con PHP 7.4: normalizacion explicita de strings, `FILTER_SANITIZE_FULL_SPECIAL_CHARS` para el helper de cookies y eliminacion de `utf8_encode()` donde `json_encode()` ya entrega UTF-8.
- Implicacion: el frente de `system/` ya bajo de riesgo en esta rama, aunque el framework base sigue siendo antiguo y todavia requiere validacion real sobre PHP 8.2.

### Avance Aplicado 2026-05-26 En Esta Rama

- Se eliminaron los usos reales de `FILTER_SANITIZE_STRING` y `utf8_encode()` dentro de `system/`.
- Se validaron smoke tests con el bootstrap real del proyecto:
  - `php vendor/phpunit/phpunit/phpunit --bootstrap system/Test/bootstrap.php tests/app/Controllers/Deskapp/TramitesStatusWebhookTest.php`
  - `php vendor/phpunit/phpunit/phpunit --bootstrap system/Test/bootstrap.php tests/app/Controllers/Deskapp/TramitesnSessionRedirectTest.php`
- Resultado:
  - `OK (2 tests, 15 assertions)`
  - `OK (4 tests, 12 assertions)`
- Se reemplazaron tambien los usos residuales de `strftime()` dentro de [app/Libraries/GroceryCrudEnterprise/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation/DateTime.php](app/Libraries/GroceryCrudEnterprise/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation/DateTime.php) por `date()`.
- Validacion focalizada del parche en PHPExcel:
  - `php -l app/Libraries/GroceryCrudEnterprise/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation/DateTime.php`
  - resultado: `No syntax errors detected`
- Barrido posterior sobre `strftime()` en `app/` y `system/`: sin coincidencias.
- Se corrigieron incompatibilidades adicionales expuestas solo bajo PHP 8.2:
  - [system/HTTP/RequestTrait.php](system/HTTP/RequestTrait.php): evitar `filter_var(..., ..., null)` en las rutas de lectura global que usa Validation.
  - [system/Session/Handlers/ArrayHandler.php](system/Session/Handlers/ArrayHandler.php), [system/Session/Handlers/DatabaseHandler.php](system/Session/Handlers/DatabaseHandler.php), [system/Session/Handlers/FileHandler.php](system/Session/Handlers/FileHandler.php), [system/Session/Handlers/MemcachedHandler.php](system/Session/Handlers/MemcachedHandler.php) y [system/Session/Handlers/RedisHandler.php](system/Session/Handlers/RedisHandler.php): compatibilidad temporal con `SessionHandlerInterface` en PHP 8.2.
  - [app/Helpers/permissions_helper.php](app/Helpers/permissions_helper.php): eliminar firmas con parámetros opcionales antes de requeridos.
  - [system/Validation/Validation.php](system/Validation/Validation.php): tratar `null` como vacio antes de `trim()` en reglas con `permit_empty`.
  - [system/Session/Handlers/FileHandler.php](system/Session/Handlers/FileHandler.php): suprimir la deprecation de `read()` en el flujo web real con `#[\ReturnTypeWillChange]`.
  - [system/Validation/FormatRules.php](system/Validation/FormatRules.php): evitar `strtolower(null)` y `filter_var(..., ..., null)` dentro de `valid_ip()`.
  - [system/HTTP/ResponseTrait.php](system/HTTP/ResponseTrait.php): evitar `str_replace(..., null)` cuando la respuesta todavía no tiene body.

### Validacion Docker PHP 8.2 2026-05-26

- Se agregó una base Docker reproducible en [Dockerfile](Dockerfile) y [docker-compose.yml](docker-compose.yml).
- Build validado:
  - `docker compose build app`
- Spike validado dentro del contenedor:
  - `docker compose --profile tools run --rm php82-spike`
- Resultado actual dentro de Docker sobre PHP 8.2.31:
  - `spark` arranca correctamente.
  - [tests/app/Controllers/Deskapp/TramitesStatusWebhookTest.php](tests/app/Controllers/Deskapp/TramitesStatusWebhookTest.php): `OK (2 tests, 15 assertions)`
  - [tests/app/Controllers/Deskapp/TramitesnSessionRedirectTest.php](tests/app/Controllers/Deskapp/TramitesnSessionRedirectTest.php): `OK (4 tests, 12 assertions)`
  - [tests/app/Controllers/Deskapp](tests/app/Controllers/Deskapp): `OK (43 tests, 177 assertions)` tras corregir deprecations remanentes en `RequestTrait` y `Validation`.
  - [tests/app](tests/app): `OK (77 tests, 353 assertions)` dentro del mismo contenedor PHP 8.2.
  - Validación manual web dentro del contenedor en `http://localhost:18080`:
    - login exitoso
    - dashboard carga
    - buscador de trámites carga
    - cobranza carga
    - wizard moderno en [deskapp/tramitewizard](deskapp/tramitewizard) carga; al entrar puede aparecer confirm de borrador guardado y se validó la carga descartándolo.
    - flujo normal de pagos validado sobre el trámite `12426`:
      - [deskapp/tramitesn/update/12426](deskapp/tramitesn/update/12426) resuelve al slice activo de evidencias finales
      - [deskapp/tramitesn/ver_seccion_pago_gestor/12426](deskapp/tramitesn/ver_seccion_pago_gestor/12426) carga
      - [deskapp/tramitesn/ver_seccion_cobro_cliente/12426](deskapp/tramitesn/ver_seccion_cobro_cliente/12426) carga
    - persistencia del flujo activo Tramitesn validada sin tocar el wizard legado:
      - `POST /deskapp/tramitesn/update_save/12427` con los valores actuales del formulario respondió `success: true` y mensaje `El trámite se guardó correctamente.`
      - `POST /deskapp/tramitesn/update_pago_gestor/12426` con los valores actuales del formulario respondió `success: true` y mensaje `Pago a gestor guardado correctamente.`
      - [tests/app/Controllers/Deskapp/TramitesnUpdateFinalSaveTest.php](tests/app/Controllers/Deskapp/TramitesnUpdateFinalSaveTest.php) y [tests/app/Controllers/Deskapp/TramitesnUpdateFinalSaveIsolationTest.php](tests/app/Controllers/Deskapp/TramitesnUpdateFinalSaveIsolationTest.php): `OK (5 tests, 20 assertions)` en Docker PHP 8.2.
    - archivos del flujo activo Tramitesn validados en vivo sobre el trámite `12426`:
      - `POST /deskapp/tramitesn/upload_pago_gestor/12426` con `comprobante_final=factura_gestor`: `success: true`
      - `POST /deskapp/tramitesn/delete_pago_gestor`: `success: true`
      - `POST /deskapp/tramitesn/upload_cobro_cliente/12426` con `cobro_correcto=completo`: `success: true`
      - `GET /deskapp/tramitesn/getCobroClienteFiles/12426` devolvió el archivo recién cargado
      - `POST /deskapp/tramitesn/delete_cobro_cliente`: `success: true`
      - [tests/app/Controllers/Deskapp/TramitesnCobroClienteEvidenceIsolationTest.php](tests/app/Controllers/Deskapp/TramitesnCobroClienteEvidenceIsolationTest.php) y [tests/app/Controllers/Deskapp/TramitesnFinalDocsGuardTest.php](tests/app/Controllers/Deskapp/TramitesnFinalDocsGuardTest.php): `OK (3 tests, 18 assertions)` en Docker PHP 8.2.
    - exportaciones y descargas del flujo activo validadas en Docker PHP 8.2:
      - `GET /deskapp/tramitesn/tramite?action=export...` ya entrega `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` con `attachment;filename="Tramites (Nuevo Flujo)_2026-05-27.xlsx"`.
      - El desbloqueo requerido fue declarar la propiedad heredada `_debugLog` en [vendor/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation.php](vendor/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation.php) y en la copia espejo de [app/Libraries/GroceryCrudEnterprise/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation.php](app/Libraries/GroceryCrudEnterprise/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation.php) para evitar la deprecation de propiedad dinámica bajo PHP 8.2.
      - `GET /deskapp/tramitesn/tramite?action=export-pdf...` sigue devolviendo HTML y no un binario PDF; esto coincide con el comportamiento de Grocery CRUD, cuyo `ExportPdfState` renderiza una vista `export-pdf.php` en lugar de emitir `application/pdf`.
      - Descarga real de evidencia en cobro cliente validada con un archivo efímero sobre el trámite `12426`: `GET /assets/uploads/cobro_cliente/12426/...txt` respondió `200`, `text/plain`, contenido correcto, y después `POST /deskapp/tramitesn/delete_cobro_cliente` dejó nuevamente vacío `getCobroClienteFiles/12426`.
- Conclusión: ya existe una ruta viable para ejecutar la app en PHP 8.2 sin depender del runtime instalado en el host.

### Ajustes Docker Confirmados Durante La Validación Web

- [app/Config/App.php](app/Config/App.php): `DOCKER_BASE_URL` tiene prioridad explícita sobre `.env` para que los redirects del contenedor no salten al host local.
- [app/Config/Database.php](app/Config/Database.php): `DOCKER_DB_HOST` permite que el contenedor use la base de datos del host en local y otra ruta en servidor.
- [docker-compose.yml](docker-compose.yml): define `DOCKER_BASE_URL` y `DOCKER_DB_HOST` para el runtime local Docker.
- [docker/apache/000-default.conf](docker/apache/000-default.conf): el docroot correcto para este proyecto en Docker es la raíz del repo, no `public/`, porque el front controller activo está en [index.php](index.php) y los assets se referencian como `/public/...`.

### Exportaciones: Doble Stack Tecnologico

- El codigo propio moderno ya usa PhpSpreadsheet en [app/Controllers/Deskapp/TramiteWizard.php](app/Controllers/Deskapp/TramiteWizard.php#L17).
- Pero Grocery CRUD Enterprise sigue exportando con PHPExcel en [app/Libraries/GroceryCrudEnterprise/grocery-crud/enterprise/src/GroceryCrud/Core/State/ExportState.php](app/Libraries/GroceryCrudEnterprise/grocery-crud/enterprise/src/GroceryCrud/Core/State/ExportState.php#L5) y [app/Libraries/GroceryCrudEnterprise/grocery-crud/enterprise/src/GroceryCrud/Core/State/ExportState.php](app/Libraries/GroceryCrudEnterprise/grocery-crud/enterprise/src/GroceryCrud/Core/State/ExportState.php#L207).
- Ya no quedan usos visibles de `strftime()` en `app/` ni en `system/`, pero el riesgo de exportaciones sigue concentrado en el stack heredado de PHPExcel/PHPExcel export dentro de Grocery CRUD.
- El export Excel del listado activo Tramitesn ya quedo validado en PHP 8.2 despues de declarar la propiedad heredada `_debugLog` en la clase `PHPExcel_Calculation` que carga Grocery CRUD; el warning real salia de [vendor/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation.php](vendor/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation.php).
- El control `export-pdf` de Grocery CRUD no genera hoy un binario PDF: [vendor/grocery-crud/enterprise/src/GroceryCrud/Core/State/ExportPdfState.php](vendor/grocery-crud/enterprise/src/GroceryCrud/Core/State/ExportPdfState.php) renderiza HTML (`export-pdf.php`) para impresión/exportación posterior desde el navegador.
- Implicacion: el modulo wizard parece mejor posicionado que Grocery CRUD/export de tablas, aunque el frente de exportaciones heredadas ya tiene menos ruido directo para PHP 8.2.

### Codigo Propio: Riesgo Inicial Menor Que El Del Framework

- En esta pasada focalizada no aparecieron coincidencias relevantes de `FILTER_SANITIZE_STRING`, `utf8_encode()`, `utf8_decode()`, `money_format()` o `strftime()` dentro del codigo propio de aplicacion fuera del framework embebido y las librerias embebidas.
- Esto no prueba compatibilidad total con PHP 8.2, pero si sugiere que el primer cuello no esta en controladores/modelos/helpers propios sino en framework + librerias embebidas.

### Conclusion Operativa Del Diagnostico

- La migracion a PHP 8.2 no debe empezar por refactors amplios de negocio.
- El primer frente tecnico correcto es:
  1. framework base `system/`
  2. Grocery CRUD embebido y su stack de exportacion
  3. validacion funcional bajo runtime 8.2 real
- El retiro de `/deskapp` y otros refactors de rutas deben quedar despues, porque mezclarlos con este frente volveria opaca la causa de cualquier falla.

## Baseline Manual Con Sesion Web 2026-05-26

Se verifico navegacion manual en la aplicacion actual bajo el entorno PHP 7.4 existente, usando una sesion web valida, para dejar una referencia minima antes del spike en PHP 8.2.

### Slice Navegado Exitosamente

- Login exitoso hacia [http://admin-sgl/deskapp/dashboard](http://admin-sgl/deskapp/dashboard).
- Dashboard autenticado carga y muestra menu operativo.
- Busqueda de tramites carga en [http://admin-sgl/deskapp/tramitesn/search](http://admin-sgl/deskapp/tramitesn/search).
- Cobranza carga en [http://admin-sgl/deskapp/cobranza](http://admin-sgl/deskapp/cobranza).

### Ruido Conocido Detectado En El Baseline

- Al cargar dashboard, buscador y cobranza aparecieron respuestas 404 de imagenes faltantes, principalmente bajo:
  - `/public/uploads/avatars/*.jpg`
  - `/public/uploads/avatars/*.jpeg`
  - `/public/uploads/avatars/default.png`
  - `/public/1703838995_d827466926fa9fe81594.png`
- Este ruido visual ya existe en el baseline previo a PHP 8.2 y no debe confundirse con una regresion nueva de la migracion.

### Uso Recomendado Del Baseline

- Repetir este mismo slice cuando exista runtime PHP 8.2 real.
- Si login, dashboard, buscador o cobranza dejan de cargar, tratarlo como regresion prioritaria.
- Si solo persisten los 404 de avatars ya detectados, clasificarlos como deuda previa separada del frente PHP 8.2.

## Hallazgos Clave

### 1. Bloqueo Mayor: Framework Base Antiguo

- El framework actual es CodeIgniter 4.0.4.
- Para una migracion funcional a PHP 8.2, no conviene quedarse en esa base.
- El trabajo correcto es subir primero el framework base a una version de CodeIgniter 4 compatible y mantenida para PHP 8.2.

### 2. Bloqueo Mayor: Dependencias Embebidas Antiguas

- app/Libraries/GroceryCrudEnterprise/composer/platform_check.php ya exige PHP >= 8.1.
- Dentro de Grocery CRUD embebido sigue existiendo PHPExcel antiguo, con uso de strftime en:
  - app/Libraries/GroceryCrudEnterprise/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation/DateTime.php
- El flujo de exportacion de Grocery CRUD sigue instanciando `PHPExcel` directamente, mientras que partes mas nuevas del sistema ya usan `PhpSpreadsheet`.
- Eso indica que la migracion no debe limitarse a cambiar la version de PHP del servidor.
- La evaluacion de salida gradual de Grocery CRUD quedo documentada en [PLAN_SALIDA_GROCERYCRUD.md](PLAN_SALIDA_GROCERYCRUD.md).

### 3. Codigo Propio

- Se detecto uso de strftime en el dashboard y se sustituyo por un helper basado en IntlDateFormatter.
- No se detectaron, en esta pasada inicial, otros usos directos equivalentes dentro del codigo de aplicacion fuera de librerias embebidas.
- El riesgo visible inmediato es mas alto en framework embebido y librerias antiguas que en controladores/modelos/helpers propios.

### 4. Riesgo De Entorno

- Ya se detecto una falla previa causada por una tabla faltante de entorno: notification_reads.
- Antes de validar la migracion de PHP se debe homologar base de datos, extensiones y configuracion entre local, QA y produccion.

## Alcance Recomendado

La migracion debe ejecutarse en cuatro frentes:

1. Runtime PHP 8.2
2. Framework base CodeIgniter
3. Librerias de terceros y embebidas
4. Codigo propio y validacion funcional

## Plan Por Fases

## Fase 0. Congelamiento Y Respaldo

- Congelar cambios de negocio mientras se ejecuta la migracion.
- Respaldar base de datos, vendor y archivos de configuracion.
- Documentar versiones actuales de:
  - PHP
  - extensiones PHP
  - CodeIgniter
  - Grocery CRUD
  - Composer
  - servidor web
- Preparar ambiente de QA con PHP 8.2 real antes de tocar produccion.

## Fase 1. Homologacion De Entorno

- Confirmar que QA y produccion tengan todas las extensiones requeridas:
  - curl
  - intl
  - json
  - mbstring
  - fileinfo
- Si se adopta Docker como runtime, homologar tambien la imagen base, docroot (`public/`) y el montaje de `writable/` entre entornos.
- Verificar que existan todas las tablas que el sistema asume, incluyendo notification_reads.
- Verificar timezone, locale y configuracion regional, especialmente donde se formatean fechas.

## Fase 2. Upgrade Del Framework Base

- Subir CodeIgniter desde 4.0.4 a una version moderna compatible con PHP 8.2.
- Revisar breaking changes entre la version actual y la version objetivo.
- Ajustar:
  - Config
  - routing
  - manejo de sesiones
  - filtros
  - respuestas JSON
  - excepciones y logging
- Validar que index.php, spark, app/Config y system/ sigan alineados.

Resultado esperado:

- El proyecto arranca en PHP 8.2 sin errores fatales del framework.

## Fase 3. Saneamiento De Dependencias

- Revisar compatibilidad de estas dependencias declaradas:
  - grocery-crud/enterprise
  - phpoffice/phpspreadsheet
  - laminas/laminas-escaper
  - kint-php/kint
  - phpunit/phpunit
- Revisar dependencias embebidas dentro de app/Libraries/GroceryCrudEnterprise/.
- Definir si PHPExcel embebido:
  - se actualiza
  - se parchea
  - se reemplaza por PhpSpreadsheet donde aplique
- Evitar mantener codigo vendor viejo que dependa de APIs obsoletas de PHP.

Resultado esperado:

- Ninguna libreria critica debe emitir errores fatales o incompatibilidades conocidas en PHP 8.2.

## Fase 4. Ajustes De Codigo Propio

- Sustituir APIs obsoletas o deprecadas si aparecen durante pruebas.
- Priorizar estos puntos:
  - formateo de fechas y locales
  - respuestas JSON para AJAX y Grocery CRUD
  - manejo de uploads
  - exportaciones Excel y PDF
  - notificaciones
  - dashboards
- Revisar objetos dinamicos, warnings, notices y deprecations visibles en PHP 8.2.

Resultado esperado:

- Los flujos de negocio principales deben operar sin warnings relevantes ni errores fatales.

## Fase 5. Validacion Funcional

Probar como minimo estos flujos:

1. Login y sesion
2. Dashboard admin y dashboard cliente
3. Listado de tramites
4. Detalle de tramite
5. Paso 1 a paso 6 del flujo de tramite
6. Bitacora y tra_evidencias
7. Pago derechos
8. Pago gestor
9. Cobro cliente
10. Notificaciones y lectura de notificaciones
11. Exportaciones Excel
12. Cargas de archivos y borrado de archivos
13. Vistas de concluidos y cancelados
14. Wizard de tramites

## Fase 6. Salida A Produccion

- Desplegar primero a QA o staging con PHP 8.2.
- Ejecutar smoke tests.
- Revisar logs de PHP, CodeIgniter y servidor web.
- Limpiar caches y OPcache.
- Desplegar a produccion en ventana controlada.
- Monitorear errores por al menos 24 horas.

## Riesgos Principales

- Incompatibilidades del framework 4.0.4 con PHP 8.2.
- Dependencias embebidas antiguas dentro de Grocery CRUD.
- Diferencias de entorno entre local y produccion.
- Errores silenciosos en respuestas AJAX que terminen rompiendo modales o tablas.
- Exportaciones y calculos Excel afectados por PHPExcel viejo.

## Criterios De Exito

- El sistema arranca en PHP 8.2 sin errores fatales.
- No hay deprecations criticas en logs de produccion.
- Los flujos principales de tramites funcionan de punta a punta.
- Notificaciones, uploads, bitacora y exportaciones quedan operativas.
- QA y produccion quedan homologados en estructura de base de datos y configuracion minima.

## No Recomendado

- Cambiar solo la version de PHP en servidor y asumir compatibilidad.
- Mantener el framework en 4.0.4 si se quiere estabilidad real en 8.2.
- Validar solo con login y dashboard.
- Omitir pruebas de Grocery CRUD, exportaciones y notificaciones.

## Siguiente Paso Recomendado

El siguiente trabajo tecnico correcto es abrir una rama de migracion y ejecutar primero un spike controlado sobre el framework base y Grocery CRUD/exportaciones, antes de seguir parchando incompatibilidades aisladas en vistas o controladores.

Orden recomendado inmediato:

1. Preparar ambiente QA o local separado con PHP 8.2 real.
2. Probar arranque del proyecto con logs de deprecations activos.
3. Atacar incompatibilidades de `system/` o subir CodeIgniter a una base mantenida para PHP 8.2.
4. Validar exportaciones de Grocery CRUD y wizard por separado.

Comando base sugerido cuando exista el runtime 8.2 en la maquina:

- `bash admin/php82-spike.sh /ruta/al/php8.2`

Ese script:

- valida que el binario recibido sea PHP 8.2+
- registra salida en `writable/logs/php82-spike-*.log`
- ejecuta un bootstrap de `spark`
- corre un par de pruebas focalizadas para detectar fallas tempranas sin lanzar toda la suite