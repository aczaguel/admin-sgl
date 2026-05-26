# Memoria de trabajo (pendientes y cambios)

> Objetivo: tener un registro simple y compartido de lo ya hecho, lo pendiente y lo que se deja en pausa.
> Estado actual: **freeze de cambios** para demo del lunes (no mover rutas/arquitectura por ahora).

## Hecho (confirmado)

### Estatus de usuario
- Corregido el bug donde el estatus de usuario no reflejaba correctamente los cambios.
- Se validó el flujo de escritura/lectura y se dejó como resuelto.

### Trámites (nuevo flujo) - tipos asociados y permisos
- Se agregó UI en el update del flujo `Tramitesn` para:
  - Ver tipos ligados (listón superior + header de detalles).
  - Agregar tipo asociado (pendiente → guardar por renglón).
  - Cambiar tipo principal y cambiar/eliminar asociados vía modals (según permisos).
- Permisos nuevos (pendientes de mapear en roles/DB):
  - `editar_tramite_principal`: permite cambiar el **tipo principal** (modal “Cambiar tipo de trámite principal”).
  - `editar_tramite_asociado`: permite cambiar un **tipo asociado** (modal “Cambiar tipo de trámite asociado”).
  - `delete_tramite_asociado`: permite **eliminar** un tipo asociado (modal “Eliminar tipo asociado”).
- Endpoints protegidos por esos permisos:
  - `POST /deskapp/tramitesn/principal/update_tipo`
  - `POST /deskapp/tramitesn/services/update`
  - `POST /deskapp/tramitesn/services/delete`

### Header (Admin)
- Dropdown de usuario sin “caret/flechita” extra.
- Avatar/ícono estable (sin brincos al abrir dropdown).
- Campana de notificaciones ajustada ligeramente hacia arriba.
- Bloque “Sesión activa” en el dropdown:
  - Nombre completo (con fallback a username/email).
  - Segunda línea con identificador (Usuario o Email).
  - Mejoras de formato (ancho, jerarquía visual, ellipsis, separador).

Archivos relacionados:
- app/Views/deskapp/includes/_header.php
- app/Views/deskapp/includes/_notifications_dropdown.php

### Header (Cliente)
- Replicado el bloque “Sesión activa” con el mismo patrón de formato.

Archivos relacionados:
- app/Views/deskapp/includes/_header_cliente.php

### Nota de estilos / assets
- El proyecto usa CSS compilado en `public/assets/vendors/styles/style.css`.
- Para asegurar consistencia inmediata se dejaron overrides/local styles en los headers (evita depender del build de `public/assets/src/styles/...`).

## En pausa (no tocar antes del demo)

### Limpieza/estandarización de rutas
- Idea: estandarizar rutas a un prefijo consistente (ej. `/deskapp/...`), corregir typos y retirar superficie antigua heredada.
- Razón de pausa: todo funciona y se necesita estabilidad para demo.
- Estado 25-05-2026: los typos evidentes y placeholders malformados ya se corrigieron en `app/Config/Routes.php`; lo restante queda pendiente y puede bajar de prioridad si se arranca migración a PHP 8.*.

Puntos detectados (a retomar):
- `setAutoRoute(true)` habilitado en `app/Config/Routes.php` (impacta qué rutas están realmente “vivas”).
- Inconsistencia de prefijos: existen rutas con y sin `/deskapp`.
- Notificaciones: el UI consume `deskapp/notifications/...` pero en rutas explícitas aparece `/notifications/...`.

## Pendientes (backlog)

### Prioridad técnica propuesta: migración a PHP 8.2
- Estado sugerido: priorizar este frente por encima de la limpieza de rutas una vez pasado el demo.
- Contexto confirmado:
  - el proyecto corre con framework embebido `CodeIgniter 4.0.4`
  - Grocery CRUD embebido ya exige PHP >= 8.1 en su `platform_check`
  - siguen existiendo dependencias legacy dentro de librerías embebidas, incluyendo PHPExcel antiguo
  - el plan base ya está documentado en `MIGRACION_PHP_8_2_README.md`

