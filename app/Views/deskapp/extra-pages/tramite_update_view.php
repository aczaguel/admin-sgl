<?php 
// var_dump(json_decode($images_derechos_comprobante));
// $images_derechos_comprobante = json_decode(json_encode($images_derechos_comprobante));
// var_dump($images_derechos_comprobante);

if (isset($tra_status_id)) {
    // Obtener la URL actual
    $currentUrl = $_SERVER['REQUEST_URI'];
    // Definir la URL de destino según el valor de $tra_status_id
    if ($tra_status_id == 21) {
        $targetUrl = "/deskapp/cancelado/cancelado/$id";
    } 
	// elseif ($tra_status_id == 20) {
    //     $targetUrl = "/deskapp/concluido/ver/$id";
    // } 
	elseif ($tra_status_id == 29) {
        $targetUrl = "/deskapp/tramites/update_cotizacion/$id";
    }else {
        $targetUrl = "/deskapp/tramites/update/$id";
    }

    // Comparar la URL actual con la de destino y redirigir solo si son diferentes
    if ($currentUrl !== $targetUrl) {
        header("Location: " . $targetUrl);
        exit;
    }
}
?>
<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>

<script>
	var tramite_id = <?php echo $id; ?>
</script>

<?php $assets = base_url('/public/assets'); ?>
	<?php foreach($css_files as $file): ?>
        <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
    <?php endforeach; ?>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css">
	<link rel="stylesheet" href="<?= $assets ?>/src/styles/wizard_modern.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="<?= $assets ?>/src/styles/my_wizard.scss">
	<link rel="stylesheet" href="<?= $assets ?>/src/styles/jquery.steps.css">
	<link rel="stylesheet" href="<?= $assets ?>/src/styles/dropzone.css">
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
	<script>
		var wiz_step = "<?php echo isset($step) ? (int)($step - 1) : 0; ?>";
		var tra_status_id = "<?php echo (int)$tra_status_id; ?>";
	</script>

	<style>
		/* Stepper ("gusanito") arriba del wizard */
		.sgl-wizard-stepper-wrap{
			background: #fff;
			border-radius: 14px;
			padding: 14px 16px;
			box-shadow: 0 4px 16px rgba(0,0,0,.06);
			margin-bottom: 14px;
		}
		.sgl-wizard-stepper{
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 8px;
			position: relative;
			padding: 8px 6px 2px;
		}
		.sgl-wizard-stepper:before{
			content: '';
			position: absolute;
			top: 22px;
			left: 18px;
			right: 18px;
			height: 6px;
			background: #e9ecef;
			border-radius: 999px;
			z-index: 0;
		}
		.sgl-wizard-step{
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 6px;
			min-width: 90px;
			flex: 1;
			position: relative;
			z-index: 1;
		}
		.sgl-wizard-step .sgl-dot{
			width: 26px;
			height: 26px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 12px;
			font-weight: 800;
			background: #fff;
			border: 2px solid #cfd4da;
			color: #6c757d;
			box-shadow: 0 2px 8px rgba(0,0,0,.06);
		}
		.sgl-wizard-step .sgl-title{
			font-size: 11px;
			font-weight: 700;
			color: #6c757d;
			text-align: center;
			line-height: 1.15;
			max-width: 140px;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}
		.sgl-wizard-step.is-active .sgl-dot{
			border-color: #10B981;
			color: #10B981;
		}
		.sgl-wizard-step.is-active .sgl-title{
			color: #202342;
		}
		.sgl-wizard-step.is-completed .sgl-dot{
			background: linear-gradient(135deg, #10B981 0%, #06B6D4 100%);
			border-color: transparent;
			color: #fff;
		}
		.sgl-wizard-step.is-completed .sgl-dot:after{
			content: '✓';
			font-size: 12px;
			font-weight: 900;
		}
		.sgl-wizard-step.is-completed .sgl-dot span{ display:none; }
		.sgl-wizard-stepper .sgl-fill{
			position: absolute;
			top: 22px;
			left: 18px;
			height: 6px;
			background: linear-gradient(90deg, #10B981 0%, #06B6D4 100%);
			border-radius: 999px;
			z-index: 0;
			width: 0%;
			transition: width .25s ease;
		}
		@media (max-width: 768px){
			.sgl-wizard-step{ min-width: 70px; }
			.sgl-wizard-step .sgl-title{ max-width: 90px; }
		}

		/* Listón discreto de "datos completos" por step */
		.sgl-step-form-ribbon{
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 10px 12px;
			border-radius: 12px;
			background: #f8f9fa;
			border: 1px solid #e9ecef;
			margin: 0 0 12px 0;
		}
		.sgl-step-form-ribbon .sgl-icon{
			width: 26px;
			height: 26px;
			border-radius: 999px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-size: 13px;
		}
		.sgl-step-form-ribbon .sgl-text{
			font-size: 12px;
			font-weight: 700;
			color: #495057;
			line-height: 1.2;
		}
		.sgl-step-form-ribbon.is-complete{
			background: rgba(16,185,129,0.08);
			border-color: rgba(16,185,129,0.25);
		}


		.sgl-step-form-ribbon.is-complete .sgl-icon{
			background: linear-gradient(135deg, #10B981 0%, #06B6D4 100%);
			color: #fff;
		}
		.sgl-step-form-ribbon.is-incomplete{
			background: rgba(245,158,11,0.10);
			border-color: rgba(245,158,11,0.25);
		}
		.sgl-step-form-ribbon.is-incomplete .sgl-icon{
			background: #F59E0B;
			color: #fff;
		}
	</style>


<?= $this->endSection() ?>

<?= $this->section('content') ?>	
	
	<?php if(isset($tra_status_id) && $tra_status_id == 23) : ?>
		<li class="nav-item">
			<a class="nav-link text-blue" data-toggle="tab" href="#final_evi" role="tab" aria-selected="false">Evidencias Finales</a>
		</li>
	<?php endif; ?>
	<div class="main-container">
		<!-- Header Moderno del Trámite -->
		<div class="tramite-header-modern">
			<div class="header-top-row">
				<div class="folio-badge">
					<i class="fas fa-file-alt"></i>
					<?php echo (isset($folio) ? $folio : "TR-0000-000"); ?>
				</div>
				<div class="status-badge status-<?php 
					$statusClass = 'en-proceso';
					if (isset($tra_status_id)) {
						if ($tra_status_id == 20) $statusClass = 'concluido';
						elseif ($tra_status_id == 21) $statusClass = 'cancelado';
						elseif (in_array($tra_status_id, [27, 28])) $statusClass = 'urgente';
						elseif ($tra_status_id == 29) $statusClass = 'cotizacion';
					}
					echo $statusClass;
				?>">
					<i class="fas fa-circle"></i>
					<?php echo (isset($tra_status) ? $tra_status : "En Proceso"); ?>
				</div>
			</div>
			
			<div class="timeline-info">
				<div class="timeline-item">
					<div class="timeline-icon">
						<i class="fas fa-calendar-plus"></i>
					</div>
					<div class="timeline-content">
						<h6>Fecha Creación</h6>
						<p><?php echo (isset($created_at) ? date('d/m/Y H:i', strtotime($created_at)) : "-- / -- / ----"); ?></p>
					</div>
				</div>
				
				<div class="timeline-item">
					<div class="timeline-icon">
						<i class="fas fa-play-circle"></i>
					</div>
					<div class="timeline-content">
						<h6>Fecha Inicio</h6>
						<p><?php echo (isset($started_at) ? date('d/m/Y H:i', strtotime($started_at)) : "-- / -- / ----"); ?></p>
					</div>
				</div>
				
				<div class="timeline-item">
					<div class="timeline-icon">
						<i class="fas fa-file-contract"></i>
					</div>
					<div class="timeline-content">
						<h6>Tipo de Trámite</h6>
						<p><?php echo isset($tipo_tramite) ? $tipo_tramite : "N/A"; ?></p>
					</div>
				</div>
				
				<div class="timeline-item">
					<div class="timeline-icon">
						<i class="fas fa-user-tie"></i>
					</div>
					<div class="timeline-content">
						<h6>Gestor Asignado</h6>
						<p><?php echo isset($gestor) ? $gestor : "Sin asignar"; ?></p>
					</div>
				</div>
				
				<div class="timeline-item">
					<div class="timeline-icon">
						<i class="fas fa-building"></i>
					</div>
					<div class="timeline-content">
						<h6>Cliente</h6>
						<p><?php echo isset($cliente) ? $cliente : "N/A"; ?></p>
					</div>
				</div>
			</div>

			<!-- Botones de acciones -->
			<?php if (has_permission('important_cancelar_tramite', $session->get('user_permissions'), $session->get('user_roles')) || 
			          has_permission('important_concluir_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
				<div class="header-actions">
					<?php if (has_permission('important_cancelar_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
						<?php if ($tra_status_id == 11) { ?>
							<button type="button" class="btn-modern btn-warning" onclick="changeStatusTramite(<?php echo $id;?>, 29)">
								<i class="fas fa-file-invoice"></i>
								Es solo Cotización
							</button>
						<?php } ?>
						<?php if (!in_array($tra_status_id, [20, 21])) { ?>
							<button type="button" class="btn-modern btn-danger" data-toggle="modal" data-target="#Medium-modal">
								<i class="fas fa-times-circle"></i>
								Cancelar Trámite
							</button>
						<?php } ?>
					<?php endif; ?>

				<?php if (has_permission('important_concluir_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
					<?php if (in_array($tra_status_id, array(28))) : ?>
						<button type="button" class="btn-modern btn-success" onclick="concluirTramite(<?php echo $id;?>, 20)">
							<i class="fas fa-check-circle"></i>
							Concluir Trámite
						</button>
					<?php endif; ?>
				<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		
		<!-- Listón de Acciones Rápidas -->
		<div class="quick-actions-ribbon">
			<div class="ribbon-title">
				<i class="fas fa-bolt"></i>
				<span>Acciones Rápidas</span>
			</div>
			<div class="ribbon-buttons">
				<!-- Botón Documentos -->
				<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-documentos">
					<div class="ribbon-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
						<i class="fas fa-folder-open"></i>
					</div>
					<span class="ribbon-label">Documentos</span>
				</button>

				<!-- Botón Bitácora -->
				<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-bitacora">
					<div class="ribbon-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
						<i class="fas fa-history"></i>
					</div>
					<span class="ribbon-label">Bitácora</span>
				</button>

				<!-- Botón Pagos de Derecho -->
				<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pagos-derecho">
					<div class="ribbon-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
						<i class="fas fa-receipt"></i>
					</div>
					<span class="ribbon-label">Pagos Derecho</span>
				</button>

				<!-- Botón Pago al Gestor -->
				<?php if(isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21]) && has_permission('section_pago_gestor', $session->get('user_permissions'), $session->get('user_roles'))) : ?>
					<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pago-gestor">
						<div class="ribbon-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
							<i class="fas fa-hand-holding-usd"></i>
						</div>
						<span class="ribbon-label">Pago Gestor</span>
					</button>
				<?php endif; ?>

				<!-- Botón Cobros al Cliente -->
				<?php if(isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
					<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-cobro-cliente">
						<div class="ribbon-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
							<i class="fas fa-money-check-alt"></i>
						</div>
						<span class="ribbon-label">Cobros Cliente</span>
					</button>
				<?php endif; ?>

				<!-- Botón Evidencias Finales -->
				<?php if(isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
					<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-evidencias-finales">
						<div class="ribbon-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
							<i class="fas fa-check-double"></i>
						</div>
						<span class="ribbon-label">Evidencias</span>
					</button>
				<?php endif; ?>
			</div>
		</div>

		<!-- Contenedor Full Width del Wizard -->
		<div class="wizard-full-width-container">
			<div class="tramite-content-wrapper">
				<div class="pd-20">
						<div class="sgl-wizard-stepper-wrap" aria-label="Progreso del wizard">
							<div class="sgl-wizard-stepper" id="sglWizardStepper" role="navigation" aria-label="Progreso del trámite">
								<div class="sgl-fill" aria-hidden="true"></div>
								<div class="sgl-wizard-step" data-step="0">
									<div class="sgl-dot"><span>1</span></div>
									<div class="sgl-title" title="Información">Información</div>
								</div>
								<div class="sgl-wizard-step" data-step="1">
									<div class="sgl-dot"><span>2</span></div>
									<div class="sgl-title" title="Gestor">Gestor</div>
								</div>
								<div class="sgl-wizard-step" data-step="2">
									<div class="sgl-dot"><span>3</span></div>
									<div class="sgl-title" title="Pago de Derechos">Pago de Derechos</div>
								</div>
								<div class="sgl-wizard-step" data-step="3">
									<div class="sgl-dot"><span>4</span></div>
									<div class="sgl-title" title="Pago a Gestor">Pago a Gestor</div>
								</div>
								<div class="sgl-wizard-step" data-step="4">
									<div class="sgl-dot"><span>5</span></div>
									<div class="sgl-title" title="Cobro a Cliente">Cobro a Cliente</div>
								</div>
							</div>
						</div>
						<div id="wizard" class="wizard-modern">
							<!-- Step 1: Datos principales -->
							<?php if (has_permission('section_inicial_datos', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<h3>Información</h3>
								<section>
									<div class="min-height-200px">
										<div class="sgl-step-form-ribbon" data-form-id="tramiteForm" aria-live="polite">
											<div class="sgl-icon"><i class="fas fa-check"></i></div>
											<div class="sgl-text">Datos completos</div>
										</div>
										<?php 
											$prefix_form = "tramite";
											$form_action = "/deskapp/tramites/update_save/$id";
											$form_id = 'tramiteForm';
											$cancel_url = '/tramites/tramite';
											$submit_permission = 'editar_tramite';
											$field_values = $fields;
											echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $tra_status_id, $reembolso_status_id, $cobro_status_id, 1); 											
											?>
										<script>
											var cliDirectoId = "<?php echo isset($fields['cli_directo']['value']) ? $fields['cli_directo']['value'] : ''; ?>";
											var ejecutivoId = "<?php echo isset($fields['cli_directo_ejecutivo_id']['value']) ? $fields['cli_directo_ejecutivo_id']['value'] : ''; ?>";
										</script>
									</div>
									<div class="min-height-200px">
										<h5>Tipos de Servicio</h5>
										<div id="service-list">
											<!-- Aquí se pintarán dinámicamente los tipos de servicio -->
										</div>
										<?php if(puede_editar_modulo($session->get('user_roles'), $tra_status_id, 'botones_agregar_servicio', $reembolso_status_id, $cobro_status_id, 1)): ?>
										<button type="button" id="add-service" class="btn-wizard btn-primary mt-3">
											<i class="fas fa-plus-circle"></i> Agregar Servicio
										</button>

										<button type="button" id="save-services" class="btn-wizard btn-success mt-3">
											<i class="fas fa-save"></i> Guardar Servicios
											</button>
										<?php endif; ?>		
									</div>

									
								</section>
							<?php endif; ?>			
							<!-- Step 2: Asignacion de Gestor -->
							
							<?php if (has_permission('section_asigna_gestor', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<h3>Gestor</h3>
								<section>
									<div class="min-height-200px">
										<div class="sgl-step-form-ribbon" data-form-id="gestorForm" aria-live="polite">
											<div class="sgl-icon"><i class="fas fa-check"></i></div>
											<div class="sgl-text">Datos completos</div>
										</div>
										<?php 
											/* Paso 2: Asignar Gestor */
											$prefix_form = "gestor";
											$form_action = "/deskapp/tramites/update_gestor_save/$id";
											$form_id = 'gestorForm';
											$cancel_url = '/tramites/tramite';
											$submit_permission = 'editar_gestores';
											$field_values = $gestor_campos;
											echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $tra_status_id, $reembolso_status_id, $cobro_status_id, 2); 
										?>
									</div>
									
									<script>
										var empresaGestoraId = "<?php echo isset($gestor_campos['empresa_gestora']['value']) ? $gestor_campos['empresa_gestora']['value'] : ''; ?>";
										var gestorId = "<?php echo isset($gestor_campos['gestor_id']['value']) ? $gestor_campos['gestor_id']['value'] : ''; ?>";
									</script>
								</section>
							<?php endif; ?>
							<?php if (has_permission('section_pago_derechos', $session->get('user_permissions'), $session->get('user_roles')) && !in_array($tra_status_id, [11])): ?>
								<!-- Step 3: La forma en que se pagan los derechos -->
						<h3>Pago de Derechos</h3>
						<section>
							<!-- Grid 70/30: Formulario | Dropzone -->
							<div class="form-dropzone-grid">
								<!-- Columna Izquierda: Formulario (70%) -->
								<div class="form-column">
									<div class="sgl-step-form-ribbon" data-form-id="derechosForm" aria-live="polite">
										<div class="sgl-icon"><i class="fas fa-check"></i></div>
										<div class="sgl-text">Datos completos</div>
									</div>
									<?php 
										/* Paso 3: Pago de Derechos */
										$prefix_form = "derechos";
										$form_action = "/deskapp/tramites/update_derechos_save/$id";
										$form_id = 'derechosForm';
										$cancel_url = '/tramites/tramite';
										$submit_permission = 'editar_derechos';
										$field_values = $derechos_campos;
										echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $tra_status_id, $reembolso_status_id, $cobro_status_id, 3); 
									?>
								
									<?php 
									$paso3_completo = !empty($derechos_campos['derechos_tramite']['value']) && 
									                  !empty($derechos_campos['derechos_refer_banc']['value']) &&
									                  !empty($derechos_campos['derechos_revol_cliente']['value']);
									
									// Mapeo de estados a steps
									$arr_status = [
									    11 => 1, 22 => 2, 25 => 3, 26 => 3, 27 => 3, 
									    23 => 4, 28 => 5, 20 => 6, 21 => 7
									];
									$step_actual = isset($arr_status[$tra_status_id]) ? $arr_status[$tra_status_id] : 1;
									
									// Solo mostrar el botón si estamos en step 3 o inferior (no aprobado aún)
														if (has_permission('important_pasar_a_pagos', $session->get('user_permissions'), $session->get('user_roles'))):
															if($step_actual <= 3 && puede_editar_modulo($session->get('user_roles'), $tra_status_id, 'boton_aprobar_tramite', $reembolso_status_id, $cobro_status_id, 3)): ?>
											<?php if ($paso3_completo): ?>
												<div class="alert alert-info approval-ready" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border: 2px solid #4caf50; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 6px rgba(76, 175, 80, 0.15);">
													<div style="flex-shrink: 0;">
														<i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50;"></i>
													</div>
													<div style="flex: 1;">
														<h5 style="margin: 0 0 8px 0; color: #2e7d32; font-weight: 700;">
															<i class="fas fa-clipboard-check"></i> Información Completa
														</h5>
														<p style="margin: 0 0 15px 0; color: #1b5e20; font-size: 0.95rem;">
															Los datos de pago de derechos están completos. El trámite está listo para ser aprobado y continuar al siguiente paso.
														</p>
														<button type="button" 
												        class="btn-wizard btn-success btn-lg approval-button" 
												        onclick="if(confirm('¿Estás seguro de aprobar este trámite? Esta acción cambiará el estado del trámite.')) { changeStatusTramite(<?php echo $id;?>, 23); }">
															<i class="fas fa-check-double"></i> Aprobar Trámite
														</button>
													</div>
												</div>
											<?php else: ?>
												<div class="alert alert-warning approval-pending" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border: 2px solid #ff9800; border-radius: 12px; padding: 20px; display: flex; align-items; center; gap: 20px; box-shadow: 0 4px 6px rgba(255, 152, 0, 0.15);">
													<div style="flex-shrink: 0;">
														<i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ff9800;"></i>
													</div>
													<div style="flex: 1;">
														<h5 style="margin: 0 0 8px 0; color: #e65100; font-weight: 700;">
															<i class="fas fa-info-circle"></i> Información Incompleta
														</h5>
														<p style="margin: 0; color: #bf360c; font-size: 0.95rem;">
															Para aprobar el trámite, primero debes completar y guardar los siguientes campos obligatorios:
														</p>
														<ul style="margin: 10px 0 0 0; color: #bf360c; font-size: 0.9rem;">
															<?php if(empty($derechos_campos['derechos_tramite']['value'])): ?>
																<li><strong>Monto pago de derechos</strong></li>
															<?php endif; ?>
															<?php if(empty($derechos_campos['derechos_revol_cliente']['value'])): ?>
																<li><strong>Forma de Pago</strong></li>
															<?php endif; ?>
															<?php if(empty($derechos_campos['derechos_refer_banc']['value'])): ?>
																<li><strong>Referencia Bancaria</strong></li>
															<?php endif; ?>
														</ul>
													</div>
												</div>
											<?php endif; ?>
										<?php endif; ?>
									<?php endif; ?>
								</div>
								<!-- FIN: Columna Izquierda (Formulario) -->
							
							<!-- Columna Derecha: Dropzone (30%) -->
							<div class="dropzone-column">
								<div class="dropzone-sticky">
									<h5 class="dropzone-title">
										<i class="fas fa-cloud-upload-alt"></i> Documentos de Derechos
									</h5>
									<?php if(puede_editar_modulo($session->get('user_roles'), $tra_status_id, 'step3_upload', $reembolso_status_id, $cobro_status_id, 3)): ?>									<!-- Contenedor Dropzone -->
									<div class="dropzone-container">											<form class="dropzone dropzone-documentos dropzone-compact" id="miDropzone">
												<div class="dz-default dz-message">
													<button class="dz-button" type="button">
														<img src="/public/assets/src/images/upload.svg" class="dz-icon" alt="Subir Archivo">
														<p class="dz-text">Arrastra archivos aquí o haz clic</p>
														<p class="dz-subtext">Documentos de pago de derechos</p>
													</button>
												</div>
											</form>
										</div>
										<!-- Botón Subir -->
									<button id="btnSubirDocumentos" class="btnSubir">
										<i class="fas fa-cloud-upload-alt"></i> Subir Archivos
									</button>
										<!-- Mensaje de Eliminación -->
										<div class="delete-notice">
											<i class="fas fa-info-circle"></i>
											<h6>Para eliminar un archivo, solicítalo al administrador</h6>
										</div>
									
									<!-- Galería de Imágenes -->
									<div class="gallery-preview" id="documentos-container"></div>
								<?php endif; ?>
								</div>
							</div>
						</div> <!-- Cierra form-dropzone-grid -->
						<?php if (isset($sumatoria_derechos)):
							$sumDerechosNum = (float)$sumatoria_derechos;
						?>
							<div class="mt-3 alert alert-light" style="border: 1px solid #e9ecef; border-radius: 12px;">
								<strong>Sumatoria de Derechos:</strong> $<?= number_format($sumDerechosNum, 2) ?>
							</div>
						<?php endif; ?>
						</section>
							<?php endif; ?>
							<?php if (in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
								<?php if (has_permission('section_pago_gestor', $session->get('user_permissions'), $session->get('user_roles')) ): ?>
									<!-- Step 4: Se paga al gestor -->
									<h3>Pago a Gestor</h3>
									<section>
									<div class="form-dropzone-grid">
										<!-- Columna Izquierda: Formulario (70%) -->
										<div class="form-column">
											<div class="sgl-step-form-ribbon" data-form-id="pagoGestorForm" aria-live="polite">
												<div class="sgl-icon"><i class="fas fa-check"></i></div>
												<div class="sgl-text">Datos completos</div>
											</div>
											<?php 
												$prefix_form = "pago_gestor";
												$form_action = "/deskapp/tramites/update_pago_gestor/$id";
												$form_id = 'pagoGestorForm';
												$cancel_url = '/tramites/tramite';
												$submit_permission = 'editar_pago_gestor';
												$field_values = $pago_gestor;
												echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $tra_status_id, $reembolso_status_id, $cobro_status_id, 4);  
										?>
										<hr>
										<div class="gestor_costos_tipo_servicio" id="gestor_costos_tipo_servicio">
											<h5>Costos de Tipos de Servicio</h5>
										</div>
										<label for="costo_tramite_total" class="form-label"><b>Total de Costos:</b></label>
										<input type="text" id="costo_tramite_total" class="form-control text-end" readonly>
									</div>
									
									<!-- Columna Derecha: Dropzone (30%) -->
									<div class="dropzone-column">
										<div class="dropzone-sticky">
											<h5 class="dropzone-title">
												<i class="fas fa-cloud-upload-alt"></i> Documentos de Pago
											</h5>
											<?php if(puede_editar_modulo($session->get('user_roles'), $tra_status_id, 'upload_pago_gestor', $reembolso_status_id, $cobro_status_id, 4)): ?>
													<!-- Contenedor Dropzone -->
													<div class="dropzone-container">
														<form class="dropzone dropzone-gestor dropzone-compact" id="miDropzoneGestor">
															<div class="dz-default dz-message">
																<button class="dz-button" type="button">
																	<img src="/public/assets/src/images/upload.svg" class="dz-icon" alt="Subir Archivo">
																	<p class="dz-text">Arrastra archivos aquí</p>
																	<p class="dz-subtext">Pago al gestor</p>
																</button>
															</div>
														</form>
													</div>
													<button id="btnSubirGestor" class="btnSubir">
													<i class="fas fa-cloud-upload-alt"></i> Subir Archivos
												</button>
													<div class="delete-notice">
														<i class="fas fa-info-circle"></i>
													<h6>Para eliminar un archivo, solicítalo al administrador</h6>
												</div>
												<!-- Galería de Imágenes -->
													<div class="gallery-preview" id="gestor-container"></div>
												<?php endif; ?>
											</div>
										</div>
									</div> <!-- Cierra form-dropzone-grid -->
									</section>
								<?php endif; ?>
								<?php if (has_permission('section_final_costos', $session->get('user_permissions'), $session->get('user_roles')) ): ?>
									<!-- Step 5: Se cobra al cliente -->
									<h3>Cobro a Cliente</h3>
									<section>
									<!-- Grid 70/30: Formulario | Dropzone -->
									<div class="form-dropzone-grid">
										<!-- Columna Izquierda: Formulario (70%) -->
										<div class="form-column">
											<div class="sgl-step-form-ribbon" data-form-id="finalForm" aria-live="polite">
												<div class="sgl-icon"><i class="fas fa-check"></i></div>
												<div class="sgl-text">Datos completos</div>
											</div>
											<?php 
												$prefix_form = "final";
												$form_action = "/deskapp/tramites/update_final_save/$id";
												$form_id = 'finalForm';
												$cancel_url = '/tramites/tramite';
												$submit_permission = 'editar_final';
												$field_values = $final_campos;
												echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $tra_status_id, $reembolso_status_id, $cobro_status_id, 5); 
											?>
										</div>
										
										<!-- Columna Derecha: Dropzone (30%) -->
										<div class="dropzone-column">
											<div class="dropzone-sticky">
												<h5 class="dropzone-title">
													<i class="fas fa-cloud-upload-alt"></i> Documentos
												</h5>
												<?php if(puede_editar_modulo($session->get('user_roles'), $tra_status_id, 'upload_cobro_cliente', $reembolso_status_id, $cobro_status_id, 5)): ?>
												<!-- Contenedor Dropzone -->
												<div class="dropzone-container">
												<form class="dropzone dropzone-cliente dropzone-compact" id="miDropzoneCliente">
													<div class="dz-default dz-message">
														<button class="dz-button" type="button">
															<img src="/public/assets/src/images/upload.svg" class="dz-icon" alt="Subir Archivo">
															<p class="dz-text">Arrastra archivos aquí o haz clic</p>
															<p class="dz-subtext">Documentos de cobros al cliente</p>
															</button>
														</div>
													</form>
												</div>

												<!-- Botón Subir -->
									<button id="btnSubirCliente" class="btnSubir">
										<i class="fas fa-cloud-upload-alt"></i> Subir Archivos
									</button>
											<!-- Mensaje de Eliminación -->
											<div class="delete-notice">
												<i class="fas fa-info-circle"></i>
												<h6>Para eliminar un archivo, solicítalo al administrador</h6>
											</div>
											
											<!-- Galería de Imágenes -->
											<div class="gallery-preview" id="cliente-container"></div>
										<?php endif; ?>
										</div>
									</div>
								</div> <!-- Cierra form-dropzone-grid -->
								</section>
			<?php endif; ?>
			<?php endif; ?> <!-- Cierra el if: in_array($tra_status_id, [23, 27, 28, 20, 21]) -->
				</div> <!-- Cierra wizard-modern -->
				</div> <!-- Cierra pd-20 -->
			</div> <!-- Cierra tramite-content-wrapper -->
		</div> <!-- Cierra wizard-full-width-container -->


<!-- MODALS DE INFORMACIÓN -->

<!-- Modal Documentos -->
<div class="modal fade" id="modal-documentos" tabindex="-1" role="dialog" aria-labelledby="modalDocumentosLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
				<h4 class="modal-title" id="modalDocumentosLabel">
					<i class="fas fa-folder-open"></i> Documentos
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php 
						if (!empty($output_docs)) {
							echo $output_docs;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay documentos disponibles</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal Bitácora -->
<div class="modal fade" id="modal-bitacora" tabindex="-1" role="dialog" aria-labelledby="modalBitacoraLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
				<h4 class="modal-title" id="modalBitacoraLabel">
					<i class="fas fa-history"></i> Bitácora
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_bitacora)) {
							echo $output_bitacora;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay registros en la bitácora</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal Pagos de Derecho -->
<div class="modal fade" id="modal-pagos-derecho" tabindex="-1" role="dialog" aria-labelledby="modalPagosDerechoLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
				<h4 class="modal-title" id="modalPagosDerechoLabel">
					<i class="fas fa-receipt"></i> Pagos de Derecho
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_derechos)) {
							echo $output_derechos;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay documentos de pago de derecho</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal Pago al Gestor -->
<?php if(isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
<div class="modal fade" id="modal-pago-gestor" tabindex="-1" role="dialog" aria-labelledby="modalPagoGestorLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
				<h4 class="modal-title" id="modalPagoGestorLabel">
					<i class="fas fa-hand-holding-usd"></i> Pago al Gestor
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_pago_gestor)) {
							echo $output_pago_gestor;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay información de pago al gestor</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- Modal Cobros al Cliente -->
<?php if(isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
<div class="modal fade" id="modal-cobro-cliente" tabindex="-1" role="dialog" aria-labelledby="modalCobroClienteLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
				<h4 class="modal-title" id="modalCobroClienteLabel">
					<i class="fas fa-money-check-alt"></i> Cobros al Cliente
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_cobro_cliente)) {
							echo $output_cobro_cliente;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay registros de cobros al cliente</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- Modal Evidencias Finales -->
<?php if(isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
<div class="modal fade" id="modal-evidencias-finales" tabindex="-1" role="dialog" aria-labelledby="modalEvidenciasFinalesLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
				<h4 class="modal-title" id="modalEvidenciasFinalesLabel">
					<i class="fas fa-check-double"></i> Evidencias Finales
				</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($outputevidencias_finales)) {
							echo $outputevidencias_finales;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay evidencias finales disponibles</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- FIN MODALS DE INFORMACIÓN -->

		<div class="modal fade" id="Medium-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="myLargeModalLabel">Cancelar Trámite</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>
					<div class="modal-body">
						<!-- Campo para motivo de cancelación -->
						<form id="cancelForm">
						<div class="mb-3">
							<label for="motivo" class="form-label">Motivo de Cancelación</label>
							<textarea class="form-control" id="motivo" rows="3" required></textarea>
						</div>
						<input type="hidden" id="tramite_id" value="<!-- ID del trámite aquí -->">
						<input type="hidden" id="status_id" value="<!-- ID del estatus a cancelar aquí -->">
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
						<button type="button" class="btn btn-primary" id="saveCancelBtn">Continuar</button>
					</div>
				</div>
			</div>
		</div>
<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
	<?php
		if (!empty($js_files)) {
			foreach($js_files as $file) { ?>
				<script src="<?php echo $file . "?v=" .time() ; ?>"></script>
			<?php }
		}
	?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script src="<?= $assets ?>/src/plugins/jquery-steps/jquery.steps.js"></script>
	<script src="<?= $assets ?>/src/scripts/forms_scripts.js?v=<?php echo time(); ?>"></script>
	<script src="<?= $assets ?>/src/scripts/my_scripts.js?v=<?php echo time(); ?>"></script>
	<script src="<?= $assets ?>/src/scripts/my_wizard.js?v=<?php echo time(); ?>"></script>
	<script src="<?= $assets ?>/src/scripts/wizard_enhancements.js?v=<?php echo time(); ?>"></script>
	<script src="<?= $assets ?>/src/scripts/dropzone.js?v=<?php echo time(); ?>"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		(function() {
			var ribbonUpdateTimer = null;

			function clampInt(value, min, max) {
				var n = parseInt(value, 10);
				if (isNaN(n)) n = 0;
				if (n < min) n = min;
				if (n > max) n = max;
				return n;
			}

			function isValuePresent(el) {
				var tag = (el.tagName || '').toLowerCase();
				var type = (el.getAttribute('type') || '').toLowerCase();
				if (type === 'hidden') return true;
				if (el.disabled) return true;
				// Ignorar campos no visibles (por tabs/condiciones)
				if (el.offsetParent === null) return true;

				if (type === 'checkbox' || type === 'radio') {
					return !!el.checked;
				}

				if (tag === 'select') {
					return (el.value !== null && String(el.value).trim() !== '');
				}

				return (el.value !== null && String(el.value).trim() !== '');
			}

			function isFormCompleteByRequired(formEl) {
				if (!formEl) return true;
				var required = formEl.querySelectorAll('input[required], select[required], textarea[required]');
				if (!required || !required.length) return true;
				for (var i = 0; i < required.length; i++) {
					if (!isValuePresent(required[i])) return false;
				}
				return true;
			}

			function updateFormCompletionRibbons() {
				var ribbons = document.querySelectorAll('.sgl-step-form-ribbon[data-form-id]');
				if (!ribbons.length) return;
				ribbons.forEach(function(ribbon) {
					var formId = ribbon.getAttribute('data-form-id');
					if (!formId) return;
					var formEl = document.getElementById(formId);
					var ok = isFormCompleteByRequired(formEl);
					ribbon.classList.toggle('is-complete', ok);
					ribbon.classList.toggle('is-incomplete', !ok);
					var text = ribbon.querySelector('.sgl-text');
					if (text) {
						text.textContent = ok ? 'Datos completos' : 'Capturando información';
					}
					var icon = ribbon.querySelector('.sgl-icon i');
					if (icon) {
						icon.className = ok ? 'fas fa-check' : 'fas fa-pen';
					}
				});
			}

			function scheduleRibbonUpdate() {
				if (ribbonUpdateTimer) {
					clearTimeout(ribbonUpdateTimer);
				}
				ribbonUpdateTimer = setTimeout(updateFormCompletionRibbons, 120);
			}

			function getCurrentWizardIndex() {
				try {
					var $wizard = window.jQuery ? window.jQuery('#wizard') : null;
					if (!$wizard || !$wizard.length) return null;
					var $steps = $wizard.find('.steps li');
					var $current = $wizard.find('.steps li.current');
					if (!$steps.length || !$current.length) return null;
					return $steps.index($current);
				} catch (e) {
					return null;
				}
			}

			function renderStepper(activeIndex) {
				var stepper = document.getElementById('sglWizardStepper');
				if (!stepper) return;
				var steps = stepper.querySelectorAll('.sgl-wizard-step');
				var total = steps.length;
				if (!total) return;

				activeIndex = clampInt(activeIndex, 0, total - 1);
				steps.forEach(function(stepEl) {
					var idx = clampInt(stepEl.getAttribute('data-step'), 0, total - 1);
					stepEl.classList.toggle('is-active', idx === activeIndex);
					stepEl.classList.toggle('is-completed', idx < activeIndex);
				});

				var fill = stepper.querySelector('.sgl-fill');
				if (fill) {
					var pct = total <= 1 ? 0 : (activeIndex / (total - 1)) * 100;
					fill.style.width = pct + '%';
				}
			}

			function initStepperSync() {
				function statusToStepIndex(statusId) {
					var id = clampInt(statusId, 0, 999);
					// Mapeo de estatus a steps del wizard (0-4)
					// 0: Información, 1: Gestor, 2: Pago Derechos, 3: Pago Gestor, 4: Cobro Cliente
					var map = {
						11: 0,
						22: 1,
						25: 2,
						26: 2,
						27: 2,
						23: 3,
						28: 4,
						20: 4,
						21: 4,
						29: 0
					};
					return (typeof map[id] !== 'undefined') ? map[id] : 0;
				}

				// Siempre fijo al estatus actual (no se mueve al navegar en el wizard)
				var statusStepIndex = clampInt(statusToStepIndex(window.tra_status_id), 0, 4);
				renderStepper(statusStepIndex);

				if (window.jQuery) {
					var $wizard = window.jQuery('#wizard');
					$wizard.on('stepChanged', function() {
						renderStepper(statusStepIndex);
					});
				}
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', function() {
					setTimeout(function() {
						initStepperSync();
						updateFormCompletionRibbons();
						var wizard = document.getElementById('wizard');
						if (wizard) {
							wizard.addEventListener('input', scheduleRibbonUpdate, true);
							wizard.addEventListener('change', scheduleRibbonUpdate, true);
						}
					}, 0);
				});
			} else {
				setTimeout(function() {
					initStepperSync();
					updateFormCompletionRibbons();
					var wizard = document.getElementById('wizard');
					if (wizard) {
						wizard.addEventListener('input', scheduleRibbonUpdate, true);
						wizard.addEventListener('change', scheduleRibbonUpdate, true);
					}
				}, 0);
			}
		})();
	</script>
<?= $this->endSection() ?>




