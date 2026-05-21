<!-- <div class="pre-loader">
	<div class="pre-loader-box">
		<div class="loader-logo"><img width="50%" src="<?php echo base_url(); ?>/public/assets/vendors/images/logo_sasgl_bicolor.png" alt="Logo SASGL"></div>
		<div class='loader-progress' id="progress_div">
			<div class='bar' id='bar1'></div>
		</div>
		<div class='percent' id='percent1'>0%</div>
		<div class="loading-text">
			Loading...
		</div>
	</div>
</div>
 -->

<?php
	helper(['cliente_filter', 'cliente_context', 'permissions']);

	$session = $session ?? session();
	$userId = $session->get('id');
	$request = service('request');
	$requestedClienteId = $request ? $request->getGet('cliente_id') : null;

	$cliente_id_filtro = $cliente_id_filtro ?? resolve_active_cliente_id($userId, $requestedClienteId);
	$clientes_lista = $clientes_lista ?? get_clientes_lista_for_user($userId);

	$clientes_count = is_array($clientes_lista) ? count($clientes_lista) : 0;
	$isAdmin = user_is_admin($userId);
	$solo_uno = ($clientes_count === 1) && !user_has_global_cliente_access($userId);
	$nombre_unico = $solo_uno ? ($clientes_lista[0]['nombre'] ?? 'Cliente') : null;

	$currentUrl = function_exists('current_url') ? current_url() : '';
	$qs = $_GET ?? [];
?>

<link rel="stylesheet" href="<?= base_url('/public/assets/src/styles/sgl_layout_2026.css') ?>">


