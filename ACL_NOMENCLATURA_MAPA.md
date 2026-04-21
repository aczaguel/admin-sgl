# Mapa de Nomenclatura ACL

Fecha de corte: 2026-04-14

Objetivo: proponer el renombre canónico de permisos actuales al formato nuevo.

Implementacion runtime actual:

- La proyeccion ejecutable de este mapa vive en app/Config/AclPermissionMap.php.
- Ese archivo alimenta alias de compatibilidad y etiquetas visibles en app/Helpers/permissions_helper.php.
- Este markdown sigue siendo la referencia documental y debe mantenerse alineado con la config.

Formato:

- anterior -- nueva
- cuando un permiso viejo mezcla dos conceptos, el mapeo puede ser uno a muchos
- cuando dos permisos viejos significan lo mismo, ambos apuntan al mismo nombre nuevo

## Reglas usadas

- estructura global: global_header_*, global_sidebar_*, global_guard_*
- zonas de negocio: dashboard, tramites_listado, tramite_detalle, paso_1, paso_2, paso_3, paso_4, paso_5, paso_6, tramites_finalizados, tramites_cancelados, monitoreo, configuracion_catalogos
- elementos: menu, tabla, filtros, formulario, panel, resumen, dropzone, quick_actions, boton
- acciones: ver, listar, crear, editar, eliminar, exportar, imprimir, subir, ejecutar, autorizar, filtrar, buscar

## Guards y overrides

- bypass_cliente_filter -- global_guard_cliente_bypass_ejecutar
- bypass_tramite_tenant_access -- global_guard_tenant_tramite_bypass_ejecutar
- override_puede_editar_modulo -- tramite_detalle_override_edicion_modulo_ejecutar
- override_tramite_approved_lock -- tramite_detalle_override_aprobado_editar
- override_tramite_status_28_readonly -- tramite_detalle_override_status_28_editar
- sincronizar_tramites -- tramites_listado_sincronizacion_ejecutar

## Header y sidebar global

- menu_notifications -- global_header_notifications_ver
- header_buttons -- global_header_quick_actions_ver
- listar_settings -- global_header_profile_menu_ver + configuracion_catalogos_tabla_listar
- ui_sidebar_cliente -- global_sidebar_cliente_ver
- menu_dashboard_admin -- global_sidebar_menu_dashboard_admin_ver
- menu_dashboard_cliente -- global_sidebar_menu_dashboard_cliente_ver
- menu_tramites -- global_sidebar_menu_tramites_ver
- menu_tramites_cliente -- global_sidebar_menu_tramites_cliente_ver
- menu_tramites_tenencias -- global_sidebar_menu_tenencias_ver
- menu_proceso_final -- global_sidebar_menu_proceso_final_ver
- menu_clientes -- global_sidebar_menu_clientes_ver
- menu_gestores -- global_sidebar_menu_gestores_ver
- menu_documentos -- global_sidebar_menu_documentos_ver
- menu_configuracion -- global_sidebar_menu_configuracion_ver
- menu_permisos -- global_sidebar_menu_permisos_ver
- menu_roles -- global_sidebar_menu_roles_ver
- menu_monitoreo_actividad -- global_sidebar_menu_monitoreo_ver
- menu_erp_sa -- global_sidebar_menu_erp_sa_ver

## Tramites listado y detalle

- listar_tramite -- tramites_listado_tabla_listar
- search_tramite -- tramites_listado_filtros_buscar
- export_tramite -- tramites_listado_tabla_exportar
- print_tramite -- tramites_listado_tabla_imprimir
- read_tramite -- tramite_detalle_ver
- create_tramite -- tramites_listado_boton_crear
- clone_tramite -- tramites_listado_accion_clonar
- editar_tramite -- tramite_detalle_formulario_editar
- delete_tramite -- tramites_listado_accion_eliminar
- editar_tramite_principal -- tramite_detalle_principal_formulario_editar
- editar_tramite_asociado -- tramite_detalle_asociados_formulario_editar
- delete_tramite_asociado -- tramite_detalle_asociados_registro_eliminar

