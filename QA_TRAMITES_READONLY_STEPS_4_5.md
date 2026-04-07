# QA manual — Readonly por pasos 4–5 (Trámites)

Objetivo: validar que los pasos 4–5 se comporten como **solo lectura** cuando el usuario **no** cumple los permisos/guardas requeridas, incluyendo:

- Formularios del wizard
- Dropzones (uploads)
- Botones de detalle con GroceryCRUD (modales)
- **Bloqueo server-side** ante requests directos (endpoints y `gc_state`)

## Permisos y guardas involucradas

### Paso 4 — Pago a Gestor

Escritura (guardar formulario y subir comprobantes) debe requerir:

- Base: `editar_tramite`
- Sección: `section_pago_gestor`
- Acción: `editar_pago_gestor`
- Upload (permiso fino Dropzone): `can_upload_dropzone_pago_gestor`
- Guarda por estatus: `puede_editar_modulo(..., 'editar_pago_gestor'/'upload_pago_gestor', step=4)`

Bypass:

- Admin / Super Admin: bypass de permisos finos (pero se respetan bloqueos por estatus donde aplique)
- Closer: **Paso 4 debe permanecer readonly** (en el flujo nuevo se oculta/inhabilita)

### Paso 5 — Final / Cobro a Cliente + Evidencias finales

Escritura (uploads/CRUD) debe requerir:

- Actor permitido: (Admin/Super Admin) **o** (Closer) **o** (tiene `editar_tramite`)
- Sección: `section_final_costos` (para no-admin)
- Upload cobro (permiso fino Dropzone): `can_upload_dropzone_cobro_cliente`
- Guarda por estatus: `puede_editar_modulo(..., 'upload_cobro_cliente', step=5)`
- Evidencias finales: `puede_editar_modulo(..., 'evidencias_finales_cliente', step=5)`

### Verificación de etiquetas de permiso (modo debug)

Las vistas imprimen etiquetas de auditoría (`perm_audit_tag`) **solo para Super Admin**. Se usan para confirmar el permiso fino que gobierna cada Dropzone:

- Paso 4 (Dropzone “Documentos de Pago”): `can_upload_dropzone_pago_gestor`
- Paso 5 (Dropzone “Documentos” / Cobro cliente): `can_upload_dropzone_cobro_cliente`

Nota: si tu toggle de debug usa `localStorage.debugMode`, actívalo para que se muestren las etiquetas.

## Flujos a probar

- Flujo nuevo (vista + JS + flags/paths):
  - UI: [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php)
  - JS: [public/assets/src/scripts/tramitesn_update_v2.js](public/assets/src/scripts/tramitesn_update_v2.js)
  - Backend flags/paths: [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php)

- Endpoints reales de mutación (importante: el flujo nuevo llama varios del controller clásico):
  - Backend: [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php)

## Precondiciones (recomendado)

- Un mismo `tramite_id` accesible para todos los usuarios de prueba (multi-tenancy OK).
- Probar con un trámite en estatus editable para step 4/5 (según tus reglas) y con otro en estatus Concluido/Cancelado para validar solo-lectura fuerte.
- Preparar usuarios de prueba con combinaciones típicas:

| Usuario | Rol/Idea | `editar_tramite` | `section_pago_gestor` | `editar_pago_gestor` | `section_final_costos` |
|---|---|---:|---:|---:|---:|
| A1 | Admin/Super Admin | (n/a) | (n/a) | (n/a) | (n/a) |
| C1 | Closer (con final costos) | ❌/✅ | ❌ | ❌ | ✅ |
| G1 | Ejecutivo con Pago Gestor | ✅ | ✅ | ✅ | ❌ |
| F1 | Ejecutivo con Final Costos | ✅ | ❌ | ❌ | ✅ |
| R0 | Usuario sin permisos de sección | ✅ | ❌ | ❌ | ❌ |

Agregar al menos 2 usuarios/casos adicionales para validar permisos finos de Dropzone:

- `GnoU`: tiene permisos del Paso 4 (sección/editar) pero **NO** `can_upload_dropzone_pago_gestor`.
- `FnoU`: tiene permisos del Paso 5 (sección/actor permitido) pero **NO** `can_upload_dropzone_cobro_cliente`.

Nota: las guardas `puede_editar_modulo(...)` dependen del estatus del trámite; si el estatus bloquea, debe quedar readonly aunque el usuario tenga permisos.

## Matriz — Validación de UI (flujo nuevo)

### Paso 4 (Pago a Gestor)

- Usuario G1:
  - Campos del formulario: editables.
  - Botón de guardar: funciona.
  - Dropzone de pago a gestor: visible y permite subir.

- Usuario GnoU:
  - Formulario: puede ser editable (según sus permisos de paso 4).
  - Dropzone: debe quedar **readonly** (no permite subir; ideal: Dropzone y botón de subir no visibles).
  - La galería/preview de documentos existentes debe seguir siendo visible (solo lectura).