<style>
    .pre-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9); /* Fondo semitransparente */
        z-index: 9999; /* Asegura que el preloader esté por encima de todo */
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .pre-loader-box {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%; /* Ajusta la altura si es necesario */
        flex-direction: column; /* Asegura que el contenido se apile en una columna */
    }

    .loader-logo {
        width: 100%;
        display: flex;
        justify-content: center;
    }

    .loader-logo img {
        max-width: 100%;
        height: auto;
    }

	.btn-custom {
        padding: 5px 15px; /* Relleno reducido para hacer los botones más estilizados */
        font-size: 11px !important; /* Tipografía más pequeña */
        font-weight: 200; /* Tipografía más clara */
        border-radius: 5px; /* Borde redondeado */
		width: 120px;
		height: 35px;
		margin-top: 20px;
    }

    .btn-custom i {
        margin-right: 1px; /* Separación entre el ícono y el texto */
    }

    /* .btn-danger.btn-lg {
        font-size: 14px; 
    } */

    .btn-group a {
        margin-right: 5px; /* Separar los botones ligeramente */
    }

	.btn-xs {
		font-size: 9px !important;
    	padding: 2px 6px !important;
	}

	/* Animación pulse para botón destacado */
	@keyframes pulse {
		0% {
			box-shadow: 0 4px 15px rgba(255, 107, 107, 0.5);
		}
		50% {
			box-shadow: 0 4px 20px rgba(255, 107, 107, 0.7), 0 0 0 8px rgba(255, 107, 107, 0.1);
		}
		100% {
			box-shadow: 0 4px 15px rgba(255, 107, 107, 0.5);
		}
	}

	/* Ajustes finos del header (perfil/notificaciones) */
	.header-right .user-info-dropdown .dropdown-toggle.no-arrow::after {
		display: none;
	}
	.header-right .user-info-dropdown .dropdown-toggle {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 0;
	}
	/* Evita “saltitos” por cambios de glyph/inline layout */
	.header-right .user-info-dropdown .dropdown-toggle .user-icon {
		flex: 0 0 auto;
	}
	/* Sube un poco la campanita sin afectar otros layouts */
	.header-right .user-notification {
		padding-top: 16px;
	}

	/* Dropdown usuario: bloque de sesión más prolijo */
	.header-right .user-info-dropdown .dropdown-menu{
		min-width: 260px;
		max-width: min(360px, 92vw);
	}
	.header-right .user-info-dropdown .sgl-session-summary{
		padding: 12px 14px;
		background: #f8f9fa;
		border-bottom: 1px solid #eef0f2;
	}
	.header-right .user-info-dropdown .sgl-session-summary-inner{
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.header-right .user-info-dropdown .sgl-session-indicator{
		width: 10px;
		height: 10px;
		border-radius: 50%;
		background: #28a745;
		box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.15);
		flex: 0 0 auto;
	}
	.header-right .user-info-dropdown .sgl-session-label{
		font-size: 10px;
		font-weight: 700;
		color: #6c757d;
		text-transform: uppercase;
		letter-spacing: 0.08em;
		line-height: 1.2;
		margin: 0 0 2px 0;
	}
	.header-right .user-info-dropdown .sgl-session-name{
		font-size: 14px;
		font-weight: 700;
		color: #202342;
		line-height: 1.2;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		max-width: 300px;
	}
	.header-right .user-info-dropdown .sgl-session-meta{
		margin-top: 3px;
		font-size: 12px;
		font-weight: 600;
		color: #6c757d;
		line-height: 1.2;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		max-width: 300px;
	}
	.header-right .user-info-dropdown .dropdown-divider{
		height: 1px;
		background: #e9ecef;
		margin: 0;
	}

	/* Selector de cliente en header (lado izquierdo) */
	.header-left .sgl-cliente-context{
		display: flex;
		align-items: center;
		gap: 10px;
		margin-left: 15px;
	}
	.header-left .sgl-cliente-context .sgl-cliente-label{
		font-size: 10px;
		font-weight: 700;
		color: #6c757d;
		text-transform: uppercase;
		letter-spacing: 0.08em;
		line-height: 1.2;
		margin: 0;
	}
	.header-left .sgl-cliente-context .sgl-cliente-name{
		font-size: 12px;
		font-weight: 700;
		color: #202342;
		line-height: 1.2;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		max-width: 220px;
	}
	.header-left .sgl-cliente-context select.form-control{
		height: 34px;
		padding-top: 4px;
		padding-bottom: 4px;
		font-size: 12px;
		min-width: 260px;
	}

	/* Sidebar colapsable (desktop): icon-only + persistencia */
	.header-left .sgl-sidebar-collapse-btn{
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 36px;
		height: 36px;
		margin-left: 8px;
		border-radius: 8px;
		background: #f3f5f7;
		color: #111;
		cursor: pointer;
		transition: background 0.2s ease-in-out, transform 0.2s ease-in-out;
	}
	/* En <=1300px el botón funciona como abrir/cerrar (overlay). */
	@media (max-width: 1300px) {
		.header-left .sgl-sidebar-collapse-btn{ width: 40px; height: 40px; }
	}

	/* Botón también visible dentro del sidebar (brand-logo) */
	.left-side-bar .brand-logo{ position: relative; }
	.left-side-bar .brand-logo .sgl-sidebar-collapse-btn--sidebar{
		position: absolute;
		left: 10px;
		top: 18px;
		margin-left: 0;
	}
	.header-left .sgl-sidebar-collapse-btn:hover{
		background: #e8ecf0;
	}
	@media (min-width: 1301px) {
		body.sgl-sidebar-collapsed .left-side-bar{
			width: 72px;
		}
		body.sgl-sidebar-collapsed .left-side-bar .brand-logo{
			width: 72px;
			padding-left: 0;
			padding-right: 0;
		}
		body.sgl-sidebar-collapsed .left-side-bar .brand-logo a{
			justify-content: center !important;
		}
		body.sgl-sidebar-collapsed .left-side-bar .brand-logo a img{
			max-width: 46px !important;
		}
		body.sgl-sidebar-collapsed .header{
			width: calc(100% - 72px);
		}
		body.sgl-sidebar-collapsed .main-container{
			padding-left: 92px;
		}

		body.sgl-sidebar-collapsed .left-side-bar .sidebar-menu .menu-section-title{
			display: none;
		}
		body.sgl-sidebar-collapsed .left-side-bar .sidebar-menu .dropdown-toggle .mtext{
			display: none;
		}
		body.sgl-sidebar-collapsed .left-side-bar .sidebar-menu .dropdown-toggle:after{
			display: none;
		}

		body.sgl-sidebar-collapsed .left-side-bar .sidebar-menu .dropdown-toggle{
			padding-left: 0;
			padding-right: 0;
			text-align: center;
		}
		body.sgl-sidebar-collapsed .left-side-bar .sidebar-menu .dropdown-toggle .micon{
			left: 0;
			width: 72px;
			text-align: center;
		}

		/* Submenús como flyout al pasar el mouse */
		body.sgl-sidebar-collapsed .left-side-bar .sidebar-menu > ul > li.dropdown{
			position: relative;
		}
		body.sgl-sidebar-collapsed .left-side-bar .sidebar-menu > ul > li.dropdown > .submenu{
			position: absolute;
			left: 72px;
			top: 0;
			min-width: 240px;
			background: #142127;
			border-radius: 10px;
			padding: 10px 0;
			box-shadow: 0 10px 30px rgba(0,0,0,0.25);
			display: none;
			z-index: 2000;
		}
		body.sgl-sidebar-collapsed .left-side-bar .sidebar-menu > ul > li.dropdown:hover > .submenu{
			display: block;
		}
		body.sgl-sidebar-collapsed .left-side-bar .sidebar-menu > ul > li.dropdown > .submenu li a{
			padding-left: 18px;
		}
	}

