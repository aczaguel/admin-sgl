<?php
$sglReadonlyRibbonStyle = 'position:relative;display:flex;align-items:center;gap:8px;padding:8px 56px 8px 10px;';
$sglReadonlyTextStyle = 'flex:1 1 auto;min-width:0;';
$sglReadonlyToggleStyle = 'position:absolute;top:50%;right:10px;left:auto;transform:translateY(-50%);width:28px;min-width:28px;max-width:28px;height:28px;min-height:28px;max-height:28px;padding:0;margin-left:0;display:inline-flex;align-items:center;justify-content:center;line-height:1;border-radius:999px;';
?>
<div class="sgl-step-center">
	<?php if (has_permission('tramite_detalle_quick_actions_historial_actividad_ver', $user_permissions ?? [], $user_roles ?? [])): ?>
		<div class="d-flex justify-content-end mb-3">
			<a href="<?= site_url('/deskapp/tramites/audit_timeline/' . (int) ($id ?? 0)) ?>" class="btn btn-info btn-sm sgl-btn-pill">
				<i class="fas fa-stream"></i> Ver Historial de Actividad
			</a>
		</div>
	<?php endif; ?>
	<?php if ($showSection('generales')): ?>
	<div class="sgl-step-form-ribbon <?= !empty($step1_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="1" style="<?= $sglReadonlyRibbonStyle ?>">
		<div class="sgl-icon"><i class="<?= !empty($step1_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
		<div class="sgl-text" style="<?= $sglReadonlyTextStyle ?>">Paso 1: Datos del tramite</div>
		<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso1" aria-expanded="false" aria-controls="collapsePaso1" style="<?= $sglReadonlyToggleStyle ?>">
			<i class="fas fa-chevron-down"></i>
		</button>
	</div>

	<div id="collapsePaso1" class="collapse">
	<div class="sgl-soft-panel">
		<p class="sgl-soft-panel-title">Tipos de tramite ligados</p>
		<div class="d-flex flex-wrap" style="gap:8px;">
			<?php if (!empty($servicios_asociados) && is_array($servicios_asociados)): ?>
				<?php foreach ($servicios_asociados as $srv): ?>
					<span class="badge badge-success badge-pill sgl-pill">✓ <?= esc($srv['label'] ?? '') ?></span>
				<?php endforeach; ?>
			<?php else: ?>
				<span class="badge badge-secondary badge-pill sgl-pill">Sin tipos ligados</span>
			<?php endif; ?>
		</div>
	</div>

	<div class="sgl-soft-panel mt-3">
		<p class="sgl-soft-panel-title">Datos del tramite</p>
		<div class="sgl-info-grid">
			<?php if (!empty($readonly_step1) && is_array($readonly_step1)): ?>
				<?php foreach ($readonly_step1 as $item): ?>
					<?php
						$label = $item['label'] ?? '';
						$val = $item['value'] ?? '';
						$display = ($val === null || $val === '') ? '--' : $val;
					?>
					<div class="sgl-info-item">
						<div class="sgl-info-label"><?= esc($label) ?></div>
						<div class="sgl-info-value"><?= esc($display) ?></div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
	</div>
	<?php endif; ?>

	<?php if ($showSection('asigna_gestor')): ?>
	<div class="sgl-step-form-ribbon mt-3 <?= !empty($step2_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="2" style="<?= $sglReadonlyRibbonStyle ?>">
		<div class="sgl-icon"><i class="<?= !empty($step2_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
		<div class="sgl-text" style="<?= $sglReadonlyTextStyle ?>">Paso 2: Gestor y Empresa</div>
		<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso2" aria-expanded="false" aria-controls="collapsePaso2" style="<?= $sglReadonlyToggleStyle ?>">
			<i class="fas fa-chevron-down"></i>
		</button>
	</div>

	<div id="collapsePaso2" class="collapse">
	<div class="sgl-soft-panel mt-3">
		<p class="sgl-soft-panel-title">Gestor y Empresa</p>
		<div class="sgl-info-grid">
			<?php if (!empty($readonly_step2) && is_array($readonly_step2)): ?>
				<?php foreach ($readonly_step2 as $item): ?>
					<?php
						$label = $item['label'] ?? '';
						$val = $item['value'] ?? '';
						$display = ($val === null || $val === '') ? '--' : $val;
					?>
					<div class="sgl-info-item">
						<div class="sgl-info-label"><?= esc($label) ?></div>
						<div class="sgl-info-value"><?= esc($display) ?></div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
	</div>
	<?php endif; ?>

	<?php if ($showSection('pago_derechos')): ?>
	<div class="sgl-step-form-ribbon mt-3 <?= !empty($step3_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="3" style="<?= $sglReadonlyRibbonStyle ?>">
		<div class="sgl-icon"><i class="<?= !empty($step3_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
		<div class="sgl-text" style="<?= $sglReadonlyTextStyle ?>">Paso 3: Pagos de Derechos</div>
		<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso3" aria-expanded="false" aria-controls="collapsePaso3" style="<?= $sglReadonlyToggleStyle ?>">
			<i class="fas fa-chevron-down"></i>
		</button>
	</div>

	<div id="collapsePaso3" class="collapse">
	<div class="sgl-soft-panel mt-3">
		<p class="sgl-soft-panel-title">Datos de pagos de derechos</p>
		<div class="sgl-info-grid">
			<?php if (!empty($readonly_step3) && is_array($readonly_step3)): ?>
				<?php foreach ($readonly_step3 as $item): ?>
					<?php
						$label = $item['label'] ?? '';
						$val = $item['value'] ?? '';
						$display = ($val === null || $val === '') ? '--' : $val;
					?>
					<div class="sgl-info-item">
						<div class="sgl-info-label"><?= esc($label) ?></div>
						<div class="sgl-info-value"><?= esc($display) ?></div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="sgl-soft-panel mt-3">
		<p class="sgl-soft-panel-title">Documentos de derechosssssss</p>
		<div class="gallery-preview" id="documentos-container-readonly">
			<?php if (!empty($pago_derechos_db) && is_array($pago_derechos_db)): ?>
				<?php foreach ($pago_derechos_db as $doc): ?>
					<?php
						$fileName = (string) ($doc['file'] ?? '');
						$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
						$isImage = in_array($fileExt, ['jpg','jpeg','png','gif','webp'], true);
						$fileUrl = base_url('/assets/uploads/pago_derechos/' . $id . '/' . $fileName);
					?>
						<div class="file-preview" data-file="<?= esc($fileName) ?>" style="position:relative;border:1px solid #ddd;border-radius:5px;padding:5px;background-color:#f9f9f9;display:inline-block;margin:4px;text-align:center;">
					<a href="<?= esc($fileUrl) ?>" target="_blank">
						<?php if ($isImage): ?>
							<img src="<?= esc($fileUrl) ?>" alt="<?= esc($fileName) ?>" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">
						<?php else: ?>
							<i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>
						<?php endif; ?>
					</a>
					<p style="font-size:10px;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
						<?= esc($fileName) ?>
					</p>
				</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="text-muted">Sin documentos registrados.</div>
			<?php endif; ?>
		</div>
	</div>
	</div>
	<?php endif; ?>

	<?php if ($showSection('pago_gestor') && !empty($can_section_pago_gestor)): ?>
		<?php $canEditPagoGestor = !empty($can_edit_pago_gestor); ?>
		<?php
			$hasTramiteRecibido = !empty($has_comprobante_tramite_recibido);
			$hasAcuseRecibo = !empty($has_comprobante_acuse_recibo);
			$canCobrar = $hasTramiteRecibido && $hasAcuseRecibo;
		?>
		<div class="sgl-step-form-ribbon mt-3" data-ribbon-step="4" data-has-tramite-recibido="<?= $hasTramiteRecibido ? '1' : '0' ?>" data-has-acuse-recibo="<?= $hasAcuseRecibo ? '1' : '0' ?>">
			<div class="sgl-icon"><i class="fas fa-credit-card"></i></div>
			<div>
				<div class="sgl-text">Paso 4: Pago a Gestor</div>
				<div class="sgl-status-row">
					<span id="chipTramiteRecibido" class="sgl-status-chip <?= $hasTramiteRecibido ? 'is-success' : 'is-muted' ?>">Tramite Entregado por Gestor</span>
					<span id="chipAcuseRecibo" class="sgl-status-chip <?= $hasAcuseRecibo ? 'is-success' : 'is-muted' ?>">Acuse de Recibo del Cliente</span>
					<?php if ($canCobrar): ?>
						<span id="chipPuedeCobrar" class="sgl-status-chip is-success">Ya se puede cobrar</span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-dropzone-grid">
			<div class="form-column">
				<div class="sgl-soft-panel">
					<p class="sgl-soft-panel-title">Datos de pago a gestor</p>
						<form id="pagoGestorFormCustom" method="post" action="<?= site_url('/deskapp/tramitesn/update_pago_gestor/' . $id) ?>">
					<?= csrf_field() ?>
					<div class="row">
						<?php
							$gastoGroup = ['impuesto_gestoria', 'gestoria_comision', 'costo_paqueteria', 'gestor_total_pago'];
							$primaryGroup = ['gestor_name', 'num_factura_gestor', 'deposito_gestor'];
							$statusGroup = ['pago_gestor_st_id', 'status_doctos_gestor', 'reembolso_status_id'];
							$renderField = static function ($name, $cfg, $canEditPagoGestor) {
								$type = $cfg['type'] ?? 'text';
								$label = $cfg['label'] ?? ucfirst((string) $name);
								$value = $cfg['value'] ?? '';
								$required = !empty($cfg['required']);
								$isReadonly = !empty($cfg['readonly']);
								$disabledAttr = (!$canEditPagoGestor || $isReadonly) ? 'disabled' : '';
								ob_start();
							?>
								<div class="col-lg-3 col-md-4 col-sm-6 mb-2">
									<label for="<?= esc($name) ?>">
										<?= esc($label) ?><?= $required ? ' *' : '' ?>
									</label>
									<?php if ($type === 'select'): ?>
										<select class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $required ? 'required' : '' ?> <?= $disabledAttr ?>>
											<option value="">Seleccione...</option>
											<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
												<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
													<option value="<?= esc($optValue, 'attr') ?>" <?= ($value ?? null) == $optValue ? 'selected' : '' ?>>
														<?= esc($optLabel) ?>
													</option>
												<?php endforeach; ?>
											<?php endif; ?>
										</select>
									<?php else: ?>
										<input type="<?= esc($type) ?>" class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($value ?? '', 'attr') ?>" <?= $required ? 'required' : '' ?> <?= $disabledAttr ?>>
									<?php endif; ?>
								</div>
							<?php
								return ob_get_clean();
							};
						?>
						<?php if (!empty($pago_gestor_campos) && is_array($pago_gestor_campos)): ?>
							<?php
								$costoHidden = $pago_gestor_campos['costo_tramite']['value'] ?? '';
								$saldoHidden = $pago_gestor_campos['col_a_favor']['value'] ?? '';
							?>
							<input type="hidden" id="costo_tramite" name="costo_tramite" value="<?= esc($costoHidden, 'attr') ?>">
							<input type="hidden" id="col_a_favor" name="col_a_favor" value="<?= esc($saldoHidden, 'attr') ?>">

							<?php foreach ($primaryGroup as $fieldName): ?>
								<?php if (!empty($pago_gestor_campos[$fieldName])): ?>
									<?= $renderField($fieldName, $pago_gestor_campos[$fieldName], $canEditPagoGestor) ?>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<div id="saldoPendienteInfo" class="sgl-saldo-info is-even">Sin saldo pendiente</div>

					<?php
						$servicios_costos = (!empty($servicios_asociados) && is_array($servicios_asociados)) ? $servicios_asociados : [];
						$servicios_costos_total = 0.0;
						foreach ($servicios_costos as $srv) {
							$val = (float) ($srv['costo_tramite'] ?? 0);
							$servicios_costos_total += $val;
						}
					?>
					<div class="sgl-cost-panel mt-3" id="gestorCostosPanel">
						<div class="sgl-cost-title">
							<i class="fas fa-receipt"></i> Costos de Tramites
							<span class="sgl-cost-status" id="costosSaveStatus">Guardado</span>
						</div>
						<div class="sgl-cost-list" id="gestor_costos_tipo_servicio">
							<?php if (empty($servicios_costos)): ?>
								<div class="text-muted">No hay tramites asociados.</div>
							<?php else: ?>
								<?php foreach ($servicios_costos as $srv): ?>
									<?php
										$asociadoId = (int) ($srv['asociado_id'] ?? 0);
										$label = (string) ($srv['label'] ?? '');
										$costoValue = (string) ($srv['costo_tramite'] ?? '0.00');
									?>
									<div class="sgl-cost-item">
										<div class="sgl-cost-name"><?= esc($label) ?></div>
										<input type="number" class="form-control form-control-sm text-end sgl-cost-input" data-cost-id="<?= $asociadoId ?>" value="<?= esc($costoValue, 'attr') ?>" <?= $canEditPagoGestor ? '' : 'disabled' ?>>
										<?php if ($canEditPagoGestor): ?>
											<button type="button" class="btn btn-success btn-sm sgl-btn-pill sgl-cost-save" data-save-id="<?= $asociadoId ?>" title="Guardar"><i class="fas fa-save"></i></button>
										<?php endif; ?>
										<span class="sgl-cost-icon" aria-hidden="true"></span>
										<span class="sgl-cost-row-status">Guardado</span>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
						<div class="sgl-cost-total">
							<div class="label">Total de Costos</div>
							<div class="value" id="costo_tramite_total">$<?= number_format($servicios_costos_total, 2, '.', '') ?></div>
						</div>
					</div>

					<div class="sgl-gasto-panel mt-3">
						<p class="panel-title">Gastos</p>
						<div class="row">
							<?php if (!empty($pago_gestor_campos['impuesto_gestoria'])): ?>
								<?= $renderField('impuesto_gestoria', $pago_gestor_campos['impuesto_gestoria'], $canEditPagoGestor) ?>
							<?php endif; ?>
							<?php if (!empty($pago_gestor_campos['gestoria_comision'])): ?>
								<?= $renderField('gestoria_comision', $pago_gestor_campos['gestoria_comision'], $canEditPagoGestor) ?>
							<?php endif; ?>
							<?php if (!empty($pago_gestor_campos['costo_paqueteria'])): ?>
								<?= $renderField('costo_paqueteria', $pago_gestor_campos['costo_paqueteria'], $canEditPagoGestor) ?>
							<?php endif; ?>
							<div class="col-lg-3 col-md-4 col-sm-6 mb-2">
								<label>Gasto Total</label>
								<div class="sgl-total-display">
									<span id="gasto_total_text">$<?= number_format((float) ($pago_gestor_campos['gestor_total_pago']['value'] ?? 0), 2, '.', '') ?></span>
								</div>
								<small id="gasto_total_breakdown" class="text-muted d-block mt-1"></small>
								<input type="hidden" id="gestor_total_pago" name="gestor_total_pago" value="<?= esc($pago_gestor_campos['gestor_total_pago']['value'] ?? 0, 'attr') ?>">
							</div>
						</div>
					</div>

					<div class="sgl-soft-panel mt-3">
						<p class="sgl-soft-panel-title">Estatus</p>
						<div class="row">
							<?php if (!empty($pago_gestor_campos) && is_array($pago_gestor_campos)): ?>
								<?php foreach ($statusGroup as $fieldName): ?>
									<?php if (!empty($pago_gestor_campos[$fieldName])): ?>
										<?= $renderField($fieldName, $pago_gestor_campos[$fieldName], $canEditPagoGestor) ?>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
					<div id="pagoGestorMessage" class="alert mt-2" style="display:none;"></div>
					<?php if ($canEditPagoGestor): ?>
						<button type="submit" class="btn btn-success btn-sm sgl-btn-pill" data-submit="pago-gestor">
							<i class="fas fa-save"></i> Guardar
						</button>
					<?php endif; ?>
				</form>
			</div>
		</div>

		<div class="dropzone-column">
			<div class="dropzone-sticky">
				<h5 class="dropzone-title">
					<i class="fas fa-cloud-upload-alt"></i> Documentos de Pago
					<?= perm_audit_tag('can_upload_dropzone_pago_gestor') ?>
				</h5>
				<?php if (!empty($can_upload_pago_gestor) && !empty($can_upload_dropzone_pago_gestor)): ?>
					<div class="form-group mb-2">
						<label for="pagoGestorComprobanteFinal" class="mb-1">Tipo de comprobante final</label>
						<select id="pagoGestorComprobanteFinal" class="form-control form-control-sm sgl-important-select">
							<option value="tramite_recibido">Tramite Entregado por Gestor</option>
							<option value="acuse_recibo_cliente">Acuse de Recibo del Cliente</option>
							<option value="otro" selected>Otro</option>
						</select>
						<small class="text-muted">Se guarda con cada archivo que subas.</small>
					</div>
					<div class="dropzone-container">
						<form class="dropzone dropzone-gestor dropzone-compact" id="miDropzoneGestor">
							<div class="dz-default dz-message">
								<button class="dz-button" type="button">
									<img src="/public/assets/src/images/upload.svg" class="dz-icon" alt="Subir Archivo">
									<p class="dz-text">Arrastra archivos aqui o haz clic</p>
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
						<h6>Para eliminar un archivo, solicitalo al administrador</h6>
					</div>
				<?php endif; ?>
				<div class="gallery-preview" id="gestor-container">
					<?php if (!empty($pago_gestor_db) && is_array($pago_gestor_db)): ?>
						<?php foreach ($pago_gestor_db as $doc): ?>
							<?php
								$fileName = (string) ($doc['file'] ?? '');
								$docType = (string) ($doc['comprobante_final'] ?? '');
								$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
								$isImage = in_array($fileExt, ['jpg','jpeg','png','gif','webp'], true);
								$fileUrl = base_url('/assets/uploads/pago_gestor/' . $id . '/' . $fileName);
							?>
							<div class="file-preview" data-file="<?= esc($fileName) ?>" style="border:1px solid #ddd;border-radius:5px;padding:5px;background-color:#f9f9f9;display:inline-block;margin:4px;text-align:center;">
								<a href="<?= esc($fileUrl) ?>" target="_blank">
									<?php if ($isImage): ?>
										<img src="<?= esc($fileUrl) ?>" alt="<?= esc($fileName) ?>" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">
									<?php else: ?>
										<i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>
									<?php endif; ?>
								</a>
								<p style="font-size:10px;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
									<?= esc($fileName) ?>
								</p>
								<?php if ($docType !== ''): ?>
									<?php
										$docTypeLabel = $docType;
										if ($docType === 'tramite_recibido') {
											$docTypeLabel = 'Tramite Entregado por Gestor';
										} elseif ($docType === 'acuse_recibo_cliente') {
											$docTypeLabel = 'Acuse de Recibo del Cliente';
										} elseif ($docType === 'otro') {
											$docTypeLabel = 'Otro';
										}
									?>
									<span class="badge badge-info"><?= esc($docTypeLabel) ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					<?php else: ?>
						<div class="text-muted">Sin documentos registrados.</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

</div>
