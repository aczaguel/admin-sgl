<div class="left-side-bar">
		<div class="brand-logo">
			<a href="<?php echo base_url('deskapp/tramites'); ?>">
				<img src="<?php echo base_url(); ?>/assets/vendors/images/logoes_sgt.png" alt="" class="dark-logo">
				<img src="<?php echo base_url(); ?>/assets/vendors/images/logoes_sgt_white.png" alt="" class="light-logo">
			</a>
			<div class="close-sidebar" data-toggle="left-sidebar-close">
				<i class="ion-close-round"></i>
			</div>
		</div>
		<div class="menu-block customscroll">
			<div class="sidebar-menu">
				<ul id="accordion-menu">
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-house-1"></span><span class="mtext">Trámites</span>
						</a>
						<ul class="submenu">
							<?php if (has_permission('listar_tramite', esc($session->get('user_permissions'))) || is_super_admin($session->get('user_roles'))): ?>
            					<li><a href="<?php echo base_url('deskapp/tramites/tramite'); //listar_tramite?>">Trámites</a></li>
        					<?php endif; ?>	
							<?php if (has_permission('listar_mis_tramites', esc($session->get('user_permissions'))) || is_super_admin($session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramites/mios'); //listar_mios?>">Mis Trámites</a></li>
							<?php endif; ?>	
							<?php if (has_permission('listar_tramite', esc($session->get('user_permissions'))) || is_super_admin($session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramites/tipo'); //listar_tp_tramite?>">Tipo de Trámite</a></li>
							<?php endif; ?>
							<?php if (has_permission('listar_tramite', esc($session->get('user_permissions'))) || is_super_admin($session->get('user_roles'))): ?>
								<li><a href="<?php echo base_url('deskapp/tramites/status'); //listar_st_tramite?>">Estatuses de Trámite</a></li>
							<?php endif; ?>
						</ul>
					</li>
					<!-- <li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-library"></span><span class="mtext">Seguimiento</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/tradocstatus/documento'); ?>">Documentos del trámite</a></li>
						</ul>
					</li> -->
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-house-1"></span><span class="mtext">Proceso Final</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/proceso/final'); //listar_final_tramite?>">Finalizando</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-edit2"></span><span class="mtext">Documentos</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/documentos/documento'); //listar_documentos?>">Documento</a></li>
							<li><a href="<?php echo base_url('deskapp/documentos/status'); //listar_st_documentos?>">Estatus</a></li>

							
						</ul>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-library"></span><span class="mtext">Gestores</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/gestores/gestores'); //listar_emp_gestoras?>">Empresa Gestora</a></li>
							<li><a href="<?php echo base_url('deskapp/gestores/gestor'); //listar_gestores?>">Gestor</a></li>
						</ul>
					</li>
					
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">

							<span class="micon dw dw-library"></span><span class="mtext">Clientes</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/cliente/cliente'); //listar_clientes?>">Clientes</a></li>
							<li><a href="<?php echo base_url('deskapp/clidirecto/clidirecto'); //listar_cte_directo?>">Cliente Directo</a></li>
							<li><a href="<?php echo base_url('deskapp/clidirecto/ejecutivo'); //listar_ejecutivos?>">Ejecutivo</a></li>
						</ul>
					</li>
					<li>
						<div class="dropdown-divider"></div>
					</li>
					
					<li>
						<div class="dropdown-divider"></div>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-library"></span><span class="mtext">Permisos</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/users/users'); //listar_usuarios?>">Usuarios</a></li>
							<li><a href="<?php echo base_url('deskapp/users/user_roles') //listar_usuarios_roles; ?>">Usuarios - Roles</a></li>
							<li><a href="<?php echo base_url('deskapp/roles/roles'); //listar_roles;?>"> Roles </a></li>
							<li><a href="<?php echo base_url('deskapp/permisos/permisos'); //listar_permisos?>">Permisos</a></li>
							<li><a href="<?php echo base_url('deskapp/roles/role_permissions');//listar_roles_permisos ?>">Roles - Permisos</a></li>
							
						</ul>
					</li>
					<li>
						<div class="dropdown-divider"></div>
					</li>
					
					<li>
						<div class="dropdown-divider"></div>
					</li>



					<?php if (has_permission('menu_erp_sa', esc($session->get('user_permissions'))) || is_super_admin($session->get('user_roles'))): //menu_erp_sa ?>
					<li>
						<a href="<?php echo base_url('deskapp/calendar'); ?>" class="dropdown-toggle no-arrow">
							<span class="micon dw dw-calendar1"></span><span class="mtext">Calendar</span>
						</a>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-apartment"></span><span class="mtext"> UI Elements </span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/ui/buttons'); ?>">Buttons</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/cards'); ?>">Cards</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/cardsHover'); ?>">Cards Hover</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/modals'); ?>">Modals</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/tabs'); ?>">Tabs</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/tooltip') ?>">Tooltip &amp; Popover</a></li>
							
							<li><a href="<?php echo base_url('deskapp/ui/sweetAlert'); ?>">Sweet Alert</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/notification'); ?>">Notification</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/timeline'); ?>">Timeline</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/progressbar'); ?>">Progressbar</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/typography'); ?>">Typography</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/listgroup'); ?>">List group</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/rangeSlider'); ?>">Range slider</a></li>
							<li><a href="<?php echo base_url('deskapp/ui/carousel'); ?>">Carousel</a></li>
							
							<li><a href="<?php echo base_url('deskapp/forms/wizard') ?>">Form Wizard</a></li>
							<li><a href="<?php echo base_url('deskapp/forms/html5Editor'); ?>">HTML5 Editor</a></li>
							<li><a href="<?php echo base_url('deskapp/forms/pickers'); ?>">Form Pickers</a></li>
							<li><a href="<?php echo base_url('deskapp/forms/imageCropper'); ?>">Image Cropper</a></li>
							<li><a href="<?php echo base_url('deskapp/forms/imageDropZone'); ?>">Image Dropzone</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-paint-brush"></span><span class="mtext">Icons</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/icons/fontawesome'); ?>">FontAwesome Icons</a></li>
							<li><a href="<?php echo base_url('deskapp/icons/foundation'); ?>">Foundation Icons</a></li>
							<li><a href="<?php echo base_url('deskapp/icons/ionicons'); ?>">Ionicons Icons</a></li>
							<li><a href="<?php echo base_url('deskapp/icons/themify'); ?>">Themify Icons</a></li>
							<li><a href="<?php echo base_url('deskapp/icons/custom'); ?>">Custom Icons</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-analytics-21"></span><span class="mtext">Charts</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/charts/highchart'); ?>">Highchart</a></li>
							<li><a href="<?php echo base_url('deskapp/charts/knobchart'); ?>">jQuery Knob</a></li>
							<li><a href="<?php echo base_url('deskapp/charts/jvectormap'); ?>">jvectormap</a></li>
							<li><a href="<?php echo base_url('deskapp/charts/apexcharts'); ?>">Apexcharts</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-right-arrow1"></span><span class="mtext">Additional Pages</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/Additionalpages/videoplayer'); ?>">Video Player</a></li>
							<li><a href="<?php echo base_url('deskapp/Additionalpages/login'); ?>">Login</a></li>
							<li><a href="<?php echo base_url('deskapp/Additionalpages/forgot_password'); ?>">Forgot Password</a></li>
							<li><a href="<?php echo base_url('deskapp/Additionalpages/reset_password'); ?>">Reset Password</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-browser2"></span><span class="mtext">Error Pages</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/error/error_400'); ?>">400</a></li>
							<li><a href="<?php echo base_url('deskapp/error/error_403'); ?>">403</a></li>
							<li><a href="<?php echo base_url('deskapp/error/error_404'); ?>">404</a></li>
							<li><a href="<?php echo base_url('deskapp/error/error_500'); ?>">500</a></li>
							<li><a href="<?php echo base_url('deskapp/error/error_503'); ?>">503</a></li>
						</ul>
					</li>

					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-copy"></span><span class="mtext">Extra Pages</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/extrapages/blank'); ?>">Blank</a></li>
							<li><a href="<?php echo base_url('deskapp/extrapages/contact_directory'); ?>">Contact Directory</a></li>
							<li><a href="<?php echo base_url('deskapp/extrapages/blog'); ?>">Blog</a></li>
							<li><a href="<?php echo base_url('deskapp/extrapages/blog_detail'); ?>">Blog Detail</a></li>
							<li><a href="<?php echo base_url('deskapp/extrapages/product'); ?>">Product</a></li>
							<li><a href="<?php echo base_url('deskapp/extrapages/product_detail'); ?>">Product Detail</a></li>
							<li><a href="<?php echo base_url('deskapp/extrapages/faq'); ?>">FAQ</a></li>
							<li><a href="<?php echo base_url('deskapp/extrapages/profile'); ?>">Profile</a></li>
							<li><a href="<?php echo base_url('deskapp/extrapages/gallery'); ?>">Gallery</a></li>
							<li><a href="<?php echo base_url('deskapp/extrapages/pricing'); ?>">Pricing Tables</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-list3"></span><span class="mtext">Multi Level Menu</span>
						</a>
						<ul class="submenu">
							<li><a href="javascript:;">Level 1</a></li>
							<li><a href="javascript:;">Level 1</a></li>
							<li><a href="javascript:;">Level 1</a></li>
							<li class="dropdown">
								<a href="javascript:;" class="dropdown-toggle">
									<span class="micon fa fa-plug"></span><span class="mtext">Level 2</span>
								</a>
								<ul class="submenu child">
									<li><a href="javascript:;">Level 2</a></li>
									<li><a href="javascript:;">Level 2</a></li>
								</ul>
							</li>
							<li><a href="javascript:;">Level 1</a></li>
							<li><a href="javascript:;">Level 1</a></li>
							<li><a href="javascript:;">Level 1</a></li>
						</ul>
					</li>
					<li>
						<a href="<?php echo base_url('deskapp/sitemap'); ?>" class="dropdown-toggle no-arrow">
							<span class="micon dw dw-diagram"></span><span class="mtext">Sitemap</span>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url('deskapp/chat/'); ?>" class="dropdown-toggle no-arrow">
							<span class="micon dw dw-chat3"></span><span class="mtext">Chat</span>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url('deskapp/invoice/'); ?>" class="dropdown-toggle no-arrow">
							<span class="micon dw dw-invoice"></span><span class="mtext">Invoice</span>
						</a>
					</li>
					<li>
						<div class="dropdown-divider"></div>
					</li>
					
					<li>
						<div class="dropdown-divider"></div>
					</li>
					<li>
						<div class="sidebar-small-cap">Extra</div>
					</li>
					<li>
						<a href="javascript:;" class="dropdown-toggle">
							<span class="micon dw dw-edit-2"></span><span class="mtext">Documentation</span>
						</a>
						<ul class="submenu">
							<li><a href="<?php echo base_url('deskapp/docs/introduction'); ?>">Introduction</a></li>
							<li><a href="<?php echo base_url('deskapp/docs/getting_started'); ?>">Getting Started</a></li>
							<li><a href="<?php echo base_url('deskapp/docs/color_settings'); ?>">Color Settings</a></li>
							<li><a href="<?php echo base_url('deskapp/docs/third_party_plugins'); ?>">Third Party Plugins</a></li>
						</ul>
					</li>
					<li>
						<a href="https://dropways.github.io/deskapp-free-single-page-website-template/" target="_blank" class="dropdown-toggle no-arrow">
							<span class="micon dw dw-paper-plane1"></span>
							<span class="mtext">Landing Page <img src="<?php echo base_url(); ?>/assets/vendors/images/coming-soon.png" alt="" width="25"></span>
						</a>
					</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
	<div class="mobile-menu-overlay"></div>