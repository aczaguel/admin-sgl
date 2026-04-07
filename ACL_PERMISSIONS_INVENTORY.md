# Inventario de permisos (ACL)

Este archivo lista cada `has_permission('...')` encontrado en el código y su ubicación.
> Nota: “Action” se infiere buscando la función PHP más cercana hacia arriba; es una aproximación.

Permisos distintos: **89**  Referencias totales: **354**

## bypass_cliente_filter

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L538) — Concluido::getEjecutivosByClienteId
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L847) — Concluido::getDependentData
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L3368) — Tramites::getDependentData
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L7340) — Tramites::change_status

## bypass_tramite_tenant_access

**Otros (helpers, libraries, etc.)**
- [app/Helpers/acl_guard_helper.php](app/Helpers/acl_guard_helper.php#L134)

## can_upload_dropzone_cobro_cliente

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2476) — Concluido::single_cobro_cliente
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2481) — Concluido::single_cobro_cliente
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2702) — Concluido::single_cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6691) — Tramites::single_cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6696) — Tramites::single_cobro_cliente
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L2193) — Tramitesn::update

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L414)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L757)

## can_upload_dropzone_pago_derechos

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1789) — Concluido::single_pago_derechos
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1794) — Concluido::single_pago_derechos
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2077) — Concluido::single_pago_derechos
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5972) — Tramites::single_pago_derechos
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5977) — Tramites::single_pago_derechos
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L2191) — Tramitesn::update

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L308)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L621)

## can_upload_dropzone_pago_gestor

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2177) — Concluido::single_pago_gestor
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2182) — Concluido::single_pago_gestor
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2404) — Concluido::single_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6399) — Tramites::single_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6404) — Tramites::single_pago_gestor
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L2192) — Tramitesn::update

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L357)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L696)

## clone_tramite

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L105) — Concluido::final
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L346) — Proceso::cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L154) — Tramites::tramite
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L378) — Tramites::tramite_2024
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L578) — Tramites::tramite_2025
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L778) — Tramites::finalizados
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L980) — Tramites::tenencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L1175) — Tramites::cotizaciones
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L7789) — Tramites::list_cobro_clientes
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1297) — Tramitesn::tramite
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1512) — Tramitesn::cobro_cliente

## clone_tramite_cancelado

**Controllers**
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L8016) — Tramites::cancelados

## create_tramite

**Controllers**
- [app/Controllers/Deskapp/TramiteWizard.php](app/Controllers/Deskapp/TramiteWizard.php#L47) — TramiteWizard::index
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L1294) — Tramites::add
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L1400) — Tramites::insert
- [app/Controllers/Deskapp/Users.php](app/Controllers/Deskapp/Users.php#L839) — Users::users_mapa

**Vistas**
- [app/Views/deskapp/includes/_header.php](app/Views/deskapp/includes/_header.php#L420)

**Otros (helpers, libraries, etc.)**
- [app/Helpers/permissions_helper.php](app/Helpers/permissions_helper.php#L192)

## delete_tramite

**Controllers**
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L328) — Proceso::cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L135) — Tramites::tramite
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L360) — Tramites::tramite_2024
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L560) — Tramites::tramite_2025
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L760) — Tramites::finalizados
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L962) — Tramites::tenencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L1163) — Tramites::cotizaciones
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L7771) — Tramites::list_cobro_clientes

## delete_tramite_cancelado

**Controllers**
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L7998) — Tramites::cancelados

## delete_tramite_asociado

**Controllers**
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L373) — Tramitesn::services_delete
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1706) — Tramitesn::update

