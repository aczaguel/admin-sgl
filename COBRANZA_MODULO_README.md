# Modulo Profesional de Cobranza - Admin SGL

## Objetivo

Definir un modulo de cobranza profesional, separado del wizard actual de tramites, para operar la recuperacion de pagos con reglas, estados, asignacion, seguimiento y auditoria propios.

Este documento aterriza dos frentes antes de implementar backend:

1. Operacion del modulo de cobranza.
2. Diseno de datos y estados.

## Diagnostico del estado actual

Hoy el sistema ya tiene piezas utiles para cobro, pero siguen acopladas al flujo del tramite:

- El macro-estatus del tramite usa `tra_status_id = 28` para `Cobro a Cliente`.
- El flujo visible depende del wizard de `Tramitesn` y de la vista `tramite_cobro_cliente_view`.
- Existen catalogos y permisos de cobro, pero no un expediente operativo de cobranza.
- La operacion de cobranza esta modelada como el paso final del tramite, no como una disciplina propia.

Esto sirve para cierre administrativo, pero no para una operacion real de cobranza con:

- asignacion por ejecutivo de cobranza,
- bandejas de trabajo,
- promesas de pago,
- reintentos,
- incidencias y disputas,
- SLA y aging,
- seguimiento de pagos parciales,
- escalamiento y supervision.

## Decision de arquitectura

La cobranza no debe vivir como un paso adicional del wizard ni depender solo de `tramite.cobro_status_id`.

Se propone:

- Mantener `tramite` como expediente maestro del servicio.
- Mantener `tra_status_id` como macro-estatus del tramite.
- Mantener `cobro_status_id` como estatus resumido o de interoperabilidad.
- Crear un expediente de cobranza por tramite con estados, timeline y reglas propias.

Regla base:

- Un tramite puede entrar a cobranza cuando cumple las condiciones de negocio para cobro.
- La operacion diaria ocurre sobre el expediente de cobranza.
- El wizard solo consulta resumenes o accesos rapidos, pero no concentra la operacion.

## 1. Operacion del modulo de cobranza

### Objetivo operativo

Dar a un equipo de cobranza una bandeja profesional para recuperar pagos sin depender del detalle tecnico del wizard del tramite.

### Roles del modulo

Se proponen estos roles operativos, independientes de si una persona ya tiene otros permisos del sistema:

- Ejecutivo de cobranza: gestiona cartera asignada, registra llamadas, acuerdos, promesas, pagos e incidencias.
- Supervisor de cobranza: monitorea bandejas, reasigna cuentas, autoriza ajustes, marca disputas y cierra casos.
- Coordinador operativo: define reglas de priorizacion, SLA y seguimiento.
- Administrador: configura catalogos, permisos, motivos, plantillas y parametros.
- Consulta/auditoria: solo lectura, acceso a indicadores, historial y evidencia.

### Objetos funcionales

El modulo gira sobre estos objetos:

- Expediente de cobranza.
- Gestion de cobranza.
- Promesa de pago.
- Pago o confirmacion de pago.
- Incidencia o disputa.
- Documento o evidencia.
- Asignacion y reasignacion.

### Flujo operativo propuesto

#### 1. Ingreso a cobranza

Un tramite entra a cobranza cuando:

- ya llego al punto de negocio donde debe cobrarse al cliente,
- tiene monto objetivo definido,
- tiene cliente y ejecutivo ligados,
- cumple precondiciones de documentos y validaciones.

Accion del sistema:

- crear o reactivar expediente de cobranza,
- asignar responsable inicial,
- calcular fecha compromiso/SLA,
- clasificar prioridad,
- enviar evento de apertura a bitacora.

#### 2. Gestion operativa

Cada expediente debe permitir registrar:

- llamada,
- WhatsApp,
- correo,
- comentario interno,
- visita,
- intento fallido,
- confirmacion de contacto,
- promesa de pago,
- pago recibido,
- rechazo o disputa,
- escalamiento.

Cada gestion debe guardar:

- quien la hizo,
- fecha y hora,
- canal,
- resultado,
- observaciones,
- siguiente accion,
- fecha del siguiente seguimiento.

#### 3. Promesa de pago

Una promesa de pago debe tener:

- monto prometido,
- fecha promesa,
- medio prometido,
- observaciones,
- responsable que la registró,
- estatus de la promesa.

Estados de promesa sugeridos:

- activa,
- cumplida,
- vencida,
- cancelada.

#### 4. Pago

El modulo debe soportar:

- pago total,
- pago parcial,
- pago en revision,
- pago rechazado,
- pago conciliado.

El pago no debe cerrar el expediente automaticamente sin reglas. Antes debe pasar por validacion o conciliacion, segun el proceso de negocio.

#### 5. Disputa o incidencia

Se requiere una ruta formal para:

- monto incorrecto,
- tramite cancelado o improcedente,
- documento faltante,
- cliente inconforme,
- pago no identificado,
- solicitud de ajuste,
- devolucion o nota de credito.

Una disputa suspende o cambia el SLA, pero no borra historial.

#### 6. Cierre

