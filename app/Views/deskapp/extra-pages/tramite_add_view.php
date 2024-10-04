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
			</ul>
			<div class="tab-content">
				<div class="tab-pane fade show active" id="home" role="tabpanel">
					<div class="pd-20">
						<div id="wizard">
							<!-- Step 1: Formulario de paso 1 -->
							<h3>Información</h3>
							<section>
								<div class="min-height-200px">
									<?php echo form_open('/deskapp/tramites/insert', ['class' => 'form-horizontal', 'id' => 'tramiteForm']); ?>
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
																		<option value="null">Seleccione</option>
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
																		<option value="null">Seleccione</option>
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
											<button type="submit" class="btn btn-primary"><?php echo 'Guardar'; ?></button>
										</div>

									<?php echo form_close(); ?>

									<script>
										var cliDirectoId = "<?php echo isset($fields['cli_directo']['value']) ? $fields['cli_directo']['value'] : ''; ?>";
										var ejecutivoId = "<?php echo isset($fields['cli_directo_ejecutivo_id']['value']) ? $fields['cli_directo_ejecutivo_id']['value'] : ''; ?>";
									</script>
								</div>
							</section>
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
	<script src="<?php echo base_url(); ?>/public/assets/src/scripts/my_scripts.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/scripts/forms_scripts.js"></script>
</body>
</html>