Checklist sugerido:
- [ ] Definir versión objetivo exacta de PHP 8.* para la migración inicial
- [ ] Preparar ambiente QA real con esa versión de PHP y extensiones homologadas
- [ ] Evaluar upgrade del framework base desde `CodeIgniter 4.0.4` a una versión compatible con PHP 8.2
- [ ] Auditar dependencias embebidas y vendor crítico: Grocery CRUD, PHPExcel/PhpSpreadsheet, PHPUnit y librerías de soporte
- [ ] Ejecutar smoke tests de flujos críticos en runtime PHP 8.*: login, dashboard, trámites, notificaciones, uploads, exportaciones y cobranza
- [ ] Re-priorizar después la limpieza de rutas/AutoRoute con el sistema ya estable en PHP 8.*

### Operación / Demo
- [ ] Colocar el DNS correctamente (revisar registros y propagación)
- [ ] Contratar y configurar SendGrid (API key, remitente, plantillas, pruebas de envío)
- [ ] Solicitar un número de WhatsApp para atención (definir proveedor y flujo)1º
- [ ] Generar dummy/datos de ejemplo para mostrar en el demo (usuarios, trámites, notificaciones)
- [ ] Subir a S3 todas las imágenes actualmente almacenadas en el servidor como respaldo y medida de seguridad (avatars, evidencias, uploads históricos y cualquier imagen operativa vigente)

### Soporte / Comunicación (WhatsApp)
- [ ] Diseñar e implementar administrador de comunicación (ver COMUNICACION_WHATSAPP_SOPORTE_README.md)

### Menú / Trámites por etapa
- [ ] Separar el menú de Trámites por bloques operativos:
  - Wizard pasos 1, 2 y 3
  - Paso 4
  - Paso 5
- [ ] En el bloque de Paso 4 agregar un botón visible: "Revisar pasos anteriores para editar"
- [ ] Validar qué permisos y rutas deben usarse para que el botón de revisión no rompa el flujo actual ni los bloqueos por estatus

### Cobranza / permisos / retiro de superficie antigua
- [ ] Quitar bypass por rol o usuario especial en permisos y visibilidad; todo debe resolverse por permisos efectivos asignados
- [ ] Mantener que Super Admin vea todo por tener todos los checks y roles asignados, no por bypass implícito en helpers
- [ ] Revisar y simplificar el acceso a Cobro a Cliente para que no dependa de combinaciones legacy como `section_final_costos` o permisos de navegación redundantes
- [ ] Eliminar consideraciones extra de acceso en cobranza y trámite final; solo se debe ver y operar lo permitido por permisos
- [ ] Retirar legacy de autorización y navegación relacionado con cobranza conforme se migre al módulo nuevo
- [ ] Definir y ejecutar pruebas end-to-end del flujo operativo desde creación de trámite hasta entrada a cobranza
- [ ] Cubrir con pruebas los puntos mínimos del flujo: creación, avance de estatus, condición de listo para cobranza, visibilidad en bandeja y acceso al expediente de cobranza

### Auth / redirects Deskapp
- [ ] Extender el barrido de redirects de sesión expirada y de rutas/redirecciones antiguas al resto de controladores Deskapp fuera del slice ya corregido (Cobranza, Tramites, Tramitesn)

### 2) Completar auditoría de cambios de estatus (migración `tra_user_log` → `tramite_audit_log`)
- Estado: **no se puede retirar `tra_user_log` aún**, pero la revisión puntual del 24-05-2026 confirmó que los cambios activos de `tramite.tra_status_id` ya registran `status_change`.
- Hallazgo actualizado: el backlog estaba desfasado; los controladores activos ya llaman `log_tramite_status_change()` de forma directa o a través de `updateTramiteStatus()`.

Cobertura confirmada en la revisión:
- `Deskapp\\Tramites::updateTramiteStatus()`
- `Deskapp\\Tramites::change_status()` y `Deskapp\\Tramites::cancelar_tramite()`
- `Deskapp\\Concluido::change_status()` y variantes equivalentes de estatus final
- `Deskapp\\Tramitesn` vía `updateTramiteStatus()` y `syncCobroClienteStatusFromPagoGestor()`

