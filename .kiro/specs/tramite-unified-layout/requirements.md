# Requirements Document

## Introduction

Refactorización de la vista de trámite unificado que reemplaza la navegación por pestañas con un layout de 5 filas (una por paso), cada una con 3 carriles (formulario | documentos | notas). La vista usa un diseño compacto orientado a power users, con AJAX para guardado y carga de archivos sin recarga de página. Los pasos operativos (1-3) se muestran expandidos por defecto y los pasos financieros (4-5) colapsados en acordeón. La vista existente monolítica (~10K líneas) se descompone en partials independientes por paso.

## Glossary

- **Unified_Layout**: Vista principal del trámite que muestra los 5 pasos simultáneamente en filas independientes con 3 carriles cada una
- **Step_Row**: Bloque visual que representa un paso completo del trámite, compuesto por tres columnas (formulario, documentos, notas)
- **Three_Rail_Grid**: Distribución de columnas dentro de cada Step_Row: carril izquierdo (formulario), carril centro (documentos/checklist), carril derecho (bitácora/notas)
- **Phase_Divider**: Separador visual entre la fase operativa (pasos 1-3) y la fase financiera (pasos 4-5)
- **Step_Partial**: Archivo PHP parcial independiente que contiene el HTML de un paso específico (_step1_row.php, _step2_row.php, etc.)
- **Accordion_Panel**: Contenedor colapsable que envuelve los pasos 4 y 5, permitiendo expandir/contraer su contenido
- **Color_Accent**: Borde lateral y fondo de tono suave que identifica visualmente a qué paso pertenece una fila
- **AJAX_Save**: Operación de guardado de formulario mediante petición asíncrona sin recarga de página
- **AJAX_Upload**: Operación de carga de archivo mediante petición asíncrona con preview inmediato
- **Readonly_Mode**: Estado de la vista donde los campos de formulario están deshabilitados y los botones de acción se eliminan del DOM

## Requirements

### Requirement 1: Decomposición en Partials por Paso

**User Story:** As a developer, I want each step rendered from its own partial file, so that the monolithic view is maintainable and testable independently.

#### Acceptance Criteria

1. THE Unified_Layout SHALL render each step by including a dedicated Step_Partial file from the directory `app/Views/deskapp/tramite_unified/`
2. WHEN the Unified_Layout is loaded, THE Unified_Layout SHALL include `_step1_row.php`, `_step2_row.php`, `_step3_row.php`, `_step4_row.php`, and `_step5_row.php` as view partials
3. THE Unified_Layout SHALL pass the tramite data context to each Step_Partial via CodeIgniter view variables
4. THE Unified_Layout SHALL maintain a single parent layout file (`tramite_unified_layout.php`) that orchestrates the inclusion of all Step_Partial files

### Requirement 2: Three-Rail Layout por Step Row

**User Story:** As a power user, I want to see form data, documents, and notes side-by-side for each step, so that I can work efficiently without scrolling between sections.

#### Acceptance Criteria

1. THE Three_Rail_Grid SHALL display three columns within each Step_Row: formulario (left), documentos (center), and notas (right)
2. THE Three_Rail_Grid SHALL use CSS grid or flexbox layout defined in `public/assets/src/styles/tramite_unified_layout.css`
3. THE Unified_Layout SHALL contain zero inline style attributes in the HTML markup
4. WHEN the viewport width is below 992px, THE Three_Rail_Grid SHALL stack the three columns vertically in order: formulario, documentos, notas

### Requirement 3: Color Coding por Paso

**User Story:** As a user, I want each step visually distinguished by color, so that I can quickly identify which phase I am looking at.

#### Acceptance Criteria

1. THE Unified_Layout SHALL apply a left border accent color to each Step_Row: paso 1 uses `#123b66`, paso 2 uses `#1d5f8f`, paso 3 uses `#2878b0`, paso 4 uses `#0f766e`, paso 5 uses `#0d9488`
2. THE Unified_Layout SHALL apply a subtle background tint to each Step_Row: paso 1 uses `#eef5ff`, paso 2 uses `#f0f7ff`, paso 3 uses `#f3f9ff`, paso 4 uses `#edfaf7`, paso 5 uses `#e8f9f8`
3. THE Phase_Divider SHALL render a visible horizontal separator between paso 3 and paso 4 to distinguish the operational phase from the financial phase

### Requirement 4: Accordion Comportamiento por Defecto

**User Story:** As a power user, I want operational steps expanded and financial steps collapsed by default, so that I focus on the current workflow without visual overload.

#### Acceptance Criteria

1. WHEN the Unified_Layout is loaded, THE Unified_Layout SHALL render steps 1, 2, and 3 in expanded state
2. WHEN the Unified_Layout is loaded, THE Unified_Layout SHALL render steps 4 and 5 in collapsed state within Accordion_Panel containers
3. WHEN a user clicks on a collapsed Accordion_Panel header, THE Accordion_Panel SHALL expand to reveal the full Step_Row content
4. WHEN a user clicks on an expanded Accordion_Panel header, THE Accordion_Panel SHALL collapse to hide the Step_Row content
5. THE Accordion_Panel SHALL animate the expand/collapse transition with a duration no greater than 300ms

### Requirement 5: Compact Dense Layout

**User Story:** As a power user, I want a dense interface with reduced spacing and smaller typography, so that I can see more information on screen simultaneously.

#### Acceptance Criteria

