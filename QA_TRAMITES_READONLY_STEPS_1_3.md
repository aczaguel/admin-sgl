# QA manual — Readonly por pasos 1–3 (Trámites)

Objetivo: validar que los pasos 1–3 se comporten como **solo lectura** cuando el usuario **no** tiene el permiso `write_*` correspondiente, incluyendo **Dropzone** y **bloqueo server-side** ante requests directos.

## Permisos involucrados

- Paso 1: `write_tramite_datos_tramite`
- Paso 2: `write_tramite_asigna_gestor`
- Paso 3: `write_tramite_pago_derechos`

Permisos finos de upload (Dropzone):

- Step 3 (Pago derechos / uploads): `can_upload_dropzone_pago_derechos`

Nota: en ambos flujos, además puede existir bloqueo por estatus/reglas de negocio (p.ej. trámite ya aprobado). El permiso `write_*` es **necesario pero no siempre suficiente**.

## Flujos a probar

- Flujo clásico (vista + endpoints):
  - UI: [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php)
  - Backend: [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php)

- Flujo nuevo (vista + JS + endpoints):
  - UI: [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php)
  - JS: [public/assets/src/scripts/tramitesn_update_v2.js](public/assets/src/scripts/tramitesn_update_v2.js)
  - Backend: [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php)

## Precondiciones (recomendado)

- Tener un mismo `tramite_id` accesible para todos los usuarios de prueba (multi-tenancy OK).
- Asegurar que todos los usuarios tengan el permiso base para entrar/editar el trámite (según tu ACL actual suele ser `editar_tramite`, más los permisos de lectura/menú necesarios).
- Crear (o elegir) 5 usuarios de prueba con el mismo rol base pero con variación en `write_*`:

| Usuario | Paso 1 | Paso 2 | Paso 3 |
|---|---:|---:|---:|
| U0 | ❌ | ❌ | ❌ |
| U1 | ✅ | ❌ | ❌ |
| U2 | ❌ | ✅ | ❌ |
| U3 | ❌ | ❌ | ✅ |
| U123 | ✅ | ✅ | ✅ |

## Matriz — Validación de UI (ambos flujos)

Para cada usuario U0/U1/U2/U3/U123, abrir el trámite en ambos flujos y verificar:

### Paso 1 (Datos del trámite)

- Si NO tiene `write_tramite_datos_tramite`:
  - Inputs/selects del paso se ven **deshabilitados/readonly**.
  - Acciones de mutación del paso (agregar/editar/eliminar asociados, cambiar tipo principal, guardar servicios, etc.) **no deben ejecutarse** (idealmente no visibles o deshabilitadas).
  - Debe poder **navegar** al siguiente paso (no quedar bloqueado por validaciones de “required”).

- Si SÍ tiene `write_tramite_datos_tramite`:
  - Puede editar campos del paso 1 y guardar.

### Paso 2 (Asignación gestor)

- Si NO tiene `write_tramite_asigna_gestor`:
  - Inputs/selects del paso 2 **solo lectura**.
  - No debe poder “Guardar” cambios del paso 2.

- Si SÍ tiene `write_tramite_asigna_gestor`:
  - Puede guardar cambios del paso 2.

### Paso 3 (Pago derechos + Dropzone)

- Si NO tiene `write_tramite_pago_derechos` **o** NO tiene `can_upload_dropzone_pago_derechos`:
  - La zona debe comportarse como **readonly**: no debe permitir subir (ideal: Dropzone y botón de subir no visibles).
  - La galería/preview de documentos existentes debe seguir siendo visible (solo lectura).
  - Botón/acción de “Aprobar” (si existe en ese flujo) **no aparece**.

- Si SÍ tiene `write_tramite_pago_derechos` **y** `can_upload_dropzone_pago_derechos`:
  - Dropzone visible y funcional (sujeto a estatus y guardas del sistema).
  - Acciones permitidas (sujetas a estatus).

### Verificación de etiquetas de permiso (modo debug)

Estas vistas imprimen etiquetas de auditoría (`perm_audit_tag`) **solo para Super Admin**. Sirven para confirmar rápidamente qué permiso gobierna cada zona.

- En Step 3 (Dropzone “Documentos de Derechos”), debe aparecer la etiqueta del permiso:
  - `can_upload_dropzone_pago_derechos`

Nota: si tu toggle de debug usa `localStorage.debugMode`, actívalo para que se muestren las etiquetas.

## Validación específica — Flujo nuevo (JS)

En el flujo nuevo:

- Para pasos readonly, al intentar guardar:
  - Debe aparecer el mensaje: “Este paso es solo lectura.”
  - No debe disparar request de guardado.

- Navegación:
  - Para pasos readonly, la validación no debe bloquear el “Next” por campos requeridos deshabilitados.

## Matriz — Validación server-side (bloqueo real)

Objetivo: aunque el usuario fuerce requests directos (POST/AJAX), el backend debe negar mutaciones sin el `write_*` correcto.

Recomendación práctica: usar DevTools → Network y repetir el request con el usuario sin permiso (o intentar la acción desde la UI y confirmar la respuesta).

### Endpoints críticos (flujo nuevo)

- Paso 1 (debe exigir `write_tramite_datos_tramite`):
  - `update_save`
  - `services_add`
  - `services_update`
  - `services_delete`
  - `principal_update_tipo`

- Paso 2 (debe exigir `write_tramite_asigna_gestor`):
  - `update_gestor_save`

- Paso 3 (debe exigir `write_tramite_pago_derechos`):
  - `update_derechos_save`

### Endpoints críticos (flujo clásico)

- Paso 1: `update_save` (y acciones del paso 1)
- Paso 2: `update_gestor_save`
- Paso 3: `update_derechos_save`
- Upload: `upload_comprobante` (debe exigir `write_tramite_pago_derechos`)

### Expected

- Sin el `write_*` correcto:
  - Respuesta denegada (típicamente HTTP 403) en endpoints JSON/AJAX.
  - No debe persistir ningún cambio.

## Caso adicional — bloqueo por estatus (sanity)

Probar al menos 1 trámite que el negocio considere “aprobado”/en paso >= 4:

- Aun con U123, pasos 1–3 deben quedar solo lectura si así lo dicta el estatus (reglas existentes del sistema).

## Registro rápido de resultados

| Caso | Flujo | Usuario | Resultado | Observaciones |
|---|---|---|---|---|
| UI Step1 readonly | Clásico/Nuevo | U0 | ✅/❌ | |
| UI Step1 edit | Clásico/Nuevo | U1 | ✅/❌ | |
| UI Step2 readonly | Clásico/Nuevo | U0/U1/U3 | ✅/❌ | |
| UI Step3 sin Dropzone | Clásico/Nuevo | U0/U1/U2 | ✅/❌ | |
| UI Step3 readonly por falta de `can_upload_dropzone_pago_derechos` | Clásico/Nuevo | (usuario sin ese permiso) | ✅/❌ | |
| Backend deny Step1 POST | Nuevo | U0/U2/U3 | ✅/❌ | |
| Backend deny Step3 upload | Clásico | U0/U1/U2 | ✅/❌ | |
| Backend deny Step3 upload (sin `can_upload_dropzone_pago_derechos`) | Clásico/Nuevo | (usuario sin ese permiso) | ✅/❌ | |

---

Ver también: [QA_TRAMITES_READONLY_STEPS_4_5.md](QA_TRAMITES_READONLY_STEPS_4_5.md)