## editar_pago_gestor

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L417) — Concluido::update_gestor_costos
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2170) — Concluido::single_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6381) — Tramites::single_pago_gestor
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L2180) — Tramitesn::update
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L2185) — Tramitesn::update
- [app/Controllers/Deskapp/Users.php](app/Controllers/Deskapp/Users.php#L850) — Users::users_mapa

## editar_tramite

**Controllers**
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L176) — Proceso::documentostatus
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L322) — Proceso::cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L128) — Tramites::tramite
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L354) — Tramites::tramite_2024
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L554) — Tramites::tramite_2025
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L754) — Tramites::finalizados
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L956) — Tramites::tenencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L1157) — Tramites::cotizaciones
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L1990) — Tramites::update_cotizacion
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L7765) — Tramites::list_cobro_clientes

## editar_tramite_cancelado

**Controllers**
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L7992) — Tramites::cancelados

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L161)

**Otros (helpers, libraries, etc.)**
- [app/Helpers/permissions_helper.php](app/Helpers/permissions_helper.php#L205)

## editar_tramite_asociado

**Controllers**
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L285) — Tramitesn::services_update
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1705) — Tramitesn::update

## editar_tramite_principal

**Controllers**
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L432) — Tramitesn::principal_update_tipo
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1704) — Tramitesn::update

## export_final_tramite

**Controllers**
- [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L238) — Customers::list
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L599) — Proceso::final

## export_tramite

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L91) — Concluido::final
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L332) — Proceso::cobro_cliente
- [app/Controllers/Deskapp/TramiteWizard.php](app/Controllers/Deskapp/TramiteWizard.php#L336) — TramiteWizard::exportar_excel
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L139) — Tramites::tramite
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L364) — Tramites::tramite_2024
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L564) — Tramites::tramite_2025
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L764) — Tramites::finalizados
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L966) — Tramites::tenencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L1167) — Tramites::cotizaciones
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L7775) — Tramites::list_cobro_clientes
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1282) — Tramitesn::tramite
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1504) — Tramitesn::cobro_cliente

## export_tramite_cancelado

**Controllers**
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L8002) — Tramites::cancelados

## header_buttons

**Vistas**
- [app/Views/deskapp/includes/_header.php](app/Views/deskapp/includes/_header.php#L397)

## important_cancelar_tramite

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L159)
- [app/Views/deskapp/extra-pages/tramite_cotizacion_view.php](app/Views/deskapp/extra-pages/tramite_cotizacion_view.php#L157)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L289)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L292)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L299)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L1260)

## important_concluir_tramite

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_cobro_cliente_view.php](app/Views/deskapp/extra-pages/tramite_cobro_cliente_view.php#L125)
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L406)
- [app/Views/deskapp/extra-pages/tramite_finalizados_view.php](app/Views/deskapp/extra-pages/tramite_finalizados_view.php#L326)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L290)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L309)

## important_pasar_a_pagos

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L300)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L560)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L754)

**Otros (helpers, libraries, etc.)**
- [app/Helpers/permissions_helper.php](app/Helpers/permissions_helper.php#L315)

## list_cobro_cliente

**Controllers**
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1477) — Tramitesn::cobro_cliente
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1496) — Tramitesn::cobro_cliente
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1647) — Tramitesn::cobro_cliente_ver
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1736) — Tramitesn::update

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L94)

## listar_concluidos_tramite

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L159)

## listar_final_tramite

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L153)
- [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L64)

## listar_settings

**Controllers**
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L85) — Proceso::tipo
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L130) — Proceso::status

**Vistas**
- [app/Views/deskapp/includes/_header_cliente.php](app/Views/deskapp/includes/_header_cliente.php#L361)

## listar_tramite

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L84)

## listar_tramites_concluidos

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L106)

## menu_clientes

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L192)
- [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L82)

## menu_configuracion

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L220)
- [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L95)

## menu_dashboard_admin

