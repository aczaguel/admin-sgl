<?php
	helper(['permissions']);
	$session = session();
	$userRolesForSidebar = $session->get('user_roles') ?? [];
	if (is_client($userRolesForSidebar) && !is_super_admin($userRolesForSidebar)) {
		echo view('deskapp/includes/_sidebar_cliente', ['session' => $session]);
		return;
	}
?>
<div class="left-side-bar">
	<div class="brand-logo">
		<div class="sgl-sidebar-collapse-btn sgl-sidebar-collapse-btn--sidebar" role="button" tabindex="0" aria-label="Contraer/expandir menú" title="Contraer/expandir menú">
			<i class="dw dw-left-arrow" aria-hidden="true"></i>
		</div>
		<a href="<?php echo base_url('deskapp/dashboard'); ?>" style="display: flex; justify-content: center; align-items: center; width: 100%; padding: 10px 0;">
			<img src="<?php echo base_url(); ?>/public/assets/vendors/images/logoes_sgt.png" alt="Logo SGT" class="dark-logo" style="max-width: 150px; height: auto;">
			<img src="<?php echo base_url(); ?>/public/assets/vendors/images/logoes_sgt_white.png" alt="Logo SGT" class="light-logo" style="max-width: 150px; height: auto;">
		</a>
		<div class="close-sidebar" data-toggle="left-sidebar-close">
			<i class="ion-close-round"></i>
		</div>
	</div>
	<div class="menu-block customscroll">
		<div class="sidebar-menu">
			<ul id="accordion-menu">
				<?php 
					helper(['cliente_filter']);
					// Determinar si el usuario es Admin o Super Admin por rol
					$userRole = $session->get('user_roles');
					$userRoleStr = is_array($userRole) ? implode(',', $userRole) : (string)$userRole;
					$userRoleLower = strtolower(str_replace(' ', '', $userRoleStr)); // Remover espacios y convertir a minúsculas
					$isAdmin = user_is_admin($session->get('id'));
					$hasGlobalClienteAccess = user_has_global_cliente_access($session->get('id'));
					$isSuperAdmin = is_super_admin($userRole);
					$isLimitedAdmin = $isAdmin && !$isSuperAdmin && !$hasGlobalClienteAccess;
				?>

				<?php if ($isAdmin || has_permission('menu_dashboard_admin', $session->get('user_permissions'), $session->get('user_roles'))): ?>
				<!-- SECCIÓN: DASHBOARD ADMINISTRATIVO -->
				<li class="menu-section-title">
					<span><i class="fas fa-chart-line me-2"></i> Análisis y Reportes</span>
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
						<li class="submenu-title">Histórico por Año</li>
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
								</a></li>
								<li><a href="<?php echo base_url('deskapp/tramitesn/search'); ?>">
									<i class="fas fa-search text-info"></i> Busca un trámite
								</a></li>
							<?php endif; ?>
							<?php if (has_permission('section_final_costos', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramitesn/cobro_cliente'); ?>">
									<i class="fas fa-receipt text-success"></i> Cobro a Cliente
								</a></li>
							<?php endif; ?>
							<?php if (has_permission('menu_tramites', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/flotillas/import'); ?>">
									<i class="fas fa-layer-group text-info"></i> Flotillas (Importar)
								</a></li>
							<?php endif; ?>
							<?php if (has_permission('listar_tramites_concluidos', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/concluido/final'); ?>">
									<i class="fas fa-check-circle"></i> Concluidos
								</a></li>
							<?php endif; ?>	
							<?php if (has_permission('menu_tramites_tenencias', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramites/tenencias'); ?>">
									<i class="fas fa-car"></i> Tenencias
								</a></li>
							<?php endif; ?>	
						</ul>
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
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon"><i class="fas fa-tasks"></i></span>
							<span class="mtext">Cierre</span>
						</a>
						<ul class="submenu">
							<?php if (has_permission('listar_final_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/proceso/final'); ?>">
									<i class="fas fa-check-double text-success"></i> Finalizado
								</a></li>
							<?php endif; ?>	
							<?php if (has_permission('listar_concluidos_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramites/cancelados'); ?>">
									<i class="fas fa-times-circle text-danger"></i> Cancelados
								</a></li>
							<?php endif; ?>	
						</ul>
					</li>
				<?php endif; ?>
				
				<!-- SECCIÓN: GESTORES -->
				<?php if (has_permission('menu_gestores', $session->get('user_permissions'), $session->get('user_roles')) && !$isLimitedAdmin): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-handshake me-2"></i> Gestión de Gestores</span>
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
				<?php if (has_permission('menu_clientes', $session->get('user_permissions'), $session->get('user_roles')) && !$isLimitedAdmin): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-users me-2"></i> Gestión de Clientes</span>
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
				<?php if (has_permission('menu_configuracion', $session->get('user_permissions'), $session->get('user_roles')) && !$isLimitedAdmin): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-cog me-2"></i> Configuración del Sistema</span>
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
				<?php if (has_permission('menu_documentos', $session->get('user_permissions'), $session->get('user_roles')) && !$isLimitedAdmin): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-folder me-2"></i> Gestión Documental</span>
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
				<?php if (has_permission('menu_roles', $session->get('user_permissions'), $session->get('user_roles')) && !$isLimitedAdmin): ?>
					<li class="menu-section-title">
						<span><i class="fas fa-shield-alt me-2"></i> Seguridad y Accesos</span>
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

				<?php if ($isAdmin): ?>
				<li class="menu-section-title">
					<span><i class="fas fa-eye me-2"></i> Monitoreo de Actividad</span>
				</li>
				<li class="dropdown">
					<a href="javascript:;" class="dropdown-toggle">
						<span class="micon"><i class="fas fa-clipboard-list"></i></span>
						<span class="mtext">Monitoreo de Actividad</span>
					</a>
					<ul class="submenu">
						<li><a href="<?php echo site_url('/bitacora/search'); ?>">
							<i class="fas fa-history text-primary"></i> Bitacora Search
						</a></li>
						<li><a href="<?php echo base_url('correccion-tramites'); ?>">
							<i class="fas fa-edit text-info"></i> Corrección de Trámites
						</a></li>
						<li><a href="<?php echo base_url('deskapp/tramites/audit_search'); ?>">
							<i class="fas fa-clipboard-check"></i> Auditoría de Trámite
						</a></li>
					</ul>
				</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
<div class="mobile-menu-overlay"></div>
