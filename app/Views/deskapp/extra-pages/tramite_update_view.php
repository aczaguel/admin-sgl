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
			<?php if (has_permission('important_cancelar_tramite', esc($session->get('user_permissions')), esc($session->get('user_roles'))) || 
			          has_permission('important_concluir_tramite', esc($session->get('user_permissions')), esc($session->get('user_roles')))): ?>
				<div class="header-actions">
					<?php if (has_permission('important_cancelar_tramite', esc($session->get('user_permissions')), esc($session->get('user_roles')))): ?>
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

				<?php if (has_permission('important_concluir_tramite', esc($session->get('user_permissions')), esc($session->get('user_roles')))): ?>
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
				<?php if(isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
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
						<div id="wizard" class="wizard-modern">
							<!-- Step 1: Datos principales -->
							<?php if (has_permission('section_inicial_datos', esc($session->get('user_permissions')), esc($session->get('user_roles')))): ?>
								<h3>Información</h3>
								<section>
									<div class="min-height-200px">
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
										<?php if(puede_editar_modulo(esc($session->get('user_roles')), $tra_status_id, 'botones_agregar_servicio', $reembolso_status_id, $cobro_status_id, 1)): ?>
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
							
							<?php if (has_permission('section_asigna_gestor', esc($session->get('user_permissions')),esc($session->get('user_roles')))): ?>
								<h3>Gestor</h3>
								<section>
									<div class="min-height-200px">
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
							<?php if (has_permission('section_pago_derechos', esc($session->get('user_permissions')),esc($session->get('user_roles'))) && !in_array($tra_status_id, [11])): ?>
								<!-- Step 3: La forma en que se pagan los derechos -->
						<h3>Pago de Derechos</h3>
						<section>
							<!-- Grid 70/30: Formulario | Dropzone -->
							<div class="form-dropzone-grid">
								<!-- Columna Izquierda: Formulario (70%) -->
								<div class="form-column">
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
									if (has_permission('important_pasar_a_pagos', esc($session->get('user_permissions')),esc($session->get('user_roles')))):
										if($step_actual <= 3 && puede_editar_modulo(esc($session->get('user_roles')), $tra_status_id, 'boton_aprobar_tramite', $reembolso_status_id, $cobro_status_id, 3)): ?>
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
									<?php if(puede_editar_modulo(esc($session->get('user_roles')), $tra_status_id, 'step3_upload', $reembolso_status_id, $cobro_status_id, 3)): ?>									<!-- Contenedor Dropzone -->
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
						</section>
							<?php endif; ?>
							<?php if (in_array($tra_status_id, [23, 28, 20, 21])) : ?>
								<?php if (has_permission('section_pago_gestor', esc($session->get('user_permissions')),esc($session->get('user_roles'))) ): ?>
									<!-- Step 4: Se paga al gestor -->
									<h3>Pago a Gestor</h3>
									<section>
									<div class="form-dropzone-grid">
										<!-- Columna Izquierda: Formulario (70%) -->
										<div class="form-column">
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
											<?php if(puede_editar_modulo(esc($session->get('user_roles')), $tra_status_id, 'upload_pago_gestor', $reembolso_status_id, $cobro_status_id, 4)): ?>
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
								<?php if (has_permission('section_final_costos', esc($session->get('user_permissions')),esc($session->get('user_roles'))) ): ?>
									<!-- Step 5: Se cobra al cliente -->
									<h3>Cobro a Cliente</h3>
									<section>
									<!-- Grid 70/30: Formulario | Dropzone -->
									<div class="form-dropzone-grid">
										<!-- Columna Izquierda: Formulario (70%) -->
										<div class="form-column">
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
												<?php if(puede_editar_modulo(esc($session->get('user_roles')), $tra_status_id, 'upload_cobro_cliente', $reembolso_status_id, $cobro_status_id, 5)): ?>
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
			<?php endif; ?> <!-- Cierra el if de línea 293: in_array($tra_status_id, [23, 28, 20, 21]) -->
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
<?= $this->endSection() ?>




