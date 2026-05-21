# API Externa de Tramites

## Objetivo

Exponer una primera superficie versionada para que sistemas externos creen tramites y consulten su estatus sin pasar por la sesion web del admin.

## Especificacion OpenAPI

La especificacion OpenAPI versionable de los endpoints actuales vive en `openapi.external-api-tramites-v1.yaml`.

Tambien hay una version JSON importable en Postman o Swagger UI en `openapi.external-api-tramites-v1.json`.

La coleccion Postman derivada de ese contrato vive en `postman/ExternalApiTramitesV1.postman_collection.json`.

Ambientes Postman listos para importar:

- `postman/ExternalApiTramitesV1.local.postman_environment.json`
- `postman/ExternalApiTramitesV1.qa.postman_environment.json`
- `postman/ExternalApiTramitesV1.prod.postman_environment.json`

El contrato de webhooks salientes quedó documentado en el mismo YAML usando la extension `x-webhooks`.

## Seguridad

La API usa una llave por header o bearer token.

Variables de entorno esperadas:

- `externalApi.enabled=true`
- `externalApi.keys=llave-1,llave-2`
- `externalApi.integrationUserId=123`
- `externalApi.defaultSourceSystem=erp_principal`
- `externalApi.webhookUrls=https://erp.ejemplo.com/webhooks/tramites`

Headers soportados:

- `X-API-Key: tu-llave`
- `Authorization: Bearer tu-llave`
- `Idempotency-Key: clave-unica-por-intento`
- `X-Source-System: erp_principal`

Nota operativa:

- El filtro intenta primero `X-API-Key` y solo usa `Authorization` si `X-API-Key` viene vacio.
- Si pruebas con bearer token en Postman, no mandes un `X-API-Key` placeholder o invalido al mismo tiempo.

## Tablas de soporte requeridas

Antes de usar esta API hay que crear las tablas de integracion externa con:

```bash
mysql -u [usuario] -p [base] < create_external_api_integration_tables.sql
```

## Endpoints disponibles

### POST /api/v1/tramites

Crea un tramite y registra documentos adjuntos o documentos inline en base64.

Reglas nuevas del contrato:

- `Idempotency-Key` es obligatorio.
- `external_reference` es obligatoria y unica por `source_system`.
- Si se reenvia la misma solicitud con la misma llave y el mismo payload, la API devuelve la misma respuesta marcada como replay idempotente.
- Si se reutiliza la misma llave con un payload distinto, devuelve `409`.

Payload JSON ejemplo:

```json
{
  "external_reference": "ERP-TRX-000145",
  "contrato": "CTR-001",
  "unidad": "Unidad 45",
  "serie": "SERIE123456",
  "placas": "ABC123A",
  "tra_tipos_id": 7,
  "entidad_id": 14,
  "ent_municipio_id": 266,
  "cli_directo_id": 22,
  "cli_directo_ejecutivo_id": 18,
  "empresa_gestora_id": 3,
  "gestor_id": 9,
  "observaciones": "Alta desde sistema externo",
  "statuses": {
    "tra_status_id": 22,
    "cobro_status_id": 1,
    "reembolso_status_id": 1,
    "pago_gestor_st_id": 2
  },
  "documentos": [
    {
      "nombre": "identificacion.pdf",
      "mime_type": "application/pdf",
      "contenido_base64": "JVBERi0xLjQK..."
    }
  ]
}
```

Tambien soporta `multipart/form-data` con los mismos campos y archivos en `documentos[]`.

Respuesta exitosa:

```json
{
  "success": true,
  "message": "Trámite creado exitosamente.",
  "tramite_id": 1234,
  "folio": "TR-2026-001234",
  "external_reference": "ERP-TRX-000145",
  "source_system": "erp_principal",
  "documentos_registrados": 2,
  "status_snapshot": {
    "id": 1234,
    "folio": "TR-2026-001234",
    "tra_status_id": 22,
    "tra_status": "DCTOS COMPLETOS",
    "cobro_status_id": 1,
    "cobro_status": "PENDIENTE",
    "empresa_solicitante": "Cliente Demo",
    "ejecutivo": "Ejecutivo Demo"
  }
}
```

