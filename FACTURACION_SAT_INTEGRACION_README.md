# Integración de Facturación SAT (vía API de proveedor)

> Documento de trabajo para implementar un módulo de facturación (CFDI) desde este sistema.
> **Nota:** este archivo no implementa nada; solo define arquitectura, pantallas, rutas y checklist.

## Objetivo

Conectar el sistema con un proveedor/módulo externo que genera facturas para el SAT, mediante APIs documentadas (PDF), para poder:
- Crear / timbrar facturas (CFDI)
- Listar y consultar facturas
- Descargar PDF/XML
- Cancelar facturas (con motivo SAT / sustitución si aplica)
- Mantener trazabilidad (quién hizo qué y cuándo)

## Alcance (MVP recomendado)

- Listado con filtros
- Detalle de factura
- Crear factura (formulario)
- Cancelar factura
- Descarga PDF/XML

## Suposiciones (ajustar cuando tengamos el PDF de la API)

- El proveedor expone endpoints REST JSON.
- La autenticación es API Key u OAuth2.
- Las facturas tienen un identificador único: UUID (típico CFDI) y/o ID interno del proveedor.
- La cancelación requiere motivo (catálogo SAT) y en algunos casos UUID de sustitución.

## Arquitectura propuesta

### 1) Capa de cliente API (adaptador)

Crear un wrapper (servicio) que encapsule el proveedor.

Responsabilidad:
- Autenticación, headers, base URL
- Serialización/deserialización
- Manejo de errores homogéneo
- Retries/timeouts
- Logging seguro (sin secretos)

Interfaz sugerida (métodos):
- `createInvoice(array $payload): ProviderInvoiceResult`
- `listInvoices(array $filters): ProviderInvoiceListResult`
- `getInvoice(string $providerIdOrUuid): ProviderInvoiceResult`
- `cancelInvoice(string $providerIdOrUuid, array $payload): ProviderCancelResult`
- `downloadPdf(string $providerIdOrUuid): binary|string`
- `downloadXml(string $providerIdOrUuid): binary|string`
- (Opcional) `getStatus(string $providerIdOrUuid)`

### 2) Persistencia local (recomendada)

Aunque el proveedor “guarde todo”, es buena práctica tener una tabla local para:
- Relacionar factura con cliente/trámite
- Evitar dependencia total de API para listados
- Cache de PDF/XML
- Auditoría de acciones y errores

**Tabla sugerida:** `sat_facturas`
Campos sugeridos:
- `id` (PK)
- `provider` (varchar) — nombre del proveedor si hay más de uno
- `provider_invoice_id` (varchar) — ID interno proveedor
- `uuid` (varchar) — UUID CFDI
- `serie` (varchar) / `folio` (varchar)
- `cliente_id` (int, nullable)
- `tramite_id` (int, nullable)
- `receptor_rfc` (varchar)
- `receptor_nombre` (varchar)
- `total` (decimal)
- `moneda` (varchar)
- `estatus` (enum/varchar: borrador|timbrada|cancelada|error)
- `fecha_emision` (datetime, nullable)
- `fecha_cancelacion` (datetime, nullable)
- `motivo_cancelacion` (varchar, nullable)
- `uuid_sustitucion` (varchar, nullable)
- `request_payload` (json/text) — opcional para soporte
- `response_payload` (json/text) — opcional para soporte
- `ultimo_error` (text)
- `created_by` (int)
- `created_at`, `updated_at`

**Tabla sugerida (opcional):** `sat_facturas_eventos`
- Para registrar intentos, errores, reintentos, y eventos de webhook.

### 3) UI / Módulo (pantallas)

**Menú:** “Facturación” (solo roles autorizados)

Pantallas mínimas:
1. **Listado**
   - Columnas: UUID/folio, receptor, total, fecha, estatus, (cliente/trámite), acciones
   - Filtros: rango fecha, estatus, cliente, búsqueda por UUID/folio
   - Acciones: Ver, Descargar PDF, Descargar XML, Cancelar