## Paso 1

- section_inicial_datos -- paso_1_panel_datos_iniciales_ver
- write_tramite_datos_tramite -- paso_1_formulario_editar

## Paso 2

- section_asigna_gestor -- paso_2_panel_asignacion_gestor_ver
- tramite_view_gestor -- paso_2_resumen_gestor_ver
- write_tramite_asigna_gestor -- paso_2_formulario_editar

## Paso 3

- section_pago_derechos -- paso_3_panel_pago_derechos_ver
- section_linea_captura -- paso_3_panel_linea_captura_ver
- section_documentos_pago -- paso_3_panel_documentos_pago_ver
- write_tramite_pago_derechos -- paso_3_formulario_editar
- can_upload_dropzone_pago_derechos -- paso_3_dropzone_documentos_pago_subir
- important_pasar_a_pagos -- paso_3_accion_autorizar

## Paso 4

- can_upload_dropzone_evidencias_finales -- paso_4_dropzone_evidencias_finales_subir
- important_ir_pago_gestor -- paso_4_navegacion_ir_paso_5_ver

## Paso 5

- section_pago_gestor -- paso_5_panel_pago_gestor_ver
- editar_pago_gestor -- paso_5_formulario_editar
- can_upload_dropzone_pago_gestor -- paso_5_dropzone_pago_gestor_subir_legacy
- can_upload_dropzone_pago_gestor_documentos -- paso_5_dropzone_documentos_pago_gestor_subir
- important_ir_cobro_cliente -- paso_5_navegacion_ir_paso_6_ver

## Paso 6 y cierre final

- section_final_costos -- paso_6_panel_costos_finales_ver
- list_cobro_cliente -- paso_6_tabla_cobro_cliente_listar
- can_upload_dropzone_cobro_cliente -- paso_6_dropzone_cobro_cliente_subir
- important_cancelar_tramite -- paso_6_accion_cancelar
- important_concluir_tramite -- paso_6_accion_concluir
- upload_cobro_cliente -- paso_6_dropzone_cobro_cliente_subir

## Tramites finalizados y cancelados

- listar_final_tramite -- tramites_finalizados_tabla_listar
- listar_concluidos_tramite -- tramites_finalizados_tabla_listar
- listar_tramites_concluidos -- tramites_finalizados_tabla_listar
- read_final_tramite -- tramites_finalizados_detalle_ver
- export_final_tramite -- tramites_finalizados_tabla_exportar
- print_final_tramite -- tramites_finalizados_tabla_imprimir
- clone_tramite_cancelado -- tramites_cancelados_accion_clonar
- listar_tramites_cancelado -- tramites_cancelados_tabla_listar
- read_tramite_cancelado -- tramites_cancelados_detalle_ver
- editar_tramite_cancelado -- tramites_cancelados_formulario_editar
- delete_tramite_cancelado -- tramites_cancelados_accion_eliminar
- export_tramite_cancelado -- tramites_cancelados_tabla_exportar
- print_tramite_cancelado -- tramites_cancelados_tabla_imprimir

## Quick actions

