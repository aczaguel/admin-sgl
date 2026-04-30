<!-- <div class="pre-loader">
	<div class="pre-loader-box">
		<div class="loader-logo"><img width="50%" src="<?php echo base_url(); ?>/public/assets/vendors/images/logoes_sgt.jpg" alt=""></div>
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
	helper(['cliente_filter', 'cliente_context']);

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
</style>
<div class="header">
	<div class="header-left">
		<div class="menu-icon dw dw-menu"></div>
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
		<div class="dashboard-setting user-notification">
			<!-- Botones con iconos -->
			<div class="dropdown">
				<a class="dropdown-toggle no-arrow" href="javascript:;" data-toggle="right-sidebar">
					<i class="dw dw-settings2"></i>
				</a>
				
			</div>
			
		</div>

		<!-- <div class="user-notification">
			<div class="dropdown">
				<a class="dropdown-toggle no-arrow" href="#" role="button" data-toggle="dropdown">
					<i class="icon-copy dw dw-notification"></i>
					<span class="badge notification-active"></span>
				</a>
				<div class="dropdown-menu dropdown-menu-right">
					<div class="notification-list mx-h-350 customscroll">
						<ul>
							<li>
								<a href="#">
									<img src="<?php echo base_url(); ?>/public/assets/vendors/images/img.jpg" alt="">
									<h3>John Doe</h3>
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed...</p>
								</a>
							</li>
							<li>
								<a href="#">
									<img src="<?php echo base_url(); ?>/public/assets/vendors/images/photo1.jpg" alt="">
									<h3>Lea R. Frith</h3>
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed...</p>
								</a>
							</li>
							<li>
								<a href="#">
									<img src="<?php echo base_url(); ?>/public/assets/vendors/images/photo2.jpg" alt="">
									<h3>Erik L. Richards</h3>
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed...</p>
								</a>
							</li>
							<li>
								<a href="#">
									<img src="<?php echo base_url(); ?>/public/assets/vendors/images/photo3.jpg" alt="">
									<h3>John Doe</h3>
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed...</p>
								</a>
							</li>
							<li>
								<a href="#">
									<img src="<?php echo base_url(); ?>/public/assets/vendors/images/photo4.jpg" alt="">
									<h3>Renee I. Hansen</h3>
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed...</p>
								</a>
							</li>
							<li>
								<a href="#">
									<img src="<?php echo base_url(); ?>/public/assets/vendors/images/img.jpg" alt="">
									<h3>Vicki M. Coleman</h3>
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed...</p>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div> -->
		
		<div class="user-info-dropdown">
			<div class="dropdown">
				<a class="dropdown-toggle no-arrow" href="#" role="button" data-toggle="dropdown">
					<span class="user-icon">
						<img src="/public/<?= esc($session->get('avatar')) ?>" alt="">
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
							</div>
						</div>
					</div>
					<div class="dropdown-divider"></div>
					<?php if (has_permission('listar_settings', $session->get('user_permissions'), $session->get('user_roles'))): ?>
						<a class="dropdown-item" href="<?php echo base_url('deskapp/extrapages/profile'); ?>"><i class="dw dw-user1"></i> Profile</a>
						<a class="dropdown-item" href="<?php echo base_url('deskapp/extrapages/profile'); ?>"><i class="dw dw-settings2"></i> Setting</a>
						<a class="dropdown-item" href="<?php echo base_url('deskapp/extrapages/faq'); ?>"><i class="dw dw-help"></i> Help</a>
					<?php else: ?>
						<a class="dropdown-item" href="<?php echo base_url('users/profile'); ?>"><i class="dw dw-user1"></i> Mi Perfil</a>
					<?php endif; ?>		
					<a class="dropdown-item" href="<?php echo base_url('deskapp/logout'); ?>"><i class="dw dw-logout"></i> Log Out</a>
				</div>
			</div>
		</div>
		<div class="github-link__">
			<!-- <a href="https://github.com/dropways/deskapp" target="_blank"><img src="<?php echo base_url(); ?>/public/assets/vendors/images/github.svg" alt=""></a> -->
			&nbsp;
		</div>
	</div>
</div>

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