Errores comunes:

Falta `Idempotency-Key`:

```json
{
  "success": false,
  "message": "El header Idempotency-Key es obligatorio."
}
```

Payload invalido:

```json
{
  "success": false,
  "message": "Payload inválido.",
  "errors": {
    "contrato": "El contrato es obligatorio.",
    "cli_directo_ejecutivo_id": "El ejecutivo es obligatorio."
  }
}
```

Idempotency-Key reutilizado con payload distinto:

```json
{
  "success": false,
  "message": "El Idempotency-Key ya fue utilizado con un payload distinto."
}
```

Referencia externa ya registrada para ese sistema origen:

```json
{
  "success": false,
  "message": "La referencia externa ya existe para este sistema origen.",
  "existing_tramite_id": 1234,
  "external_reference": "ERP-TRX-000145",
  "source_system": "erp_principal"
}
```

Duplicado de negocio por tipo y serie:

```json
{
  "success": false,
  "duplicate": true,
  "confirmable": true,
  "message": "El trámite ya fue registrado previamente.",
  "existing_tramite_id": 777,
  "duplicate_details": {
    "id_existente": 777,
    "folio_existente": "TR-2026-000777",
    "contrato_existente": "CTR-EXISTENTE",
    "serie_existente": "SER123",
    "tipo_tramite_existente": "TENENCIA",
    "nombre_usuario_existente": "Usuario Previo Test",
    "created_at_existente": "13/05/2026 09:55",
    "created_at_existente_raw": "2026-05-13 09:55:00"
  }
}
```

### GET /api/v1/tramites/{id}

Devuelve el snapshot actual del tramite y su conteo de documentos registrados.

Errores comunes:

ID invalido:

```json
{
  "success": false,
  "message": "ID de trámite inválido."
}
```

Tramite no encontrado:

```json
{
  "success": false,
  "message": "Trámite no encontrado."
}
```

### GET /api/v1/tramites/referencia/{external_reference}

Consulta el snapshot actual usando la referencia externa unica del sistema origen. `source_system` puede viajar en el header `X-Source-System` o como query param `source_system`.

Errores comunes:

Referencia sin match:

```json
{
  "success": false,
  "message": "No existe un trámite ligado a esa referencia externa."
}
```

Tabla de referencias no configurada:

```json
{
  "success": false,
  "message": "La tabla de referencias externas no está configurada."
}
```

Errores transversales de autenticacion:

Credencial faltante:

```json
{
  "success": false,
  "status": "error",
  "message": "Falta credencial de API."
}
```

Credencial invalida:

```json
{
  "success": false,
  "status": "error",
  "message": "Credencial de API inválida."
}
```

## Ejemplos curl

Alta JSON:

```bash
curl --request POST \
  --url 'http://admin-sgl/api/v1/tramites' \
  --header 'Accept: application/json' \
  --header 'Content-Type: application/json' \
  --header 'X-API-Key: sgl-local-erp-principal-2026' \
  --header 'Idempotency-Key: idem-1710000000000' \
  --header 'X-Source-System: erp_principal' \
  --data '{
    "external_reference": "ERP-TRX-1710000000000",
    "contrato": "CTR-1710000000000",
    "unidad": "Unidad 45",
    "serie": "SERIE1710000000000",
    "placas": "ABC123A",
    "tra_tipos_id": 7,
    "entidad_id": 14,
    "ent_municipio_id": 266,
    "cli_directo_id": 22,
    "cli_directo_ejecutivo_id": 18,
    "empresa_gestora_id": 3,
    "gestor_id": 9,
    "observaciones": "Alta desde curl",
    "statuses": {
      "tra_status_id": 22,
      "cobro_status_id": 22
    }
  }'
```

Alta multipart con archivo:

