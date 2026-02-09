# Memoria de trabajo (pendientes y cambios)

> Objetivo: tener un registro simple y compartido de lo ya hecho, lo pendiente y lo que se deja en pausa.
> Estado actual: **freeze de cambios** para demo del lunes (no mover rutas/arquitectura por ahora).

## Hecho (confirmado)

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
- Idea: estandarizar rutas a un prefijo consistente (ej. `/deskapp/...`), corregir typos y decidir estrategia para legacy.
- Razón de pausa: todo funciona y se necesita estabilidad para demo.

Puntos detectados (a retomar):
- `setAutoRoute(true)` habilitado en `app/Config/Routes.php` (impacta qué rutas están realmente “vivas”).
- Inconsistencia de prefijos: existen rutas con y sin `/deskapp`.
- Posibles typos en destinos de rutas (uso de `:` en lugar de `::`).
- Notificaciones: el UI consume `deskapp/notifications/...` pero en rutas explícitas aparece `/notifications/...`.

## Pendientes (backlog)

### Operación / Demo
- [ ] Colocar el DNS correctamente (revisar registros y propagación)
- [ ] Contratar y configurar SendGrid (API key, remitente, plantillas, pruebas de envío)
- [ ] Solicitar un número de WhatsApp para atención (definir proveedor y flujo)
- [ ] Generar dummy/datos de ejemplo para mostrar en el demo (usuarios, trámites, notificaciones)

### Soporte / Comunicación (WhatsApp)
- [ ] Diseñar e implementar administrador de comunicación (ver COMUNICACION_WHATSAPP_SOPORTE_README.md)

### 1) Bug: estatus de usuario no se actualiza bien
- Pendiente de investigar:
  - Dónde se escribe el estatus (DB/modelo).
  - Dónde se lee para mostrarlo (vista/consulta/cache).
  - Si hay sesión/cache impidiendo ver cambios.

Checklist sugerido:
- [ ] Reproducir con 2 usuarios (admin y no-admin)
- [ ] Encontrar tabla/campos implicados
- [ ] Confirmar flujo de actualización (controller -> model -> DB)
- [ ] Confirmar invalidación de cache/session

### 2) Completar auditoría de cambios de estatus (migración `tra_user_log` → `tramite_audit_log`)
- Estado: **no se puede retirar `tra_user_log` aún**.
- Hallazgo: `tramite_audit_log` ya se usa (timeline y logs), pero no registra `status_change` en todos los puntos donde cambia `tramite.tra_status_id`.

Puntos detectados a cubrir (cuando pase el demo):
- `Deskapp\\Tramites::change_status()` y `Deskapp\\Tramites::cancelar_tramite()` actualizan `tra_status_id` y solo insertan `tra_user_log` (falta `log_tramite_status_change()`).
- Flujos similares en `Deskapp\\Concluido` también insertan `tra_user_log` sin auditoría de `status_change`.

Checklist sugerido:
- [ ] Inventariar todos los lugares donde cambia `tramite.tra_status_id`
- [ ] Asegurar que cada cambio llame `log_tramite_status_change($tramiteId, $old, $new)`
- [ ] Verificar que el timeline muestre esos cambios como `action='status_change'`
- [ ] Confirmar si hay reportes/consultas que dependan de `tra_user_log`
- [ ] Definir estrategia de transición: alias temporal vs retiro de `tra_user_log`

### 3) Auditoría de rutas vs menú
Objetivo: identificar rutas “huérfanas” o sospechosas que no aparecen en el menú ni se consumen por JS.

Entregable deseado:
- Lista de rutas con clasificación:
  - Navegación (menú)
  - Internas (AJAX/API)
  - Acceso directo legítimo
  - Huérfanas
  - Sospechosas por typo/legacy

### 4) Plan de implementación (cuando se retome rutas)
Estrategia propuesta (a validar):
- PR pequeño #1: corregir typos evidentes (`:` -> `::`) sin cambiar URLs.
- PR #2: aliases/redirects de rutas legacy hacia `/deskapp/...`.
- PR #3: endurecer/limitar AutoRoute (si aplica).
- PR #4: limpieza final de demos/huérfanas.

## Decisiones pendientes (cuando pase el demo)
- Política para rutas legacy sin `/deskapp`:
  - Redirect 301 recomendado vs mantener alias temporal vs eliminar.
- Si AutoRoute debe quedar activo:
  - Si se apaga, asegurar rutas explícitas para todo lo usado.

## Registro rápido (cómo usar este archivo)
- Agrega nuevos puntos en “Pendientes” con checklist.
- Cuando algo se complete, muévelo a “Hecho” con fecha.
- Si algo se congela por releases/demos, muévelo a “En pausa” con razón.
