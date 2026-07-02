# Technical Design Document

## Overview

Este documento detalla la arquitectura para la vista unificada de trámite que reemplaza la navegación por pestañas con un layout de 5 filas (una por paso), cada una con 3 carriles (formulario | documentos | notas). La implementación se basa en la descomposición de la vista monolítica existente (`tramites_layout_prototipo.php`) en partials independientes, un archivo CSS dedicado con prefijo `tul-`, y un módulo JavaScript vanilla que maneja las operaciones AJAX de guardado y carga de archivos.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│  Controller: Tramitesn.php (método existente reutilizado)       │
│  → Carga datos, evalúa permisos server-side, pasa a vista      │
└──────────────────────────────┬──────────────────────────────────┘
                               │ view variables
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│  Orchestrator: app/Views/deskapp/tramite_unified/index.php      │
│  ├── _step1_row.php  (Información General)                      │
│  ├── _step2_row.php  (Gestión y Derechos)                       │
│  ├── _step3_row.php  (Evidencias Finales)                       │
│  ├── Phase Divider                                              │
│  ├── _step4_row.php  (Pago a Gestor)        ← collapsed        │
│  └── _step5_row.php  (Cobro a Cliente)      ← collapsed        │
└──────────────────────────────┬──────────────────────────────────┘
                               │
          ┌────────────────────┼────────────────────┐
          ▼                    ▼                    ▼
┌──────────────┐   ┌──────────────────┐   ┌─────────────────┐
│ CSS dedicado │   │ JS module único  │   │ Endpoints exist.│
│ tul-* prefix │   │ IIFE pattern     │   │ Sin cambios     │
│ Grid + Acc.  │   │ AJAX save/upload │   │ Backend intacto │
└──────────────┘   └──────────────────┘   └─────────────────┘
```

## Components and Interfaces

### 1. Orchestrator View (`index.php`)

El archivo principal que incluye los 5 partials y configura la estructura general de la página.

```php
<?php
// app/Views/deskapp/tramite_unified/index.php
// Recibe todas las view variables del controller
?>
<link rel="stylesheet" href="<?= base_url('assets/src/styles/tramite_unified_layout.css') ?>">

<div class="tul-container">
    <!-- Pasos operativos (expandidos) -->
    <?= view('deskapp/tramite_unified/_step1_row', $viewData) ?>
    <?= view('deskapp/tramite_unified/_step2_row', $viewData) ?>
    <?= view('deskapp/tramite_unified/_step3_row', $viewData) ?>

    <!-- Divisor de fase -->
    <div class="tul-phase-divider">
        <span class="tul-phase-divider__label">Fase Financiera</span>
    </div>

    <!-- Pasos financieros (colapsados en acordeón) -->
    <?= view('deskapp/tramite_unified/_step4_row', $viewData) ?>
    <?= view('deskapp/tramite_unified/_step5_row', $viewData) ?>
</div>

<script src="<?= base_url('assets/src/js/tramite_unified.js') ?>"></script>
```

### 2. Step Partial (`_stepN_row.php`)

Cada partial sigue la misma estructura de 3 carriles. Ejemplo para paso 1:

```php
<?php
// app/Views/deskapp/tramite_unified/_step1_row.php
$canEdit = $prototypeStep1Form['canEdit'] ?? false;
$blockedReason = $prototypeStep1Form['blockedReason'] ?? null;
?>
<section class="tul-step-row tul-step-row--1" data-step-row="1">
    <header class="tul-step-row__header">
        <h3 class="tul-step-row__title">Paso 1 — Información General</h3>
        <?php if (!$canEdit): ?>
            <span class="tul-readonly-badge">
                <i class="icon-lock"></i> Solo lectura
            </span>
        <?php endif; ?>
    </header>

    <div class="tul-three-rail">
        <!-- Carril izquierdo: Formulario -->
        <div class="tul-rail tul-rail--form" data-rail="form">
            <?php if (!$canEdit && $blockedReason): ?>
                <div class="tul-blocked-notice"><?= esc($blockedReason) ?></div>
            <?php endif; ?>
            <!-- Campos del formulario paso 1 -->
        </div>

        <!-- Carril centro: Documentos -->
        <div class="tul-rail tul-rail--docs" data-rail="docs">
            <!-- Dropzone y galería de documentos -->
        </div>

        <!-- Carril derecho: Notas/Bitácora -->
        <div class="tul-rail tul-rail--notes" data-rail="notes">
            <!-- Bitácora general -->
        </div>
    </div>
