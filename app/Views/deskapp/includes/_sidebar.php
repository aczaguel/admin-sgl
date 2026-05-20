<?php
	helper(['permissions']);
	$session = session();
	$perms = $session->get('user_permissions') ?? [];
	$roles = $session->get('user_roles') ?? [];
	$useClienteSidebar = has_permission_strict('ui_sidebar_cliente', $perms)
		&& !(
			has_permission('menu_dashboard_admin', $perms, $roles)
			|| has_permission('menu_tramites', $perms, $roles)
			|| has_permission('menu_proceso_final', $perms, $roles)
			|| has_permission('menu_gestores', $perms, $roles)
			|| has_permission('menu_clientes', $perms, $roles)
			|| has_permission('menu_configuracion', $perms, $roles)
			|| has_permission('menu_permisos', $perms, $roles)
		);
	if ($useClienteSidebar) {
		echo view('deskapp/includes/_sidebar_cliente', ['session' => $session]);
		return;
	}
?>
<div class="left-side-bar" data-sidebar-version="2026-04-06-1">
	<div class="brand-logo">
		<div class="sgl-sidebar-collapse-btn sgl-sidebar-collapse-btn--sidebar" role="button" tabindex="0" aria-label="Contraer/expandir menú" title="Contraer/expandir menú">
			<i class="dw dw-left-arrow" aria-hidden="true"></i>
		</div>
		<a href="<?php echo base_url('deskapp/dashboard'); ?>" class="sgl-brand-link" style="display: flex; justify-content: center; align-items: center; padding: 10px 0;">
			<img src="<?php echo base_url(); ?>/public/assets/vendors/images/logo_sgl_bicolor.png" alt="Logo SGL" class="dark-logo sgl-brand-image" style="max-width: 150px; height: auto;">
			<img src="<?php echo base_url(); ?>/public/assets/vendors/images/logo_sgl_bicolor.png" alt="Logo SGL" class="light-logo sgl-brand-image" style="max-width: 150px; height: auto;">
		</a>
		<div class="close-sidebar" data-toggle="left-sidebar-close">
			<i class="ion-close-round"></i>
		</div>
	</div>
	<div class="menu-block customscroll">
		<div class="sidebar-menu">
			<ul id="accordion-menu">
				<?php 
					// Sidebar gobernado por permisos (sin gates por rol).
				?>

				<?php if (has_permission('menu_dashboard_admin', $session->get('user_permissions'), $session->get('user_roles'))): ?>
				<!-- SECCIÓN: DASHBOARD ADMINISTRATIVO -->
				<li class="menu-section-title">
					<span><i class="fas fa-chart-line me-2"></i> Análisis y Reportes</span>
					<?= perm_audit_tag('menu_dashboard_admin', $session) ?>
				</li>
				<li class="dropdown">
					<a href="javascript:;" class="dropdown-toggle">
						<span class="micon"><i class="fas fa-tachometer-alt"></i></span>
						<span class="mtext">Dashboard Admin</span>
					</a>
					<ul class="submenu">
						<li><a href="<?php echo base_url('deskapp/dashboardadmin'); ?>">
							<i class="fas fa-home"></i> Panel Principal 2026
						</a></li>
						<li><a href="<?php echo base_url('deskapp/dashboardadmin/alertas'); ?>">
							<i class="fas fa-exclamation-triangle text-warning"></i> Alertas Críticas
						</a></li>
						<li><a href="<?php echo base_url('deskapp/dashboardadmin/financiero'); ?>">
							<i class="fas fa-file-invoice-dollar text-success"></i> Análisis Financiero
						</a></li>
						<li><a href="<?php echo base_url('deskapp/dashboardadmin/reportes'); ?>">
							<i class="fas fa-chart-bar"></i> Reportes y Estadísticas
						</a></li>
						<li><a href="<?php echo base_url('deskapp/dashboardadmin/por_cliente'); ?>">
							<i class="fas fa-building"></i> Trámites por Cliente
						</a></li>
						<li class="submenu-title sgl-submenu-title"><span style="display:block;color:rgba(255,255,255,.58);font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;">Histórico por Año</span></li>
						<li><a href="<?php echo base_url('deskapp/dashboardadmin?anio=2025'); ?>">
							<i class="fas fa-calendar-alt"></i> 2025
						</a></li>
						<li><a href="<?php echo base_url('deskapp/dashboardadmin?anio=2024'); ?>">
							<i class="fas fa-calendar-alt"></i> 2024
						</a></li>
						<li><a href="<?php echo base_url('deskapp/dashboardadmin?anio=2023'); ?>">
							<i class="fas fa-calendar-alt"></i> 2023
						</a></li>
					</ul>
				</li>
				<?php endif; ?>



				<!-- SECCIÓN: TRÁMITES -->
				<?php if (has_permission('menu_tramites', $session->get('user_permissions'), $session->get('user_roles'))): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-file-alt me-2"></i> Gestión de Trámites</span>
						<?= perm_audit_tag('menu_tramites', $session) ?>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon"><i class="fas fa-folder-open"></i></span>
							<span class="mtext">Trámites</span>
						</a>
						<ul class="submenu">
							<?php if (has_permission('listar_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramitesn/tramite'); ?>">
									<i class="fas fa-magic text-primary"></i> Trámites (nuevo flujo)
									<?= perm_audit_tag('listar_tramite', $session) ?>
								</a></li>
							<?php endif; ?>
							<?php if (has_permission('search_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramitesn/search'); ?>">
									<i class="fas fa-search text-info"></i> Busca un trámite
									<?= perm_audit_tag('search_tramite', $session) ?>
								</a></li>
							<?php endif; ?>
							<?php if (has_permission('menu_tramites', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/flotillas/import'); ?>">
									<i class="fas fa-layer-group text-info"></i> Flotillas (Importar)
									<?= perm_audit_tag('menu_tramites', $session) ?>
								</a></li>
							<?php endif; ?>
							<?php if (has_permission('menu_tramites', $session->get('user_permissions'), $session->get('user_roles')) && has_permission('create_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramites_masivos/import'); ?>">
									<i class="fas fa-file-upload text-success"></i> Trámites Masivos
									<?= perm_audit_tag('menu_tramites', $session) ?>
									<?= perm_audit_tag('create_tramite', $session) ?>
								</a></li>
							<?php endif; ?>
							<?php if (has_permission('listar_tramites_concluidos', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/concluido/final'); ?>">
									<i class="fas fa-check-circle"></i> Concluidos
									<?= perm_audit_tag('listar_tramites_concluidos', $session) ?>
								</a></li>
							<?php endif; ?>	
							<?php if (has_permission('menu_tramites_tenencias', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramites/tenencias'); ?>">
									<i class="fas fa-car"></i> Tenencias
									<?= perm_audit_tag('menu_tramites_tenencias', $session) ?>
								</a></li>
							<?php endif; ?>	
						</ul>
					</li>
				<?php endif; ?>

				<?php if (has_permission('list_cobro_cliente', $session->get('user_permissions'), $session->get('user_roles'))): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-hand-holding-usd me-2"></i> Cobranza</span>
						<?= perm_audit_tag('list_cobro_cliente', $session) ?>
					</li>
					<li>
						<a href="<?php echo base_url('deskapp/cobranza'); ?>" class="dropdown-toggle no-arrow">
							<span class="micon"><i class="fas fa-receipt"></i></span>
							<span class="mtext">Centro de Cobranza</span>
						</a>
					</li>
				<?php endif; ?>
				
				<!-- SECCIÓN: WIZARD TRÁMITES
				<li class="menu-section-title">
					<span><i class="fas fa-magic me-2"></i> Creación Rápida</span>
				</li>
				<li class="dropdown">
					<a href="javascript:;" class="dropdown-toggle">
						<span class="micon"><i class="fas fa-magic"></i></span>
						<span class="mtext">Wizard Trámites</span>
					</a>
					<ul class="submenu">
						<li><a href="<?php echo base_url('deskapp/tramitewizard'); ?>">
							<i class="fas fa-plus-circle text-primary"></i> Crear Nuevo Trámite
						</a></li>
						<li><a href="<?php echo base_url('deskapp/tramitewizard/listado'); ?>">
							<i class="fas fa-list-ul"></i> Listado de Trámites
						</a></li>
					</ul>
				</li> -->
				
				<!-- SECCIÓN: PROCESO FINAL -->
				<?php if (has_permission('menu_proceso_final', $session->get('user_permissions'), $session->get('user_roles'))): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-flag-checkered me-2"></i> Cierre de Trámites</span>
						<?= perm_audit_tag('menu_proceso_final', $session) ?>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon"><i class="fas fa-tasks"></i></span>
							<span class="mtext">Cierre</span>
						</a>
						<ul class="submenu">
							<?php if (has_permission('read_final_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/proceso/final'); ?>">
									<i class="fas fa-check-double text-success"></i> Finalizado
									<?= perm_audit_tag('read_final_tramite', $session) ?>
								</a></li>
							<?php endif; ?>	
							<?php if (has_permission('listar_tramites_cancelado', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramites/cancelados'); ?>">
									<i class="fas fa-times-circle text-danger"></i> Cancelados
									<?= perm_audit_tag('listar_tramites_cancelado', $session) ?>
								</a></li>
							<?php endif; ?>	
						</ul>
					</li>
				<?php endif; ?>
				
				<!-- SECCIÓN: GESTORES -->
				<?php if (has_permission('menu_gestores', $session->get('user_permissions'), $session->get('user_roles'))): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-handshake me-2"></i> Gestión de Gestores</span>
						<?= perm_audit_tag('menu_gestores', $session) ?>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon"><i class="fas fa-handshake"></i></span>
							<span class="mtext">Gestores</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/gestores/gestores'); ?>">
								<i class="fas fa-building"></i> Empresa Gestora
							</a></li>
							<li><a href="<?php echo base_url('deskapp/gestores/gestor'); ?>">
								<i class="fas fa-user-tie"></i> Gestor
							</a></li>
						</ul>
					</li>
				<?php endif; ?>
				
				<!-- SECCIÓN: CLIENTES -->
				<?php if (has_permission('menu_clientes', $session->get('user_permissions'), $session->get('user_roles'))): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-users me-2"></i> Gestión de Clientes</span>
						<?= perm_audit_tag('menu_clientes', $session) ?>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon"><i class="fas fa-user-friends"></i></span>
							<span class="mtext">Clientes</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/clientes/cliente'); ?>">
								<i class="fas fa-user-circle"></i> Cliente
							</a></li>
							<li><a href="<?php echo base_url('deskapp/clidirecto/clidirecto'); ?>">
								<i class="fas fa-building"></i> Clientes directos
								</a></li>
							<li><a href="<?php echo base_url('deskapp/clidirecto/ejecutivo'); ?>">
								<i class="fas fa-user-tie"></i> Ejecutivos de cliente
								</a></li>
							<!-- <li><a href="<?php echo base_url('deskapp/clientes/contactos'); ?>">
								<i class="fas fa-address-book"></i> Contactos
							</a></li> -->
						</ul>
					</li>
				<?php endif; ?>
				
				<!-- SECCIÓN: CONFIGURACIÓN -->
				<?php if (has_permission('menu_configuracion', $session->get('user_permissions'), $session->get('user_roles'))): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-cog me-2"></i> Configuración del Sistema</span>
						<?= perm_audit_tag('menu_configuracion', $session) ?>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon"><i class="fas fa-cogs"></i></span>
							<span class="mtext">Configuración</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/tramites/tipo'); ?>">
								<i class="fas fa-tags"></i> Tipo de Trámite
							</a></li>
							<li><a href="<?php echo base_url('deskapp/tramites/status'); ?>">
								<i class="fas fa-traffic-light"></i> Estatuses de Trámite
							</a></li>
						</ul>
					</li>
				<?php endif; ?>
				
				<!-- SECCIÓN: DOCUMENTOS -->
				<?php if (has_permission('menu_documentos', $session->get('user_permissions'), $session->get('user_roles'))): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-folder me-2"></i> Gestión Documental</span>
						<?= perm_audit_tag('menu_documentos', $session) ?>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon"><i class="fas fa-file-alt"></i></span>
							<span class="mtext">Documentos</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/documentos/documento'); ?>">
								<i class="fas fa-file"></i> Documento
							</a></li>
							<li><a href="<?php echo base_url('deskapp/documentos/status'); ?>">
								<i class="fas fa-info-circle"></i> Status de Documentos
							</a></li>
						</ul>
					</li>
				<?php endif; ?>
				
				<!-- SECCIÓN: PERMISOS Y USUARIOS -->
				<?php if (has_permission('menu_roles', $session->get('user_permissions'), $session->get('user_roles'))): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-shield-alt me-2"></i> Seguridad y Accesos</span>
						<?= perm_audit_tag('menu_roles', $session) ?>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon"><i class="fas fa-user-shield"></i></span>
							<span class="mtext">Roles y Permisos</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/roles/roles'); ?>">
								<i class="fas fa-user-tag"></i> Roles
							</a></li>
							<li><a href="<?php echo base_url('deskapp/permisos/permisos'); ?>">
								<i class="fas fa-key"></i> Permisos
							</a></li>
							<li><a href="<?php echo base_url('deskapp/roles/role_permissions'); ?>">
								<i class="fas fa-link"></i> Roles-Permisos
							</a></li>
							<li><a href="<?php echo base_url('deskapp/users/users'); ?>">
								<i class="fas fa-users"></i> Usuarios
							</a></li>
							<li><a href="<?php echo base_url('deskapp/users/user_roles'); ?>">
								<i class="fas fa-user-cog"></i> Usuarios-Roles
							</a></li>
						</ul>
					</li>
				<?php endif; ?>

				<?php if (has_permission('menu_monitoreo_actividad', $session->get('user_permissions'), $session->get('user_roles'))): ?>
				<li class="menu-section-title">
					<span><i class="fas fa-eye me-2"></i> Monitoreo de Actividad</span>
					<?= perm_audit_tag('menu_monitoreo_actividad', $session) ?>
				</li>
				<li class="dropdown">
					<a href="javascript:;" class="dropdown-toggle">
						<span class="micon"><i class="fas fa-clipboard-list"></i></span>
						<span class="mtext">Monitoreo de Actividad</span>
					</a>
					<ul class="submenu">
						<?php if (has_permission('monitoreo_bitacora_search', $session->get('user_permissions'), $session->get('user_roles'))): ?>
							<li><a href="<?php echo site_url('/bitacora/search'); ?>">
								<i class="fas fa-history text-primary"></i> Bitacora Search
								<?= perm_audit_tag('monitoreo_bitacora_search', $session) ?>
							</a></li>
						<?php endif; ?>
						<?php if (has_permission('monitoreo_correccion_tramites', $session->get('user_permissions'), $session->get('user_roles'))): ?>
							<li><a href="<?php echo base_url('correccion-tramites'); ?>">
								<i class="fas fa-edit text-info"></i> Corrección de Trámites
								<?= perm_audit_tag('monitoreo_correccion_tramites', $session) ?>
							</a></li>
						<?php endif; ?>
						<?php if (has_permission('monitoreo_auditoria_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
							<li><a href="<?php echo base_url('deskapp/tramites/audit_search'); ?>">
								<i class="fas fa-clipboard-check"></i> Auditoría de Trámite
								<?= perm_audit_tag('monitoreo_auditoria_tramite', $session) ?>
							</a></li>
						<?php endif; ?>
					</ul>
				</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
<div class="mobile-menu-overlay"></div>