Checklist sugerido:
- [x] Inventariar los lugares activos donde cambia `tramite.tra_status_id`
- [x] Asegurar que cada cambio activo llame `log_tramite_status_change($tramiteId, $old, $new)`
- [x] Verificar en código y pruebas existentes que el timeline/auditoría use `action='status_change'`
- [x] Confirmar si hay reportes/consultas que dependan de `tra_user_log`
  - Resultado de la revisión 24-05-2026: no se detectaron lectores activos en app sobre `tra_user_log`; los consumidores actuales usan `tramite_audit_log` y, en fallback legacy, `tramite_auditoria`.
  - `tra_user_log` permanece como escritura legacy en varios controladores, no como fuente activa para timeline/reportes.
- [x] Definir estrategia de transición: alias temporal vs retiro de `tra_user_log`
  - Fase 1: mantener inserción en `tra_user_log` como compatibilidad temporal, pero centralizarla detrás de un helper/servicio único para dejar de duplicar lógica por controlador.
  - Fase 2: migrar controladores legacy (`Tramites`, `Tramitesn`, `Concluido`, `Flotillas`, `TramitesMasivos`) a ese punto único y dejar `tramite_audit_log` como fuente oficial de lectura.
  - Fase 3: ejecutar smoke tests de timelines, cambios de estatus y flujos de creación/avance; si no aparece ningún consumidor externo real de `tra_user_log`, convertir su escritura en no-op controlado o retirarla.
  - Fase 4: eliminar `TraUserLogModel` y la tabla solo cuando ya no haya integraciones SQL/reportes fuera de app que dependan de ella.

### 3) Auditoría de rutas vs menú
Objetivo: identificar rutas “huérfanas” o sospechosas que no aparecen en el menú ni se consumen por JS.

Entregable deseado:
- Lista de rutas con clasificación:
  - Navegación (menú)
  - Internas (AJAX/API)
  - Acceso directo legítimo
  - Huérfanas
  - Sospechosas por typo o superficie antigua

Estado 25-05-2026:
- Auditoría realizada y documentada en `AUDIT_RUTAS_MENU_2026-05-25.md`.
- Hallazgos principales:
  - se detectaron typos reales en destinos GET (`:` en lugar de `::`) y placeholders malformados; quedaron corregidos en `app/Config/Routes.php` el 25-05-2026 sin cambiar URLs
  - hay rutas cliente modernas comentadas que hoy sobreviven por `AutoRoute`
  - siguen existiendo aliases heredados con y sin `/deskapp`, y deben retirarse en favor de rutas canónicas
  - permanecen rutas demo y superficie antigua candidatas a limpieza posterior
  - los siguientes pasos de rutas quedan pendientes y pueden bajar de prioridad frente a la migración a PHP 8.*

### 4) Plan de implementación (cuando se retome rutas)
Estrategia propuesta (a validar):
- [x] PR pequeño #1: corregir typos evidentes (`:` -> `::`) y placeholders malformados sin cambiar URLs.
- [ ] PR #2: retirar aliases heredados y dejar solo rutas canónicas con `/deskapp/...`.
- [ ] PR #3: endurecer/limitar AutoRoute (si aplica).
- [ ] PR #4: limpieza final de demos, huérfanas y superficie antigua restante.
- Diseño de retiro de prefijo documentado en `PLAN_RETIRO_PREFIJO_DESKAPP.md`.

### 5) Plan de limpieza de permisos y acceso
Estrategia propuesta (a validar):
- PR pequeño #1: quitar bypass de `has_permission()` y helpers de cliente para que la autorización dependa solo de permisos y alcance asignado
- PR #2: unificar acceso de cobranza en una sola regla reutilizable para controller, vistas y acciones
- PR #3: retirar dependencias legacy de `section_final_costos`, permisos de navegación y condiciones especiales del wizard para entrar a cobranza
- PR #4: agregar pruebas integrales del flujo desde alta de trámite hasta bandeja/expediente de cobranza

## Decisiones pendientes (cuando pase el demo)
- Plan de retiro para rutas antiguas sin `/deskapp`:
  - Corregir consumidores hacia la ruta canónica y eliminar aliases; evitar sostener nuevas dependencias sobre rutas antiguas.
- Si AutoRoute debe quedar activo:
  - Si se apaga, asegurar rutas explícitas para todo lo usado.

## Registro rápido (cómo usar este archivo)
- Agrega nuevos puntos en “Pendientes” con checklist.
- Cuando algo se complete, muévelo a “Hecho” con fecha.
- Si algo se congela por releases/demos, muévelo a “En pausa” con razón.
