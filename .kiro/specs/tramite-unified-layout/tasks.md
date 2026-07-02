# Implementation Plan: Tramite Unified Layout

## Overview

Refactorización de la vista monolítica `tramites_layout_prototipo.php` (~10K líneas) en un layout unificado de 5 filas con 3 carriles cada una (formulario | documentos | notas). Se crean partials PHP independientes por paso, un CSS dedicado con prefijo `tul-`, y un módulo JS vanilla (IIFE) para AJAX. No se requieren cambios backend — todos los endpoints existentes se reutilizan.

## Tasks

- [x] 1. Create CSS foundation and base layout
  - [x] 1.1 Create `public/assets/src/styles/tramite_unified_layout.css` with all `tul-` prefixed styles
    - Define `.tul-container` base styles
    - Define `.tul-step-row` with left border accent and background tint per step (1-5)
    - Define `.tul-three-rail` CSS grid (3 columns with `gap: 12px`)
    - Define `.tul-rail`, `.tul-rail--form`, `.tul-rail--docs`, `.tul-rail--notes`
    - Define `.tul-phase-divider` separator styles
    - Define `.tul-accordion`, `.tul-accordion__trigger`, `.tul-accordion__body`, `.tul-accordion__icon` with `max-height` transition ≤ 300ms
    - Define responsive breakpoint `@media (max-width: 991px)` stacking columns vertically
    - Define compact typography: base font 13px, labels 11px, padding ≤ 8px, margins ≤ 12px
    - Define `.tul-readonly-badge`, `.tul-blocked-notice` indicator styles
    - Define `.tul-loading` button state styles
    - Define `.tul-notify` notification container styles (success/error)
    - _Requirements: 2.1, 2.2, 2.4, 3.1, 3.2, 3.3, 4.5, 5.1, 5.2, 5.3, 5.4, 10.1, 10.2, 10.3, 10.4_

