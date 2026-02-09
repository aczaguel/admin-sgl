# Administrador de Comunicación (Soporte por WhatsApp/META)

> Diseño/MVP para un módulo de soporte y seguimiento de usuarios que escriben por WhatsApp (Meta), con persistencia en BD, asignación, trazabilidad, y métricas operativas.
> **Nota:** este documento no implementa nada todavía.

## Objetivo

Ofrecer el sistema por META (WhatsApp) para que prospectos/clientes se contacten. Capturar mensajes + “intenciones” y convertirlo en un flujo administrable:
- Saber quién **ya pagó**
- Quién **ya entregó documentos** / quién **tiene documentos incompletos**
- Cuántos trámites están **detenidos** y por qué
- Control de conversaciones, responsables, próximos pasos y recordatorios

## Conceptos clave

- **Contacto**: persona que escribe (WhatsApp number) y sus datos (nombre, RFC si aplica).
- **Conversación (thread)**: canal por contacto (y por “tema” si se desea separar).
- **Mensaje**: inbound/outbound, texto y adjuntos.
- **Ticket/Seguimiento**: estado operativo (nuevo, en seguimiento, esperando docs, esperando pago, resuelto).
- **Intención**: etiqueta detectada o seleccionada (p.ej. “cotizar trámite”, “enviar documentos”, “preguntar estatus”, “pagar”).
- **Vinculación**: contacto ↔ cliente ↔ trámite(s) en el sistema.

## Integración WhatsApp (opciones)

### Opción A (recomendada): WhatsApp Cloud API (Meta)
- Webhook entrante (mensajes y estados)
- Endpoint para enviar mensajes (incluye plantillas)
- Requiere:
  - Facebook App + verificación webhook
  - Token y Phone Number ID

### Opción B: Proveedor (Twilio/360dialog/etc.)
- Menos fricción a veces, pero con costo/limitaciones propias.

### Opción C: n8n como “puente”
- n8n recibe webhook de WhatsApp y reenvía a tu sistema.
- Útil si quieres orquestar flujos sin tocar backend (pero igual conviene persistir en tu BD).

## Arquitectura propuesta

### 1) Ingesta (webhook)

Un endpoint que recibe eventos del proveedor:
- Mensaje inbound
- Mensaje entregado/leído
- Errores

Responsabilidades:
- Validar firma/token (según proveedor)
- Normalizar payload → estructura interna
- Insertar en BD (`com_messages`)
- Encolar procesamiento de intención (si se usa)

### 2) Procesamiento de intención

Dos caminos compatibles:
- **Manual (MVP):** el agente asigna intención desde UI.
- **Semiautomático:** reglas simples (keywords) + sugerencias.
- **Avanzado (opcional):** clasificador (LLM o modelo) con guardrails.

**Importante:** siempre permitir corrección manual.

### 3) Gestión (UI)

- Bandeja de entrada (inbox) tipo helpdesk
- Ficha del contacto (datos, tags, historial)
- Timeline de conversación
- Panel de seguimiento: pagos/docs/estatus
- Reportes y métricas

### 4) Sincronización con Trámites

Cuando exista un trámite en el sistema:
- Vincular conversación a `tramite_id`
- Leer estatus del trámite y checklist de documentos
- Generar tareas/recordatorios

## Modelo de datos (BD)

### Tablas mínimas (MVP)

1) `com_contacts`
- `id`, `whatsapp_e164` (único), `nombre`, `email` (nullable), `rfc` (nullable)
- `origen` (meta, web, referido)
- `created_at`, `updated_at`

2) `com_conversations`
- `id`, `contact_id`
- `status` (open|pending|closed)
- `assigned_user_id` (nullable)
- `last_message_at`
- `created_at`, `updated_at`

3) `com_messages`
- `id`, `conversation_id`
- `direction` (in|out)
- `provider_message_id` (para idempotencia)
- `type` (text|image|document|audio|video|location)
- `text` (nullable)
- `media_url`/`media_id` (nullable)
- `raw_payload` (json/text)
- `status` (received|sent|delivered|read|failed)
- `created_at`

