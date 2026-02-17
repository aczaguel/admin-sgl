<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>
	<?php $assets = base_url('/public/assets'); ?>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css">
	<link rel="stylesheet" href="<?= $assets ?>/src/styles/jquery.steps.css">
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <?php foreach($css_files as $file): ?>
        <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
    <?php endforeach; ?>

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
	.tramite-add-compact .page-header { margin-bottom: 16px !important; }
	.tramite-add-compact .card-header { padding: 12px 18px !important; }
	.tramite-add-compact .card-body { padding: 18px !important; }
	.tramite-add-compact .title h4 { font-size: 22px !important; margin-bottom: 4px !important; }
	.tramite-add-compact .title p { font-size: 13px !important; }
	.tramite-add-compact .form-group { padding: 6px !important; margin-bottom: 14px !important; }
	.tramite-add-compact .form-group label { margin-bottom: 4px !important; font-size: 13px !important; }
	.tramite-add-compact #boton_autorizar { padding: 16px 0 !important; margin-top: 10px !important; }
	.tramite-add-compact #boton_autorizar .btn { padding: 8px 20px !important; font-size: 14px !important; }
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

		/* Estilos modernos para inputs */
		.modern-input, .modern-textarea, .modern-select {
			border: 2px solid #e0e0e0 !important;
			border-radius: 8px !important;
			padding: 12px 16px !important;
			font-size: 15px !important;
			line-height: 1.5 !important;
			transition: all 0.3s !important;
			background: #f8f9fa !important;
			height: auto !important;
			min-height: 45px !important;
		}

		.modern-input:focus, .modern-textarea:focus {
			border-color: #667eea !important;
			background: white !important;
			box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
			outline: none !important;
		}

		.modern-textarea {
			min-height: 100px !important;
		}
		
		/* Ajustar Select2 para que sea visible */
		.select2-container--default .select2-selection--single {
			height: 45px !important;
			padding: 8px 12px !important;
			border: 2px solid #e0e0e0 !important;
			border-radius: 8px !important;
			background: #f8f9fa !important;
			line-height: 1.5 !important;
		}

		.select2-container--default .select2-selection--single .select2-selection__rendered {
			color: #495057 !important;
			line-height: 27px !important;
			font-size: 15px !important;
			padding-left: 4px !important;
		}

		.select2-container--default .select2-selection--single .select2-selection__arrow {
			height: 43px !important;
			top: 1px !important;
			right: 4px !important;
		}

		.select2-container--default .select2-selection--single .select2-selection__placeholder {
			color: #6c757d !important;
			font-size: 15px !important;
		}

		/* Hover effect for tabs */
		.nav-tabs .nav-link:hover {
			color: rgba(255,255,255,0.9) !important;
			background: rgba(255,255,255,0.1);
		}

		.nav-tabs .nav-link.active {
			background: rgba(255,255,255,0.2) !important;
		}
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

	<div class="main-container tramite-add-compact">
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">
				<div class="page-header" style="margin-bottom: 30px;">
					<div class="row align-items-center">
						<div class="col-md-6 col-sm-12">
							<div class="title">
								<h4 style="color: #1b2850; font-weight: 700; font-size: 28px; margin-bottom: 8px;">
									<i class="icon-copy dw dw-add-file" style="color: #667eea; margin-right: 12px;"></i>
									Agregar Nuevo Trámite
								</h4>
								<p style="color: #6c757d; font-size: 14px; margin: 0;">Complete los datos del trámite</p>
							</div>
						</div>
						<div class="col-md-6 col-sm-12 text-right">
							<a href="/tramites/tramite" class="btn btn-secondary" style="border-radius: 8px; padding: 10px 20px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
								<i class="fa fa-arrow-left"></i> Volver
							</a>
						</div>
					</div>
				</div>

				<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; border: none;">
					<div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 20px 30px;">
						<h5 style="color: white; margin: 0; font-weight: 600; font-size: 18px;">
							<i class="fa fa-file-text-o" style="margin-right: 10px;"></i>
							Información del Trámite
						</h5>
					</div>
					<div class="card-body" style="padding: 30px;">

						<?php echo form_open('/deskapp/tramites/insert', ['class' => 'form-horizontal', 'id' => 'tramiteForm', 'method' => 'post']); ?>
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
														<div class="form-group" style="margin-bottom: 24px;">
															<label for="<?php echo $field_name; ?>" style="display: block; font-weight: 600; color: #1b2850; margin-bottom: 8px; font-size: 14px;">
																<?php echo $field_info['label']; ?>
																<?php if ($required): ?><span style="color: #e74c3c;">*</span><?php endif; ?>
															</label>
															<div style="position: relative;">
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
																	<input type="text" class="form-control datetime-picker" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $value; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?> style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 12px 16px; font-size: 14px; background: #f8f9fa;">
																<?php endif; ?>
																<div class="invalid-feedback" style="color: #e74c3c; font-size: 13px; margin-top: 6px;">
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
														<div class="form-group" style="margin-bottom: 24px;">
															<label for="<?php echo $field_name; ?>" style="display: block; font-weight: 600; color: #1b2850; margin-bottom: 8px; font-size: 14px;">
																<?php echo $field_info['label']; ?>
																<?php if ($required): ?><span style="color: #e74c3c;">*</span><?php endif; ?>
															</label>
															<div style="position: relative;">
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
																	<input type="text" class="form-control datetime-picker" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $value; ?>" <?php echo $readonly; ?> <?php echo $disabled; ?> style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 12px 16px; font-size: 14px; background: #f8f9fa;">
																<?php endif; ?>
																<div class="invalid-feedback" style="color: #e74c3c; font-size: 13px; margin-top: 6px;">
																	<?php echo \Config\Services::validation()->showError($field_name); ?>
																</div>
															</div>
														</div>
													<?php endif; ?>
												<?php endforeach; ?>
											</div>
										</div>

										<div class="text-center mt-4" id="boton_autorizar" style="padding: 30px 0; border-top: 2px solid #f0f0f0; margin-top: 20px;">
											<button type="submit" name="accion" value="quotation" class="btn btn-warning" style="background: linear-gradient(135deg, #f5af19 0%, #f12711 100%); border: none; border-radius: 8px; padding: 12px 32px; font-weight: 600; font-size: 15px; color: white; box-shadow: 0 4px 15px rgba(245, 175, 25, 0.4); transition: all 0.3s; margin: 0 8px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(245, 175, 25, 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(245, 175, 25, 0.4)';">
												<i class="fa fa-file-text-o"></i> Guardar Como Cotización
											</button>
											<a href="/tramites/tramite" class="btn btn-secondary ml-2" style="background: #6c757d; border: none; border-radius: 8px; padding: 12px 32px; font-weight: 600; font-size: 15px; color: white; box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3); transition: all 0.3s; margin: 0 8px; text-decoration: none; display: inline-block;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(108, 117, 125, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(108, 117, 125, 0.3)';">
												<i class="fa fa-times"></i> Cancelar
											</a>
											<button type="submit" name="accion" value="tramite" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 8px; padding: 12px 32px; font-weight: 600; font-size: 15px; color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: all 0.3s; margin: 0 8px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)';">
												<i class="fa fa-save"></i> Guardar Trámite
											</button>
										</div>

									<?php echo form_close(); ?>

									<script>
										var cliDirectoId = "<?php echo isset($fields['cli_directo']['value']) ? $fields['cli_directo']['value'] : ''; ?>";
										var ejecutivoId = "<?php echo isset($fields['cli_directo_ejecutivo_id']['value']) ? $fields['cli_directo_ejecutivo_id']['value'] : ''; ?>";
									</script>

					</div>
				</div>
			</div>
		</div>
	</div>	

<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
	<?php foreach($js_files as $file): ?>
        <script src="<?php echo $file; ?>"></script>
    <?php endforeach; ?>
    
	<script src="<?php echo base_url(); ?>/public/assets/src/plugins/jquery-steps/jquery.steps.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/scripts/my_scripts.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/scripts/forms_scripts_add.js?v=<?php echo time(); ?>"></script>
	<script>
		var wiz_step = 0;
		
		// Aplicar estilos modernos a los campos del formulario
		$(document).ready(function() {
			// Agregar clases modern a inputs y selects
			$('#tramiteForm input[type="text"]:not(.modern-input)').addClass('modern-input');
			$('#tramiteForm textarea:not(.modern-textarea)').addClass('modern-textarea');
			$('#tramiteForm select:not(.modern-select)').addClass('modern-select');
		});
	</script>
<?= $this->endSection() ?>