Un expediente de cobranza se puede cerrar como:

- cobrado,
- incobrable,
- cancelado,
- devuelto a operacion,
- absorbido por ajuste,
- duplicado o fusionado.

El cierre debe registrar motivo y responsable.

### Bandejas del modulo

La UI del modulo profesional de cobranza debe partir de bandejas, no del detalle del tramite:

- Mi cartera.
- Vencidos hoy.
- Promesas por vencer.
- Promesas vencidas.
- Pagos en revision.
- Disputas abiertas.
- Sin gestion reciente.
- Escalados.
- Cerrados del periodo.

### Indicadores operativos minimos

- cartera activa,
- cartera vencida,
- recuperacion diaria/semanal/mensual,
- promesas activas,
- tasa de cumplimiento de promesas,
- tiempo promedio al primer contacto,
- tiempo promedio de resolucion,
- expedientes sin gestion,
- efectividad por ejecutivo,
- aging por rangos.

### Reglas operativas clave

- Solo puede existir un expediente de cobranza abierto por tramite.
- Toda mutacion relevante debe generar bitacora.
- Toda promesa vencida debe reabrir seguimiento automaticamente.
- Un pago parcial no cierra expediente si el saldo sigue abierto.
- Un expediente concluido o cancelado en el tramite no acepta nuevas mutaciones de cobranza, salvo permisos de override auditado.
- Las acciones masivas deben quedar restringidas a supervisor/administrador.

## 2. Diseno de datos y estados

### Principio de modelado

No conviene seguir usando solo estos campos para operar cobranza:

- `tramite.tra_status_id`
- `tramite.cobro_status_id`

Sirven como resumen del flujo general, pero no alcanzan para timeline, SLA, promesas, pagos parciales y disputas.

Se propone un submodelo propio de cobranza.

### Tablas nuevas propuestas

#### `cobranza_expediente`

Tabla principal del modulo.

Campos sugeridos:

- `id`
- `tramite_id`
- `cliente_id`
- `cli_directo_id`
- `cli_directo_ejecutivo_id`
- `empresa_solicitante_id` o referencia equivalente si el solicitante se modela distinto
- `owner_user_id` (ejecutivo de cobranza responsable)
- `supervisor_user_id`
- `status_id`
- `prioridad_id`
- `origen_apertura`
- `monto_objetivo`
- `saldo_actual`
- `moneda`
- `fecha_apertura`
- `fecha_ultimo_contacto`
- `fecha_proximo_seguimiento`
- `fecha_promesa_actual`
- `fecha_cierre`
- `motivo_cierre_id`
- `sla_at_first_contact_at`
- `sla_resolve_at`
- `is_disputa`
- `is_requiere_revision`
- `external_reference`
- `created_at`
- `updated_at`
- `created_by`
- `updated_by`

Restricciones:

- indice unico para un expediente abierto por `tramite_id`, usando bandera de activo o cierre nulo,
- indices por `owner_user_id`, `status_id`, `fecha_proximo_seguimiento`, `fecha_promesa_actual`.

#### `cobranza_gestion`

Timeline de gestiones.

Campos sugeridos:

- `id`
- `expediente_id`
- `tipo_gestion_id`
- `canal_id`
- `resultado_id`
- `fecha_gestion`
- `siguiente_accion`
- `fecha_proximo_seguimiento`
- `comentarios`
- `metadata_json`
- `created_at`
- `created_by`

#### `cobranza_promesa_pago`

Campos sugeridos:

- `id`
- `expediente_id`
- `monto_prometido`
- `fecha_promesa`
- `medio_pago_id`
- `status_id`
- `observaciones`
- `created_at`
- `updated_at`
- `created_by`
- `updated_by`

#### `cobranza_pago`

Campos sugeridos:

- `id`
- `expediente_id`
- `monto`
- `fecha_pago_reportada`
- `fecha_pago_confirmada`
- `medio_pago_id`
- `referencia_pago`
- `status_id`
- `documento_id` o puntero a evidencia
- `observaciones`
- `created_at`
- `updated_at`
- `created_by`
- `updated_by`

#### `cobranza_incidencia`

Campos sugeridos:

- `id`
- `expediente_id`
- `tipo_incidencia_id`
- `status_id`
- `descripcion`
- `resolucion`
- `opened_at`
- `resolved_at`
- `created_by`
- `resolved_by`

#### `cobranza_asignacion_log`

Historial de cambios de responsable.

Campos sugeridos:

- `id`
- `expediente_id`
- `from_user_id`
- `to_user_id`
- `motivo`
- `created_at`
- `created_by`

### Catalogos nuevos propuestos

- `cobranza_status`
- `cobranza_prioridad`
- `cobranza_tipo_gestion`
- `cobranza_canal`
- `cobranza_resultado_gestion`
- `cobranza_motivo_cierre`
- `cobranza_tipo_incidencia`
- `cobranza_medio_pago`

### Estado del tramite vs estado de cobranza

#### Macro-estados existentes

El tramite ya maneja macro-estados de negocio como:

- 23 `Pago a Gestor`
- 28 `Cobro a Cliente`
- 20 `Concluido`
- 21 `Cancelado`

