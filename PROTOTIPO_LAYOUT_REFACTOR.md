> # ⚠️ DOCUMENTO SUPERADO
>
> **Este documento quedó obsoleto y fue superado por el spec oficial:**
> `.kiro/specs/tramite-unified-layout/` (`requirements.md`, `design.md`, `tasks.md`).
>
> La implementación final vive en la ruta **`/deskapp/tramitesn/unified-layout?tramite_id={id}`**
> (no en `prototipo-layout`). El detalle histórico consolidado de todo el desarrollo está en
> **`HISTORIAL_DESARROLLO.md`**.
>
> Se conserva únicamente como referencia histórica del diseño inicial. **No usar como fuente de verdad.**
>
> _Nota de paleta:_ el Paso 5 final se cambió a índigo `#4338ca` (no teal) para diferenciarlo del Paso 4.

---

# Refactorización del Prototipo Layout Unificado

> Documento de seguimiento para la pantalla `/deskapp/tramitesn/prototipo-layout`
> Última actualización: 2026-06-14

## Objetivo

Convertir la pantalla prototipo en una **vista unificada** donde los 5 pasos del trámite se muestran simultáneamente, sin navegación entre pestañas/pasos. Cada paso ocupa su propia **fila de 3 carriles**:

- **Carril izquierdo**: formulario principal + interacción con datos
- **Carril centro**: checklist/estado + uploader de documentos del paso
- **Carril derecho**: bitácora/notas del paso

Cada fila tiene un **color de acento propio** para identificación visual inmediata.

## Paleta de colores por paso

| Paso | Acento | Fondo | Concepto |
|------|--------|-------|----------|
| 1 | `#123b66` (azul marino oscuro) | `#eef5ff` | Información general |
| 2 | `#1d5f8f` (azul medio) | `#f0f7ff` | Gestión y derechos |
| 3 | `#2878b0` (azul claro) | `#f3f9ff` | Evidencias finales |
| 4 | `#0f766e` (verde) | `#edfaf7` | Pago a gestor |
| 5 | `#0d9488` (teal) | `#e8f9f8` | Cobro a cliente |

Divisor visual entre fase operativa (1-3) y fase financiera (4-5).

---

## Mapa de elementos por paso

### Paso 1 — Información General

**Carril izquierdo (formulario)**:
- Cliente (`cli_directo_id`) → dependiente: Ejecutivo (`cli_directo_ejecutivo_id`)
- Contrato, Unidad, Serie, Placas
- Entidad (`entidad_id`)
- Observaciones
- Subflujo: Tipo principal (`tra_tipos_id`) — cambiar
- Subflujo: Tipos asociados — agregar/editar/eliminar
- Botón: "Guardar datos base"
- Endpoint: `POST /deskapp/tramitesn/update_save/{id}` con `current_step=1`

**Carril centro (documentos + checklist)**:
- Uploader documentos del expediente
  - Endpoint upload: `POST /deskapp/tramitesn/upload_step1_doc/{id}`
  - Endpoint delete: `POST /deskapp/tramitesn/delete_step1_doc`
  - Carpeta: `/assets/uploads/documentostatus/`
  - Tabla: `tra_doc_status`
  - Catálogo: `tra_tipo_documentos` cruzado por tipos ligados
- Checklist: completitud mínima (contrato + entidad), relación cliente-ejecutivo, riesgo de duplicado

**Carril derecho (bitácora)**:
- Notas generales del expediente (`tra_evidencias`)
- Endpoint: `POST /deskapp/tramitesn/prototype_evidencias_add/{id}`

### Paso 2 — Gestión y Pago de Derechos

**Carril izquierdo (formulario)**:
- Empresa gestora (`empresa_gestora_id`) → dependiente: Gestor (`gestor_id`)
  - Endpoint: `POST /deskapp/tramitesn/update_gestor_save/{id}`
- Pago de derechos: monto, sitio de pago, vigencia, forma de pago, referencia bancaria
  - Endpoint: `POST /deskapp/tramitesn/update_derechos_save/{id}`
- Panel de aprobación (cuando derechos completos + permiso): botón "Aprobar trámite"
  - Endpoint: `POST /deskapp/tramites/autorizar`
  - Acción: cambia `tra_status_id` a 23 (Pago a Gestor)

**Carril centro (documentos + checklist)**:
- Uploader comprobantes de derechos (línea de captura)
  - Endpoint upload: `POST /deskapp/tramites/upload_comprobante/{id}`
  - Endpoint delete: `POST /deskapp/tramites/delete_comprobante/{id}`
  - Carpeta: `/assets/uploads/pago_derechos/{id}/`
  - Tabla: `tra_pago_derechos`