- quick_action_documentos -- tramite_detalle_quick_actions_documentos_ver
- quick_action_documentos_add -- tramite_detalle_quick_actions_documentos_crear
- quick_action_documentos_edit -- tramite_detalle_quick_actions_documentos_editar
- quick_action_documentos_delete -- tramite_detalle_quick_actions_documentos_eliminar
- quick_action_bitacora -- tramite_detalle_quick_actions_bitacora_ver
- quick_action_bitacora_add -- tramite_detalle_quick_actions_bitacora_crear
- quick_action_bitacora_edit -- tramite_detalle_quick_actions_bitacora_editar
- quick_action_bitacora_delete -- tramite_detalle_quick_actions_bitacora_eliminar
- quick_action_pagos_derecho -- tramite_detalle_quick_actions_pago_derechos_ver
- quick_action_pagos_derecho_add -- tramite_detalle_quick_actions_pago_derechos_crear
- quick_action_pagos_derecho_edit -- tramite_detalle_quick_actions_pago_derechos_editar
- quick_action_pagos_derecho_delete -- tramite_detalle_quick_actions_pago_derechos_eliminar
- quick_action_pago_gestor -- tramite_detalle_quick_actions_pago_gestor_ver
- quick_action_pago_gestor_add -- tramite_detalle_quick_actions_pago_gestor_crear
- quick_action_pago_gestor_edit -- tramite_detalle_quick_actions_pago_gestor_editar
- quick_action_pago_gestor_delete -- tramite_detalle_quick_actions_pago_gestor_eliminar
- quick_action_evidencias_finales -- tramite_detalle_quick_actions_evidencias_finales_ver
- quick_action_evidencias_finales_add -- tramite_detalle_quick_actions_evidencias_finales_crear
- quick_action_evidencias_finales_edit -- tramite_detalle_quick_actions_evidencias_finales_editar
- quick_action_evidencias_finales_delete -- tramite_detalle_quick_actions_evidencias_finales_eliminar
- quick_action_cobros_cliente -- tramite_detalle_quick_actions_cobro_cliente_ver
- quick_action_cobros_cliente_add -- tramite_detalle_quick_actions_cobro_cliente_crear
- quick_action_cobros_cliente_edit -- tramite_detalle_quick_actions_cobro_cliente_editar
- quick_action_cobros_cliente_delete -- tramite_detalle_quick_actions_cobro_cliente_eliminar

## Monitoreo

- monitoreo_auditoria_tramite -- monitoreo_auditoria_tabla_ver
- monitoreo_bitacora_search -- monitoreo_bitacora_filtros_buscar
- monitoreo_correccion_tramites -- monitoreo_correcciones_ver

## Wizard y filtros propios

- wizard_list_only_own -- wizard_tramites_listado_propios_listar
- tramitesn_filter_owner_only -- wizard_tramites_listado_propios_listar
- mios_filter_status_11 -- tramites_mios_filtro_status_11_filtrar
- mios_filter_status_22 -- tramites_mios_filtro_status_22_filtrar

## Legacy activos que conviene alinear aunque no sean el nucleo del ACL nuevo

- editar_final -- tramites_finales_formulario_editar
- eliminar_final -- tramites_finales_registro_eliminar
- crear_final -- tramites_finales_registro_crear
- listar_final -- tramites_finales_tabla_listar
- final_autoriza_termino -- tramites_finales_accion_autorizar
- tramite_pasa_a_final -- tramite_detalle_legacy_pasar_final_ejecutar

## Duplicados o convergencias que conviene resolver en migracion

- listar_concluidos_tramite -- tramites_finalizados_tabla_listar
- listar_tramites_concluidos -- tramites_finalizados_tabla_listar
- wizard_list_only_own -- wizard_tramites_listado_propios_listar
- tramitesn_filter_owner_only -- wizard_tramites_listado_propios_listar
- important_concluir_tramite -- paso_6_accion_concluir

## Permisos que requieren confirmacion antes de volverlos definitivos

- listar_settings -- hoy mezcla menu de perfil y catalogos de configuracion; conviene partirlo en la migracion
- header_buttons -- hoy agrupa atajos de header de naturaleza mixta; conviene revisar si se parte en permisos mas chicos
- menu_dashboard_admin -- hoy tambien funciona como permiso de acceso al dashboard, no solo como visibilidad del menu
- menu_dashboard_cliente -- misma observacion que dashboard admin
- can_upload_dropzone_pago_gestor -- hoy parece el permiso legacy anterior al split de evidencias finales y documentos de pago a gestor
