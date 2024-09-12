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
	
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-119386393-1"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


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
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">

			<div class="page-header">
				<h2 class="h4"><?php echo isset($id) ? 'Actualizar datos del trámite ' : 'Agregar nuevo Trámite'; ?></h2>
				<hr class="my-4">
				
				
				<?php 
					if (isset($target_title)){
						if (isset($id)){ ?>
							<button class="btn btn-primary" onclick="changeStatusTramite(<?php echo $id?>, <?php echo $target_id?>); return false;" id="boton_autorizar">
							<i class="fas fa-check"></i> <?php echo $target_title; ?>
						</button>
				<?php }
				} ?>

			</div>
				<?php echo form_open(isset($id) ? "/deskapp/tramites/update_save/$id" : '/deskapp/tramites/insert', ['class' => 'form-horizontal', 'id' => 'tramiteForm']); ?>

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
					<button type="submit" class="btn btn-primary"><?php echo isset($id) ? 'Actualizar' : 'Guardar'; ?></butto
				<?php } ?>
			</div>

			<?php echo form_close(); ?>
			
			<script>
				var empresaGestoraId = "<?php echo isset($fields['empresa_gestora']['value']) ? $fields['empresa_gestora']['value'] : ''; ?>";
				var gestorId = "<?php echo isset($fields['gestor_id']['value']) ? $fields['gestor_id']['value'] : ''; ?>";
				var cliDirectoId = "<?php echo isset($fields['cli_directo']['value']) ? $fields['cli_directo']['value'] : ''; ?>";
				var ejecutivoId = "<?php echo isset($fields['cli_directo_ejecutivo_id']['value']) ? $fields['cli_directo_ejecutivo_id']['value'] : ''; ?>";
			</script>
			</div>
			<br>
			<?php
									if (!empty($output)) {
											echo $output;
									}
									?>
			<!-- footer -->
			<?php echo view('deskapp/includes/_footer'); ?>
		</div>
	</div>
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
	<script src="<?php echo base_url(); ?>/public/assets/src/scripts/my_scripts.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/scripts/forms_scripts.js"></script>
</body>
</html>