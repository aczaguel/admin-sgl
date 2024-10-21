<!DOCTYPE html>
<html>
<head>
	<!-- Basic Page Info -->
	<meta charset="utf-8">
	<title>SGT - Tramites</title>
    <?php foreach($css_files as $file): ?>
        <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
    <?php endforeach; ?>
    <?php foreach($js_files as $file): ?>
        <script src="<?php echo $file; ?>"></script>
    <?php endforeach; ?>
	<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css">
	<link rel="stylesheet" href="/public/assets/src/styles/forms_styles.css">
	<link rel="stylesheet" href="/public/assets/src/styles/my_wizard.scss">
	<link rel="stylesheet" href="/public/assets/src/styles/my_grocery.css">
	<!-- jQuery Steps CSS -->
	<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-steps/1.1.0/jquery.steps.css"> -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/src/styles/jquery.steps.css">
    <style>
        .form-container {
            display: flex;
            flex-wrap: wrap;
        }
        .form-group {
            width: 50%;
            box-sizing: border-box;
            padding: 10px;
        }
        .form-group label {
            display: block;
        }
        .form-group input,
        .form-group select {
            width: 100%;
        }

		.error {
            color: red;
        }
        .error-message {
            font-size: 0.875em; /* Smaller font size for error messages */
        }
		.select2-container--default .select2-selection--single {
			height: calc(2.25rem + 2px); /* Tamaño del campo ajustado a Bootstrap */
			padding: 0.375rem 0.75rem;    /* Padding ajustado a los inputs de Bootstrap */
			border: 1px solid #ced4da;    /* Estilo del borde de Bootstrap */
			border-radius: 0.25rem;       /* Bordes redondeados típicos de Bootstrap */
		}

		.select2-selection__rendered {
			line-height: calc(2.25rem);   /* Alineación vertical del texto dentro del select */
		}

		.select2-container .select2-selection--single .select2-selection__arrow {
			height: calc(2.25rem);        /* Tamaño del icono de la flecha */
		}

		.select2-container--default .select2-selection--single .select2-selection__rendered {
			color: #444;
			line-height: 15px !important;
		}
    </style>
	<style>
        .header_wizard {
			display: flex;
			flex-direction: column;
			padding: 10px;
			border: 1px solid #ccc;
			width: 100%;
			font-family: Arial, sans-serif;
			font-size: 14px;
			font-style: italic;
			border-radius: 5px;
        }

        .header_wizard-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .header_wizard-bottom {
            text-align: right;
            margin-top: 10px;
        }
		.wizard > .steps > ul > li {
			width: 12.5% !important;
		}


		a.nav-link.active.text-blue {
			font-size: 14px !important;
			font-weight: 600;
		}

		.nav-tabs .nav-link {
			border: 1px solid transparent;
			border-top-left-radius: 0.25rem;
			border-top-right-radius: 0.25rem;
			font-size: 14px !important;
			color: #000 !important;
			font-weight: 400;
		}
		

    </style>
	<!-- Site favicon -->
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url(); ?>/public/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url(); ?>/public/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url(); ?>/public/assets/vendors/images/favicon-16x16.png">
		
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- Google Font -->
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/style.css">
	
	
	
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- jQuery Validate (opcional si usas validaciones de jQuery) -->


	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'UA-119386393-1');
	</script>