2. **Crear factura**
   - Receptor: RFC, nombre, régimen, uso CFDI, etc.
   - Conceptos: CRUD inline (producto/servicio, cantidad, precio, impuestos)
   - Botón: “Timbrar/Crear”
   - Resultado: mostrar UUID + links PDF/XML + estatus

3. **Detalle**
   - Resumen de datos
   - Historial de eventos
   - Botones de descarga + cancelar (si aplica)

4. **Cancelar**
   - Modal: motivo SAT, comentario, UUID sustitución (si aplica)
   - Confirmación y resultado

## Rutas/Endpoints del sistema (propuesta CI4)

Prefijo recomendado: `/deskapp/facturas`.

- `GET  /deskapp/facturas` → listado
- `GET  /deskapp/facturas/nueva` → formulario
- `POST /deskapp/facturas` → crear/timbrar
- `GET  /deskapp/facturas/{id}` → detalle
- `POST /deskapp/facturas/{id}/cancelar` → cancelar
- `GET  /deskapp/facturas/{id}/pdf` → descargar PDF
- `GET  /deskapp/facturas/{id}/xml` → descargar XML

Opcional (AJAX):
- `GET /deskapp/facturas/api_list`
- `GET /deskapp/facturas/api_status/{id}`

## Permisos / Seguridad

- Proteger rutas con filtro `auth`.
- Permisos por rol:
  - `facturas.view`
  - `facturas.create`
  - `facturas.cancel`
  - `facturas.download`

- Validar inputs (RFC, totales, conceptos) y sanitizar.
- No guardar API keys en código ni en DB.
- En logs, nunca imprimir secretos ni payload completo si incluye datos sensibles.

## Auditoría

- Registrar cada operación relevante:
  - `create` (timbrado) + resultado (uuid)
  - `cancel` + motivo
  - `download` (opcional)

Opciones:
- Usar `tramite_audit_log` si la factura está ligada a un trámite.
- O una tabla propia de eventos (`sat_facturas_eventos`).

## Webhooks (si el proveedor los ofrece)

- Endpoint para recibir eventos: `POST /deskapp/facturas/webhook` (validación de firma/IP).
- Actualizar estatus local y registrar evento.

## Checklist de implementación (pasos)

### A) Preparación
- [ ] Obtener PDF/API spec del proveedor
- [ ] Definir autenticación (API key / OAuth2)
- [ ] Definir ambientes (sandbox vs producción)

### B) Backend
- [ ] Crear migración de `sat_facturas` (+ eventos opcional)
- [ ] Implementar `InvoiceApiClient`
- [ ] Crear `FacturasController` (list/create/detail/cancel/download)
- [ ] Manejo de errores y timeouts

### C) Frontend
- [ ] Menú + permisos
- [ ] Vista listado + filtros
- [ ] Vista crear (form + conceptos)
- [ ] Vista detalle + acciones
- [ ] Modal de cancelación

### D) Calidad
- [ ] Pruebas en sandbox: crear, listar, descargar, cancelar
- [ ] Casos borde: cancelación inválida, UUID no encontrado, timeout
- [ ] Registro de auditoría y logs

### E) Go-live
- [ ] Variables de entorno en prod
- [ ] Rotación/seguridad de llaves
- [ ] Monitoreo de errores y reintentos

## Datos que necesitamos extraer del PDF del proveedor (para aterrizar el código)

Pega aquí (o en un issue) lo siguiente del PDF:
- **Base URL** y ambientes
- **Auth:** headers/tokens
- **Endpoints:**
  - Crear/timbrar
  - Listar (paginación, filtros)
  - Consultar por UUID/ID
  - Cancelar (motivos y reglas)
  - Descargar PDF/XML (base64/url/stream)
- **Modelos JSON** request/response
- **Webhooks** (si existen)

## Notas

- Recomendación: iniciar con un MVP acotado y luego expandir (notas de crédito, complementos, etc.).
- Todo cambio se hará después del demo para no afectar estabilidad.