```bash
curl --request POST \
  --url 'http://admin-sgl/api/v1/tramites' \
  --header 'Accept: application/json' \
  --header 'X-API-Key: sgl-local-erp-principal-2026' \
  --header 'Idempotency-Key: idem-multipart-1710000000000' \
  --header 'X-Source-System: erp_principal' \
  --form 'external_reference=ERP-MULTIPART-1710000000000' \
  --form 'contrato=CTR-MULTIPART-1710000000000' \
  --form 'unidad=Unidad 45' \
  --form 'serie=SERIEMULTI1710000000000' \
  --form 'placas=ABC123A' \
  --form 'tra_tipos_id=7' \
  --form 'entidad_id=14' \
  --form 'ent_municipio_id=266' \
  --form 'cli_directo_id=22' \
  --form 'cli_directo_ejecutivo_id=18' \
  --form 'empresa_gestora_id=3' \
  --form 'gestor_id=9' \
  --form 'observaciones=Alta multipart desde curl' \
  --form 'documentos[]=@/ruta/al/identificacion.pdf'
```

Consulta por id:

```bash
curl --request GET \
  --url 'http://admin-sgl/api/v1/tramites/1234' \
  --header 'Accept: application/json' \
  --header 'X-API-Key: sgl-local-erp-principal-2026'
```

Consulta por referencia externa:

```bash
curl --request GET \
  --url 'http://admin-sgl/api/v1/tramites/referencia/ERP-TRX-1710000000000?source_system=erp_principal' \
  --header 'Accept: application/json' \
  --header 'X-API-Key: sgl-local-erp-principal-2026'
```

Alternativa con bearer token:

```bash
curl --request GET \
  --url 'http://admin-sgl/api/v1/tramites/1234' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer sgl-local-erp-principal-2026'
```

## Webhooks

La API ya deja eventos en cola en `external_api_webhook_event`.

Primer evento emitido:

- `tramite.created`
- `tramite.status_changed`

Payload base del evento:

```json
{
  "event": "tramite.created",
  "source_system": "erp_principal",
  "external_reference": "ERP-TRX-000145",
  "tramite": {
    "id": 1234,
    "folio": "TR-2026-001234",
    "tra_status_id": 22,
    "tra_status": "DCTOS COMPLETOS"
  }
}
```

El despacho HTTP de esos eventos queda como siguiente fase. En este corte ya se generan y almacenan con `delivery_status = pending`.

## Despacho de webhooks

- Comando CLI: `php spark external-api:dispatch-webhooks --limit=50`
- Variables opcionales:
  - `externalApi.webhookUrls`
  - `externalApi.webhookTimeoutSeconds`
  - `externalApi.webhookMaxAttempts`

Cada webhook se entrega como `POST` JSON al `webhook_url` configurado. Si responde con HTTP `2xx`, el evento queda en `delivery_status = delivered`. Si falla, incrementa `attempts` y permanece `pending` hasta agotar el máximo configurado; al rebasarlo queda en `failed`.

Payload base de `tramite.status_changed`:

```json
{
  "event": "tramite.status_changed",
  "source_system": "erp_principal",
  "external_reference": "ERP-TRX-000145",
  "tramite": {
    "id": 1234,
    "folio": "TR-2026-001234",
    "tra_status_id": 25,
    "tra_status": "PAGO DERECHOS COTIZACION"
  },
  "previous_status": {
    "id": 22,
    "name": "DCTOS COMPLETOS"
  },
  "current_status": {
    "id": 25,
    "name": "PAGO DERECHOS COTIZACION"
  }
}
```

## Reglas del primer corte

- Requiere empresa solicitante y ejecutivo.
- El ejecutivo debe pertenecer a la empresa solicitante.
- El ejecutivo debe tener `user_id` ligado para asignar el tramite.
- No permite crear directamente en estatus bloqueados.
- La logica de alta comparte la misma base que el wizard para evitar dos caminos divergentes.
- La referencia externa es unica por sistema origen.
- La consulta por referencia externa ya está disponible.
- La API deja eventos webhook en cola al crear el tramite.
- Los cambios de `tra_status_id` en el flujo principal encolan `tramite.status_changed` cuando el trámite viene de integración externa.

## Alcance pendiente

- Catalogo formal de tipos documentales para integracion.
- Emision de `tramite.status_changed` desde mutadores adicionales fuera del flujo principal si aparecen nuevos caminos de cambio de estatus.