- Checklist: asignación gestor, datos derechos, documentos, aprobación

**Carril derecho**:
- Estado de aprobación (ready/pendiente/ya aprobado)
- Info de vigencia y urgencia

### Paso 3 — Evidencias Finales (Cierre Documental)

**Carril izquierdo (formulario/uploader)**:
- Uploader evidencias finales
  - Endpoint upload: `POST /deskapp/tramitesn/upload_pago_gestor/{id}`
  - Endpoint delete: `POST /deskapp/tramitesn/delete_pago_gestor`
  - Tipos: `tramite_recibido`, `acuse_recibo_cliente`
  - Carpeta: `/assets/uploads/pago_gestor/{id}/`
  - Tabla: `tra_pago_gestor`
- Chips de estado: "Trámite Entregado por Gestor" ✓/✗, "Acuse de Recibo del Cliente" ✓/✗
- Gate: las 2 evidencias completas desbloquean paso 4

**Carril centro (checklist + gate)**:
- Checklist: asignación, derechos, documentos finales, aprobación
- Panel gate: estado de cierre documental

**Carril derecho**:
- Notas generales del expediente (mismo que paso 1)

---

### ═══ DIVISOR DE FASE: Operativa → Financiera ═══

---

### Paso 4 — Pago a Gestor

**Carril izquierdo (formulario financiero)**:
- Costos por servicio (lista editable por asociado)
  - Endpoint lectura: `GET /deskapp/tramitesn/get_service_costs_by_tramite/{id}`
  - Endpoint guardado: `POST /deskapp/tramitesn/update_service_cost`
- Campos financieros: costo_tramite, deposito_gestor, col_a_favor, num_factura_gestor, impuesto_gestoria, gestoria_comision, costo_paqueteria, gestor_total_pago
- Estatus: pago_gestor_st_id, status_doctos_gestor, reembolso_status_id
- Botón: "Guardar pago a gestor"
  - Endpoint: `POST /deskapp/tramitesn/update_pago_gestor/{id}`

**Carril centro (documentos)**:
- Uploader docs pago a gestor
  - Endpoint upload: `POST /deskapp/tramitesn/upload_pago_gestor/{id}`
  - Endpoint delete: `POST /deskapp/tramitesn/delete_pago_gestor`
  - Tipos: `factura_gestor`, `comprobante_pago`
  - Carpeta: `/assets/uploads/pago_gestor/{id}/`
  - Tabla: `tra_pago_gestor`

**Carril derecho (notas)**:
- Notas internas de Pago a gestor
- Endpoint: `POST /deskapp/tramitesn/prototype_step4_notes_add/{id}`

### Paso 5 — Cobro a Cliente

**Carril izquierdo (formulario cobro)**:
- Campos: id_give_cliente, numero_factura, numero_refactura, cobro_status_id, evidencia_cobro_txt
- Campos calculados: costo_gestoria (suma de costos asociados), costo_pago_cliente, comision_derechos, iva, costo_total
- Botón: "Guardar cobro a cliente"
  - Endpoint: `POST /deskapp/tramitesn/update_final_save/{id}`

**Carril centro (documentos)**:
- Uploader evidencias de cobro
  - Endpoint upload: `POST /deskapp/tramitesn/upload_cobro_cliente/{id}`
  - Endpoint delete: `POST /deskapp/tramitesn/delete_cobro_cliente`
  - Lectura: `GET /deskapp/tramitesn/getCobroClienteFiles/{id}`
  - Tipos: `parcial`, `completo`, `otro`
  - Carpeta: `/assets/uploads/cobro_cliente/{id}/`

**Carril derecho (notas)**:
- Notas internas de Cobro a cliente
- Endpoint: `POST /deskapp/tramitesn/prototype_step5_notes_add/{id}`

---

## Estado actual de la implementación

### ✅ Completado

1. **Routes.php** — rutas explícitas agregadas:
   - `POST tramitesn/prototype_evidencias_add/(:num)`
   - `POST tramitesn/prototype_step4_notes_add/(:num)`
   - `POST tramitesn/prototype_step5_notes_add/(:num)`
   - `POST tramitesn/upload_step1_doc/(:num)`
   - `POST tramitesn/delete_step1_doc`

2. **Controlador Tramitesn.php**:
   - Eliminado el redirect automático de paso 1→4 cuando `$stepActual === 4 && $activeStep <= 3`

