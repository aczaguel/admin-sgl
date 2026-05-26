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
- Esto confirma que hoy no existe validacion real del proyecto sobre PHP 8.2 en este workspace.

### Inconsistencia de Compatibilidad Declarada

- [composer.json](composer.json) declara `"php": "^7.2 || ^8.0"`.
- Pero Grocery CRUD embebido exige PHP >= 8.1 en [app/Libraries/GroceryCrudEnterprise/composer/platform_check.php](app/Libraries/GroceryCrudEnterprise/composer/platform_check.php).
- Implicacion: el arbol principal y la libreria embebida no cuentan la misma historia de compatibilidad. No conviene confiar solo en composer.json como fuente de verdad para esta migracion.

### Bloqueadores Concretos Verificados En Framework Base

- El framework embebido sigue en CodeIgniter 4.0.4, confirmado en [system/CodeIgniter.php](system/CodeIgniter.php#L39).
- El arbol `system/` todavia usa `FILTER_SANITIZE_STRING`, deprecado en PHP 8.1, por ejemplo en:
  - [system/Router/RouteCollection.php](system/Router/RouteCollection.php#L253)
  - [system/HTTP/CLIRequest.php](system/HTTP/CLIRequest.php#L205)
  - [system/HTTP/CURLRequest.php](system/HTTP/CURLRequest.php#L125)
  - [system/Helpers/cookie_helper.php](system/Helpers/cookie_helper.php#L84)
- El framework tambien usa `utf8_encode()`, deprecado en PHP 8.2, en [system/Log/Handlers/ChromeLoggerHandler.php](system/Log/Handlers/ChromeLoggerHandler.php#L180).
- Implicacion: aun sin tocar codigo de negocio, el framework base ya trae deprecations conocidas para 8.1/8.2.

### Exportaciones: Doble Stack Tecnologico

- El codigo propio moderno ya usa PhpSpreadsheet en [app/Controllers/Deskapp/TramiteWizard.php](app/Controllers/Deskapp/TramiteWizard.php#L17).
- Pero Grocery CRUD Enterprise sigue exportando con PHPExcel en [app/Libraries/GroceryCrudEnterprise/grocery-crud/enterprise/src/GroceryCrud/Core/State/ExportState.php](app/Libraries/GroceryCrudEnterprise/grocery-crud/enterprise/src/GroceryCrud/Core/State/ExportState.php#L5) y [app/Libraries/GroceryCrudEnterprise/grocery-crud/enterprise/src/GroceryCrud/Core/State/ExportState.php](app/Libraries/GroceryCrudEnterprise/grocery-crud/enterprise/src/GroceryCrud/Core/State/ExportState.php#L207).
- Dentro de PHPExcel embebido siguen existiendo usos de `strftime` en [app/Libraries/GroceryCrudEnterprise/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation/DateTime.php](app/Libraries/GroceryCrudEnterprise/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation/DateTime.php#L537).
- Implicacion: el riesgo de exportaciones no esta distribuido uniformemente; el modulo wizard parece mejor posicionado que Grocery CRUD/export de tablas.

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