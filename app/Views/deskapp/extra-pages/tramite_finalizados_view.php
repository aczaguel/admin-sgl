<?php 
if (isset($tra_status_id)) {
    // Obtener la URL actual
    $currentUrl = $_SERVER['REQUEST_URI'];

    // Definir la URL de destino según el valor de $tra_status_id
    if ($tra_status_id == 21) {
        $targetUrl = "/deskapp/cancelado/cancelado/$id";
    } elseif ($tra_status_id == 20) {
        $targetUrl = "/deskapp/concluido/ver/$id";
    } else {
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
<!-- CSS adicionales -->
<style>
        .file-preview {
            border: 1px solid #ddd;
            padding: 3px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
        }
        .file-preview img {
            max-width: 100px;
            height: auto;
            max-height: 100px;
            margin-bottom: 10px;
        }
        .file-preview a {
            display: block;
            word-wrap: break-word;
        }


		/* Contenedor principal */
        .dropzone-container {
            text-align: center;
            margin: 30px auto;
            width: 50%;
            border: 2px dashed #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            padding: 20px;
        }

        /* Botón de subida */
        .btnSubir {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btnSubir:hover {
            background-color: #0056b3;
        }

        /* Dropzone personalizado */
        .dz-default.dz-message .dz-button {
            background: none;
            border: none;
            cursor: pointer;
        }

        .dz-default.dz-message img {
            width: 90px;
        }

		.wizard {
			display: flex;
			flex-direction: column; /* Asegura que sea un diseño vertical */
		}

		.wizard .steps {
			order: 1; /* Pasos en primer lugar */
		}

		.wizard .actions {
			order: 2; /* Botones en segundo lugar */
		}

		.wizard .content {
			order: 3; /* Contenido al final */
		}
    </style>
<?php $assets = base_url('/public/assets'); ?>
	<?php foreach($css_files as $file): ?>
        <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
    <?php endforeach; ?>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css">
	<link rel="stylesheet" href="<?= $assets ?>/src/styles/my_wizard.scss">
	<link rel="stylesheet" href="<?= $assets ?>/src/styles/jquery.steps.css">
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<script>
		var wiz_step = "<?php echo isset($step) ? (int)($step - 1) : 0; ?>";
	</script>


<?= $this->endSection() ?>

<?= $this->section('content') ?>	
	
	
	<li class="nav-item">
		<a class="nav-link text-blue" data-toggle="tab" href="#final_evi" role="tab" aria-selected="false">Evidencias Finales</a>
	</li>
	<div class="main-container">
		
		<div class="header_wizard">
				<!-- Botón para abrir el modal -->
			
			<div class="header_wizard-row">
				<div>
					<strong>FECHA INICIO: </strong> <?php echo (isset($created_at)?$created_at:"-- / -- / ----	--:--:--"); ?>
				</div>
				<div>
					<strong>Tipo:</strong> <?php echo (isset($tra_tipo)?$tra_tipo:""); ?>
				</div>
			</div>
			<div class="header_wizard-row">
				<div>
					<strong>ESTATUS:</strong> <?php echo (isset($tra_status)?$tra_status:"");?>
				</div>
				<div>
					<strong>FECHA ASIGNACIÓN: </strong> <?php echo (isset($started_at)?$started_at:"-- / -- / ----	--:--:--"); ?>
				</div>
			</div>
			<div class="header_wizard-bottom">
				<strong>FOLIO: <?php echo (isset($folio)?$folio:"");?> </strong>
			</div>
			
		</div>
		<br>
		
		<div class="tab">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active text-blue" data-toggle="tab" href="#home" role="tab" aria-selected="true">Trámite</a>
				</li>
				<li class="nav-item">
					<a class="nav-link text-blue" data-toggle="tab" href="#profile" role="tab" aria-selected="false">Documentos</a>
				</li>
				<li class="nav-item">
					<a class="nav-link text-blue" data-toggle="tab" href="#contact" role="tab" aria-selected="false">Bitácora</a>
				</li>
				<li class="nav-item">
					<a class="nav-link text-blue" data-toggle="tab" href="#documentos_pago" role="tab" aria-selected="false">Pagos de Derecho</a>
				</li>

				<li class="nav-item">
					<a class="nav-link text-blue" data-toggle="tab" href="#pago_gestor" role="tab" aria-selected="false">Pago al Gestor</a>
				</li>
				
				<li class="nav-item">
					<a class="nav-link text-blue" data-toggle="tab" href="#cobro_cliente" role="tab" aria-selected="false">Cobros al Cliente</a>
				</li>

				<li class="nav-item">
					<a class="nav-link text-blue" data-toggle="tab" href="#final_evi" role="tab" aria-selected="false">Evidencias Finales</a>
				</li>
				
			</ul>
			<div class="tab-content">
				<div class="tab-pane fade show active" id="home" role="tabpanel">
					<div class="pd-20">
						<div id="wizard">
							<!-- Step 1: Datos principales -->
							<?php if (has_permission('section_inicial_datos', $session->get('user_permissions'), $session->get('user_roles'))): ?>

								<h3>Información</h3>
								<section>
									<div class="min-height-200px">
										<?php 
											/* Paso 1: Formulario principal */
											$prefix_form = "tramite";
											$form_action = "/deskapp/tramites/update_save/$id";
											$form_id = 'tramiteForm';
											$cancel_url = '/tramites/tramite';
											$submit_permission = 'editar_tramite';
											$field_values = $fields;
											$show_buttons = false;
											echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $show_buttons);  
										?>
										<script>
											var cliDirectoId = "<?php echo isset($fields['cli_directo']['value']) ? $fields['cli_directo']['value'] : ''; ?>";
											var ejecutivoId = "<?php echo isset($fields['cli_directo_ejecutivo_id']['value']) ? $fields['cli_directo_ejecutivo_id']['value'] : ''; ?>";
										</script>
									</div>
								</section>
							<?php endif; ?>					
							<!-- Step 2: Asignacion de Gestor -->
							
							<?php if (has_permission('section_asigna_gestor', $session->get('user_permissions'), $session->get('user_roles'))): ?>
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
											echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session); 
										?>
									</div>
									
									<script>
										var empresaGestoraId = "<?php echo isset($gestor_campos['empresa_gestora']['value']) ? $gestor_campos['empresa_gestora']['value'] : ''; ?>";
										var gestorId = "<?php echo isset($gestor_campos['gestor_id']['value']) ? $gestor_campos['gestor_id']['value'] : ''; ?>";
									</script>
								</section>
							<?php endif; ?>
							<?php if (has_permission('section_pago_derechos', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<!-- Step 3: La forma en que se pagan los derechos -->
								<h3>Pago de Derechos</h3>
								<section>
									<div class="min-height-200px">
										<?php 
											/* Paso 3: Pago de Derechos */
											$prefix_form = "derechos";
											$form_action = "/deskapp/tramites/update_derechos_save/$id";
											$form_id = 'derechosForm';
											$cancel_url = '/tramites/tramite';
											$submit_permission = 'editar_derechos';
											$field_values = $derechos_campos;
											$show_buttons = false;
											echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $show_buttons);  
										?>										
									</div>
								</section>
							<?php endif; ?>
							<?php if (has_permission('section_linea_captura', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<!-- Step 4: Solo se agrega la linea de captura -->
								<h3>Linea de Captura</h3>
								<section>
									<div class="min-height-200px">
										<?php 
											/* Paso 4: Datos Bancarios, Linea de captura */
											$prefix_form = "bancario";
											$form_action = "/deskapp/tramites/update_bancario_save/$id";
											$form_id = 'bancarioForm';
											$cancel_url = '/tramites/tramite';
											$submit_permission = 'editar_bancario';
											$field_values = $bancario_campos;
											$show_buttons = false;
											echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $show_buttons);  
										?>
									</div>
								</section>
							<?php endif; ?>
							<?php if (has_permission('section_documentos_pago', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<!-- Step 5: Todos los documentos relacionados con el proceso en ventanilla -->
								<h3>Documentos de Pago</h3>
								<section>
									<div>
										<div class="row" id="documentos-container"></div>
									</div>

								</section>
							<?php endif; ?>
							<?php if (has_permission('section_pago_gestor', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<!-- Step 6: Se paga al gestor -->
								<h3>Pago a Gestor</h3>
								<section>
									<div class="min-height-200px">
										<?php 
											/* Paso 3: Pago de Derechos */
											$prefix_form = "pago_gestor";
											$form_action = "/deskapp/concluido/update_gestor_costos/$id";
											$form_id = 'pagoGestorForm';
											$cancel_url = '/tramites/tramite';
											$submit_permission = 'editar_pago_gestor';
											$field_values = $pago_gestor;
											$show_buttons = $reembolso_pendiente;
											echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $show_buttons);  
										?>										
									</div>
									<hr>
									<div>
									<!-- Galería de Imágenes -->
									<div class="row" id="gestor-container"></div>
								</div>
								</section>
							<?php endif; ?>
							<?php if (has_permission('section_final_costos', $session->get('user_permissions'), $session->get('user_roles'))): ?>
								<!-- Step 7: Se cobra al cliente -->
								<h3>Cobro a Cliente</h3>
								<section>
									<div class="min-height-200px">
										
										<?php 
											/* Paso 3: Pago de Derechos */
											$prefix_form = "final";
											$form_action = "/deskapp/tramites/update_final_save/$id";
											$form_id = 'finalForm';
											$cancel_url = '/tramites/tramite';
											$submit_permission = 'editar_final';
											$field_values = $final_campos;
											$show_buttons = false;
											echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $show_buttons);  
										?>	
										<?php if (has_permission('important_concluir_tramite', $session->get('user_permissions'), $session->get('user_roles'))): 
												if (!in_array($tra_status_id, array(20, 21))) : ?>
													<button type="button" class="btn btn-danger" id="" onclick="changeStatusTramite(<?php echo $id;?>, 20)">Concluir Trámite</button>
											<?php endif; 
										endif; ?>									
									</div>
									<hr>
									<div>
										<!-- Galería de Imágenes -->
										<div class="row" id="cliente-container"></div>
									</div>
								</section>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="profile" role="tabpanel">
					<div class="pd-20">
						<div class="pd-ltr-20 xs-pd-20-10">
							<?php 
								if (!empty($output_docs)) {
										echo $output_docs;
								}
							?>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="contact" role="tabpanel">
					<div class="pd-20">
						<div class="pd-ltr-20 xs-pd-20-10">
							<?php
								if (!empty($output_bitacora)) {
										echo $output_bitacora;
								}
							?>
						</div>			
					</div>
				</div>
				<div class="tab-pane fade" id="documentos_pago" role="tabpanel">
					<div class="pd-20">
						<div class="pd-ltr-20 xs-pd-20-10">
									<?php 
										if (!empty($output_derechos)) {
												echo $output_derechos;
										}
									?>
						</div>			
					</div>
				</div>

					<div class="tab-pane fade" id="pago_gestor" role="tabpanel">
						<div class="pd-20">
							<div class="pd-ltr-20 xs-pd-20-10">
								<?php
									if (!empty($output_pago_gestor)) {
											echo $output_pago_gestor;
									}
								?>
							</div>			
						</div>
					</div>

					<div class="tab-pane fade" id="cobro_cliente" role="tabpanel">
						<div class="pd-20">
							<div class="pd-ltr-20 xs-pd-20-10">
								<?php
									if (!empty($output_cobro_cliente)) {
											echo $output_cobro_cliente;
									}
								?>
							</div>			
						</div>
					</div>

				
					<div class="tab-pane fade" id="final_evi" role="tabpanel">
						<div class="pd-20">
							<div class="pd-ltr-20 xs-pd-20-10">
								<?php
									if (!empty($outputevidencias_finales)) {
											echo $outputevidencias_finales;
									}
								?>
							</div>			
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
				<script src="<?php echo $file . "?v=" .time() ; ?>"></script>
			<?php }
		}
	?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script src="<?= $assets ?>/src/plugins/jquery-steps/jquery.steps.js"></script>
	<script src="<?= $assets ?>/src/scripts/forms_scripts_final.js?v=<?php echo time(); ?>"></script>
	<script src="<?= $assets ?>/src/scripts/my_scripts.js?v=<?php echo time(); ?>"></script>
	<script src="<?= $assets ?>/src/scripts/my_wizard.js?v=<?php echo time(); ?>"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->endSection() ?>