# Planeacion De Migracion A PHP 8.2

## Objetivo

Migrar la aplicacion a PHP 8.2 con el menor riesgo posible, manteniendo funcionales los flujos criticos de tramites, dashboard, notificaciones, cargas de archivos, Grocery CRUD y exportaciones.

## Estado Actual Detectado

- El proyecto corre actualmente con PHP 7.4 en este entorno de trabajo.
- El repo declara compatibilidad amplia en composer, pero el framework embebido sigue siendo CodeIgniter 4.0.4.
- El framework no esta consumido como dependencia moderna externa; se usa el directorio local system/.
- Grocery CRUD embebido ya incluye piezas orientadas a PHP 8.1+, pero arrastra una dependencia antigua de PHPExcel dentro de app/Libraries/GroceryCrudEnterprise/.
- Ya se corrigio un uso propio de strftime en el dashboard para evitar una deprecacion directa al pasar a PHP 8.2.

## Hallazgos Clave

### 1. Bloqueo Mayor: Framework Base Antiguo

- El framework actual es CodeIgniter 4.0.4.
- Para una migracion funcional a PHP 8.2, no conviene quedarse en esa base.
- El trabajo correcto es subir primero el framework base a una version de CodeIgniter 4 compatible y mantenida para PHP 8.2.

### 2. Bloqueo Mayor: Dependencias Embebidas Antiguas

- app/Libraries/GroceryCrudEnterprise/composer/platform_check.php ya exige PHP >= 8.1.
- Dentro de Grocery CRUD embebido sigue existiendo PHPExcel antiguo, con uso de strftime en:
  - app/Libraries/GroceryCrudEnterprise/scoumbourdis/phpexcel/Classes/PHPExcel/Calculation/DateTime.php
- Eso indica que la migracion no debe limitarse a cambiar la version de PHP del servidor.

### 3. Codigo Propio

- Se detecto uso de strftime en el dashboard y se sustituyo por un helper basado en IntlDateFormatter.
- No se detectaron, en esta pasada inicial, otros usos directos equivalentes dentro del codigo de aplicacion fuera de librerias embebidas.

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

El siguiente trabajo tecnico correcto es abrir una rama de migracion y ejecutar primero el upgrade del framework base, antes de seguir parchando incompatibilidades aisladas en vistas o controladores.