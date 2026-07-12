<?php if (!empty($can_section_pago_gestor)): ?>
	<?php $canEditPagoGestor = !empty($can_edit_pago_gestor); ?>
	<?php
		$hasTramiteRecibido = !empty($has_comprobante_tramite_recibido);
		$hasAcuseRecibo = !empty($has_comprobante_acuse_recibo);
		$hasFacturaGestor = !empty($has_factura_gestor);
		$hasComprobantePago = !empty($has_comprobante_pago);
		$pagoCompleto = $hasFacturaGestor && $hasComprobantePago;
		$canViewCobroCliente = !empty($can_navigate_cobro_cliente);
	?>
	<div class="sgl-step-center mt-3">
		<?php if (has_permission('tramite_detalle_quick_actions_historial_actividad_ver', $user_permissions ?? [], $user_roles ?? [])): ?>
			<div class="d-flex justify-content-end mb-3">
				<a href="<?= site_url('/deskapp/tramites/audit_timeline/' . (int) ($id ?? 0)) ?>" class="btn btn-info btn-sm sgl-btn-pill">
					<i class="fas fa-stream"></i> Ver Historial de Actividad
				</a>
			</div>
		<?php endif; ?>
		<div class="sgl-step-form-ribbon is-complete" data-ribbon-step="4" data-has-tramite-recibido="<?= $hasTramiteRecibido ? '1' : '0' ?>" data-has-acuse-recibo="<?= $hasAcuseRecibo ? '1' : '0' ?>">
			<div class="sgl-icon"><i class="fas fa-cloud-upload-alt"></i></div>
			<div>
				<div class="sgl-text">Paso 4: Evidencias Finales</div>
				<div class="sgl-status-row">
					<span class="sgl-status-chip <?= $hasTramiteRecibido ? 'is-success' : 'is-muted' ?>">Tramite Entregado por Gestor</span>
					<span class="sgl-status-chip <?= $hasAcuseRecibo ? 'is-success' : 'is-muted' ?>">Acuse de Recibo del Cliente</span>
				</div>
			</div>
			<a href="<?= site_url('/deskapp/tramitesn/ver_seccion_evidencias_finales/' . (int) ($id ?? 0)) ?>" class="btn btn-sm btn-outline-secondary" style="margin-left:auto;">
				Ver paso 4
			</a>
		</div>

		<div class="sgl-step-form-ribbon" data-ribbon-step="5" data-has-factura-gestor="<?= $hasFacturaGestor ? '1' : '0' ?>" data-has-comprobante-pago="<?= $hasComprobantePago ? '1' : '0' ?>">
			<div class="sgl-icon"><i class="fas fa-credit-card"></i></div>
			<div>
				<div class="sgl-text">Paso 5: Pago a Gestor</div>
				<div class="sgl-status-row">
					<span id="chipFacturaGestor" class="sgl-status-chip <?= $hasFacturaGestor ? 'is-success' : 'is-muted' ?>">Factura del Gestor</span>
					<span id="chipComprobantePago" class="sgl-status-chip <?= $hasComprobantePago ? 'is-success' : 'is-muted' ?>">Comprobante de Pago</span>
					<?php if ($pagoCompleto): ?>
						<span id="chipPagoGestorCompleto" class="sgl-status-chip is-success">Pago completado</span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="sgl-soft-panel mt-3">
			<div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:8px;">
				<p class="sgl-soft-panel-title" style="margin:0;">Datos de pago a gestor</p>
				<div class="d-flex flex-wrap" style="gap:8px;">
					<a href="<?= site_url('/deskapp/tramitesn/ver_seccion_evidencias_finales/' . (int) ($id ?? 0)) ?>" class="btn btn-outline-secondary btn-sm sgl-btn-pill">
						<i class="fas fa-arrow-left"></i> Ver Paso 4
					</a>
					<?php if ($pagoCompleto && $canViewCobroCliente): ?>
						<a href="<?= site_url('/deskapp/tramitesn/ver_seccion_cobro_cliente/' . (int) ($id ?? 0)) ?>" class="btn btn-success btn-sm sgl-btn-pill">
							<i class="fas fa-arrow-right"></i> Ir a Cobro al Cliente
						</a>
					<?php endif; ?>
				</div>
			</div>

			<form id="pagoGestorFormCustom" method="post" action="<?= site_url('/deskapp/tramitesn/update_pago_gestor/' . $id) ?>">
			<?= csrf_field() ?>
			<div class="row mt-2">
				<?php
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

		<div class="sgl-soft-panel mt-3">
			<h5 class="dropzone-title">
				<i class="fas fa-cloud-upload-alt"></i> Dropbox Pago a Gestor
			</h5>
			<?php if (!empty($can_upload_pago_gestor) && !empty($can_upload_dropzone_pago_gestor_documentos)): ?>
				<div class="form-group mb-2">
					<label for="pagoGestorDocumentoTipo" class="mb-1">Tipo de documento de pago</label>
					<select id="pagoGestorDocumentoTipo" class="form-control form-control-sm sgl-important-select">
						<option value="factura_gestor">Factura del Gestor</option>
						<option value="comprobante_pago">Comprobante de Pago</option>
					</select>
					<small class="text-muted">Se guarda con cada archivo que subas.</small>
				</div>
				<div class="dropzone-container">
					<form class="dropzone dropzone-gestor-pago dropzone-compact" id="miDropzoneGestorPago">
						<div class="dz-default dz-message">
							<button class="dz-button" type="button">
								<img src="/public/assets/src/images/upload.svg" class="dz-icon" alt="Subir Archivo">
								<p class="dz-text">Arrastra archivos aqui o haz clic</p>
								<p class="dz-subtext">Factura del gestor y comprobante de pago</p>
							</button>
						</div>
					</form>
				</div>
				<button id="btnSubirGestorPago" class="btnSubir" type="button">
					<i class="fas fa-cloud-upload-alt"></i> Subir Archivos
				</button>
				<div class="delete-notice">
					<i class="fas fa-info-circle"></i>
					<h6>Para eliminar un archivo, solicitalo al administrador</h6>
				</div>
			<?php endif; ?>

			<div class="gallery-preview" id="gestor-pago-container">
				<?php if (!empty($pago_gestor_pago_db) && is_array($pago_gestor_pago_db)): ?>
					<?php foreach ($pago_gestor_pago_db as $doc): ?>
						<?php
							$fileName = (string) ($doc['file'] ?? '');
							$docType = (string) ($doc['comprobante_final'] ?? '');
							$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
							$isImage = in_array($fileExt, ['jpg','jpeg','png','gif','webp'], true);
							$fileUrl = file_url($fileName, 'pago_gestor', (int) $id);
							$docTypeLabel = $docType === 'factura_gestor' ? 'Factura del Gestor' : ($docType === 'comprobante_pago' ? 'Comprobante de Pago' : 'Otro');
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
							<span class="badge badge-info"><?= esc($docTypeLabel) ?></span>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="text-muted">Sin documentos de pago a gestor registrados.</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ($pagoCompleto && $canViewCobroCliente): ?>
			<div class="d-flex justify-content-end mt-3">
				<a href="<?= site_url('/deskapp/tramitesn/ver_seccion_cobro_cliente/' . (int) ($id ?? 0)) ?>" class="btn btn-success btn-sm sgl-btn-pill">
					<i class="fas fa-arrow-right"></i> Ir a Cobro al Cliente
				</a>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>