- Usuario R0:
  - Formulario: readonly (inputs disabled/readonly).
  - Dropzone: no permite subir (ideal: no visible o deshabilitado).
  - No debe bloquear navegación por validaciones de required.

- Usuario C1 (Closer):
  - Paso 4 debe estar oculto o readonly.

### Paso 5 (Final Costos / Cobro + Evidencias)

- Usuario F1 y/o C1 (Closer con `section_final_costos`):
  - Debe poder operar en los modales/botones de detalle que correspondan (según el estatus).

- Usuario R0:
  - Secciones de uploads/CRUD del paso 5 deben verse readonly.

- Usuario FnoU:
  - La zona de documentos de cobro debe quedar **readonly** (sin Dropzone activo ni botón de subir).
  - La galería/preview debe seguir visible (solo lectura).

## Matriz — Validación server-side (bloqueo real)

Objetivo: aunque el usuario fuerce requests directos (POST/AJAX), el backend debe negar mutaciones.

### Endpoints críticos (Paso 4)

- Guardar formulario:
  - `POST /deskapp/tramites/update_pago_gestor/{tramite_id}`
  - Expected sin permisos finos (o si `puede_editar_modulo` bloquea): HTTP 403/409 y **no** persiste cambios.

- Upload:
  - `POST /deskapp/tramites/upload_pago_gestor/{tramite_id}`
  - Expected sin permisos finos: HTTP 403/409.

- Delete (si aplica en UI):
  - `POST /deskapp/tramites/delete_pago_gestor` (admin-only)

### Endpoints críticos (Paso 5)

- Upload cobro:
  - `POST /deskapp/tramites/upload_cobro_cliente/{tramite_id}`
  - Expected si no cumple actor permitido + sección + guarda: HTTP 403/409.

- Upload cobro (permiso fino):
  - Sin `can_upload_dropzone_cobro_cliente` debe responder HTTP 403/409 aunque el usuario tenga la sección/actor permitido.

- Delete cobro (por endpoint):
  - `POST /deskapp/tramites/delete_cobro_cliente` con `tramite_id` + `file`
  - Expected si no cumple actor permitido + sección + guarda: HTTP 403/409.

## GroceryCRUD — pruebas por URL directa (`gc_state`)

Objetivo: bloquear mutaciones por URL aunque el modal/UI se “fuerce”.

Probar (con usuario sin write) abrir directamente:

- Paso 4:
  - `GET /deskapp/tramites/single_pago_gestor/{tramite_id}?gc_state=add`
  - `GET /deskapp/tramites/single_pago_gestor/{tramite_id}?gc_state=edit`

- Paso 5:
  - `GET /deskapp/tramites/single_cobro_cliente/{tramite_id}?gc_state=add`
  - `GET /deskapp/tramites/single_evidencias_finales/{tramite_id}?gc_state=add`

Expected:

- Si no cumple permisos/guardas: responde error (ideal 409) y no permite mutación.
- En modo readonly, los botones Add/Edit/Delete deben estar ocultos.
- `Delete` debe permanecer oculto para no-admin incluso cuando sí pueden escribir.

## Casos de estatus (sanity)

- Trámite Concluido/Cancelado (estatus 20/21):
  - Todos los endpoints de mutación deben responder solo-lectura (típicamente 409) para cualquier no-privilegiado.
  - En modales GroceryCRUD, no debe haber Add/Edit/Delete.

- Trámite en estatus “Pago a Gestor completado” (28):
  - Paso 4: no-Admin debe quedar readonly.

## Registro rápido de resultados

| Caso | Usuario | Sección | Resultado | Observaciones |
|---|---|---|---|---|
| UI Paso 4 editable | G1 | Pago Gestor | ✅/❌ | |
| UI Paso 4 readonly | R0 | Pago Gestor | ✅/❌ | |
| UI Paso 4 readonly | C1 | Pago Gestor | ✅/❌ | |
| UI Paso 5 editable | F1/C1 | Final Costos | ✅/❌ | |
| API deny update_pago_gestor | R0/C1 | Paso 4 | ✅/❌ | |
| API deny upload_pago_gestor | R0/C1 | Paso 4 | ✅/❌ | |
| API deny upload_pago_gestor (sin `can_upload_dropzone_pago_gestor`) | GnoU | Paso 4 | ✅/❌ | |
| API deny upload_cobro_cliente | R0 | Paso 5 | ✅/❌ | |
| API allow upload_cobro_cliente | F1/C1 | Paso 5 | ✅/❌ | |
| API deny upload_cobro_cliente (sin `can_upload_dropzone_cobro_cliente`) | FnoU | Paso 5 | ✅/❌ | |
| gc_state bloqueado (single_*) | R0 | Modales | ✅/❌ | |