3. **Vista `tramites_layout_prototipo.php`**:
   - `$isOperationalBasePhase = true` forzado siempre
   - `$useThreeRailLayout = true` forzado siempre
   - Paso 3 (`data-operational-step3-sequence`): removido `hidden`
   - Paso 4 inline (`data-operational-step4-inline`): removido `hidden`
   - Notas paso 4: removida condición `!$isOperationalBasePhase && $activeStep === 4`
   - Notas paso 5: removida condición `!$isOperationalBasePhase && $activeStep === 5`
   - Readonly correcto en paso 1: mensaje de bloqueo visible antes del formulario, botón no renderiza si `canEdit=false`
   - Eliminados textos descriptivos de desarrollo (hero-copy, snapshot lateral, topbar descriptions)
   - CSS de colores por paso aplicado con `border-left` en cada `tp-sequence-block` y `tp-section-card`

4. **Uploaders y galerías de preview faltantes**:
   - Paso 4: Agregado dropzone + galería para `factura_gestor` y `comprobante_pago` (data-step4-doc-dropzone, data-step4-doc-gallery)
   - Paso 5: Agregado dropzone + galería para evidencias de cobro `parcial/completo/otro` (data-step5-doc-dropzone, data-step5-doc-gallery)
   - Ambos con preview de archivos ya subidos desde `$prototypeStep4Form['docs']` y `$prototypeStep5Form['docs']`

### 🔄 En progreso — Fila por paso con 3 carriles

**Lo que falta para el layout completo de "cada paso = su propia fila":**

4. **Refactorizar el HTML del grid principal**:
   - Actualmente: Un `tp-content-layout.is-operational-base` con 3 columnas globales (izq: todos los pasos apilados, centro: un solo checklist+docs, derecho: bitácora)
   - Objetivo: 5 bloques `.tp-step-row[data-step-row="N"]`, cada uno con:
     - `.tp-step-col-main` (formulario del paso)
     - `.tp-step-col-center` (docs/checklist del paso)
     - `.tp-step-col-right` (notas del paso)
   - Un `tp-phase-divider` entre paso 3 y paso 4

5. **Distribuir el contenido del carril centro**:
   - Paso 1: uploader docs expediente + checklist completitud
   - Paso 2: uploader derechos + checklist gestor/derechos/aprobación
   - Paso 3: checklist gate + estado de cierre
   - Paso 4: uploader factura/comprobante gestor
   - Paso 5: uploader evidencias cobro + getCobroClienteFiles

6. **Distribuir el contenido del carril derecho**:
   - Paso 1: bitácora general (`prototype_evidencias_add`)
   - Paso 2: panel de aprobación + vigencia
   - Paso 3: bitácora general (compartida con paso 1)
   - Paso 4: notas internas pago a gestor (`prototype_step4_notes_add`)
   - Paso 5: notas internas cobro a cliente (`prototype_step5_notes_add`)

7. **JS**: Verificar que los selectores `data-*` sigan funcionando con la nueva estructura de DOM

### ❌ No empezado

8. Reducir el tamaño de fuente general (el usuario quiere más información visible)
9. Pruebas E2E sobre Docker PHP 8.2

---

## Archivos involucrados

| Archivo | Estado |
|---------|--------|
| `app/Config/Routes.php` | ✅ Modificado |
| `app/Controllers/Deskapp/Tramitesn.php` | ✅ Modificado (redirect eliminado) |
| `app/Views/deskapp/extra-pages/tramites_layout_prototipo.php` | 🔄 En progreso |

## Cómo retomar

Si se interrumpe, el siguiente paso es:

1. Abrir `app/Views/deskapp/extra-pages/tramites_layout_prototipo.php`
2. Buscar el `<div class="tp-main-grid is-operational-base">`
3. Reemplazar la estructura interna del `tp-content-layout.is-operational-base` con 5 bloques `tp-step-row`
4. Cada `tp-step-row` tiene 3 columnas: `tp-step-col-main`, `tp-step-col-center`, `tp-step-col-right`
5. Mover los contenidos existentes a la columna correcta según el mapa de arriba
6. Los estilos CSS para `tp-step-row` ya están definidos en el `<style>` del archivo
7. Verificar con `php -l` después de cada cambio
8. Probar en `http://localhost:18080/deskapp/tramitesn/prototipo-layout/paso-1?tramite_id=12467`

## Decisiones de diseño

- **No se reescriben los endpoints** — se reutilizan exactamente los mismos del flujo actual
- **El JS existente se mantiene** — los `data-*` attributes no cambian de nombre
- **La bitácora general** se repite en pasos 1 y 3 (mismo componente, mismos datos)
- **Los uploaders del paso 3 y 4 comparten endpoint** (`upload_pago_gestor`) pero se diferencian por el valor de `comprobante_final`
- **El panel de aprobación** vive en paso 2, no en el paso 3 — la aprobación es un gesto del paso 2 que desbloquea todo lo posterior