**Controllers**
- [app/Controllers/Deskapp/DashboardAdmin.php](app/Controllers/Deskapp/DashboardAdmin.php#L30) — DashboardAdmin::requireDashboardAdminAccess

**Vistas**
- [app/Views/deskapp/dashboard/financiero.php](app/Views/deskapp/dashboard/financiero.php#L213)
- [app/Views/deskapp/dashboard/reportes.php](app/Views/deskapp/dashboard/reportes.php#L33)
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L29)

## menu_dashboard_cliente

**Controllers**
- [app/Controllers/Deskapp/DashboardCliente.php](app/Controllers/Deskapp/DashboardCliente.php#L24) — DashboardCliente::requireClienteAccess

**Vistas**
- [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L4)

## menu_documentos

**Controllers**
- [app/Controllers/Deskapp/Documentos.php](app/Controllers/Deskapp/Documentos.php#L31) — Documentos::guardDocumentosAccess
- [app/Controllers/Deskapp/Tradocstatus.php](app/Controllers/Deskapp/Tradocstatus.php#L28) — Tradocstatus::guardDocStatusAccess

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L242)
- [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L107)

## menu_erp_sa

**Vistas**
- [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L138)

## menu_gestores

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L170)
- [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L70)

## menu_monitoreo_actividad

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L294)

## menu_notifications

**Vistas**
- [app/Views/deskapp/includes/_header.php](app/Views/deskapp/includes/_header.php#L440)
- [app/Views/deskapp/includes/_notifications_dropdown.php](app/Views/deskapp/includes/_notifications_dropdown.php#L7)

## menu_permisos

**Vistas**
- [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L119)

## menu_proceso_final

**Controllers**
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L526) — Proceso::final
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L673) — Proceso::final_documentostatus
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L832) — Proceso::final_evidencias
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L1017) — Proceso::final_pago_derechos
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L1177) — Proceso::final_evidencias_finales

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L142)
- [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L58)

## menu_roles

**Controllers**
- [app/Controllers/Deskapp/Permisos.php](app/Controllers/Deskapp/Permisos.php#L35) — Permisos::guardManagementAccess
- [app/Controllers/Deskapp/Roles.php](app/Controllers/Deskapp/Roles.php#L35) — Roles::guardManagementAccess
- [app/Controllers/Deskapp/Users.php](app/Controllers/Deskapp/Users.php#L61) — Users::guardManagementAccess

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L264)

## menu_tramites

**Controllers**
- [app/Controllers/Deskapp/Flotillas.php](app/Controllers/Deskapp/Flotillas.php#L37) — Flotillas::guardImportAccess

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L73)
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L100)

## menu_tramites_cliente

**Controllers**
- [app/Controllers/Deskapp/ClienteTramites.php](app/Controllers/Deskapp/ClienteTramites.php#L29) — ClienteTramites::requireClienteAccess

**Vistas**
- [app/Views/deskapp/includes/_sidebar_cliente.php](app/Views/deskapp/includes/_sidebar_cliente.php#L5)

## menu_tramites_tenencias

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L112)

## mios_filter_status_11

**Controllers**
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L4491) — Tramites::mios

## mios_filter_status_22

**Controllers**
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L4494) — Tramites::mios

## monitoreo_auditoria_tramite

**Controllers**
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L8279) — Tramites::buscar_por_folio

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L317)

## monitoreo_bitacora_search

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L305)

## monitoreo_correccion_tramites

**Vistas**
- [app/Views/deskapp/includes/_sidebar.php](app/Views/deskapp/includes/_sidebar.php#L311)

## override_puede_editar_modulo

**Otros (helpers, libraries, etc.)**
- [app/Helpers/permissions_helper.php](app/Helpers/permissions_helper.php#L471)

## override_tramite_approved_lock

**Controllers**
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L77) — Tramitesn::requireNotApprovedForSteps123Json

## override_tramite_status_28_readonly

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1796) — Concluido::single_pago_derechos
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2184) — Concluido::single_pago_gestor
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2483) — Concluido::single_cobro_cliente
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2788) — Concluido::single_evidencias_finales
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L2717) — Tramites::delete_comprobante
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L2843) — Tramites::upload_comprobante
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L2932) — Tramites::delete_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L3048) — Tramites::upload_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L4059) — Tramites::update_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5340) — Tramites::single_documentostatus
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5732) — Tramites::single_evidencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5979) — Tramites::single_pago_derechos
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6384) — Tramites::single_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6676) — Tramites::single_cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6980) — Tramites::single_evidencias_finales

