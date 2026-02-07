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
	</div>
	<div class="header-right">
			<!-- Botones con iconos modernos -->
			<?php if (has_permission('header_buttons', esc($session->get('user_permissions')), esc($session->get('user_roles')))): ?>
				<div class="d-flex align-items-center" style="gap: 8px; margin-right: 15px;">
					<a href="/deskapp/tramites/tenencias/" class="btn btn-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 11px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(245, 87, 108, 0.3); white-space: nowrap;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(245, 87, 108, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(245, 87, 108, 0.3)';">
						<i class="fas fa-car"></i> Tenencias
					</a>
					<a href="/deskapp/tramites/tramite_2024" class="btn btn-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 11px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(67, 233, 123, 0.3); white-space: nowrap;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(67, 233, 123, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(67, 233, 123, 0.3)';">
						<i class="fas fa-calendar"></i> 2024
					</a>
					<a href="/deskapp/tramites/tramite_2025" class="btn btn-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 11px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3); white-space: nowrap;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(79, 172, 254, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(79, 172, 254, 0.3)';">
						<i class="fas fa-calendar-alt"></i> 2025
					</a>
					<a href="/deskapp/tramites/tramite" class="btn btn-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; padding: 7px 14px; font-size: 11px; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3); white-space: nowrap;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(102, 126, 234, 0.3)';">
						<i class="fas fa-list-alt"></i> Consolidado
					</a>
					<!-- Botón Nuevo DESTACADO -->
					<a href="/deskapp/tramites/add" class="btn btn-sm" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 700; transition: all 0.3s; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.5); white-space: nowrap; animation: pulse 2s infinite;" onmouseover="this.style.transform='translateY(-3px) scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(255, 107, 107, 0.6)';" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 15px rgba(255, 107, 107, 0.5)';">
						<i class="fas fa-plus-circle"></i> NUEVO TRÁMITE
					</a>
				</div>
			<?php endif; ?>
		
		<!-- Botón Debug Toggle - Solo Super Admin -->
		<?php if (is_array($session->get('user_roles')) && in_array('Super Admin', $session->get('user_roles'))): ?>
			<div class="dropdown" style="margin-right: 15px;">
				<button id="debugToggleBtn" class="btn btn-sm" style="background: #667eea; color: white; border: none; border-radius: 5px; padding: 8px 12px; font-size: 11px; transition: all 0.3s; white-space: nowrap;" title="Activar/Desactivar modo debug">
					<i class="fas fa-bug"></i> <span id="debugToggleText">Debug OFF</span>
				</button>
			</div>
		<?php endif; ?>
		
		<div class="dashboard-setting user-notification">
			<!-- Notificaciones -->
			<?php echo view('deskapp/includes/_notifications_dropdown'); ?>
		</div>
		
		<div class="user-info-dropdown">
			<div class="dropdown">
				<a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
					<span class="user-icon">
					<img src="/public/<?= esc($session->get('avatar')) ?>" alt="">
					<!-- <img src="<?php echo base_url(); ?>/public/assets/vendors/images/img.jpg" alt=""> -->
					</span>
					<!-- <span class="user-name"><?= esc($session->get('firstname').' '.$session->get('midname').' '.$session->get('lastname')); ?></span> -->
				</a>
				<div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
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

<!-- Script para Debug Toggle -->
<script>
(function() {
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