- [x] 2. Create orchestrator view and step partials
  - [x] 2.1 Create `app/Views/deskapp/tramite_unified/index.php` orchestrator
    - Include the dedicated CSS via `<link>` tag
    - Include all 5 step partials using CodeIgniter `view()` calls
    - Add phase divider HTML between step 3 and step 4
    - Include the JS module via `<script>` tag at end
    - Pass `$viewData` array containing all prototype variables to each partial
    - Ensure zero inline `style` attributes in markup
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.3, 3.3, 10.1, 10.3_

  - [x] 2.2 Create `app/Views/deskapp/tramite_unified/_step1_row.php` partial
    - Extract step 1 form fields from existing `tramites_layout_prototipo.php`
    - Structure with `tul-step-row tul-step-row--1` and `tul-three-rail` grid
    - Left rail: form fields with `data-tul-save`, `data-tul-step="1"`, `data-tul-url` attributes
    - Center rail: document dropzone with `data-tul-dropzone`, `data-tul-upload-url` and gallery with `data-tul-gallery`
    - Right rail: bitácora/notes form with `data-tul-notes`, `data-tul-url`
    - Implement readonly mode: check `$prototypeStep1Form['canEdit']`, disable inputs and omit action buttons when false
    - Show `tul-readonly-badge` and `tul-blocked-notice` when appropriate
    - Wire endpoints: save → `/deskapp/tramitesn/update_save/{id}`, upload → `/deskapp/tramitesn/upload_step1_doc/{id}`, delete → `/deskapp/tramitesn/delete_step1_doc`, notes → `/deskapp/tramitesn/prototype_evidencias_add/{id}`
    - _Requirements: 1.1, 2.1, 6.1, 7.1, 8.1, 8.2, 8.3, 8.4, 9.1, 9.2, 9.12_

  - [x] 2.3 Create `app/Views/deskapp/tramite_unified/_step2_row.php` partial
    - Extract step 2 form fields (gestor + derechos) from existing view
    - Structure with `tul-step-row tul-step-row--2` and `tul-three-rail` grid
    - Left rail: two forms (gestor and derechos) each with own `data-tul-save` and `data-tul-url`
    - Center rail: comprobante upload/gallery with delete
    - Right rail: approval section (if `$prototypeCanApproveStep2`)
    - Implement readonly mode per `$prototypeStep2Form['canEdit']`, `canUploadDocs`, `canDeleteDocs`
    - Wire endpoints: gestor save → `/deskapp/tramitesn/update_gestor_save/{id}`, derechos save → `/deskapp/tramitesn/update_derechos_save/{id}`, upload → `/deskapp/tramites/upload_comprobante/{id}`, delete → `/deskapp/tramites/delete_comprobante/{id}`, approve → `/deskapp/tramites/autorizar`
    - _Requirements: 1.1, 2.1, 6.1, 7.1, 8.1, 8.2, 8.3, 8.4, 9.3, 9.4, 9.5_

  - [x] 2.4 Create `app/Views/deskapp/tramite_unified/_step3_row.php` partial
    - Extract step 3 evidence upload section from existing view
    - Structure with `tul-step-row tul-step-row--3` and `tul-three-rail` grid
    - Left rail: minimal info / status indicators
    - Center rail: evidence upload dropzone (tipos: `tramite_recibido`, `acuse_recibo_cliente`) and gallery
    - Right rail: general bitácora (shared with step 1 notes endpoint)
    - Implement readonly mode per `$prototypeStep3Form['canUpload']`, `canDelete`
    - Wire endpoints: upload → `/deskapp/tramitesn/upload_pago_gestor/{id}`, notes → `/deskapp/tramitesn/prototype_evidencias_add/{id}`
    - _Requirements: 1.1, 2.1, 7.1, 8.1, 8.2, 8.3, 8.4, 9.6, 9.12_

  - [x] 2.5 Create `app/Views/deskapp/tramite_unified/_step4_row.php` partial (accordion)
    - Extract step 4 financial form from existing view
    - Wrap in `tul-accordion` container with `data-accordion`, initially collapsed (`aria-hidden="true"`, `max-height: 0`)
    - Header with `tul-accordion__trigger` and `tul-accordion__icon`
    - Body with `tul-three-rail` grid inside `tul-accordion__body`
    - Left rail: financial form fields + service costs table
    - Center rail: factura/comprobante upload (tipos: `factura_gestor`, `comprobante_pago`)
    - Right rail: step 4 notes form
    - Implement readonly mode per `$prototypeStep4Form['canView']`, `canEdit`
    - Wire endpoints: save → `/deskapp/tramitesn/update_pago_gestor/{id}`, upload → `/deskapp/tramitesn/upload_pago_gestor/{id}`, costs GET → `/deskapp/tramitesn/get_service_costs_by_tramite/{id}`, cost update → `/deskapp/tramitesn/update_service_cost`, notes → `/deskapp/tramitesn/prototype_step4_notes_add/{id}`
    - _Requirements: 1.1, 2.1, 4.2, 4.3, 4.4, 4.5, 7.1, 8.1, 8.2, 8.3, 8.4, 9.7, 9.8, 9.9, 9.13_

  - [x] 2.6 Create `app/Views/deskapp/tramite_unified/_step5_row.php` partial (accordion)
    - Extract step 5 billing/collection form from existing view
    - Wrap in `tul-accordion` container, initially collapsed
    - Body with `tul-three-rail` grid inside `tul-accordion__body`
    - Left rail: cobro form fields
    - Center rail: cobro upload/gallery with delete
    - Right rail: step 5 notes form
    - Implement readonly mode per `$prototypeStep5Form['canView']`, `canEdit`
    - Wire endpoints: save → `/deskapp/tramitesn/update_final_save/{id}`, upload → `/deskapp/tramitesn/upload_cobro_cliente/{id}`, delete → `/deskapp/tramitesn/delete_cobro_cliente`, get files → `/deskapp/tramitesn/getCobroClienteFiles/{id}`, notes → `/deskapp/tramitesn/prototype_step5_notes_add/{id}`
    - _Requirements: 1.1, 2.1, 4.2, 4.3, 4.4, 4.5, 7.1, 8.1, 8.2, 8.3, 8.4, 9.10, 9.11, 9.14_

- [x] 3. Checkpoint - Verify view structure
  - Ensure all 6 view files exist and are syntactically valid PHP. Ensure the orchestrator includes all 5 partials. Ensure no inline `style` attributes. Ask the user if questions arise.

