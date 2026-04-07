<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>
	<?php $assets = base_url('/public/assets'); ?>
	<link rel="stylesheet" href="<?= $assets ?>/src/styles/grocery-crud-custom.css?v=<?= time() ?>">
	<style>
		/* Modern Grocery CRUD Styling */
		.grocery-crud-wrapper {
			background: #ffffff;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.08);
			overflow: visible;
			margin-bottom: 30px;
		}

		.grocery-crud-header {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			padding: 25px 30px;
			color: white;
		}

		.grocery-crud-header h2 {
			margin: 0 0 8px 0;
			font-size: 24px;
			font-weight: 600;
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.grocery-crud-header h2 i {
			font-size: 28px;
			opacity: 0.9;
		}

		.grocery-crud-header p {
			margin: 0;
			opacity: 0.95;
			font-size: 14px;
		}

		.grocery-crud-body {
			padding: 30px;
		}

		.grocery-crud-body .table-responsive {
			overflow-x: auto;
			overflow-y: visible;
		}

		/* Enhanced table styling */
		.grocery-crud-body table.table {
			border-collapse: separate;
			border-spacing: 0;
			border: 1px solid #e3e8ef;
			border-radius: 8px;
			overflow: visible;
		}

		.grocery-crud-body table.table thead th {
			background: #f8fafc;
			border-bottom: 2px solid #e3e8ef;
			color: #1e293b;
			font-weight: 600;
			text-transform: uppercase;
			font-size: 12px;
			letter-spacing: 0.5px;
			padding: 16px 12px;
		}

		.grocery-crud-body table.table tbody tr {
			transition: background-color 0.2s ease;
		}

		.grocery-crud-body table.table tbody tr:hover {
			background-color: #f8fafc;
		}

		.grocery-crud-body table.table tbody td {
			padding: 14px 12px;
			border-bottom: 1px solid #f1f5f9;
			color: #475569;
			vertical-align: middle;
		}

		/* Button styling */
		.grocery-crud-body .btn {
			border-radius: 6px;
			font-weight: 500;
			padding: 8px 16px;
			font-size: 14px;
			transition: all 0.2s ease;
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.grocery-crud-body .btn:hover {
			color: inherit;
		}

		/* Remove transform for buttons inside tables to prevent dropdown issues */
		.grocery-crud-body table td .btn:hover,
		.grocery-crud-body table td .dropdown-toggle:hover {
			transform: none;
		}

		.grocery-crud-body .btn-success {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border: none;
		}

		.grocery-crud-body .btn-success:hover {
			background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
		}

		.grocery-crud-body .btn-info {
			background: #06b6d4;
			border: none;
		}

		.grocery-crud-body .btn-info:hover {
			background: #0891b2;
		}

		.grocery-crud-body .btn-warning {
			background: #f59e0b;
			border: none;
		}

		.grocery-crud-body .btn-warning:hover {
			background: #d97706;
		}

		.grocery-crud-body .btn-danger {
			background: #ef4444;
			border: none;
		}

		.grocery-crud-body .btn-danger:hover {
			background: #dc2626;
		}

		/* Form styling */
		.grocery-crud-body .form-control {
			border: 2px solid #e3e8ef;
			border-radius: 8px;
			padding: 5px 14px;
			font-size: 14px;
			transition: all 0.2s ease;
		}

		.grocery-crud-body .form-control:focus {
			border-color: #667eea;
			box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
			outline: none;
		}

		.grocery-crud-body .form-group label {
			font-weight: 600;
			color: #334155;
			margin-bottom: 8px;
			font-size: 14px;
		}

		/* Modal styling */
		.grocery-crud-body .modal-content {
			border-radius: 12px;
			border: none;
			box-shadow: 0 10px 40px rgba(0,0,0,0.15);
		}

		.grocery-crud-body .modal-header {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			border-radius: 12px 12px 0 0;
			padding: 20px 24px;
		}

		.grocery-crud-body .modal-header h5 {
			font-weight: 600;
			margin: 0;
		}

		.grocery-crud-body .modal-header .close,
		.grocery-crud-body .modal-header .btn-close {
			color: white !important;
			opacity: 0.9 !important;
			font-size: 24px;
			font-weight: 300;
			text-shadow: none;
			background: transparent;
			border: none;
			padding: 0;
			width: 32px;
			height: 32px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 4px;
			transition: all 0.2s ease;
		}

		.grocery-crud-body .modal-header .close:hover,
		.grocery-crud-body .modal-header .btn-close:hover {
			opacity: 1 !important;
			background: rgba(255, 255, 255, 0.2);
		}

		/* Asegurar que el ícono X sea visible */
		.grocery-crud-body .modal-header .close::before,
		.grocery-crud-body .modal-header .btn-close::before {
			content: '×';
			color: white;
			font-size: 32px;
			line-height: 1;
			font-weight: 300;
		}

		.grocery-crud-body .modal-header button.close,
		.grocery-crud-body .modal-header button.btn-close {
			position: relative;
			z-index: 10;
		}

		.grocery-crud-body .modal-body {
			padding: 24px;
		}

		.grocery-crud-body .modal-footer {
			padding: 16px 24px;
			border-top: 1px solid #e3e8ef;
		}

		/* Dropdown menu styling and positioning */
		.grocery-crud-body .dropdown-menu {
			z-index: 9999 !important;
			min-width: 160px;
			padding: 8px 0;
			margin: 2px 0 0;
			background-color: #ffffff;
			border: 1px solid #e3e8ef;
			border-radius: 8px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
		}

		.grocery-crud-body .dropdown-menu.show {
			display: block;
		}

		.grocery-crud-body .dropdown-item,
		.grocery-crud-body .dropdown-menu a {
			padding: 8px 16px;
			color: #1e293b;
			text-decoration: none;
			display: flex;
			align-items: center;
			gap: 8px;
			transition: background-color 0.15s ease;
			cursor: pointer;
			white-space: nowrap;
		}

		.grocery-crud-body .dropdown-item:hover,
		.grocery-crud-body .dropdown-menu a:hover {
			background-color: #f8fafc;
			color: #667eea;
		}

		.grocery-crud-body .dropdown-item i,
		.grocery-crud-body .dropdown-menu a i {
			width: 16px;
			text-align: center;
		}

		/* Pagination styling */
		.grocery-crud-body .pagination {
			gap: 6px;
		}

		.grocery-crud-body .pagination .page-link {
			border-radius: 6px;
			border: 1px solid #e3e8ef;
			color: #475569;
			font-weight: 500;
			padding: 8px 14px;
			transition: all 0.2s ease;
		}

		.grocery-crud-body .pagination .page-link:hover {
			background: #667eea;
			border-color: #667eea;
			color: white;
			transform: translateY(-2px);
		}

		.grocery-crud-body .pagination .page-item.active .page-link {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border-color: #667eea;
		}

		/* Search box styling */
		.grocery-crud-body .floatL input[type="text"],
		.grocery-crud-body .search-field input[type="text"] {
			border: 2px solid #e3e8ef;
			border-radius: 8px;
			padding: 5px 14px;
			font-size: 14px;
			transition: all 0.2s ease;
		}

		.grocery-crud-body .floatL input[type="text"]:focus,
		.grocery-crud-body .search-field input[type="text"]:focus {
			border-color: #667eea;
			box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
			outline: none;
		}

		/* Breadcrumb styling */
		.crud-breadcrumb {
			background: transparent;
			padding: 0 0 20px 0;
			margin: 0;
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 14px;
		}

		.crud-breadcrumb a {
			color: #667eea;
			text-decoration: none;
			font-weight: 500;
			transition: color 0.2s ease;
		}

		.crud-breadcrumb a:hover {
			color: #5568d3;
		}

		.crud-breadcrumb .separator {
			color: #94a3b8;
		}

		.crud-breadcrumb .current {
			color: #64748b;
		}

		/* Action buttons group */
		.grocery-crud-body .action-buttons {
			display: flex;
			gap: 6px;
			justify-content: flex-start;
		}

		.grocery-crud-body .action-buttons a,
		.grocery-crud-body .action-buttons button {
			padding: 6px 12px;
			font-size: 13px;
		}

		/* Loading state */
		.grocery-crud-body .loading-overlay {
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(255, 255, 255, 0.9);
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 12px;
			z-index: 100;
		}

		/* Responsive adjustments */
		@media (max-width: 768px) {
			.grocery-crud-header {
				padding: 20px;
			}

			.grocery-crud-header h2 {
				font-size: 20px;
			}

			.grocery-crud-body {
				padding: 20px;
			}

			.grocery-crud-body table.table {
				font-size: 13px;
			}

			.grocery-crud-body table.table thead th,
			.grocery-crud-body table.table tbody td {
				padding: 10px 8px;
			}
		}

		/* Dark mode support */
		@media (prefers-color-scheme: dark) {
			.grocery-crud-wrapper {
				background: #1e293b;
			}

			.grocery-crud-body table.table {
				border-color: #334155;
			}

			.grocery-crud-body table.table thead th {
				background: #0f172a;
				border-color: #334155;
				color: #e2e8f0;
			}

			.grocery-crud-body table.table tbody td {
				border-color: #334155;
				color: #cbd5e1;
			}

			.grocery-crud-body table.table tbody tr:hover {
				background-color: #0f172a;
			}
		}
	</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
	<div class="main-container">
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">
				<!-- Breadcrumb -->
				<div class="crud-breadcrumb">
					<a href="<?= base_url('/deskapp/dashboard') ?>">
						<i class="fas fa-home"></i> Inicio
					</a>
					<span class="separator">/</span>
					<span class="current"><?= $title ?? 'Gestión de Datos' ?></span>
				</div>

				<!-- Grocery CRUD Wrapper -->
				<div class="grocery-crud-wrapper">
					<!-- Header Section -->
					<div class="grocery-crud-header">
						<h2>
							<i class="fas fa-table"></i>
							<?= $title ?? 'Gestión de Datos' ?>
						</h2>
						<p><?= $description ?? 'Administra y gestiona la información de forma eficiente' ?></p>
					</div>

					<!-- Body Section -->
					<div class="grocery-crud-body">
						<?php
							helper(['permissions']);
							$sessionDbg = session();
							$canDebugAudit = has_permission('debug_perm_audit_tags', $sessionDbg->get('user_permissions'), $sessionDbg->get('user_roles'));
						?>
						<?php if (!empty($canDebugAudit)): ?>
							<!-- Audit Debug Div (solo Super Admin + Debug ON) -->
							<div id="audit-debug" class="debug-info-container" style="display: none; background: #1e293b; color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-bottom: 20px; font-family: 'Courier New', monospace;">
								<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 2px solid #475569; padding-bottom: 8px;">
									<strong style="color: #60a5fa; font-size: 14px;">📋 Audit Payload</strong>
									<button onclick="$('#audit-debug').hide()" style="background: #ef4444; color: white; border: none; padding: 4px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">✕ Cerrar</button>
								</div>
								<?php
									$permAuditContext = $perm_audit_context ?? null;
									$permAuditReqs = $perm_audit_requirements ?? null;
									$sessionDbgRoles = normalize_permission_list($sessionDbg->get('user_roles') ?? []);
									$sessionDbgPerms = normalize_permission_list($sessionDbg->get('user_permissions') ?? []);
								?>
								<?php if (is_array($permAuditReqs) && !empty($permAuditReqs)): ?>
									<div style="margin-bottom: 12px; padding: 10px 12px; border: 1px dashed rgba(255,255,255,.25); border-radius: 6px;">
										<div style="font-size: 12px; opacity: .9; margin-bottom: 6px;">
											<strong>Permisos requeridos</strong><?= $permAuditContext ? ' <span style="opacity:.75">(' . esc($permAuditContext) . ')</span>' : '' ?>
										</div>
										<div style="display:flex;flex-direction:column;gap:4px;font-size:12px;">
											<?php foreach ($permAuditReqs as $label => $permName): ?>
												<?php
													$permName = is_string($permName) ? trim($permName) : '';
													$assignedStrict = $permName !== '' ? has_permission_strict($permName, $sessionDbgPerms) : false;
													$canBypass = $permName !== '' ? has_permission($permName, $sessionDbgPerms, $sessionDbgRoles) : false;
												?>
												<div>
													<span style="color:#e2e8f0;"><?= esc((string)$label) ?>:</span>
													<span style="color:#93c5fd;"><?= esc($permName) ?></span>
													<span style="opacity:.85;"> | en sesión:</span>
													<strong style="color:<?= $assignedStrict ? '#34d399' : '#fca5a5' ?>;">
														<?= $assignedStrict ? 'SI' : 'NO' ?>
													</strong>
													<span style="opacity:.85;"> | acceso efectivo:</span>
													<strong style="color:<?= $canBypass ? '#34d399' : '#fca5a5' ?>;">
														<?= $canBypass ? 'SI' : 'NO' ?>
													</strong>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								<?php endif; ?>
								<pre id="audit-content" style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-size: 12px; line-height: 1.5; color: #ffffff;">Sin datos de auditoria.</pre>
							</div>
						<?php endif; ?>

					<?php
							if (!empty($output)) {
								echo $output;
							}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
	<?php
	if (!empty($js_files)) {
		foreach($js_files as $file) { ?>
			<script src="<?php echo $file; ?>"></script>
		<?php }
	}
	?>
	
	<!-- Additional JavaScript for enhanced functionality -->
	<script>
		$(document).ready(function() {
			// Add fade-in animation to rows (only once)
			if (!$('table.table tbody tr').first().hasClass('animated')) {
				$('table.table tbody tr').addClass('animated').each(function(index) {
					$(this).css({
						'opacity': '0',
						'transform': 'translateY(10px)'
					}).delay(50 * index).animate({
						'opacity': '1'
					}, 300, function() {
						$(this).css('transform', 'translateY(0)');
					});
				});
			}

			// Add icons to buttons only if they don't already have icons
			function addIconToButton(selector, iconClass) {
				$(selector).each(function() {
					if ($(this).find('i').length === 0) {
						$(this).prepend('<i class="' + iconClass + '"></i> ');
					}
				});
			}

			// Apply icons only to main action buttons, not dropdown items
			addIconToButton('.grocery-crud-body > .btn-success:contains("Add"), .grocery-crud-body > .btn-success:contains("Agregar")', 'fas fa-plus');
			addIconToButton('.modal .btn-success:contains("Save"), .modal .btn-success:contains("Guardar")', 'fas fa-save');
			addIconToButton('.modal .btn-warning:contains("Back"), .modal .btn-warning:contains("Volver")', 'fas fa-arrow-left');

			// Enhance search field placeholder
			var searchInput = $('.floatL input[type="text"], .search-field input[type="text"]');
			if (searchInput.attr('placeholder') === '' || !searchInput.attr('placeholder')) {
				searchInput.attr('placeholder', 'Buscar...');
			}

			// Ensure "Mas" dropdown works after filters/redraws
			function bindMoreDropdowns() {
				$(document).off('click.gc-more', '.grocery-crud-body .dropdown-toggle');
				$(document).on('click.gc-more', '.grocery-crud-body .dropdown-toggle', function(e) {
					e.preventDefault();
					e.stopImmediatePropagation();
					var $toggle = $(this);
					var $dropdown = $toggle.closest('.dropdown, .btn-group');
					var $menu = $dropdown.find('.dropdown-menu').first();
					if (!$menu.length) {
						return;
					}
					var isOpen = $menu.hasClass('show');
					$('.grocery-crud-body .dropdown-menu.show').not($menu).removeClass('show');
					$('.grocery-crud-body .dropdown.show').not($dropdown).removeClass('show');
					$dropdown.toggleClass('show', !isOpen);
					$menu.toggleClass('show', !isOpen);
				});

				$(document).off('click.gc-more-close');
				$(document).on('click.gc-more-close', function() {
					$('.grocery-crud-body .dropdown-menu.show').removeClass('show');
					$('.grocery-crud-body .dropdown.show').removeClass('show');
				});
			}

			bindMoreDropdowns();

			// Convert status/boolean fields to toggle switches
			function initStatusToggles() {
				// Find status/boolean input fields that haven't been converted yet
				// Includes: status, activo, active, enabled, habilitado
				var statusInputs = $(
					'input[name="status"]:not(.toggle-converted), select[name="status"]:not(.toggle-converted), ' +
					'input[name="activo"]:not(.toggle-converted), select[name="activo"]:not(.toggle-converted), ' +
					'input[name="active"]:not(.toggle-converted), select[name="active"]:not(.toggle-converted), ' +
					'input[name="enabled"]:not(.toggle-converted), select[name="enabled"]:not(.toggle-converted), ' +
					'input[name="habilitado"]:not(.toggle-converted), select[name="habilitado"]:not(.toggle-converted)'
				);
				
				statusInputs.each(function() {
					var $input = $(this);
					
					// Mark as converted to avoid duplicate processing
					$input.addClass('toggle-converted');
					
					// Keep the input visible in DOM but move it off screen
					$input.css({
						'position': 'absolute',
						'left': '-9999px',
						'width': '1px',
						'height': '1px'
					});
					
					// Ensure input is not disabled
					$input.prop('disabled', false);
					$input.prop('readonly', false);
					$input.removeAttr('disabled');
					$input.removeAttr('readonly');
					
					var currentValue = $input.val();
					var isActive = (currentValue == '1' || currentValue == 1 || currentValue === 'active' || currentValue === true);
					
					// Set initial value
					$input.val(isActive ? '1' : '0');
					
					console.log('Initialized toggle for:', $input.attr('name'), 'with value:', $input.val());
					
					// Create toggle switch HTML
					var toggleHtml = '<div class="status-toggle-wrapper">' +
						'<div class="status-toggle-switch ' + (isActive ? 'active' : '') + '" data-status="' + (isActive ? '1' : '0') + '">' +
						'<div class="status-toggle-slider"></div>' +
						'</div>' +
						'<span class="status-toggle-label">' + (isActive ? 'Activo' : 'Inactivo') + '</span>' +
						'</div>';
					
					// Get parent container
					var $parent = $input.parent();
					
					// Insert toggle
					$parent.append(toggleHtml);
					
					// Get references to the newly created elements
					var $wrapper = $parent.find('.status-toggle-wrapper').last();
					var $toggle = $wrapper.find('.status-toggle-switch');
					var $label = $wrapper.find('.status-toggle-label');
					
				// Get form reference early
				var $form = $input.closest('form');
				
				// Handle toggle click
				$toggle.on('click', function() {
					var isActive = $(this).hasClass('active');
					var newValue = isActive ? '0' : '1';
					
					// Toggle active class
					$(this).toggleClass('active');
					$(this).data('status', newValue);
					
					// Update label
					$label.text(isActive ? 'Inactivo' : 'Activo');
					
					// Buscar el campo por name y actualizar su valor (input o select)
					var fieldName = $input.attr('name');
					var $statusField = $form.find('input[name="' + fieldName + '"], select[name="' + fieldName + '"]');
					if ($statusField.length) {
						$statusField.val(newValue);
						$statusField.attr('value', newValue);
						$statusField.trigger('change');
					}
					
					// También actualizar el input original por si acaso
					$input.val(newValue);
					$input.attr('value', newValue);
					$input.trigger('change');
					
					console.log('Toggle clicked:', fieldName, 'nuevo valor:', newValue, 'Campo encontrado:', $statusField.length);
				});
				
				// Before form submission, ensure value is correct
				if ($form.length) {
						$form.on('submit', function(e) {
							var toggleValue = $toggle.data('status');
							$input.val(toggleValue);
							$input.prop('disabled', false);
							var fieldName = $input.attr('name');
							$form.find('select[name="' + fieldName + '"]').val(toggleValue);
							
							console.log('Form submit - Campo:', $input.attr('name'), 'Valor final:', $input.val(), 'Disabled:', $input.prop('disabled'));
							
							// Show audit debug
							showFormData($form);
						});
					}
				});
			}

			// Function to show form data in audit div
			function showFormData($form) {
				var auditContent = $('#audit-content');
				if (!auditContent.length) return;
				var formData = new FormData($form[0]);
				var data = {};
				var hasStatus = false;
				
				formData.forEach(function(value, key) {
					if (key === 'status' || key === 'activo' || key === 'active' || key === 'enabled' || key === 'habilitado') {
						hasStatus = true;
					}
					if (data[key]) {
						if (!Array.isArray(data[key])) {
							data[key] = [data[key]];
						}
						data[key].push(value);
					} else {
						data[key] = value;
					}
				});
				
				var output = JSON.stringify(data, null, 2);
				if (!hasStatus) {
					output = '⚠️ WARNING: Status field NOT found in FormData!\n\n' + output;
				}
				
				auditContent.text(output);
			}

			function showAuditPayload(payload) {
				if (!payload) return;
				var auditContent = $('#audit-content');
				if (!auditContent.length) return;
				var output = JSON.stringify(payload, null, 2);
				auditContent.text(output);
			}
			
			// Function para actualizar visibilidad del audit div según debug mode
			function updateAuditVisibility() {
				var isDebugOn = localStorage.getItem('debugMode') === 'true';
				var auditDiv = $('#audit-debug');
				
				if (isDebugOn) {
					auditDiv.show();
				} else {
					auditDiv.hide();
				}
			}
			
			// Verificar estado inicial al cargar la página (solo si existe el bloque)
			$(document).ready(function() {
				updateAuditVisibility();
				<?php if (!empty($canDebugAudit) && !empty($audit_payload)) : ?>
				showAuditPayload(<?= json_encode($audit_payload) ?>);
				<?php endif; ?>
			});
			
			// Escuchar cambios en el botón debug
			$(document).on('click', '#debugToggleBtn', function() {
				setTimeout(function() {
					updateAuditVisibility();
				}, 100);
			});
			
			// Listener para cambios en localStorage (otras pestañas)
			window.addEventListener('storage', function(e) {
				if (e.key === 'debugMode') {
					updateAuditVisibility();
				}
			});

			// Initialize on page load with delay
			setTimeout(function() {
				initStatusToggles();
			}, 500);

			// Re-initialize when modal opens (for add/edit forms)
			$(document).on('shown.bs.modal', '.modal', function() {
				setTimeout(function() {
					initStatusToggles();
				}, 500);
			});
			
			// Also try on document changes (for dynamic content)
			var statusObserver = new MutationObserver(function(mutations) {
				initStatusToggles();
				convertStatusBadges();
			});
			
			// Observe the body for changes
			if (document.querySelector('.grocery-crud-body')) {
				statusObserver.observe(document.querySelector('.grocery-crud-body'), {
					childList: true,
					subtree: true
				});
			}

			$(document).ajaxComplete(function() {
				bindMoreDropdowns();
				convertStatusBadges();
			});

			function normalizeHeaderText(t) {
				return (t || '').toString().trim().toLowerCase();
			}

			function findColumnIndex($table, candidates) {
				var idx = -1;
				$table.find('thead th').each(function(i) {
					var txt = normalizeHeaderText($(this).text());
					for (var c = 0; c < candidates.length; c++) {
						if (txt === candidates[c]) {
							idx = i;
							return false;
						}
					}
				});
				return idx;
			}

			function escapeAttr(s) {
				return (s || '').toString()
					.replace(/&/g, '&amp;')
					.replace(/"/g, '&quot;')
					.replace(/'/g, '&#039;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;');
			}

			function convertStatusBadges() {
				var isUsersGrid = window.location.pathname.indexOf('/users/users') !== -1;
				$('.grocery-crud-body table').each(function() {
					var $table = $(this);
					var statusIdx = findColumnIndex($table, ['status']);
					if (statusIdx < 0) return;

					var idIdx = findColumnIndex($table, ['id']);
					var usernameIdx = findColumnIndex($table, ['username']);

					$table.find('tbody tr').each(function() {
						var $tr = $(this);
						var $tds = $tr.find('td');
						var $cell = $tds.eq(statusIdx);
						if (!$cell.length) return;

						// Si ya tiene HTML (o ya fue convertido), no tocar.
						if ($cell.find('a,button,input,select,textarea,.status-badge,.js-toggle-user-status').length) {
							return;
						}

						var text = ($cell.text() || '').trim();
						var isActive = (text === '1' || text === 'Active' || text === 'Activo' || text === 'active');
						var isInactive = (text === '0' || text === 'Inactive' || text === 'Inactivo' || text === 'inactive');
						if (!isActive && !isInactive) return;

						if (isUsersGrid && idIdx >= 0) {
							var userId = parseInt(($tds.eq(idIdx).text() || '').trim(), 10) || 0;
							var username = usernameIdx >= 0 ? (($tds.eq(usernameIdx).text() || '').trim()) : '';
							if (userId > 0) {
								var cls = isActive ? 'status-badge active' : 'status-badge inactive';
								var icon = isActive ? 'fas fa-check-circle' : 'fas fa-times-circle';
								var label = isActive ? 'Activo' : 'Inactivo';
								var st = isActive ? '1' : '0';
								$cell.html(
									'<a href="#" class="' + cls + ' js-toggle-user-status"'
									+ ' data-user-id="' + userId + '"'
									+ ' data-username="' + escapeAttr(username) + '"'
									+ ' data-status="' + st + '"'
									+ ' style="cursor:pointer; text-decoration:none;">'
									+ '<i class="' + icon + '"></i> ' + label +
									'</a>'
								);
								return;
							}
						}

						// Default (no click)
						if (isActive) {
							$cell.html('<span class="status-badge active"><i class="fas fa-check-circle"></i> Activo</span>');
						} else if (isInactive) {
							$cell.html('<span class="status-badge inactive"><i class="fas fa-times-circle"></i> Inactivo</span>');
						}
					});
				});
			}

			// Convert status values in table cells to badges (solo columna Status)
			convertStatusBadges();
		});
	</script>
	
	<!-- ColResizable Library - DESACTIVADO TEMPORALMENTE
	<script src="<?= base_url('/public/assets/src/scripts/colResizable.min.js') ?>"></script>
	
	<script>
		// Initialize column resizing - DESACTIVADO
		function initColumnResize() {
			// Buscar la tabla principal de Grocery CRUD
			var $table = $('.grocery-crud-body table.table, table.dataTable');
			
			if ($table.length > 0 && !$table.hasClass('JColResizer')) {
				
				try {
					// Verificar si el usuario había redimensionado previamente
					var wasResized = localStorage.getItem('gc_user_resized_' + window.location.pathname);
					var savedWidths = localStorage.getItem('gc_column_widths_' + window.location.pathname);
					
					// Inicializar colResizable
					$table.colResizable({
						liveDrag: true,
						draggingClass: "rangeDrag",
						gripInnerHtml: "<div class='rangeGrip'></div>",
						minWidth: 50,
						headerOnly: true,
						disable: false,
						partialRefresh: true,
						onDrag: function(e) {
							// Durante el arrastre, marcar la tabla
							var $currentTable = $(e.currentTarget);
							if (!$currentTable.hasClass('user-resized')) {
								$currentTable.addClass('user-resized');
							}
						},
						onResize: function(e) {
							// Marcar que el usuario ha redimensionado manualmente
							var $currentTable = $(e.currentTarget);
							$currentTable.addClass('user-resized');
							
							// Guardar anchos en localStorage
							var columns = $currentTable.find('th');
							var widths = [];
							columns.each(function() {
								widths.push($(this).width());
							});
							localStorage.setItem('gc_column_widths_' + window.location.pathname, JSON.stringify(widths));
							localStorage.setItem('gc_user_resized_' + window.location.pathname, 'true');
						}
					});
					
					// Si había anchos guardados, restaurarlos ahora
					if (wasResized === 'true' && savedWidths) {
						$table.addClass('user-resized');
						try {
							var widths = JSON.parse(savedWidths);
							var columns = $table.find('th');
							columns.each(function(index) {
								if (widths[index]) {
									$(this).css('width', widths[index] + 'px');
								}
							});
						} catch(e) {
							// Silenciar error
						}
					}
					
					// Agregar botón para resetear anchos
					addResetButton($table);
				} catch(err) {
					console.error('Error initializing column resize:', err);
				}
			}
		}
		
		// Agregar botón para resetear anchos de columnas
		function addResetButton($table) {
			if ($('.gc-reset-columns').length === 0) {
				var $resetBtn = $('<button class="btn btn-sm btn-outline-secondary gc-reset-columns" title="Resetear anchos de columnas">' +
					'<i class="fas fa-arrows-alt-h"></i> Auto-ajustar columnas' +
					'</button>');
				
				// Insertar el botón cerca de los controles de Grocery CRUD
				$('.grocery-crud-body .floatL, .grocery-crud-header').first().prepend($resetBtn);
				
				$resetBtn.on('click', function(e) {
					e.preventDefault();
					// Remover anchos fijos
					localStorage.removeItem('gc_column_widths_' + window.location.pathname);
					localStorage.removeItem('gc_user_resized_' + window.location.pathname);
					
					// Recargar la página para aplicar cambios
					location.reload();
				});
			}
		}

		// Intentar inicializar cuando la tabla esté lista
		$(document).ready(function() {
			var resizeInitAttempts = 0;
			var resizeInitInterval = setInterval(function() {
				if ($('.grocery-crud-body table.table, table.dataTable').length > 0) {
					initColumnResize();
					clearInterval(resizeInitInterval);
				}
				resizeInitAttempts++;
				if (resizeInitAttempts > 20) {
					clearInterval(resizeInitInterval);
				}
			}, 500);

			// También reintentar después de eventos Ajax
			$(document).ajaxComplete(function() {
				setTimeout(initColumnResize, 500);
			});
		});
	</script>
	-->
	
	<style>
		/* Botón de reset (desactivado temporalmente) */
		.gc-reset-columns {
			margin: 10px 0;
			display: inline-flex;
			align-items: center;
			gap: 6px;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white !important;
			border: none !important;
			padding: 8px 16px;
			border-radius: 6px;
			font-size: 13px;
			font-weight: 500;
			transition: all 0.2s ease;
			box-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);
		}
		
		.gc-reset-columns:hover {
			background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
			transform: translateY(-1px);
			box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
		}
		
		.gc-reset-columns i {
			font-size: 12px;
		}
	</style>
<?= $this->endSection() ?>

	