</section>
```

### 3. Accordion Wrapper (para pasos 4 y 5)

Los pasos financieros se envuelven en un contenedor colapsable:

```php
<?php // Dentro de _step4_row.php y _step5_row.php ?>
<section class="tul-step-row tul-step-row--4 tul-accordion" data-step-row="4" data-accordion>
    <header class="tul-step-row__header tul-accordion__trigger" data-accordion-trigger>
        <h3 class="tul-step-row__title">Paso 4 — Pago a Gestor</h3>
        <span class="tul-accordion__icon"></span>
    </header>

    <div class="tul-accordion__body" data-accordion-body aria-hidden="true">
        <div class="tul-three-rail">
            <!-- 3 carriles -->
        </div>
    </div>
</section>
```

### 4. CSS Architecture (`tramite_unified_layout.css`)

```css
/* === Base Layout === */
.tul-container {
    max-width: 100%;
    padding: 8px;
}

.tul-step-row {
    margin-bottom: 12px;
    border-left: 4px solid;
    border-radius: 4px;
    padding: 8px 12px;
}

/* === Color Accents === */
.tul-step-row--1 { border-left-color: #123b66; background-color: #eef5ff; }
.tul-step-row--2 { border-left-color: #1d5f8f; background-color: #f0f7ff; }
.tul-step-row--3 { border-left-color: #2878b0; background-color: #f3f9ff; }
.tul-step-row--4 { border-left-color: #0f766e; background-color: #edfaf7; }
.tul-step-row--5 { border-left-color: #0d9488; background-color: #e8f9f8; }

/* === Three-Rail Grid === */
.tul-three-rail {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
}

@media (max-width: 991px) {
    .tul-three-rail {
        grid-template-columns: 1fr;
    }
}

/* === Dense Typography === */
.tul-step-row {
    font-size: 13px;
}

.tul-rail label {
    font-size: 11px;
    margin-bottom: 2px;
}

.tul-rail input,
.tul-rail select,
.tul-rail textarea {
    font-size: 13px;
    padding: 4px 8px;
}

/* === Accordion === */
.tul-accordion__body {
    overflow: hidden;
    max-height: 0;
    transition: max-height 300ms ease;
}

.tul-accordion.is-expanded .tul-accordion__body {
    max-height: 2000px;
}

/* === Phase Divider === */
.tul-phase-divider {
    border-top: 2px solid #d1d5db;
    margin: 16px 0;
    padding-top: 8px;
    text-align: center;
}
```

### 5. JavaScript Module (`tramite_unified.js`)

```javascript
// public/assets/src/js/tramite_unified.js
;(function(window, document) {
    'use strict';

    var TUL = {
        csrfName: null,
        csrfHash: null,

        init: function() {
            this.csrfName = document.querySelector('meta[name="csrf-token-name"]')?.content;
            this.csrfHash = document.querySelector('meta[name="csrf-token-hash"]')?.content;
            this.bindAccordions();
            this.bindSaveForms();
            this.bindDropzones();
            this.bindDeleteButtons();
            this.bindNoteForms();
        },

        // --- Accordion ---
        bindAccordions: function() { /* ... */ },
        toggleAccordion: function(section) { /* ... */ },

        // --- AJAX Save ---
        bindSaveForms: function() { /* ... */ },
        handleSave: function(form, btn) { /* ... */ },

        // --- AJAX Upload ---
        bindDropzones: function() { /* ... */ },
        handleUpload: function(dropzone, file) { /* ... */ },

        // --- AJAX Delete ---
        bindDeleteButtons: function() { /* ... */ },
        handleDelete: function(btn) { /* ... */ },

        // --- Notes ---
        bindNoteForms: function() { /* ... */ },

        // --- Utilities ---
        ajax: function(method, url, data, callbacks) { /* ... */ },
        notify: function(type, message) { /* ... */ },
        updateCsrf: function(xhr) { /* ... */ }
    };

    document.addEventListener('DOMContentLoaded', function() {
        TUL.init();
    });

    window.TUL = TUL;
})(window, document);
```

### 6. Controller → View Data Contract (Interfaces)

El controller pasa las siguientes variables a la vista orquestadora. Cada step partial accede a sus propias variables del scope:

```php
// Variables pasadas por el controller
[
    'prototypeTramiteId'       => int,
    'prototypeReadOnlyTramite' => array|null,
    'prototypeCanApproveStep2' => bool,
    'prototypeStep1Form'       => ['canEdit' => bool, 'blockedReason' => ?string, 'csrfName' => string, 'csrfHash' => string, 'urls' => [...], 'options' => [...], 'values' => [...]],
    'prototypeStep1ServicesForm' => ['canManageBase' => bool, 'canEditPrincipal' => bool, ...],
    'prototypeStep1DocsForm'   => ['canView' => bool, 'canUpload' => bool, 'canDelete' => bool, 'urls' => [...], 'documents' => [...], ...],
    'prototypeStep2Form'       => ['canEdit' => bool, 'canUploadDocs' => bool, 'canDeleteDocs' => bool, 'urls' => [...], 'values' => [...], 'docs' => [...]],
    'prototypeStep3Form'       => ['canUpload' => bool, 'canDelete' => bool, 'urls' => [...], 'docs' => [...]],
    'prototypeStep4Form'       => ['canView' => bool, 'canEdit' => bool, 'canUploadDocs' => bool, 'canDeleteDocs' => bool, 'urls' => [...], 'values' => [...], 'docs' => [...]],
    'prototypeStep5Form'       => ['canView' => bool, 'canEdit' => bool, 'canUploadDocs' => bool, 'canDeleteDocs' => bool, 'urls' => [...], 'values' => [...], 'docs' => [...]],
    'prototypeStep4NotesForm'  => ['canView' => bool, 'canAdd' => bool, 'urls' => [...], 'items' => [...]],
    'prototypeStep5NotesForm'  => ['canView' => bool, 'canAdd' => bool, 'urls' => [...], 'items' => [...]],
    'prototypeEvidenceForm'    => ['canView' => bool, 'canAdd' => bool, 'urls' => [...], 'items' => [...]],
]
```

### 7. AJAX Request/Response Contract

**Save Request:**
```
POST {step_save_url}
Content-Type: application/x-www-form-urlencoded
Body: csrf_token_name={hash}&field1=value1&field2=value2...
```

**Save Response (success):**
```json
{ "status": "success", "message": "Datos guardados" }
```

**Save Response (error):**
```json
{ "status": "error", "message": "Descripción del error" }
```

**Upload Request:**
```
POST {step_upload_url}
Content-Type: multipart/form-data
Body: csrf_token_name={hash}&file={binary}&tipo={tipo_documento}
```

**Upload Response (success):**
```json
{ "status": "success", "file": "filename.pdf", "path": "/uploads/..." }
```

**CSRF Token Refresh:**
Cada respuesta AJAX devuelve una cabecera o campo JSON con el nuevo CSRF hash. El módulo JS extrae este valor y lo usa en la siguiente petición (CodeIgniter 4.0.4 regenera el token en cada request).

```javascript
// En cada respuesta AJAX:
updateCsrf: function(xhr) {
    var newHash = xhr.getResponseHeader('X-CSRF-Hash');
    if (!newHash) {
        try {
            var json = JSON.parse(xhr.responseText);
            newHash = json.csrf_hash || json[this.csrfName];
        } catch(e) {}
    }
    if (newHash) {
        this.csrfHash = newHash;
        // Actualizar inputs hidden en todos los forms
        document.querySelectorAll('input[name="' + this.csrfName + '"]')
            .forEach(function(el) { el.value = newHash; });
    }
}
```

### 8. Endpoint Map (Step → URL)

| Paso | Acción | Endpoint |
|------|--------|----------|
| 1 | Save datos base | `POST /deskapp/tramitesn/update_save/{id}` |
| 1 | Upload doc | `POST /deskapp/tramitesn/upload_step1_doc/{id}` |
| 1 | Delete doc | `POST /deskapp/tramitesn/delete_step1_doc` |
| 1,3 | Nota bitácora | `POST /deskapp/tramitesn/prototype_evidencias_add/{id}` |
| 2 | Save gestor | `POST /deskapp/tramitesn/update_gestor_save/{id}` |
| 2 | Save derechos | `POST /deskapp/tramitesn/update_derechos_save/{id}` |
| 2 | Upload comprobante | `POST /deskapp/tramites/upload_comprobante/{id}` |
| 2 | Delete comprobante | `POST /deskapp/tramites/delete_comprobante/{id}` |
| 2 | Aprobar | `POST /deskapp/tramites/autorizar` |
| 3 | Upload evidencia | `POST /deskapp/tramitesn/upload_pago_gestor/{id}` (tipo: tramite_recibido, acuse_recibo_cliente) |
| 4 | Save financiero | `POST /deskapp/tramitesn/update_pago_gestor/{id}` |
| 4 | Upload factura | `POST /deskapp/tramitesn/upload_pago_gestor/{id}` (tipo: factura_gestor, comprobante_pago) |
| 4 | Get costs | `GET /deskapp/tramitesn/get_service_costs_by_tramite/{id}` |
| 4 | Update cost | `POST /deskapp/tramitesn/update_service_cost` |
| 4 | Nota | `POST /deskapp/tramitesn/prototype_step4_notes_add/{id}` |
| 5 | Save cobro | `POST /deskapp/tramitesn/update_final_save/{id}` |
| 5 | Upload cobro | `POST /deskapp/tramitesn/upload_cobro_cliente/{id}` |
| 5 | Delete cobro | `POST /deskapp/tramitesn/delete_cobro_cliente` |
| 5 | Get files | `GET /deskapp/tramitesn/getCobroClienteFiles/{id}` |
| 5 | Nota | `POST /deskapp/tramitesn/prototype_step5_notes_add/{id}` |

## Data Models

### Permission Flags por Step

El controller evalúa permisos server-side y genera flags booleanos. Estos flags determinan si los partials renderizan elementos de acción o no.

```php
// Estructura lógica de permisos por paso
$stepPermissions = [
    1 => [
        'canEditForm'    => bool,  // Formulario datos base
        'canUploadDocs'  => bool,  // Dropzone documentos
        'canDeleteDocs'  => bool,  // Botón eliminar doc
        'canManageTypes' => bool,  // CRUD tipos asociados
        'canAddNotes'    => bool,  // Bitácora
    ],
    2 => [
        'canEditGestor'    => bool,
        'canEditDerechos'  => bool,
        'canUploadDocs'    => bool,
        'canDeleteDocs'    => bool,
        'canApprove'       => bool,
    ],
    3 => [
        'canUpload'  => bool,
        'canDelete'  => bool,
    ],
    4 => [
        'canView'       => bool,
        'canEdit'       => bool,
        'canUploadDocs' => bool,
        'canDeleteDocs' => bool,
        'canAddNotes'   => bool,
    ],
    5 => [
        'canView'       => bool,
        'canEdit'       => bool,
        'canUploadDocs' => bool,
        'canDeleteDocs' => bool,
        'canAddNotes'   => bool,
    ],
];
```

### DOM Data Attributes

Cada elemento interactivo usa `data-*` attributes para que el JS localice los targets sin acoplarse a clases CSS:

```html
<!-- Formulario con save -->
<form data-tul-save data-tul-step="1" data-tul-url="/deskapp/tramitesn/update_save/123">
    <button type="submit" data-tul-save-btn>Guardar</button>
</form>

<!-- Dropzone -->
<div data-tul-dropzone data-tul-step="1" data-tul-upload-url="/deskapp/tramitesn/upload_step1_doc/123">
    <input type="file" data-tul-file-input>
</div>

<!-- Galería -->
<div data-tul-gallery data-tul-step="1">
    <div data-tul-doc data-tul-doc-id="456">
        <img src="...">
        <button data-tul-delete-btn data-tul-delete-url="/deskapp/tramitesn/delete_step1_doc">×</button>
    </div>
</div>

<!-- Notas -->
<form data-tul-notes data-tul-step="1" data-tul-url="/deskapp/tramitesn/prototype_evidencias_add/123">
    <textarea data-tul-note-input></textarea>
    <button type="submit" data-tul-note-btn>Agregar</button>
</form>
```

## Error Handling

### AJAX Error Strategy

1. **Network errors**: Si `xhr.status === 0` o timeout, mostrar notificación "Sin conexión al servidor" y NO limpiar el formulario.
2. **HTTP 419 (CSRF mismatch)**: Mostrar "Sesión expirada. Recarga la página." — no reintentar automáticamente.
3. **HTTP 4xx/5xx**: Mostrar el `message` del JSON de respuesta o un genérico "Error del servidor".
4. **Parsing error**: Si la respuesta no es JSON válido, mostrar "Respuesta inesperada del servidor".

```javascript
handleError: function(xhr, context) {
    var msg = 'Error del servidor';
    if (xhr.status === 0) {
        msg = 'Sin conexión al servidor. Verifica tu red.';
    } else if (xhr.status === 419) {
        msg = 'Sesión expirada. Recarga la página para continuar.';
    } else {
        try {
            var json = JSON.parse(xhr.responseText);
            msg = json.message || msg;
        } catch(e) {}
    }
    this.notify('error', msg);
    // Re-enable buttons
    if (context.btn) {
        context.btn.disabled = false;
        context.btn.classList.remove('tul-loading');
    }
}
```

### Upload Error Handling

- Si el upload falla, remover cualquier preview parcial que se haya agregado optimistamente.
- Si el archivo excede el tamaño máximo del server, mostrar el error de PHP sin crash.
- El dropzone mantiene su estado disponible para reintentar.

### Readonly Mode Error Prevention

- Los campos `disabled` no envían datos en POST nativos.
- El JS verifica `canEdit` via `data-tul-readonly` antes de bindear eventos de save.
- Server-side: las rutas existentes ya validan permisos — la UI es una barrera adicional, no la única.

## Accordion Behavior

### Estado inicial
- Pasos 1-3: el contenido `.tul-three-rail` está visible (sin wrapper de acordeón).
- Pasos 4-5: el contenido está dentro de `.tul-accordion__body` con `max-height: 0`.

### Toggle
```javascript
toggleAccordion: function(section) {
    var isExpanded = section.classList.contains('is-expanded');
    var body = section.querySelector('[data-accordion-body]');

    if (isExpanded) {
        body.style.maxHeight = body.scrollHeight + 'px';
        // Force reflow
        body.offsetHeight;
        body.style.maxHeight = '0';
        section.classList.remove('is-expanded');
        body.setAttribute('aria-hidden', 'true');
    } else {
        body.style.maxHeight = body.scrollHeight + 'px';
        section.classList.add('is-expanded');
        body.setAttribute('aria-hidden', 'false');
        // After transition, remove inline to allow content resize
        body.addEventListener('transitionend', function handler() {
            body.style.maxHeight = '';
            body.removeEventListener('transitionend', handler);
        });
    }
}
```

### Duración de animación
- CSS `transition: max-height 300ms ease` — no excede 300ms.

## File Structure

```
app/Views/deskapp/tramite_unified/
├── index.php              ← Orchestrator (incluye los 5 partials)
├── _step1_row.php         ← Paso 1: Información General
├── _step2_row.php         ← Paso 2: Gestión y Derechos
├── _step3_row.php         ← Paso 3: Evidencias Finales
├── _step4_row.php         ← Paso 4: Pago a Gestor (accordion)
└── _step5_row.php         ← Paso 5: Cobro a Cliente (accordion)

public/assets/src/styles/
└── tramite_unified_layout.css  ← Todos los estilos con prefijo tul-

public/assets/src/js/
└── tramite_unified.js          ← Módulo IIFE, vanilla JS
```

## Testing Strategy

### Unit Tests (Example-Based)
- **View structure**: Verify each partial file exists and the orchestrator includes all 5.
- **Accordion initial state**: Steps 1-3 expanded, steps 4-5 collapsed in initial HTML.
- **Phase divider placement**: Divider exists between step 3 and step 4.
- **CSS values**: Font size ≤ 13px, padding ≤ 8px, margins ≤ 12px.
- **Responsive breakpoint**: CSS media query at 991px stacks columns.

### Integration Tests
- **Controller data passing**: Verify the controller passes all expected view variables.
- **Permission server-side enforcement**: With canEdit=false, confirm action elements absent from rendered HTML.
- **CSRF token lifecycle**: Save → get new token → save again succeeds.

### Manual/Visual Tests
- **Color accent visibility**: Each step row has correct border and background at a glance.
- **Accordion animation**: Smooth expand/collapse at ≤ 300ms.
- **Responsive stacking**: At < 992px viewport, columns stack vertically.
- **Readonly badge**: Lock icon visible when user has no edit permission.

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Three-Rail Structure per Step

For any step N in {1, 2, 3, 4, 5}, the rendered Step_Partial SHALL contain exactly three rail containers with data attributes `data-rail="form"`, `data-rail="docs"`, and `data-rail="notes"`, in that order.

**Validates: Requirements 2.1**

### Property 2: Zero Inline Styles

For any HTML output produced by the orchestrator view and all included Step_Partials, the rendered markup SHALL contain zero occurrences of `style="` attribute declarations.

**Validates: Requirements 2.3, 10.3**

### Property 3: Step Color Mapping

For any step N in {1, 2, 3, 4, 5}, the dedicated CSS file SHALL define a rule `.tul-step-row--{N}` that sets `border-left-color` to the specified accent hex value and `background-color` to the specified tint hex value for that step.

**Validates: Requirements 3.1, 3.2**

### Property 4: AJAX Save Lifecycle

For any step N with a save form, when the save handler is invoked: (a) the submit button SHALL be disabled and display a loading state, (b) a POST request SHALL be sent to the URL in `data-tul-url`, (c) on success the button SHALL be re-enabled and a success notification displayed without page reload, (d) on failure the button SHALL be re-enabled, form values SHALL remain unchanged, and an error notification SHALL display the server message.

**Validates: Requirements 6.1, 6.2, 6.3, 6.4**

### Property 5: AJAX Upload Lifecycle

For any step N with an upload dropzone, when a file is selected: (a) a progress indicator SHALL appear on the dropzone, (b) a POST request SHALL be sent to the URL in `data-tul-upload-url` with the file as multipart data, (c) on success a thumbnail SHALL be appended to the gallery without page reload, (d) on failure any partial preview SHALL be removed and an error notification displayed.

**Validates: Requirements 7.1, 7.2, 7.4, 7.5**

### Property 6: Readonly Enforcement

For any step N where the server-side permission flag `canEdit` (or equivalent) is false, the rendered HTML for that step SHALL: (a) contain only disabled form inputs, (b) contain zero save buttons, zero delete buttons, and zero dropzone elements, and (c) contain a visible readonly indicator (lock badge or message).

**Validates: Requirements 8.1, 8.2, 8.3, 8.4**

### Property 7: Endpoint URL Correctness

For any step N and action type (save, upload, delete, notes), the URL embedded in the corresponding `data-tul-url` or `data-tul-upload-url` attribute SHALL match the endpoint specified in Requirement 9 for that step and action combination.

**Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 9.8, 9.9, 9.10, 9.11, 9.12, 9.13, 9.14**

### Property 8: CSS Namespace Isolation

For any CSS rule defined in `tramite_unified_layout.css`, all class selectors SHALL use the prefix `tul-` to avoid conflicts with existing global styles.

**Validates: Requirements 10.4**