1. THE Unified_Layout SHALL use a base font size no larger than 13px for form labels and field values
2. THE Unified_Layout SHALL use reduced vertical padding (no more than 8px) between form fields within each Step_Row
3. THE Unified_Layout SHALL use reduced margins (no more than 12px) between Step_Row containers
4. THE Unified_Layout SHALL define all spacing and typography rules in the dedicated CSS file `public/assets/src/styles/tramite_unified_layout.css`

### Requirement 6: AJAX Save sin Recarga

**User Story:** As a user, I want to save form data without page reloads, so that I maintain my context and scroll position while working.

#### Acceptance Criteria

1. WHEN a user clicks a save button in any Step_Row, THE Unified_Layout SHALL submit the form data via an AJAX POST request to the corresponding endpoint
2. WHEN an AJAX_Save request succeeds, THE Unified_Layout SHALL display a success notification without reloading the page
3. IF an AJAX_Save request fails, THEN THE Unified_Layout SHALL display an error message with the server response and retain the user-entered form values
4. WHILE an AJAX_Save request is in progress, THE Unified_Layout SHALL disable the save button and show a loading indicator

### Requirement 7: AJAX Upload sin Recarga

**User Story:** As a user, I want to upload and delete documents without page reloads, so that I can manage files quickly while staying in context.

#### Acceptance Criteria

1. WHEN a user drops or selects a file in a dropzone, THE Unified_Layout SHALL upload the file via an AJAX POST request to the step-specific upload endpoint
2. WHEN an AJAX_Upload request succeeds, THE Unified_Layout SHALL append a preview thumbnail of the uploaded file to the document gallery without reloading the page
3. WHEN a user clicks a delete button on an uploaded document, THE Unified_Layout SHALL send an AJAX POST request to the step-specific delete endpoint and remove the thumbnail from the gallery on success
4. IF an AJAX_Upload request fails, THEN THE Unified_Layout SHALL display an error message and remove any partial preview from the gallery
5. WHILE an AJAX_Upload is in progress, THE Unified_Layout SHALL display a progress indicator on the dropzone

### Requirement 8: Readonly Mode basado en Permisos

**User Story:** As an administrator, I want users without edit permissions to see data in readonly mode with action buttons removed, so that unauthorized modifications are prevented at the UI level.

#### Acceptance Criteria

1. WHILE the user lacks edit permission for a step, THE Unified_Layout SHALL render all form fields in that Step_Row as disabled inputs
2. WHILE the user lacks edit permission for a step, THE Unified_Layout SHALL omit save buttons, delete buttons, and upload dropzones from the rendered HTML of that Step_Row
3. WHILE the user lacks edit permission for a step, THE Unified_Layout SHALL display a visual indicator (lock icon or message) communicating that the step is read-only
4. THE Unified_Layout SHALL evaluate permissions server-side and exclude action elements from the HTML response rather than hiding them with CSS or JavaScript

### Requirement 9: Endpoint Integration por Paso

**User Story:** As a developer, I want each step's AJAX calls to target the correct existing endpoints, so that backend functionality is preserved without modifications.

#### Acceptance Criteria

1. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/update_save/{id}` for saving step 1 base data
2. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/upload_step1_doc/{id}` and `POST /deskapp/tramitesn/delete_step1_doc` for step 1 document management
3. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/update_gestor_save/{id}` and `POST /deskapp/tramitesn/update_derechos_save/{id}` for step 2 form saves
4. THE Unified_Layout SHALL use `POST /deskapp/tramites/upload_comprobante/{id}` and `POST /deskapp/tramites/delete_comprobante/{id}` for step 2 document management
5. THE Unified_Layout SHALL use `POST /deskapp/tramites/autorizar` for step 2 approval action
6. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/upload_pago_gestor/{id}` with tipos `tramite_recibido` and `acuse_recibo_cliente` for step 3 uploads
7. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/update_pago_gestor/{id}` for step 4 financial form save
8. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/upload_pago_gestor/{id}` with tipos `factura_gestor` and `comprobante_pago` for step 4 document uploads
9. THE Unified_Layout SHALL use `GET /deskapp/tramitesn/get_service_costs_by_tramite/{id}` and `POST /deskapp/tramitesn/update_service_cost` for step 4 service costs
10. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/update_final_save/{id}` for step 5 form save
11. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/upload_cobro_cliente/{id}`, `POST /deskapp/tramitesn/delete_cobro_cliente`, and `GET /deskapp/tramitesn/getCobroClienteFiles/{id}` for step 5 document management
12. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/prototype_evidencias_add/{id}` for the general bitácora in steps 1 and 3
13. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/prototype_step4_notes_add/{id}` for step 4 notes
14. THE Unified_Layout SHALL use `POST /deskapp/tramitesn/prototype_step5_notes_add/{id}` for step 5 notes

### Requirement 10: Dedicated CSS File

**User Story:** As a developer, I want all layout styles in a single dedicated CSS file, so that styles are maintainable, cacheable, and separate from markup.

#### Acceptance Criteria

1. THE Unified_Layout SHALL load a single dedicated stylesheet from `public/assets/src/styles/tramite_unified_layout.css`
2. THE Unified_Layout SHALL define all Three_Rail_Grid, Step_Row, Phase_Divider, Accordion_Panel, and Color_Accent styles within the dedicated CSS file
3. THE Unified_Layout SHALL contain no inline `style` attributes in any partial or parent layout file
4. THE Unified_Layout SHALL use CSS class selectors prefixed with `tul-` (tramite-unified-layout) to avoid conflicts with existing global styles