4) `com_followups` (seguimiento tipo ticket)
- `id`, `conversation_id`
- `stage` (nuevo|en_proceso|esperando_docs|esperando_pago|detenido|resuelto)
- `motivo_detenido` (nullable)
- `next_action` (text, nullable)
- `next_action_at` (datetime, nullable)
- `created_at`, `updated_at`

5) `com_links`
- `id`, `conversation_id`
- `cliente_id` (nullable)
- `tramite_id` (nullable)
- `created_at`

### Tablas opcionales (crece sin dolor)
- `com_tags` y `com_contact_tags`
- `com_intents` y `com_message_intents` (o intención por conversación)
- `com_notes` (notas internas)
- `com_tasks` (tareas con due_date)
- `com_templates` (mensajes predefinidos)

## Pantallas (UI) sugeridas

1) **Inbox**
- Lista de conversaciones
- Filtros: asignado, stage, sin responder, hoy, detenidos
- Badges: no leídos, stage

2) **Conversación (detalle)**
- Timeline de mensajes
- Enviar respuesta
- Botones: asignar agente, cambiar stage, crear tarea, vincular a trámite

3) **Contacto**
- Datos + tags
- Historial de conversaciones
- Resumen: pagó/pendiente, docs completos/incompletos

4) **Panel de seguimiento**
- Conteos: esperando pago, esperando docs, detenidos, resueltos
- Por estatus de trámite

## Reglas para “pagó / docs completos / detenido”

Definir fuentes de verdad:
- **Pago:**
  - O viene del sistema (campo/tabla de pagos), o se marca manual con evidencia.
- **Docs completos:**
  - Se calcula desde `tra_doc_status` (si ya está modelado) o un checklist por tipo de trámite.
- **Detenido:**
  - Puede venir del estatus del trámite o del stage del followup.

MVP recomendado:
- Stage en `com_followups` + motivo.
- Y (cuando esté vinculado) mostrar “insights” del trámite.

## APIs internas (tu sistema)

Endpoints sugeridos:
- `GET /deskapp/comms/inbox`
- `GET /deskapp/comms/conversation/{id}`
- `POST /deskapp/comms/conversation/{id}/message` (enviar)
- `POST /deskapp/comms/conversation/{id}/assign`
- `POST /deskapp/comms/conversation/{id}/stage`
- `POST /deskapp/comms/conversation/{id}/link_tramite`

Webhook:
- `GET /webhooks/whatsapp` (verificación)
- `POST /webhooks/whatsapp` (eventos)

## Seguridad y cumplimiento

- Guardar PII mínimo necesario.
- Proteger endpoints con auth/roles.
- Validar firma/token de webhooks.
- Idempotencia por `provider_message_id`.
- Rate limiting básico para webhooks.

## Checklist de implementación (post-demo)

- [ ] Elegir proveedor (Meta Cloud API vs tercero)
- [ ] Definir campos/tabla de pagos y cómo se reflejan
- [ ] Crear migraciones de tablas `com_*`
- [ ] Implementar webhook + normalización + persistencia
- [ ] Implementar envío de mensajes + plantillas
- [ ] Crear UI Inbox + conversación + stage
- [ ] Vincular a `tramite`/`cliente` y leer docs/estatus
- [ ] Reportes KPI (detenidos, esperando pago, esperando docs)
- [ ] Pruebas con número real y plantillas

## Preguntas que debemos responder (para cerrar el diseño)

- ¿Se atenderá 1 número o múltiples (multi-agente/multi-tenant)?
- ¿Qué define “pagó”? ¿Pago parcial vs total?
- ¿Qué define “docs completos”? ¿por tipo de trámite?
- ¿Necesitamos SLA/tiempos de respuesta?
- ¿Se enviarán recordatorios automáticos (cron) o manuales?
