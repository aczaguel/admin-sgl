-- Activa permisos que se usan en código (generado automáticamente)
-- Recomendación: agrega primero la columna `status` con default 1.
--
-- ALTER TABLE us_permissions ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1;
--

UPDATE us_permissions
SET status = 1
WHERE permission_name IN ('can_upload_dropzone_cobro_cliente', 'can_upload_dropzone_pago_derechos', 'can_upload_dropzone_pago_gestor', 'clone_tramite', 'clone_tramite_cancelado', 'create_tramite', 'debug_perm_audit_tags', 'delete_tramite', 'delete_tramite_asociado', 'delete_tramite_cancelado', 'editar_pago_gestor', 'editar_tramite', 'editar_tramite_asociado', 'editar_tramite_cancelado', 'editar_tramite_principal', 'export_final_tramite', 'export_tramite', 'export_tramite_cancelado', 'header_buttons', 'important_cancelar_tramite', 'important_concluir_tramite', 'important_pasar_a_pagos', 'list_cobro_cliente', 'listar_concluidos_tramite', 'listar_final_tramite', 'listar_settings', 'listar_tramite', 'listar_tramites_concluidos', 'menu_clientes', 'menu_configuracion', 'menu_dashboard_admin', 'menu_dashboard_cliente', 'menu_documentos', 'menu_erp_sa', 'menu_gestores', 'menu_monitoreo_actividad', 'menu_notifications', 'menu_permisos', 'menu_proceso_final', 'menu_roles', 'menu_tramites', 'menu_tramites_cliente', 'menu_tramites_tenencias', 'mios_filter_status_11', 'mios_filter_status_22', 'monitoreo_auditoria_tramite', 'monitoreo_bitacora_search', 'monitoreo_correccion_tramites', 'override_puede_editar_modulo', 'override_tramite_approved_lock', 'print_final_tramite', 'print_tramite', 'print_tramite_cancelado', 'quick_action_bitacora', 'quick_action_cobros_cliente', 'quick_action_documentos', 'quick_action_evidencias_finales', 'quick_action_pago_gestor', 'quick_action_pagos_derecho', 'read_final_tramite', 'read_tramite_cancelado', 'search_tramite', 'tramitesn_filter_owner_only')
  AND (status IS NULL OR status <> 1);

UPDATE us_permissions
SET status = 1
WHERE permission_name IN ('read_tramite', 'section_asigna_gestor', 'section_documentos_pago', 'section_final_costos', 'section_inicial_datos', 'section_linea_captura', 'section_pago_derechos', 'section_pago_gestor', 'sincronizar_tramites', 'tramite_view_gestor', 'ui_sidebar_cliente', 'wizard_list_only_own', 'write_tramite_asigna_gestor', 'write_tramite_datos_tramite', 'write_tramite_pago_derechos')
  AND (status IS NULL OR status <> 1);