Estos deben seguir existiendo para el flujo general y compatibilidad actual.

#### Estado operativo nuevo

Se propone que `cobranza_expediente.status_id` sea el estado operativo real.

Estados sugeridos del expediente:

1. `nuevo`
2. `asignado`
3. `primer_contacto_pendiente`
4. `en_seguimiento`
5. `promesa_activa`
6. `pago_reportado`
7. `pago_en_revision`
8. `pago_parcial`
9. `disputa_abierta`
10. `escalado`
11. `cobrado`
12. `incobrable`
13. `cancelado`
14. `devuelto_a_operacion`

### Mapeo recomendado de sincronizacion

#### Apertura de expediente

- Si el tramite entra al punto de cobro, `tra_status_id` puede permanecer en 28.
- Se crea expediente en `nuevo` o `asignado`.
- `tramite.cobro_status_id` se sincroniza a un valor equivalente a pendiente.

#### Seguimiento normal

- El expediente avanza entre `primer_contacto_pendiente`, `en_seguimiento` y `promesa_activa`.
- `tramite.tra_status_id` no necesita cambiar durante cada gestion.
- `tramite.cobro_status_id` puede reflejar resumen, no detalle.

#### Pago

- Si hay pago reportado, el expediente cambia a `pago_reportado` o `pago_en_revision`.
- Si el pago se confirma total, el expediente cambia a `cobrado`.
- Solo entonces el tramite puede pasar a `concluido`, si el resto del flujo lo permite.

#### Disputa

- Si hay incidencia critica, el expediente cambia a `disputa_abierta`.
- El tramite puede permanecer en 28 mientras la disputa esta viva.

#### Cierre sin cobro

- Si negocio decide no continuar, el expediente puede cerrar como `incobrable`, `cancelado` o `devuelto_a_operacion`.
- La sincronizacion a `tramite` debe pasar por una regla de negocio central, no por la UI.

### Reglas de sincronizacion

- `tramite.tra_status_id` no debe reflejar cada micro-movimiento operativo de cobranza.
- `tramite.cobro_status_id` puede seguir usandose como vista resumida o compatibilidad externa.
- El origen de verdad de cobranza debe ser `cobranza_expediente.status_id`.
- Toda sincronizacion entre expediente y tramite debe ocurrir en un servicio de dominio unico.

### Documentos y evidencias

La cobranza necesita evidencias propias, aunque parte de los documentos ya existan en el tramite.

Se propone:

- reutilizar el almacenamiento actual de archivos cuando sea posible,
- clasificar evidencia de cobranza con tipo documental propio,
- ligar cada evidencia a expediente, gestion o pago,
- diferenciar evidencia de pago de evidencia operativa.

Tipos de evidencia sugeridos:

- comprobante de pago,
- estado de cuenta,
- conversacion o acuse,
- autorizacion,
- nota interna,
- soporte de disputa.

### Permisos nuevos sugeridos

Para no mezclar la cobranza con permisos del wizard, se recomiendan permisos propios:

- `list_cobranza`
- `view_cobranza_expediente`
- `assign_cobranza_expediente`
- `manage_cobranza_gestion`
- `manage_cobranza_promesa`
- `register_cobranza_pago`
- `resolve_cobranza_pago`
- `open_cobranza_disputa`
- `close_cobranza_expediente`
- `override_cobranza_lock`
- `view_cobranza_kpis`

### Reglas de integridad

- Un expediente abierto por tramite.
- No borrar gestiones; solo cancelar o cerrar con auditoria.
- Promesa vencida no se edita: se cierra y se crea una nueva si aplica.
- Un pago confirmado actualiza saldo y dispara recalculo de estado.
- Un expediente cerrado no admite mutaciones salvo reapertura auditada.

## Implicaciones para el backend (siguiente fase)

La fase 3 debe construir una capa de dominio, no solo controladores.

Piezas sugeridas:

- servicio `CobranzaLifecycleService`,
- servicio `CobranzaAssignmentService`,
- servicio `CobranzaPaymentService`,
- servicio `CobranzaTimelineService`,
- controladores Deskapp para bandejas y expediente,
- endpoints JSON internos para acciones operativas,
- pruebas unitarias y de guardias ACL.

## Decision de alcance para implementar

Para un primer release, conviene entregar este MVP:

1. Expediente de cobranza.
2. Bandeja de cartera.
3. Timeline de gestiones.
4. Promesa de pago.
5. Registro de pago y confirmacion.
6. Reasignacion.
7. Cierre y auditoria.

Se puede dejar para una fase posterior:

- automatizaciones avanzadas,
- reglas de scoring,
- integracion profunda con WhatsApp,
- conciliacion bancaria automatica,
- tablero ejecutivo avanzado.

## Resumen ejecutivo

La decision correcta no es extender el paso 6 actual. La decision correcta es crear un modulo de cobranza con expediente, timeline, estados y permisos propios, sincronizado con `tramite` pero no subordinado al wizard.

Con esto, la fase 3 ya puede empezar sobre una base concreta.