## print_final_tramite

**Controllers**
- [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L241) — Customers::list
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L602) — Proceso::final

## print_tramite

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L95) — Concluido::final
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L336) — Proceso::cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L143) — Tramites::tramite
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L368) — Tramites::tramite_2024
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L568) — Tramites::tramite_2025
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L768) — Tramites::finalizados
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L970) — Tramites::tenencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L1171) — Tramites::cotizaciones
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L7779) — Tramites::list_cobro_clientes
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1286) — Tramitesn::tramite
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1508) — Tramitesn::cobro_cliente

## print_tramite_cancelado

**Controllers**
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L8006) — Tramites::cancelados

## quick_action_bitacora

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1534) — Concluido::single_evidencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5715) — Tramites::single_evidencias

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L336)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L332)

## quick_action_bitacora_add

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1535) — Concluido::single_evidencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5727) — Tramites::single_evidencias

## quick_action_bitacora_delete

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1537) — Concluido::single_evidencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5729) — Tramites::single_evidencias

## quick_action_bitacora_edit

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1536) — Concluido::single_evidencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5728) — Tramites::single_evidencias

## quick_action_cobros_cliente

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2472) — Concluido::single_cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6687) — Tramites::single_cobro_cliente

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L340)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L336)

## quick_action_cobros_cliente_add

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2475) — Concluido::single_cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6690) — Tramites::single_cobro_cliente

## quick_action_cobros_cliente_delete

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2480) — Concluido::single_cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6695) — Tramites::single_cobro_cliente

## quick_action_cobros_cliente_edit

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2477) — Concluido::single_cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6692) — Tramites::single_cobro_cliente

## quick_action_documentos

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1181) — Concluido::single_documentostatus
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5321) — Tramites::single_documentostatus

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L335)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L331)

## quick_action_documentos_add

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1182) — Concluido::single_documentostatus
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5335) — Tramites::single_documentostatus

## quick_action_documentos_delete

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1184) — Concluido::single_documentostatus
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5337) — Tramites::single_documentostatus

## quick_action_documentos_edit

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1183) — Concluido::single_documentostatus
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5336) — Tramites::single_documentostatus

## quick_action_evidencias_finales

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2783) — Concluido::single_evidencias_finales
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6965) — Tramites::single_evidencias_finales

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L339)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L335)

## quick_action_evidencias_finales_add

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2784) — Concluido::single_evidencias_finales
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6997) — Tramites::single_evidencias_finales

## quick_action_evidencias_finales_delete

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2786) — Concluido::single_evidencias_finales
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6999) — Tramites::single_evidencias_finales

## quick_action_evidencias_finales_edit

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2785) — Concluido::single_evidencias_finales
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6998) — Tramites::single_evidencias_finales

## quick_action_pago_gestor

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2173) — Concluido::single_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6395) — Tramites::single_pago_gestor

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L338)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L334)

## quick_action_pago_gestor_add

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2176) — Concluido::single_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6398) — Tramites::single_pago_gestor

## quick_action_pago_gestor_delete

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2181) — Concluido::single_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6403) — Tramites::single_pago_gestor

## quick_action_pago_gestor_edit

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2178) — Concluido::single_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6400) — Tramites::single_pago_gestor

## quick_action_pagos_derecho

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1785) — Concluido::single_pago_derechos
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5968) — Tramites::single_pago_derechos

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L337)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L333)

## quick_action_pagos_derecho_add

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1788) — Concluido::single_pago_derechos
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5971) — Tramites::single_pago_derechos