- [x] 4. Create JavaScript module for AJAX interactions
  - [x] 4.1 Create `public/assets/src/scripts/tramite_unified.js` with IIFE structure and init
    - Set up IIFE pattern `(function(window, document) { ... })(window, document)`
    - Implement `TUL.init()` that calls all bind methods on DOMContentLoaded
    - Implement CSRF token reading from meta tags and `TUL.updateCsrf(xhr)` for token refresh after each AJAX response
    - Implement `TUL.ajax(method, url, data, callbacks)` utility using XMLHttpRequest
    - Implement `TUL.notify(type, message)` for success/error notifications (auto-dismiss)
    - Implement `TUL.handleError(xhr, context)` with handling for status 0 (network), 419 (CSRF expired), and general 4xx/5xx
    - Expose `window.TUL` for debugging
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [x] 4.2 Implement accordion toggle functionality
    - Implement `TUL.bindAccordions()` — find all `[data-accordion-trigger]` elements, bind click
    - Implement `TUL.toggleAccordion(section)` — toggle `is-expanded` class, animate `max-height`, update `aria-hidden`
    - Handle transitionend to remove inline max-height after expand
    - _Requirements: 4.3, 4.4, 4.5_

  - [x] 4.3 Implement AJAX save form handling
    - Implement `TUL.bindSaveForms()` — find all `[data-tul-save]` forms, bind submit event
    - Implement `TUL.handleSave(form, btn)` — disable button, add `tul-loading` class, serialize form data, POST to `data-tul-url`
    - On success: re-enable button, show success notification, update CSRF
    - On failure: re-enable button, retain form values, show error notification
    - Skip binding for forms with `data-tul-readonly` attribute
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [x] 4.4 Implement AJAX file upload handling
    - Implement `TUL.bindDropzones()` — find all `[data-tul-dropzone]` elements, bind change on `[data-tul-file-input]`
    - Implement `TUL.handleUpload(dropzone, file)` — show progress indicator, build FormData, POST to `data-tul-upload-url`
    - On success: append thumbnail to `[data-tul-gallery]` in same step, hide progress
    - On failure: remove partial preview, show error notification
    - Update CSRF token after each upload
    - _Requirements: 7.1, 7.2, 7.4, 7.5_

  - [x] 4.5 Implement AJAX delete and notes handling
    - Implement `TUL.bindDeleteButtons()` — find all `[data-tul-delete-btn]`, bind click with confirmation
    - Implement `TUL.handleDelete(btn)` — POST to `data-tul-delete-url`, on success remove `[data-tul-doc]` parent from DOM
    - Implement `TUL.bindNoteForms()` — find all `[data-tul-notes]` forms, bind submit
    - Implement note submission: POST note text, on success prepend note to notes list, clear textarea
    - _Requirements: 7.3, 9.12, 9.13, 9.14_

- [x] 5. Checkpoint - Verify JS module
  - Ensure `tramite_unified.js` has no syntax errors (use `node --check`). Verify IIFE wrapping and all TUL methods exist. Ask the user if questions arise.

- [x] 6. Wire controller to new view
  - [x] 6.1 Add a new controller method or modify view path in existing `prototipo_layout`
    - Option A: Add a `unified_layout()` method in `Tramitesn.php` that reuses all existing data-loading logic but returns `view('deskapp/tramite_unified/index', [...])` instead
    - Option B: Add a query param or route that triggers the unified view from the existing method
    - Pass all existing view variables (`prototypeStep1Form`, `prototypeStep1DocsForm`, `prototypeStep2Form`, etc.) wrapped in a `$viewData` array
    - Add the new route in `app/Config/Routes.php` if Option A is chosen
    - _Requirements: 1.3, 1.4_

- [x] 7. Final checkpoint - End-to-end verification
  - Ensure all files are created: 6 PHP views, 1 CSS, 1 JS. Verify no inline styles in any view partial. Verify all endpoint URLs in partials match requirement 9. Ensure all tests pass, ask the user if questions arise.

## Notes

- All backend endpoints already exist — zero backend changes needed
- The existing `tramites_layout_prototipo.php` has working JS logic that should be extracted and adapted, not rewritten from scratch
- CSS and JS are served directly from `public/assets/src/` — no build pipeline
- The controller method `prototipo_layout()` already computes all view variables with permissions — reuse its output
- CSRF token regeneration is handled per-request by CodeIgniter 4; the JS must update the token after every AJAX call
- Partials use CodeIgniter 4 `view()` includes, not raw `include`/`require`
- Property tests are not applicable for this UI/view refactoring task

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["2.1"] },
    { "id": 2, "tasks": ["2.2", "2.3", "2.4", "2.5", "2.6"] },
    { "id": 3, "tasks": ["4.1"] },
    { "id": 4, "tasks": ["4.2", "4.3", "4.4", "4.5"] },
    { "id": 5, "tasks": ["6.1"] }
  ]
}
```