</style>
<div class="header">
	<div class="header-left">
		<div class="menu-icon dw dw-menu"></div>
		<div class="sgl-sidebar-collapse-btn" role="button" tabindex="0" aria-label="Contraer/expandir menú" title="Contraer/expandir menú">
			<i class="dw dw-left-arrow" aria-hidden="true"></i>
		</div>
		<div class="search-toggle-icon dw dw-search2" data-toggle="header_search"></div>
		<div class="header-search">
			<!-- <form>
				<div class="form-group mb-0">
					<i class="dw dw-search2 search-icon"></i>
					<input type="text" class="form-control search-input" placeholder="Search Here">
					<div class="dropdown">
						<a class="dropdown-toggle no-arrow" href="#" role="button" data-toggle="dropdown">
							<i class="ion-arrow-down-c"></i>
						</a>
						<div class="dropdown-menu dropdown-menu-right">
							<div class="form-group row">
								<label class="col-sm-12 col-md-2 col-form-label">From</label>
								<div class="col-sm-12 col-md-10">
									<input class="form-control form-control-sm form-control-line" type="text">
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-12 col-md-2 col-form-label">To</label>
								<div class="col-sm-12 col-md-10">
									<input class="form-control form-control-sm form-control-line" type="text">
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-12 col-md-2 col-form-label">Subject</label>
								<div class="col-sm-12 col-md-10">
									<input class="form-control form-control-sm form-control-line" type="text">
								</div>
							</div>
							<div class="text-right">
								<button class="btn btn-primary">Search</button>
							</div>
						</div>
					</div>
				</div>
			</form> -->
		</div>

		<?php if (!empty($userId) && $clientes_count > 0): ?>
			<div class="sgl-cliente-context">
				<?php if ($solo_uno): ?>
					<div>
						<div class="sgl-cliente-label">Cliente</div>
						<div class="sgl-cliente-name" title="<?= esc($nombre_unico) ?>">
							<?= esc($nombre_unico) ?>
						</div>
					</div>
				<?php else: ?>
					<form method="GET" action="<?= esc($currentUrl) ?>" class="m-0">
						<?php foreach ($qs as $key => $value): ?>
							<?php if ($key === 'cliente_id') continue; ?>
							<?php if (is_array($value)) continue; ?>
							<input type="hidden" name="<?= esc($key) ?>" value="<?= esc($value) ?>">
						<?php endforeach; ?>

						<select name="cliente_id" class="form-control" onchange="this.form.submit()" aria-label="Seleccionar cliente">
							<?php if (user_has_global_cliente_access($userId) || ($isAdmin && $clientes_count > 1)): ?>
								<option value="" <?= empty($cliente_id_filtro) ? 'selected' : '' ?>>Todos los clientes</option>
							<?php endif; ?>
							<?php foreach ($clientes_lista as $cliente): ?>
								<option value="<?= (int)($cliente['id'] ?? 0) ?>" <?= (!empty($cliente_id_filtro) && (int)$cliente_id_filtro === (int)($cliente['id'] ?? 0)) ? 'selected' : '' ?>>
									<?= esc($cliente['nombre'] ?? ('Cliente #' . (int)($cliente['id'] ?? 0))) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</form>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="header-right">
			<!-- Botones con iconos modernos -->
			<?php
				$canHeaderButtons = has_permission('header_buttons', $session->get('user_permissions'), $session->get('user_roles'));
				$canSearchTramite = has_permission('search_tramite', $session->get('user_permissions'), $session->get('user_roles'));
			?>
			<?php if ($canHeaderButtons): ?>
				<div class="d-flex align-items-center" style="gap: 8px; margin-right: 15px;">
					<?= perm_audit_tag('header_buttons', $session) ?>
					<!-- <a href="/deskapp/tramites/tenencias/" class="btn btn-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 11px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(245, 87, 108, 0.3); white-space: nowrap;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(245, 87, 108, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(245, 87, 108, 0.3)';">
						<i class="fas fa-car"></i> Tenencias
					</a> -->
					<a href="/deskapp/tramites/tramite_2024" class="btn btn-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 11px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(67, 233, 123, 0.3); white-space: nowrap;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(67, 233, 123, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(67, 233, 123, 0.3)';">
						<i class="fas fa-calendar"></i> 2024
					</a>
					<a href="/deskapp/tramites/tramite_2025" class="btn btn-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 11px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3); white-space: nowrap;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(79, 172, 254, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(79, 172, 254, 0.3)';">
						<i class="fas fa-calendar-alt"></i> 2025
					</a>
					<!-- <a href="/deskapp/tramites/tramite" class="btn btn-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 11px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3); white-space: nowrap;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(102, 126, 234, 0.3)';">
						<i class="fas fa-list-alt"></i> Consolidado
					</a> -->
					<?php if ($isAdmin || has_permission('create_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
						<a href="/deskapp/tramites/add" class="btn btn-sm" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 700; transition: all 0.3s; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.5); white-space: nowrap; animation: pulse 2s infinite;" onmouseover="this.style.transform='translateY(-3px) scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(255, 107, 107, 0.6)';" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 15px rgba(255, 107, 107, 0.5)';">
							<i class="fas fa-plus-circle"></i> NUEVO TRÁMITE
						</a>
						<?= perm_audit_tag('create_tramite', $session) ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ($canSearchTramite): ?>
				<div class="d-flex align-items-center" style="gap: 8px; margin-right: 15px;">
					<a href="/deskapp/tramitesn/search" class="btn btn-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 11px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3); white-space: nowrap;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(79, 172, 254, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(79, 172, 254, 0.3)';">
						<i class="fas fa-search"></i> Busca un trámite
					</a>
					<?= perm_audit_tag('search_tramite', $session) ?>
				</div>
			<?php endif; ?>
		
		<!-- Botón Debug Toggle - Solo Super Admin -->
		<?php
			helper(['permissions']);
			$canDebugAudit = has_permission('debug_perm_audit_tags', $session->get('user_permissions'), $session->get('user_roles'));
		?>
		<?php if ($canDebugAudit): ?>
			<div class="dropdown" style="margin-right: 15px;">
				<button id="debugToggleBtn" class="btn btn-sm" style="background: #667eea; color: white; border: none; border-radius: 5px; padding: 8px 12px; font-size: 11px; transition: all 0.3s; white-space: nowrap;" title="Activar/Desactivar modo debug">
					<i class="fas fa-bug"></i> <span id="debugToggleText">Debug OFF</span>
				</button>
			</div>
		<?php endif; ?>

		<div class="dashboard-setting user-notification">
			<!-- Notificaciones -->
			<?php if (has_permission('menu_notifications', $session->get('user_permissions'), $session->get('user_roles'))): ?>
				<?php echo view('deskapp/includes/_notifications_dropdown'); ?>
			<?php endif; ?>
		</div>
		
		<div class="user-info-dropdown">
			<div class="dropdown">
				<a class="dropdown-toggle no-arrow" href="#" role="button" data-toggle="dropdown">
					<span class="user-icon">
					<img src="/public/<?= esc($session->get('avatar')) ?>" alt="">
					<!-- <img src="<?php echo base_url(); ?>/public/assets/vendors/images/img.jpg" alt=""> -->
					</span>
					<!-- <span class="user-name"><?= esc($session->get('firstname').' '.$session->get('midname').' '.$session->get('lastname')); ?></span> -->
				</a>
				<div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
					<?php
						$firstName = $session->get('firstname') ?? '';
						$midName = $session->get('midname') ?? '';
						$lastName = $session->get('lastname') ?? '';
						$fullName = trim(preg_replace('/\s+/', ' ', $firstName . ' ' . $midName . ' ' . $lastName));
						$sessionUser = $session->get('user_name') ?? ($session->get('username') ?? null);
						$sessionEmail = $session->get('email') ?? null;
						$sessionMetaLabel = $sessionUser ? 'Usuario' : ($sessionEmail ? 'Email' : null);
						$sessionMetaValue = $sessionUser ?? $sessionEmail;
						$sessionRolesRaw = $session->get('user_roles');
						$sessionRoles = [];
						if (is_array($sessionRolesRaw)) {
							$sessionRoles = $sessionRolesRaw;
						} elseif (is_string($sessionRolesRaw) && trim($sessionRolesRaw) !== '') {
							$sessionRoles = preg_split('/\s*,\s*/', $sessionRolesRaw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
						}
						$sessionRoles = array_values(array_unique(array_filter(array_map(static function ($r) {
							return is_string($r) ? trim($r) : '';
						}, $sessionRoles), static function ($r) {
							return $r !== '';
						})));
						if ($fullName === '') {
							$fullName = $session->get('user_name') ?? ($session->get('username') ?? ($session->get('email') ?? 'Usuario'));
						}
					?>
					<div class="sgl-session-summary">
						<div class="sgl-session-summary-inner">
							<span class="sgl-session-indicator" aria-hidden="true"></span>
							<div class="sgl-session-text">
								<div class="sgl-session-label">Sesión activa</div>
								<div class="sgl-session-name" title="<?= esc($fullName) ?>"><?= esc($fullName) ?></div>
								<?php if ($sessionMetaLabel && $sessionMetaValue): ?>
									<div class="sgl-session-meta" title="<?= esc($sessionMetaLabel . ': ' . $sessionMetaValue) ?>"><?= esc($sessionMetaLabel) ?>: <?= esc($sessionMetaValue) ?></div>
								<?php endif; ?>
								<?php if (!empty($sessionRoles)): ?>
									<div class="sgl-session-meta" style="font-size: 11px; opacity: .85;">Roles</div>
									<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
										<?php foreach ($sessionRoles as $roleName): ?>
											<span class="badge badge-pill badge-light" style="border:1px solid rgba(0,0,0,.08);font-size:11px;">
												<?= esc($roleName) ?>
											</span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="dropdown-divider"></div>
					<a class="dropdown-item" href="<?php echo base_url('deskapp/users/profile'); ?>"><i class="dw dw-user1"></i> Perfil</a>
					<!-- <a class="dropdown-item" href="<?php echo base_url('deskapp/users/profile'); ?>"><i class="dw dw-settings2"></i> Setting</a> -->
					<!-- <a class="dropdown-item" href="<?php echo base_url('deskapp/extrapages/faq'); ?>"><i class="dw dw-help"></i> Help</a> -->
					<a class="dropdown-item" href="<?php echo base_url('deskapp/logout'); ?>"><i class="dw dw-logout"></i> Cerrar Sesión</a>
				</div>
			</div>
		</div>
		<div class="github-link__">
			<!-- <a href="https://github.com/dropways/deskapp" target="_blank"><img src="<?php echo base_url(); ?>/public/assets/vendors/images/github.svg" alt=""></a> -->
			&nbsp;
		</div>
	</div>
</div>

<?php if (!empty($canDebugAudit)): ?>
	<?php
		// Lista de permisos en sesión (para marcar "pendiente" cuando no estén asignados).
		$sessionPermsForAudit = normalize_permission_list($session->get('user_permissions') ?? []);
	?>
	<div id="perm-audit-panel" class="debug-info-container alert alert-warning" style="display:none;margin:12px 18px 0 18px;">
		<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
			<div>
				<strong>Permisos detectados en pantalla</strong>
				<div id="perm-audit-summary" style="font-size:12px;opacity:.9;margin-top:2px;"></div>
			</div>
			<button type="button" class="btn btn-sm btn-outline-dark" onclick="document.getElementById('perm-audit-panel').style.display='none'">Cerrar</button>
		</div>
		<hr style="margin:10px 0;" />
		<div id="perm-audit-items" style="display:flex;flex-wrap:wrap;gap:6px;"></div>
	</div>
<?php endif; ?>

<script>
	(function () {
		var STORAGE_SIDEBAR = 'sglSidebarCollapsed';
		var STORAGE_CLIENTE = 'sglClienteId';

		function isOverlayLayout() {
			try {
				return window.matchMedia && window.matchMedia('(max-width: 1300px)').matches;
			} catch (e) {
				return false;
			}
		}

		function setCollapseIcons(isCollapsed) {
			var icons = document.querySelectorAll('.sgl-sidebar-collapse-btn i');
			icons.forEach(function (el) {
				if (!el) return;
				el.classList.remove('dw-left-arrow', 'dw-right-arrow');
				el.classList.add(isCollapsed ? 'dw-right-arrow' : 'dw-left-arrow');
			});
		}

		function applyOverlayOpenState(isOpen) {
			var sidebar = document.querySelector('.left-side-bar');
			if (!sidebar) return;
			sidebar.classList.toggle('open', !!isOpen);
			var overlay = document.querySelector('.mobile-menu-overlay');
			if (overlay) overlay.classList.toggle('show', !!isOpen);
			// En overlay, usamos el mismo icono: abierto = flecha izq (cerrar), cerrado = flecha der (abrir)
			setCollapseIcons(!isOpen);
		}

		function toggleOverlayOpen() {
			var sidebar = document.querySelector('.left-side-bar');
			if (!sidebar) return;
			applyOverlayOpenState(!sidebar.classList.contains('open'));
		}

		function applyCollapsedState(isCollapsed) {
			if (!document || !document.body) return;
			document.body.classList.toggle('sgl-sidebar-collapsed', !!isCollapsed);
			setCollapseIcons(!!isCollapsed);
		}

		function loadCollapsedState() {
			try {
				return localStorage.getItem(STORAGE_SIDEBAR) === '1';
			} catch (e) {
				return false;
			}
		}

		function saveCollapsedState(isCollapsed) {
			try {
				localStorage.setItem(STORAGE_SIDEBAR, isCollapsed ? '1' : '0');
			} catch (e) {
				// ignore
			}
		}

		function safeGetClienteFromStorage() {
			try {
				var v = localStorage.getItem(STORAGE_CLIENTE);
				if (!v) return '';
				return String(v);
			} catch (e) {
				return '';
			}
		}

		function safeSetClienteToStorage(value) {
			try {
				if (!value) {
					localStorage.removeItem(STORAGE_CLIENTE);
					return;
				}
				localStorage.setItem(STORAGE_CLIENTE, String(value));
			} catch (e) {
				// ignore
			}
		}

		function getClienteFromUrl() {
			try {
				var params = new URLSearchParams(window.location.search || '');
				return params.get('cliente_id') || '';
			} catch (e) {
				return '';
			}
		}

		function normalizeClienteValue(value) {
			if (!value) return '';
			var n = parseInt(value, 10);
			return Number.isFinite(n) && n > 0 ? String(n) : '';
		}

		document.addEventListener('DOMContentLoaded', function () {
			applyCollapsedState(loadCollapsedState());
			if (isOverlayLayout()) {
				// sincroniza icono con estado inicial (normalmente cerrado)
				applyOverlayOpenState(!!(document.querySelector('.left-side-bar') && document.querySelector('.left-side-bar').classList.contains('open')));
			}

			var btns = document.querySelectorAll('.sgl-sidebar-collapse-btn');
			function toggleSidebarCollapsed() {
				if (isOverlayLayout()) {
					toggleOverlayOpen();
					return;
				}
				var next = !document.body.classList.contains('sgl-sidebar-collapsed');
				applyCollapsedState(next);
				saveCollapsedState(next);
			}
			btns.forEach(function (btn) {
				btn.addEventListener('click', function (e) {
					e.preventDefault();
					toggleSidebarCollapsed();
				});
				btn.addEventListener('keydown', function (e) {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						toggleSidebarCollapsed();
					}
				});
			});

			// Si se redimensiona, re-sincroniza iconos para el modo actual
			window.addEventListener('resize', function () {
				if (isOverlayLayout()) {
					applyOverlayOpenState(!!(document.querySelector('.left-side-bar') && document.querySelector('.left-side-bar').classList.contains('open')));
				} else {
					applyCollapsedState(loadCollapsedState());
				}
			});

			// Persistencia de cliente seleccionado
			var clienteSelect = document.querySelector('.sgl-cliente-context select[name="cliente_id"]');
			var clienteFromUrl = normalizeClienteValue(getClienteFromUrl());
			if (clienteFromUrl) {
				safeSetClienteToStorage(clienteFromUrl);
			}

			if (clienteSelect) {
				clienteSelect.addEventListener('change', function () {
					var v = normalizeClienteValue(clienteSelect.value);
					safeSetClienteToStorage(v);
				});
			}

			// Propagar cliente_id en navegación interna (para no re-seleccionar en cada salto)
			document.addEventListener('click', function (e) {
				var a = e.target && e.target.closest ? e.target.closest('a') : null;
				if (!a) return;
				var href = a.getAttribute('href');
				if (!href || href === '#' || href.indexOf('javascript:') === 0) return;
				if (a.getAttribute('target') === '_blank') return;
				if (a.hasAttribute('download')) return;

				var stored = normalizeClienteValue(safeGetClienteFromStorage());
				if (!stored) return;

				try {
					var url = new URL(href, window.location.origin);
					// solo mismo origen
					if (url.origin !== window.location.origin) return;
					// si ya trae cliente_id, no tocar
					if (url.searchParams.has('cliente_id')) return;
					url.searchParams.set('cliente_id', stored);
					a.setAttribute('href', url.pathname + (url.search ? url.search : '') + (url.hash ? url.hash : ''));
				} catch (err) {
					// ignore
				}
			}, true);
		});
	})();
</script>

<!-- Script para Debug Toggle -->
<script>
(function() {
	const CAN_DEBUG = <?= !empty($canDebugAudit) ? 'true' : 'false' ?>;
	const USER_PERMS = <?= json_encode(array_values(array_unique(array_filter(array_map('strval', $sessionPermsForAudit ?? []))))) ?>;
	if (!CAN_DEBUG) {
		// Si el browser trae debugMode=true de una sesión previa, no permitir que un usuario sin permiso vea debug.
		try { localStorage.setItem('debugMode', 'false'); } catch (e) {}
		// Asegurar que no haya divs/tags visibles aunque existan en el DOM.
		document.addEventListener('DOMContentLoaded', function() {
			const debugDivs = document.querySelectorAll('.debug-info-container');
			debugDivs.forEach(div => { div.style.display = 'none'; });
			const permTags = document.querySelectorAll('.sgl-perm-audit');
			permTags.forEach(tag => { tag.style.display = 'none'; });
		});
		return;
	}

	// Verificar estado del debug mode en localStorage
	const debugMode = localStorage.getItem('debugMode') === 'true';
	
	// Función para actualizar UI del botón
	function updateDebugButton() {
		const btn = document.getElementById('debugToggleBtn');
		const text = document.getElementById('debugToggleText');
		const isDebugOn = localStorage.getItem('debugMode') === 'true';
		
		if (btn && text) {
			if (isDebugOn) {
				btn.style.background = '#28a745';
				text.textContent = 'Debug ON';
			} else {
				btn.style.background = '#667eea';
				text.textContent = 'Debug OFF';
			}
		}
		
		// Mostrar/ocultar divs de debug
		const debugDivs = document.querySelectorAll('.debug-info-container');
		debugDivs.forEach(div => {
			div.style.display = isDebugOn ? 'block' : 'none';
		});

		// Mostrar/ocultar etiquetas de permisos (inline)
		const permTags = document.querySelectorAll('.sgl-perm-audit');
		permTags.forEach(tag => {
			tag.style.display = isDebugOn ? 'inline' : 'none';
		});

		// Render de panel (en debug ON)
		if (isDebugOn) {
			updatePermAuditPanel();
		}
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function collectPermsFromTags() {
		const tags = document.querySelectorAll('.sgl-perm-audit[data-perm]');
		const set = new Set();
		tags.forEach((tag) => {
			const p = (tag.getAttribute('data-perm') || '').trim();
			if (p) set.add(p);
		});
		return Array.from(set).sort((a, b) => a.localeCompare(b));
	}

	function updatePermAuditPanel() {
		const panel = document.getElementById('perm-audit-panel');
		if (!panel) return;
		const summary = document.getElementById('perm-audit-summary');
		const items = document.getElementById('perm-audit-items');
		if (!summary || !items) return;

		const perms = collectPermsFromTags();
		const userPermSet = new Set((USER_PERMS || []).map((p) => String(p)));
		let pendingCount = 0;

		summary.textContent = perms.length
			? `Encontrados: ${perms.length}. Pendientes (no están en tu sesión): calculando...`
			: 'No se detectaron etiquetas de permisos en esta vista.';

		if (!perms.length) {
			items.innerHTML = '';
			return;
		}

		const html = perms.map((perm) => {
			const isAssigned = userPermSet.has(perm);
			if (!isAssigned) pendingCount++;
			const cls = isAssigned ? 'badge badge-pill badge-light' : 'badge badge-pill badge-danger';
			const title = isAssigned ? 'En sesión' : 'Pendiente (no está en user_permissions en sesión)';
			return `<span class="${cls}" title="${escapeHtml(title)}" style="border:1px solid rgba(0,0,0,.08);font-size:11px;">${escapeHtml(perm)}</span>`;
		}).join('');

		items.innerHTML = html;
		summary.textContent = `Encontrados: ${perms.length}. Pendientes (no están en tu sesión): ${pendingCount}.`;
	}
	
	// Evento click en el botón
	document.addEventListener('DOMContentLoaded', function() {
		const btn = document.getElementById('debugToggleBtn');
		if (btn) {
			btn.addEventListener('click', function() {
				const currentMode = localStorage.getItem('debugMode') === 'true';
				localStorage.setItem('debugMode', !currentMode);
				updateDebugButton();
			});
			
			// Actualizar estado inicial
			updateDebugButton();
		}
	});
})();
</script>

<div class="right-sidebar">
	<div class="sidebar-title">
		<h3 class="weight-600 font-16 text-blue">
			Layout Settings
			<span class="btn-block font-weight-400 font-12">User Interface Settings</span>
		</h3>
		<div class="close-sidebar" data-toggle="right-sidebar-close">
			<i class="icon-copy ion-close-round"></i>
		</div>
	</div>
	<div class="right-sidebar-body customscroll">
		<div class="right-sidebar-body-content">
			<h4 class="weight-600 font-18 pb-10">Header Background</h4>
			<div class="sidebar-btn-group pb-30 mb-10">
				<a href="javascript:void(0);" class="btn btn-outline-primary header-white active">White</a>
				<a href="javascript:void(0);" class="btn btn-outline-primary header-dark">Dark</a>
			</div>

			<h4 class="weight-600 font-18 pb-10">Sidebar Background</h4>
			<div class="sidebar-btn-group pb-30 mb-10">
				<a href="javascript:void(0);" class="btn btn-outline-primary sidebar-light ">White</a>
				<a href="javascript:void(0);" class="btn btn-outline-primary sidebar-dark active">Dark</a>
			</div>

			<h4 class="weight-600 font-18 pb-10">Menu Dropdown Icon</h4>
			<div class="sidebar-radio-group pb-10 mb-10">
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="sidebaricon-1" name="menu-dropdown-icon" class="custom-control-input" value="icon-style-1" checked="">
					<label class="custom-control-label" for="sidebaricon-1"><i class="fa fa-angle-down"></i></label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="sidebaricon-2" name="menu-dropdown-icon" class="custom-control-input" value="icon-style-2">
					<label class="custom-control-label" for="sidebaricon-2"><i class="ion-plus-round"></i></label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="sidebaricon-3" name="menu-dropdown-icon" class="custom-control-input" value="icon-style-3">
					<label class="custom-control-label" for="sidebaricon-3"><i class="fa fa-angle-double-right"></i></label>
				</div>
			</div>

			<h4 class="weight-600 font-18 pb-10">Menu List Icon</h4>
			<div class="sidebar-radio-group pb-30 mb-10">
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="sidebariconlist-1" name="menu-list-icon" class="custom-control-input" value="icon-list-style-1" checked="">
					<label class="custom-control-label" for="sidebariconlist-1"><i class="ion-minus-round"></i></label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="sidebariconlist-2" name="menu-list-icon" class="custom-control-input" value="icon-list-style-2">
					<label class="custom-control-label" for="sidebariconlist-2"><i class="fa fa-circle-o" aria-hidden="true"></i></label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="sidebariconlist-3" name="menu-list-icon" class="custom-control-input" value="icon-list-style-3">
					<label class="custom-control-label" for="sidebariconlist-3"><i class="dw dw-check"></i></label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="sidebariconlist-4" name="menu-list-icon" class="custom-control-input" value="icon-list-style-4" checked="">
					<label class="custom-control-label" for="sidebariconlist-4"><i class="icon-copy dw dw-next-2"></i></label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="sidebariconlist-5" name="menu-list-icon" class="custom-control-input" value="icon-list-style-5">
					<label class="custom-control-label" for="sidebariconlist-5"><i class="dw dw-fast-forward-1"></i></label>
				</div>
				<div class="custom-control custom-radio custom-control-inline">
					<input type="radio" id="sidebariconlist-6" name="menu-list-icon" class="custom-control-input" value="icon-list-style-6">
					<label class="custom-control-label" for="sidebariconlist-6"><i class="dw dw-next"></i></label>
				</div>
			</div>

			<div class="reset-options pt-30 text-center">
				<button class="btn btn-danger" id="reset-settings">Reset Settings</button>
			</div>
		</div>
	</div>
</div>