## quick_action_pagos_derecho_delete

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1793) — Concluido::single_pago_derechos
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5976) — Tramites::single_pago_derechos

## quick_action_pagos_derecho_edit

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1790) — Concluido::single_pago_derechos
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5973) — Tramites::single_pago_derechos

## read_final_tramite

**Controllers**
- [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L132) — Customers::list
- [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L244) — Customers::list
- [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L311) — Customers::tramite
- [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L489) — Customers::proceso_documentostatus
- [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L651) — Customers::proceso_evidencias
- [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L839) — Customers::proceso_pago_derechos
- [app/Controllers/Deskapp/Customers.php](app/Controllers/Deskapp/Customers.php#L999) — Customers::proceso_evidencias_finales
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L605) — Proceso::final

## read_tramite

**Controllers**
- [app/Controllers/Deskapp/Cancelado.php](app/Controllers/Deskapp/Cancelado.php#L83) — Cancelado::cancelado
- [app/Controllers/Deskapp/Cancelado.php](app/Controllers/Deskapp/Cancelado.php#L229) — Cancelado::cancelado_documentostatus
- [app/Controllers/Deskapp/Cancelado.php](app/Controllers/Deskapp/Cancelado.php#L394) — Cancelado::cancelado_evidencias
- [app/Controllers/Deskapp/Cancelado.php](app/Controllers/Deskapp/Cancelado.php#L581) — Cancelado::cancelado_pago_derechos
- [app/Controllers/Deskapp/Cancelado.php](app/Controllers/Deskapp/Cancelado.php#L745) — Cancelado::cancelado_evidencias_finales
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L74) — Concluido::final
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L99) — Concluido::final
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L824) — Concluido::getDependentData
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L885) — Concluido::getGestoresByEmpresaId
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1170) — Concluido::single_documentostatus
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1523) — Concluido::single_evidencias
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2770) — Concluido::single_evidencias_finales
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L164) — Proceso::documentostatus
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L340) — Proceso::cobro_cliente
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L1314) — Proceso::concluido
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L1461) — Proceso::concluido_documentostatus
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L1620) — Proceso::concluido_evidencias
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L1803) — Proceso::concluido_pago_derechos
- [app/Controllers/Deskapp/Proceso.php](app/Controllers/Deskapp/Proceso.php#L1961) — Proceso::concluido_evidencias_finales
- [app/Controllers/Deskapp/TramiteWizard.php](app/Controllers/Deskapp/TramiteWizard.php#L82) — TramiteWizard::listado
- [app/Controllers/Deskapp/TramiteWizard.php](app/Controllers/Deskapp/TramiteWizard.php#L301) — TramiteWizard::recuperar_borrador
- [app/Controllers/Deskapp/TramiteWizard.php](app/Controllers/Deskapp/TramiteWizard.php#L477) — TramiteWizard::get_municipios
- [app/Controllers/Deskapp/TramiteWizard.php](app/Controllers/Deskapp/TramiteWizard.php#L513) — TramiteWizard::get_ejecutivos_cliente
- [app/Controllers/Deskapp/TramiteWizard.php](app/Controllers/Deskapp/TramiteWizard.php#L563) — TramiteWizard::get_gestores
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L147) — Tramites::tramite
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L372) — Tramites::tramite_2024
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L572) — Tramites::tramite_2025
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L772) — Tramites::finalizados
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L974) — Tramites::tenencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L3341) — Tramites::getDependentData
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5322) — Tramites::single_documentostatus
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5716) — Tramites::single_evidencias
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L7783) — Tramites::list_cobro_clientes
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L99) — Tramitesn::search
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L162) — Tramitesn::services
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1291) — Tramitesn::tramite

## read_tramite_cancelado

**Controllers**
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L8010) — Tramites::cancelados