</head>
<body>
	<!-- echo header,rightsidebar,leftsidebar and loader -->
	<?php 
		echo view('deskapp/includes/_header');
		echo view('deskapp/includes/_sidebar');

	?>

	<div class="main-container">
		<div class="header_wizard">
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
		<script>
			var tramite_id = <?php echo $id; ?>
		</script>
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
				<?php if(isset($tra_status_id) && $tra_status_id == 23) : ?>
					<li class="nav-item">
						<a class="nav-link text-blue" data-toggle="tab" href="#final_evi" role="tab" aria-selected="false">Evidencias Finales</a>
					</li>
				<?php endif; ?>

			</ul>
			<div class="tab-content">
				<div class="tab-pane fade show active" id="home" role="tabpanel">
					<div class="pd-20">
						<div id="wizard">
							<!-- Step 1: Formulario de paso 1 -->
							<h3>Información</h3>
							<section>
								<div class="min-height-200px">
									
									<?php echo form_open(isset($id) ? "/deskapp/tramites/update_save/$id" : '/deskapp/tramites/insert', ['class' => 'form-horizontal', 'id' => 'tramiteForm']); ?>
									<div id="tramite_respuesta" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
										<span id="tramite_mensaje"></span>
									</div>
									<div id="tramite_respuesta_error" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none;">
										<span id="tramite_mensaje_error"></span>
									</div>
									<div class="row">
										<div class="col-md-6">
											<?php 
											$half_fields = array_slice($fields, 0, ceil(count($fields) / 2), true);
											foreach ($half_fields as $field_name => $field_info): 
												$value = isset($field_info['value']) ? $field_info['value'] : set_value($field_name);
												$required = isset($field_info['required']) ? $field_info['required'] : "";
												$readonly = isset($field_info['readonly']) ? $field_info['readonly'] : "";
												$disabled = isset($field_info['disabled']) ? $field_info['disabled'] : "";
											?>
												<?php if ($field_info['type'] == 'hidden'): ?>
													<input type="hidden" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $value; ?>">
												<?php else: ?>
													<div class="mb-3 row">
														<label for="<?php echo $field_name; ?>" class="col-sm-4 col-form-label"><?php echo $field_info['label']; ?></label>
														<div class="col-sm-8">
															<?php if ($field_info['type'] == 'text'): ?>
																<input type="text" class="form-control" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $value; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
															<?php elseif ($field_info['type'] == 'select'): ?>
																<select class="form-control select2" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<?php foreach ($field_info['options'] as $option_value => $option_label): ?>
																		<option value="<?php echo $option_value; ?>" <?php echo set_select($field_name, $option_value, $value == $option_value); ?>><?php echo $option_label; ?></option>
																	<?php endforeach; ?>
																</select>
															<?php elseif ($field_info['type'] == 'textarea'): ?>
																<textarea class="form-control" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>><?php echo $value; ?></textarea>
															<?php elseif ($field_info['type'] == 'checkbox'): ?>
																<input type="checkbox" class="form-check-input" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="1" <?php echo set_checkbox($field_name, '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
															<?php elseif ($field_info['type'] == 'radio' && $field_name == 'status'): ?>
																<div class="form-check form-check-inline">
																	<input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo set_radio('status', '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<label class="form-check-label" for="status_active">Activo</label>
																</div>
																<div class="form-check form-check-inline">
																	<input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" <?php echo set_radio('status', '0', $value == '0'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<label class="form-check-label" for="status_inactive">Inactivo</label>
																</div>
															<?php elseif ($field_info['type'] == 'datetime'): ?>
																<input type="text" class="form-control datetime-picker" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $value; ?>" <?php echo $disabled; ?>>
															<?php endif; ?>
															<div class="invalid-feedback">
																<?php echo \Config\Services::validation()->showError($field_name); ?>
															</div>
														</div>
													</div>
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
										<div class="col-md-6">
											<?php 
											$half_fields = array_slice($fields, ceil(count($fields) / 2), count($fields), true);
											foreach ($half_fields as $field_name => $field_info): 
												$value = isset($field_info['value']) ? $field_info['value'] : set_value($field_name);
												$required = isset($field_info['required']) ? $field_info['required'] : "";
												$readonly = isset($field_info['readonly']) ? $field_info['readonly'] : "";
												$disabled = isset($field_info['disabled']) ? $field_info['disabled'] : "";
											?>
												<?php if ($field_info['type'] == 'hidden'): ?>
													<input type="hidden" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $value; ?>">
												<?php else: ?>
													<div class="mb-3 row">
														<label for="<?php echo $field_name; ?>" class="col-sm-4 col-form-label"><?php echo $field_info['label']; ?></label>
														<div class="col-sm-8">
															<?php if ($field_info['type'] == 'text'): ?>
																<input type="text" class="form-control" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $value; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
															<?php elseif ($field_info['type'] == 'select'): ?>
																<select class="form-control select2" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<?php foreach ($field_info['options'] as $option_value => $option_label): ?>
																		<option value="<?php echo $option_value; ?>" <?php echo set_select($field_name, $option_value, $value == $option_value); ?>><?php echo $option_label; ?></option>
																	<?php endforeach; ?>
																</select>
															<?php elseif ($field_info['type'] == 'textarea'): ?>
																<textarea class="form-control" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>><?php echo $value; ?></textarea>
															<?php elseif ($field_info['type'] == 'checkbox'): ?>
																<input type="checkbox" class="form-check-input" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="1" <?php echo set_checkbox($field_name, '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
															<?php elseif ($field_info['type'] == 'radio' && $field_name == 'status'): ?>
																<div class="form-check form-check-inline">
																	<input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo set_radio('status', '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<label class="form-check-label" for="status_active">Activo</label>
																</div>
																<div class="form-check form-check-inline">
																	<input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" <?php echo set_radio('status', '0', $value == '0'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<label class="form-check-label" for="status_inactive">Inactivo</label>
																</div>
															<?php elseif ($field_info['type'] == 'datetime'): ?>
																<input type="text" class="form-control datetime-picker" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $value; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
															<?php endif; ?>
															<div class="invalid-feedback">
																<?php echo \Config\Services::validation()->showError($field_name); ?>
															</div>
														</div>
													</div>
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
									</div>



									<div class="text-center mt-4" id="boton_autorizar">
										<a href="/tramites/tramite" class="btn btn-secondary ml-2">Cancelar</a>
										<?php if (has_permission('editar_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){ ?>
											<button type="submit" class="btn btn-primary">Guardar</button>
										<?php } ?>
									</div>

									<?php echo form_close(); ?>

									<script>
										var cliDirectoId = "<?php echo isset($fields['cli_directo']['value']) ? $fields['cli_directo']['value'] : ''; ?>";
										var ejecutivoId = "<?php echo isset($fields['cli_directo_ejecutivo_id']['value']) ? $fields['cli_directo_ejecutivo_id']['value'] : ''; ?>";
									</script>
								</div>
							</section>

							<!-- Step 2: Formulario de paso 2 -->
							<h3>Gestor</h3>
							<section>
								<?php echo form_open(isset($id) ? "/deskapp/tramites/update_gestor_save/$id" : '/deskapp/tramites/insert', ['class' => 'form-horizontal', 'id' => 'gestorForm']); ?>
									<div id="gestor_respuesta" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
										<span id="gestor_mensaje"></span>
									</div>
									<div id="gestor_respuesta_error" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none;">
										<span id="gestor_mensaje_error"></span>
									</div>
									<div class="row">
									
										<div class="col-md-6">
											<?php 
											$half_gestor_campos = array_slice($gestor_campos, 0, ceil(count($gestor_campos) / 2), true);
											foreach ($half_gestor_campos as $gestor_campo_name => $gestor_campo_info): 
												$value = isset($gestor_campo_info['value']) ? $gestor_campo_info['value'] : set_value($gestor_campo_name);
												$required = isset($gestor_campo_info['required']) ? $gestor_campo_info['required'] : "";
												$readonly = isset($gestor_campo_info['readonly']) ? $gestor_campo_info['readonly'] : "";
												$disabled = isset($gestor_campo_info['disabled']) ? $gestor_campo_info['disabled'] : "";
											?>
												<?php if ($gestor_campo_info['type'] == 'hidden'): ?>
													<input type="hidden" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" value="<?php echo $value; ?>">
												<?php else: ?>
													<div class="mb-3 row">
														<label for="<?php echo $gestor_campo_name; ?>" class="col-sm-4 col-form-label"><?php echo $gestor_campo_info['label']; ?></label>
														<div class="col-sm-8">
															<?php if ($gestor_campo_info['type'] == 'text'): ?>
																<input type="text" class="form-control" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
															<?php elseif ($gestor_campo_info['type'] == 'select'): ?>
																<select class="form-control select2" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<option value="">Seleccione</option>
																	<?php foreach ($gestor_campo_info['options'] as $option_value => $option_label): ?>
																		<option value="<?php echo $option_value; ?>" <?php echo set_select($gestor_campo_name, $option_value, $value == $option_value); ?>><?php echo $option_label; ?></option>
																	<?php endforeach; ?>
																</select>
															<?php elseif ($gestor_campo_info['type'] == 'textarea'): ?>
																<textarea class="form-control" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>><?php echo $value; ?></textarea>
															<?php elseif ($gestor_campo_info['type'] == 'checkbox'): ?>
																<input type="checkbox" class="form-check-input" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" value="1" <?php echo set_checkbox($gestor_campo_name, '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
															<?php elseif ($gestor_campo_info['type'] == 'radio' && $gestor_campo_name == 'status'): ?>
																<div class="form-check form-check-inline">
																	<input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo set_radio('status', '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<label class="form-check-label" for="status_active">Activo</label>
																</div>
																<div class="form-check form-check-inline">
																	<input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" <?php echo set_radio('status', '0', $value == '0'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<label class="form-check-label" for="status_inactive">Inactivo</label>
																</div>
															<?php elseif ($gestor_campo_info['type'] == 'datetime'): ?>
																<input type="text" class="form-control datetime-picker" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $disabled; ?>>
															<?php endif; ?>
															<div class="invalid-feedback">
																<?php echo \Config\Services::validation()->showError($gestor_campo_name); ?>
															</div>
														</div>
													</div>
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
										<div class="col-md-6">
											<?php 
											$half_gestor_campos = array_slice($gestor_campos, ceil(count($gestor_campos) / 2), count($gestor_campos), true);
											foreach ($half_gestor_campos as $gestor_campo_name => $gestor_campo_info): 
												$value = isset($gestor_campo_info['value']) ? $gestor_campo_info['value'] : set_value($gestor_campo_name);
												$required = isset($gestor_campo_info['required']) ? $gestor_campo_info['required'] : "";
												$readonly = isset($gestor_campo_info['readonly']) ? $gestor_campo_info['readonly'] : "";
												$disabled = isset($gestor_campo_info['disabled']) ? $gestor_campo_info['disabled'] : "";
											?>
												<?php if ($gestor_campo_info['type'] == 'hidden'): ?>
													<input type="hidden" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" value="<?php echo $value; ?>">
												<?php else: ?>
													<div class="mb-3 row">
														<label for="<?php echo $gestor_campo_name; ?>" class="col-sm-4 col-form-label"><?php echo $gestor_campo_info['label']; ?></label>
														<div class="col-sm-8">
															<?php if ($gestor_campo_info['type'] == 'text'): ?>
																<input type="text" class="form-control" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
															<?php elseif ($gestor_campo_info['type'] == 'select'): ?>
																<select class="form-control select2" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<option value="">Seleccione</option>
																	<?php foreach ($gestor_campo_info['options'] as $option_value => $option_label): ?>
																		<option value="<?php echo $option_value; ?>" <?php echo set_select($gestor_campo_name, $option_value, $value == $option_value); ?>><?php echo $option_label; ?></option>
																	<?php endforeach; ?>
																</select>
															<?php elseif ($gestor_campo_info['type'] == 'textarea'): ?>
																<textarea class="form-control" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>><?php echo $value; ?></textarea>
															<?php elseif ($gestor_campo_info['type'] == 'checkbox'): ?>
																<input type="checkbox" class="form-check-input" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" value="1" <?php echo set_checkbox($gestor_campo_name, '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
															<?php elseif ($gestor_campo_info['type'] == 'radio' && $gestor_campo_name == 'status'): ?>
																<div class="form-check form-check-inline">
																	<input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo set_radio('status', '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<label class="form-check-label" for="status_active">Activo</label>
																</div>
																<div class="form-check form-check-inline">
																	<input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" <?php echo set_radio('status', '0', $value == '0'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<label class="form-check-label" for="status_inactive">Inactivo</label>
																</div>
															<?php elseif ($gestor_campo_info['type'] == 'datetime'): ?>
																<input type="text" class="form-control datetime-picker" id="<?php echo $gestor_campo_name; ?>" name="<?php echo $gestor_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
															<?php endif; ?>
															<div class="invalid-feedback">
																<?php echo \Config\Services::validation()->showError($gestor_campo_name); ?>
															</div>
														</div>
													</div>
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
									</div>
									<div class="text-center mt-4" id="boton_autorizar">
										<a href="/tramites/tramite" class="btn btn-secondary ml-2">Cancelar</a>
										<?php if (has_permission('editar_gestores', esc($session->get('user_permissions')),esc($session->get('user_roles')))){ ?>
											<button type="submit" class="btn btn-primary">Guardar</button>
										<?php } ?>
									</div>

								<?php echo form_close(); ?>
								<script>
									var empresaGestoraId = "<?php echo isset($gestor_campos['empresa_gestora']['value']) ? $gestor_campos['empresa_gestora']['value'] : ''; ?>";
									var gestorId = "<?php echo isset($gestor_campos['gestor_id']['value']) ? $gestor_campos['gestor_id']['value'] : ''; ?>";
								</script>
							</section>
							<?php if(isset($tra_status_id) && $tra_status_id == 22 || $tra_status_id == 23) : ?>			
								<!-- Step 3: Formulario de paso 3 -->
								<h3>Pago de Derechos</h3>
								<section>
									<?php echo form_open(isset($id) ? "/deskapp/tramites/update_derechos_save/$id" : '/deskapp/tramites/insert', ['class' => 'form-horizontal', 'id' => 'derechosForm']); ?>
										<div id="derechos_respuesta" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
											<span id="derechos_mensaje"></span> 
										</div>
										<div id="derechos_respuesta_error" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none;">
											<span id="derechos_mensaje_error"></span>
										</div>
										<div class="row">
											<div class="col-md-6">
												<?php 
												$half_derechos_campos = array_slice($derechos_campos, 0, ceil(count($derechos_campos) / 2), true);
												foreach ($half_derechos_campos as $derechos_campo_name => $derechos_campo_info): 
													$value = isset($derechos_campo_info['value']) ? $derechos_campo_info['value'] : set_value($derechos_campo_name);
													$required = isset($derechos_campo_info['required']) ? $derechos_campo_info['required'] : "";
													$readonly = isset($derechos_campo_info['readonly']) ? $derechos_campo_info['readonly'] : "";
													$disabled = isset($derechos_campo_info['disabled']) ? $derechos_campo_info['disabled'] : "";
												?>
													<?php if ($derechos_campo_info['type'] == 'hidden'): ?>
														<input type="hidden" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" value="<?php echo $value; ?>">
													<?php else: ?>
														<div class="mb-3 row">
															<label for="<?php echo $derechos_campo_name; ?>" class="col-sm-4 col-form-label"><?php echo $derechos_campo_info['label']; ?></label>
															<div class="col-sm-8">
																<?php if ($derechos_campo_info['type'] == 'text' || $derechos_campo_info['type'] == 'number'): ?>
																	<input type="<?php echo $derechos_campo_info['type'] ?>" class="form-control" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																<?php elseif ($derechos_campo_info['type'] == 'select'): ?>
																	<select class="form-control select2" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<option value="">Seleccione</option>
																		<?php foreach ($derechos_campo_info['options'] as $option_value => $option_label): ?>
																			<option value="<?php echo $option_value; ?>" <?php echo set_select($derechos_campo_name, $option_value, $value == $option_value); ?>><?php echo $option_label; ?></option>
																		<?php endforeach; ?>
																	</select>
																<?php elseif ($derechos_campo_info['type'] == 'textarea'): ?>
																	<textarea class="form-control" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>><?php echo $value; ?></textarea>
																<?php elseif ($derechos_campo_info['type'] == 'checkbox'): ?>
																	<input type="checkbox" class="form-check-input" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" value="1" <?php echo set_checkbox($derechos_campo_name, '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																<?php elseif ($derechos_campo_info['type'] == 'radio' && $derechos_campo_name == 'status'): ?>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo set_radio('status', '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<label class="form-check-label" for="status_active">Activo</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" <?php echo set_radio('status', '0', $value == '0'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<label class="form-check-label" for="status_inactive">Inactivo</label>
																	</div>
																<?php elseif ($derechos_campo_info['type'] == 'datetime'): ?>
																	<input type="text" class="form-control datetime-picker" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $disabled; ?>>
																<?php endif; ?>
																<div class="invalid-feedback">
																	<?php echo \Config\Services::validation()->showError($derechos_campo_name); ?>
																</div>
															</div>
														</div>
													<?php endif; ?>
												<?php endforeach; ?>
											</div>
											<div class="col-md-6">
												<?php 
												$half_derechos_campos = array_slice($derechos_campos, ceil(count($derechos_campos) / 2), count($derechos_campos), true);
												foreach ($half_derechos_campos as $derechos_campo_name => $derechos_campo_info): 
													$value = isset($derechos_campo_info['value']) ? $derechos_campo_info['value'] : set_value($derechos_campo_name);
													$required = isset($derechos_campo_info['required']) ? $derechos_campo_info['required'] : "";
													$readonly = isset($derechos_campo_info['readonly']) ? $derechos_campo_info['readonly'] : "";
													$disabled = isset($derechos_campo_info['disabled']) ? $derechos_campo_info['disabled'] : "";
												?>
													<?php if ($derechos_campo_info['type'] == 'hidden'): ?>
														<input type="hidden" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" value="<?php echo $value; ?>">
													<?php else: ?>
														<div class="mb-3 row">
															<label for="<?php echo $derechos_campo_name; ?>" class="col-sm-4 col-form-label"><?php echo $derechos_campo_info['label']; ?></label>
															<div class="col-sm-8">
																<?php if ($derechos_campo_info['type'] == 'text' || $derechos_campo_info['type'] == 'number'): ?>
																	<input type="<?php echo $derechos_campo_info['type'] ?>" class="form-control" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																<?php elseif ($derechos_campo_info['type'] == 'select'): ?>
																	<select class="form-control select2" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<option value="">Seleccione</option>
																		<?php foreach ($derechos_campo_info['options'] as $option_value => $option_label): ?>
																			<option value="<?php echo $option_value; ?>" <?php echo set_select($derechos_campo_name, $option_value, $value == $option_value); ?>><?php echo $option_label; ?></option>
																		<?php endforeach; ?>
																	</select>
																<?php elseif ($derechos_campo_info['type'] == 'textarea'): ?>
																	<textarea class="form-control" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>><?php echo $value; ?></textarea>
																<?php elseif ($derechos_campo_info['type'] == 'checkbox'): ?>
																	<input type="checkbox" class="form-check-input" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" value="1" <?php echo set_checkbox($derechos_campo_name, '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																<?php elseif ($derechos_campo_info['type'] == 'radio' && $derechos_campo_name == 'status'): ?>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo set_radio('status', '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<label class="form-check-label" for="status_active">Activo</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" <?php echo set_radio('status', '0', $value == '0'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<label class="form-check-label" for="status_inactive">Inactivo</label>
																	</div>
																<?php elseif ($derechos_campo_info['type'] == 'datetime'): ?>
																	<input type="text" class="form-control datetime-picker" id="<?php echo $derechos_campo_name; ?>" name="<?php echo $derechos_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																<?php endif; ?>
																<div class="invalid-feedback">
																	<?php echo \Config\Services::validation()->showError($derechos_campo_name); ?>
																</div>
															</div>
														</div>
													<?php endif; ?>
												<?php endforeach; ?>
											</div>
										</div>
										<div class="text-center mt-4" id="boton_autorizar">
											<a href="/tramites/tramite" class="btn btn-secondary ml-2">Cancelar</a>
											<?php if (has_permission('editar_derechos', esc($session->get('user_permissions')),esc($session->get('user_roles')))){ ?>
												<button type="submit" class="btn btn-primary">Guardar</button>
											<?php } ?>
										</div>

									<?php echo form_close(); ?>
								</section>

								<!-- Step 4: Imagen -->
								<h3>Linea de Captura</h3>
								<section>
									<?php echo form_open(isset($id) ? "/deskapp/tramites/update_bancario_save/$id" : '/deskapp/tramites/insert', ['class' => 'form-horizontal', 'id' => 'bancarioForm']); ?>
											<div id="bancario_respuesta" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
												<span id="bancario_mensaje"></span>
											</div>
											<div id="bancario_respuesta_error" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none;">
												<span id="bancario_mensaje_error"></span>
											</div>
											<div class="row">
												<div class="col-md-6">
													<?php 
													$half_bancario_campos = array_slice($bancario_campos, 0, ceil(count($bancario_campos) / 2), true);
													foreach ($half_bancario_campos as $bancario_campo_name => $bancario_campo_info): 
														$value = isset($bancario_campo_info['value']) ? $bancario_campo_info['value'] : set_value($bancario_campo_name);
														$required = isset($bancario_campo_info['required']) ? $bancario_campo_info['required'] : "";
														$readonly = isset($bancario_campo_info['readonly']) ? $bancario_campo_info['readonly'] : "";
														$disabled = isset($bancario_campo_info['disabled']) ? $bancario_campo_info['disabled'] : "";
													?>
														<?php if ($bancario_campo_info['type'] == 'hidden'): ?>
															<input type="hidden" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" value="<?php echo $value; ?>">
														<?php else: ?>
															<div class="mb-3 row">
																<label for="<?php echo $bancario_campo_name; ?>" class="col-sm-4 col-form-label"><?php echo $bancario_campo_info['label']; ?></label>
																<div class="col-sm-8">
																	<?php if ($bancario_campo_info['type'] == 'text' || $bancario_campo_info['type'] == 'number'): ?>
																		<input type="<?php echo $bancario_campo_info['type'] ?>" class="form-control" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<?php elseif ($bancario_campo_info['type'] == 'select'): ?>
																		<select class="form-control select2" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																			<option value="">Seleccione</option>
																			<?php foreach ($bancario_campo_info['options'] as $option_value => $option_label): ?>
																				<option value="<?php echo $option_value; ?>" <?php echo set_select($bancario_campo_name, $option_value, $value == $option_value); ?>><?php echo $option_label; ?></option>
																			<?php endforeach; ?>
																		</select>
																	<?php elseif ($bancario_campo_info['type'] == 'textarea'): ?>
																		<textarea class="form-control" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>><?php echo $value; ?></textarea>
																	<?php elseif ($bancario_campo_info['type'] == 'checkbox'): ?>
																		<input type="checkbox" class="form-check-input" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" value="1" <?php echo set_checkbox($bancario_campo_name, '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<?php elseif ($bancario_campo_info['type'] == 'radio' && $bancario_campo_name == 'status'): ?>
																		<div class="form-check form-check-inline">
																			<input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo set_radio('status', '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																			<label class="form-check-label" for="status_active">Activo</label>
																		</div>
																		<div class="form-check form-check-inline">
																			<input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" <?php echo set_radio('status', '0', $value == '0'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																			<label class="form-check-label" for="status_inactive">Inactivo</label>
																		</div>
																	<?php elseif ($bancario_campo_info['type'] == 'datetime'): ?>
																		<input type="text" class="form-control datetime-picker" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $disabled; ?>>
																	<?php endif; ?>
																	<div class="invalid-feedback">
																		<?php echo \Config\Services::validation()->showError($bancario_campo_name); ?>
																	</div>
																</div>
															</div>
														<?php endif; ?>
													<?php endforeach; ?>
												</div>
												<div class="col-md-6">
													<?php 
													$half_bancario_campos = array_slice($bancario_campos, ceil(count($bancario_campos) / 2), count($bancario_campos), true);
													foreach ($half_bancario_campos as $bancario_campo_name => $bancario_campo_info): 
														$value = isset($bancario_campo_info['value']) ? $bancario_campo_info['value'] : set_value($bancario_campo_name);
														$required = isset($bancario_campo_info['required']) ? $bancario_campo_info['required'] : "";
														$readonly = isset($bancario_campo_info['readonly']) ? $bancario_campo_info['readonly'] : "";
														$disabled = isset($bancario_campo_info['disabled']) ? $bancario_campo_info['disabled'] : "";
													?>
														<?php if ($bancario_campo_info['type'] == 'hidden'): ?>
															<input type="hidden" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" value="<?php echo $value; ?>">
														<?php else: ?>
															<div class="mb-3 row">
																<label for="<?php echo $bancario_campo_name; ?>" class="col-sm-4 col-form-label"><?php echo $bancario_campo_info['label']; ?></label>
																<div class="col-sm-8">
																	<?php if ($bancario_campo_info['type'] == 'text' || $bancario_campo_info['type'] == 'number'): ?>
																		<input type="<?php echo $bancario_campo_info['type'] ?>" class="form-control" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<?php elseif ($bancario_campo_info['type'] == 'select'): ?>
																		<select class="form-control select2" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																			<option value="">Seleccione</option>
																			<?php foreach ($bancario_campo_info['options'] as $option_value => $option_label): ?>
																				<option value="<?php echo $option_value; ?>" <?php echo set_select($bancario_campo_name, $option_value, $value == $option_value); ?>><?php echo $option_label; ?></option>
																			<?php endforeach; ?>
																		</select>
																	<?php elseif ($bancario_campo_info['type'] == 'textarea'): ?>
																		<textarea class="form-control" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>><?php echo $value; ?></textarea>
																	<?php elseif ($bancario_campo_info['type'] == 'checkbox'): ?>
																		<input type="checkbox" class="form-check-input" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" value="1" <?php echo set_checkbox($bancario_campo_name, '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<?php elseif ($bancario_campo_info['type'] == 'radio' && $bancario_campo_name == 'status'): ?>
																		<div class="form-check form-check-inline">
																			<input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo set_radio('status', '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																			<label class="form-check-label" for="status_active">Activo</label>
																		</div>
																		<div class="form-check form-check-inline">
																			<input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" <?php echo set_radio('status', '0', $value == '0'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																			<label class="form-check-label" for="status_inactive">Inactivo</label>
																		</div>
																	<?php elseif ($bancario_campo_info['type'] == 'datetime'): ?>
																		<input type="text" class="form-control datetime-picker" id="<?php echo $bancario_campo_name; ?>" name="<?php echo $bancario_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																	<?php endif; ?>
																	<div class="invalid-feedback">
																		<?php echo \Config\Services::validation()->showError($bancario_campo_name); ?>
																	</div>
																</div>
															</div>
														<?php endif; ?>
													<?php endforeach; ?>
												</div>
											</div>
											<div class="text-center mt-4" id="boton_autorizar">
												<a href="/tramites/tramite" class="btn btn-secondary ml-2">Cancelar</a>
												<?php if (has_permission('editar_bancario', esc($session->get('user_permissions')),esc($session->get('user_roles')))){ ?>
													<button type="submit" class="btn btn-primary">Guardar</button>
												<?php } ?>
											</div>

										<?php echo form_close(); ?>
								</section>
								<h3>Pago de Derechos</h3>
								<section>
									<!-- <form action="<?= site_url("/deskapp/tramites/upload_comprobante/$id") ?>" method="post" enctype="multipart/form-data">
										<div class="mb-3 row">
											<label for="image" class="col-sm-4 col-form-label">Subir Imagen</label>
											<div class="col-sm-8">
												<input type="file" class="form-control" id="image" name="image" required>
											</div>
										</div>
							
										<div class="text-center mt-4">
											<button type="submit" class="btn btn-primary">Guardar Imagen</button>
										</div>
									</form> -->
									
									<form id="uploadForm" method="post" enctype="multipart/form-data">
										<div id="upload_respuesta" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
											<span id="upload_mensaje"></span>
										</div>
										<div id="upload_respuesta_error" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none;">
											<span id="upload_mensaje_error"></span>
										</div>
										
										<input type="hidden" name="tramite_id" value="<?php echo $id?>">

										<!-- Mostrar la imagen si existe -->
										<div class="mb-3 row justify-content-center">
											<?php if (!empty($derechos_comprobante)): 
												$derechos_comprobante_path = base_url() . "/assets/uploads/tramites/" . $derechos_comprobante;
												$extension = pathinfo($derechos_comprobante, PATHINFO_EXTENSION);
												
												// Si es un PDF, cargamos el ícono del PDF, de lo contrario, la imagen subida
												$is_pdf = strtolower($extension) === 'pdf';
												?>
												<div class="mb-3 text-center">

													<img src="<?php echo $is_pdf ? base_url() . '/public/assets/src/images/pdf-icon.png' : $derechos_comprobante_path; ?>"
														alt="Comprobante de Derechos" 
														id="current-image"
														style="max-width: 100px; border: 1px solid #ddd; border-radius: 8px; padding: 5px; display: block; margin: 0 auto;">
													<!-- Nombre del archivo centrado y descargable -->
													<a href="<?php echo $derechos_comprobante_path; ?>" download="<?php echo basename($derechos_comprobante); ?>" 
													style="display: block; margin-top: 10px; font-size: 14px; color: #333; text-decoration: none;">
														<?php echo basename($derechos_comprobante); ?>
													</a>

												</div>
											<?php endif; ?>
										</div>

										<!-- Subir nueva imagen -->
										<div class="mb-3 row">
											<label for="image" class="col-sm-4 col-form-label">Subir Imagen</label>
											<div class="col-sm-8">
												<input type="file" class="form-control" id="image" name="image" required>
											</div>
										</div>

										<div class="text-center mt-4">
											<button type="submit" class="btn btn-primary">Guardar Imagen</button>
											<?php if(isset($tra_status_id) && $tra_status_id == 22) : ?>
												<button type="button" class="btn btn-danger" id="" onclick="changeStatusTramite(<?php echo $id;?>, 23)">Aprobar Proceso</button>
											<?php endif; ?>
										</div>
									</form>

									<!-- Contenedor para mostrar mensajes de éxito o error -->
									<div id="responseMessage"></div>
								</section>
							<?php endif; ?>


							<?php if(isset($tra_status_id) && $tra_status_id == 23) : ?>
								<h3>Costos</h3>
								<section>
									<?php echo form_open(isset($id) ? "/deskapp/tramites/update_final_save/$id" : '/deskapp/tramites/insert', ['class' => 'form-horizontal', 'id' => 'finalForm']); ?>
										<div id="final_respuesta" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
											<span id="final_mensaje"></span>
										</div>
										<div id="final_respuesta_error" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none;">
											<span id=final_mensaje_error"></span>
										</div>
										<div class="row">
											<div class="col-md-6">
												<?php 
												$half_final_campos = array_slice($final_campos, 0, ceil(count($final_campos) / 2), true);
												foreach ($half_final_campos as $final_campo_name => $final_campo_info): 
													$value = isset($final_campo_info['value']) ? $final_campo_info['value'] : set_value($final_campo_name);
													$required = isset($final_campo_info['required']) ? $final_campo_info['required'] : "";
													$readonly = isset($final_campo_info['readonly']) ? $final_campo_info['readonly'] : "";
													$disabled = isset($final_campo_info['disabled']) ? $final_campo_info['disabled'] : "";
												?>
													<?php if ($final_campo_info['type'] == 'hidden'): ?>
														<input type="hidden" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" value="<?php echo $value; ?>">
													<?php else: ?>
														<div class="mb-3 row">
															<label for="<?php echo $final_campo_name; ?>" class="col-sm-4 col-form-label"><?php echo $final_campo_info['label']; ?></label>
															<div class="col-sm-8">
																<?php if ($final_campo_info['type'] == 'text' || $final_campo_info['type'] == 'number'): ?>
																	<input type="<?php echo $final_campo_info['type'] ?>" class="form-control" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																<?php elseif ($final_campo_info['type'] == 'select'): ?>
																	<select class="form-control select2" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<option value="">Seleccione</option>
																		<?php foreach ($final_campo_info['options'] as $option_value => $option_label): ?>
																			<option value="<?php echo $option_value; ?>" <?php echo set_select($final_campo_name, $option_value, $value == $option_value); ?>><?php echo $option_label; ?></option>
																		<?php endforeach; ?>
																	</select>
																<?php elseif ($final_campo_info['type'] == 'textarea'): ?>
																	<textarea class="form-control" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>><?php echo $value; ?></textarea>
																<?php elseif ($final_campo_info['type'] == 'checkbox'): ?>
																	<input type="checkbox" class="form-check-input" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" value="1" <?php echo set_checkbox($final_campo_name, '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																<?php elseif ($final_campo_info['type'] == 'radio' && $final_campo_name == 'status'): ?>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo set_radio('status', '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<label class="form-check-label" for="status_active">Activo</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" <?php echo set_radio('status', '0', $value == '0'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<label class="form-check-label" for="status_inactive">Inactivo</label>
																	</div>
																<?php elseif ($final_campo_info['type'] == 'datetime'): ?>
																	<input type="text" class="form-control datetime-picker" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $disabled; ?>>
																<?php endif; ?>
																<div class="invalid-feedback">
																	<?php echo \Config\Services::validation()->showError($final_campo_name); ?>
																</div>
															</div>
														</div>
													<?php endif; ?>
												<?php endforeach; ?>
											</div>
											<div class="col-md-6">
												<?php 
												$half_final_campos = array_slice($final_campos, ceil(count($final_campos) / 2), count($final_campos), true);
												foreach ($half_final_campos as $final_campo_name => $final_campo_info): 
													$value = isset($final_campo_info['value']) ? $final_campo_info['value'] : set_value($final_campo_name);
													$required = isset($final_campo_info['required']) ? $final_campo_info['required'] : "";
													$readonly = isset($final_campo_info['readonly']) ? $final_campo_info['readonly'] : "";
													$disabled = isset($final_campo_info['disabled']) ? $final_campo_info['disabled'] : "";
												?>
													<?php if ($final_campo_info['type'] == 'hidden'): ?>
														<input type="hidden" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" value="<?php echo $value; ?>">
													<?php else: ?>
														<div class="mb-3 row">
															<label for="<?php echo $final_campo_name; ?>" class="col-sm-4 col-form-label"><?php echo $final_campo_info['label']; ?></label>
															<div class="col-sm-8">
																<?php if ($final_campo_info['type'] == 'text' || $final_campo_info['type'] == 'number'): ?>
																	<input type="<?php echo $final_campo_info['type'] ?>" class="form-control" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																<?php elseif ($final_campo_info['type'] == 'select'): ?>
																	<select class="form-control select2" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<option value="">Seleccione</option>
																		<?php foreach ($final_campo_info['options'] as $option_value => $option_label): ?>
																			<option value="<?php echo $option_value; ?>" <?php echo set_select($final_campo_name, $option_value, $value == $option_value); ?>><?php echo $option_label; ?></option>
																		<?php endforeach; ?>
																	</select>
																<?php elseif ($final_campo_info['type'] == 'textarea'): ?>
																	<textarea class="form-control" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" <?php echo $required; ?> <?php echo $readonly; ?> <?php echo $disabled; ?>><?php echo $value; ?></textarea>
																<?php elseif ($final_campo_info['type'] == 'checkbox'): ?>
																	<input type="checkbox" class="form-check-input" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" value="1" <?php echo set_checkbox($final_campo_name, '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																<?php elseif ($final_campo_info['type'] == 'radio' && $final_campo_name == 'status'): ?>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo set_radio('status', '1', $value == '1'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<label class="form-check-label" for="status_active">Activo</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" <?php echo set_radio('status', '0', $value == '0'); ?> <?php echo $readonly; ?> <?php echo $disabled; ?>>
																		<label class="form-check-label" for="status_inactive">Inactivo</label>
																	</div>
																<?php elseif ($final_campo_info['type'] == 'datetime'): ?>
																	<input type="text" class="form-control datetime-picker" id="<?php echo $final_campo_name; ?>" name="<?php echo $final_campo_name; ?>" value="<?php echo $value; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?>>
																<?php endif; ?>
																<div class="invalid-feedback">
																	<?php echo \Config\Services::validation()->showError($final_campo_name); ?>
																</div>
															</div>
														</div>
													<?php endif; ?>
												<?php endforeach; ?>
											</div>
										</div>
										<div class="text-center mt-4" id="boton_autorizar">            
											<a href="/tramites/tramite" class="btn btn-secondary ml-2">Cancelar</a>
											<?php if (has_permission('editar_final', esc($session->get('user_permissions')),esc($session->get('user_roles')))){ ?>
												<button type="submit" class="btn btn-primary">Guardar</button>
											<?php } ?>
										</div>

									<?php echo form_close(); ?>
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
				<?php if(isset($tra_status_id) && $tra_status_id == 23) : ?>
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
				<?php endif; ?>
			</div>
		</div>
	</div>	
<!-- footer -->
<?php echo view('deskapp/includes/_footer'); ?>
	<!-- js -->

<?php
if (!empty($js_files)) {
    foreach($js_files as $file) { ?>
        <script src="<?php echo $file; ?>"></script>
    <?php }
}
?>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/script.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/process.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/layout-settings.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/plugins/jquery-steps/jquery.steps.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/scripts/forms_scripts.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/scripts/my_scripts.js"></script>
</body>
</html>

