# Guia Operativa para Proveedor Externo

## Objetivo

Esta guia resume lo minimo que el sistema consumidor debe saber para integrarse con la API externa de tramites de Admin SGL.

## Responsabilidades

- Admin SGL expone la API, genera la API key y la comparte al consumidor.
- El sistema consumidor llama a la API usando esa API key.
- El sistema consumidor define y envía su `external_reference`.
- `sourceSystem` identifica de forma estable al sistema consumidor.

## Convenciones acordadas

### API key

- La genera Admin SGL.
- Se recomienda una llave por ambiente y por consumidor.
- Formato sugerido: `sgl-{ambiente}-{consumidor}-{anio}`.

Ejemplos:

- `sgl-local-erp-principal-2026`
- `sgl-qa-erp-principal-2026`
- `sgl-prod-erp-principal-2026`

### sourceSystem

- Debe ser estable en el tiempo.
- No debe incluir el ambiente.
- Recomendado en minusculas y con guion bajo.

Ejemplos:

- `erp_principal`
- `sap_facturacion`
- `crm_cliente`

### external_reference

- La genera el sistema consumidor.
- Debe ser unica dentro del mismo `sourceSystem`.
- Debe ser legible y trazable para soporte.

Ejemplos:

- `ERP-TRAM-2026-000145`
- `ERP-TRAM-2026-000146`

## Headers requeridos

### Para todas las consultas

- `X-API-Key: llave-compartida-por-admin-sgl`

### Para alta de tramite

- `X-API-Key: llave-compartida-por-admin-sgl`
- `Idempotency-Key: clave-unica-por-intento`
- `X-Source-System: erp_principal`

Tambien se permite:

- `Authorization: Bearer llave-compartida-por-admin-sgl`

## Endpoints

### POST /api/v1/tramites

Crea un tramite externo.

Campos minimos esperados:

- `external_reference`
- `contrato`
- `serie`
- `tra_tipos_id`
- `entidad_id`
- `ent_municipio_id`
- `cli_directo_id`
- `cli_directo_ejecutivo_id`

Ejemplo:

```json
{
  "external_reference": "ERP-TRAM-2026-000145",
  "contrato": "CTR-001",
  "unidad": "Unidad 45",
  "serie": "SERIE123456",
  "placas": "ABC123A",
  "tra_tipos_id": 7,
  "entidad_id": 14,
  "ent_municipio_id": 266,
  "cli_directo_id": 22,
  "cli_directo_ejecutivo_id": 18,
  "observaciones": "Alta desde ERP"
}
```

Nota para ambiente local de este repo:

- Los valores genéricos del contrato no siempre coinciden con los catálogos de tu base local.
- En esta base local validada funcionan `tra_tipos_id=31`, `entidad_id=14`, `ent_municipio_id=532`, `cli_directo_id=22` y `cli_directo_ejecutivo_id=48`.
- Para `cli_directo_id=22`, el ejecutivo 48 corresponde a JANNETH CRUZ y sí tiene `user_id` ligado.

### GET /api/v1/tramites/{id}

Consulta el snapshot del tramite por id.

### GET /api/v1/tramites/referencia/{external_reference}

Consulta el snapshot del tramite por referencia externa.

## Reglas importantes

- `Idempotency-Key` es obligatoria en el alta.
- `external_reference` es obligatoria en el alta.
- Si el mismo `Idempotency-Key` llega con el mismo payload, la API reusa la respuesta previa.
- Si el mismo `Idempotency-Key` llega con un payload distinto, la API responde `409`.
- Si la misma `external_reference` ya existe para el mismo `sourceSystem`, la API responde `409`.

## Webhooks

Admin SGL puede dejar eventos en cola y despacharlos al endpoint que se configure para el consumidor.

Eventos soportados actualmente:

- `tramite.created`
- `tramite.status_changed`

## Referencias del repo

- Ver [API_EXTERNA_TRAMITES_README.md](API_EXTERNA_TRAMITES_README.md) para el contrato tecnico completo.
- Ver [postman/ExternalApiTramitesV1.postman_collection.json](postman/ExternalApiTramitesV1.postman_collection.json) para ejemplos listos para Postman.
- Ver [.env.external-api.example](.env.external-api.example) para un ejemplo de configuracion por ambiente.