**Vistas**
- [app/Views/deskapp/includes/_header.php](app/Views/deskapp/includes/_header.php#L413)

## section_asigna_gestor

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L235)
- [app/Views/deskapp/extra-pages/tramite_finalizados_view.php](app/Views/deskapp/extra-pages/tramite_finalizados_view.php#L215)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L498)

## section_documentos_pago

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L296)
- [app/Views/deskapp/extra-pages/tramite_finalizados_view.php](app/Views/deskapp/extra-pages/tramite_finalizados_view.php#L275)

## section_final_costos

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2469) — Concluido::single_cobro_cliente
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2780) — Concluido::single_evidencias_finales
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5320) — Tramites::single_documentostatus
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6673) — Tramites::single_cobro_cliente
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6966) — Tramites::single_evidencias_finales
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6977) — Tramites::single_evidencias_finales
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L2176) — Tramitesn::update
- [app/Controllers/Deskapp/Users.php](app/Controllers/Deskapp/Users.php#L853) — Users::users_mapa

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L389)
- [app/Views/deskapp/extra-pages/tramite_finalizados_view.php](app/Views/deskapp/extra-pages/tramite_finalizados_view.php#L309)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L332)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L727)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L328)

## section_inicial_datos

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L210)
- [app/Views/deskapp/extra-pages/tramite_cotizacion_view.php](app/Views/deskapp/extra-pages/tramite_cotizacion_view.php#L183)
- [app/Views/deskapp/extra-pages/tramite_finalizados_view.php](app/Views/deskapp/extra-pages/tramite_finalizados_view.php#L190)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L455)

## section_linea_captura

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L277)
- [app/Views/deskapp/extra-pages/tramite_finalizados_view.php](app/Views/deskapp/extra-pages/tramite_finalizados_view.php#L256)

## section_pago_derechos

**Controllers**
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L2174) — Tramitesn::update

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L258)
- [app/Views/deskapp/extra-pages/tramite_finalizados_view.php](app/Views/deskapp/extra-pages/tramite_finalizados_view.php#L237)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L524)

## section_pago_gestor

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L2169) — Concluido::single_pago_gestor
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L6380) — Tramites::single_pago_gestor
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L2175) — Tramitesn::update
- [app/Controllers/Deskapp/Users.php](app/Controllers/Deskapp/Users.php#L849) — Users::users_mapa

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_concluido_final.php](app/Views/deskapp/extra-pages/tramite_concluido_final.php#L338)
- [app/Views/deskapp/extra-pages/tramite_finalizados_view.php](app/Views/deskapp/extra-pages/tramite_finalizados_view.php#L285)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L331)
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L661)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L327)

## tramite_view_gestor

**Controllers**
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L2439) — Tramites::update_solicitud
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L2544) — Tramites::update_recoleccion
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L2647) — Tramites::update_en_tramite

## tramitesn_filter_owner_only

**Controllers**
- [app/Controllers/Deskapp/Tramitesn.php](app/Controllers/Deskapp/Tramitesn.php#L1257) — Tramitesn::tramite

## wizard_list_only_own

**Controllers**
- [app/Controllers/Deskapp/TramiteWizard.php](app/Controllers/Deskapp/TramiteWizard.php#L107) — TramiteWizard::listado

## write_tramite_asigna_gestor

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L190)

## write_tramite_datos_tramite

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L482)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L189)

## write_tramite_pago_derechos

**Controllers**
- [app/Controllers/Deskapp/Concluido.php](app/Controllers/Deskapp/Concluido.php#L1783) — Concluido::single_pago_derechos
- [app/Controllers/Deskapp/Tramites.php](app/Controllers/Deskapp/Tramites.php#L5966) — Tramites::single_pago_derechos

**Vistas**
- [app/Views/deskapp/extra-pages/tramite_update_view.php](app/Views/deskapp/extra-pages/tramite_update_view.php#L620)
- [app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php](app/Views/deskapp/extra-pages/tramite_update_view_nuevo.php#L191)

