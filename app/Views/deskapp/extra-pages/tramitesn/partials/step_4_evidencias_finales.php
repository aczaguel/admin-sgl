<?php if (!empty($can_section_pago_gestor)): ?>
	<?php
		$hasTramiteRecibido = !empty($has_comprobante_tramite_recibido);
		$hasAcuseRecibo = !empty($has_comprobante_acuse_recibo);
		$evidenciasCompletas = $hasTramiteRecibido && $hasAcuseRecibo;
		$canNavigatePagoGestor = !empty($can_navigate_pago_gestor);
		$canNavigateCobroCliente = !empty($can_navigate_cobro_cliente);
	?>
	<div class="sgl-step-center mt-3">
		<?php if (has_permission('tramite_detalle_quick_actions_historial_actividad_ver', $user_permissions ?? [], $user_roles ?? [])): ?>
			<div class="d-flex justify-content-end mb-3">
				<a href="<?= site_url('/deskapp/tramites/audit_timeline/' . (int) ($id ?? 0)) ?>" class="btn btn-info btn-sm sgl-btn-pill">
					<i class="fas fa-stream"></i> Ver Historial de Actividad
				</a>
			</div>
		<?php endif; ?>
		<div class="sgl-step-form-ribbon" data-ribbon-step="4" data-has-tramite-recibido="<?= $hasTramiteRecibido ? '1' : '0' ?>" data-has-acuse-recibo="<?= $hasAcuseRecibo ? '1' : '0' ?>">
			<div class="sgl-icon"><i class="fas fa-cloud-upload-alt"></i></div>
			<div>
				<div class="sgl-text">Paso 4: Evidencias Finales</div>
				<div class="sgl-status-row">
					<span id="chipTramiteRecibido" class="sgl-status-chip <?= $hasTramiteRecibido ? 'is-success' : 'is-muted' ?>">Tramite Entregado por Gestor</span>
					<span id="chipAcuseRecibo" class="sgl-status-chip <?= $hasAcuseRecibo ? 'is-success' : 'is-muted' ?>">Acuse de Recibo del Cliente</span>
					<?php if ($evidenciasCompletas): ?>
						<span id="chipPuedeCobrar" class="sgl-status-chip is-success">Evidencias finales completas</span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="form-dropzone-grid mt-3">
			<div class="form-column">
				<div class="sgl-soft-panel">
					<p class="sgl-soft-panel-title">Evidencias finales del tramite</p>
					<p class="text-muted mb-3">Aqui se suben unicamente los comprobantes finales del cierre operativo: tramite entregado por gestor y acuse de recibo del cliente.</p>
					<div class="sgl-status-row" style="margin-bottom:12px;">
						<span class="sgl-status-chip <?= $hasTramiteRecibido ? 'is-success' : 'is-muted' ?>">Tramite Entregado por Gestor</span>
						<span class="sgl-status-chip <?= $hasAcuseRecibo ? 'is-success' : 'is-muted' ?>">Acuse de Recibo del Cliente</span>
					</div>

					<?php if (!empty($can_upload_pago_gestor) && !empty($can_upload_dropzone_evidencias_finales)): ?>
						<div class="form-group mb-2">
							<label for="pagoGestorComprobanteFinal" class="mb-1">Tipo de evidencia final</label>
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
										<p class="dz-subtext">Evidencias finales del tramite</p>
									</button>
								</div>
							</form>
						</div>
						<button id="btnSubirGestor" class="btnSubir" type="button">
							<i class="fas fa-cloud-upload-alt"></i> Subir Archivos
						</button>
						<div class="delete-notice">
							<i class="fas fa-info-circle"></i>
							<h6>Para eliminar un archivo, solicitalo al administrador</h6>
						</div>
					<?php endif; ?>

					<div class="gallery-preview" id="gestor-container">
						<?php if (!empty($pago_gestor_evidencias_db) && is_array($pago_gestor_evidencias_db)): ?>
							<?php foreach ($pago_gestor_evidencias_db as $doc): ?>
								<?php
									$fileName = (string) ($doc['file'] ?? '');
									$docType = (string) ($doc['comprobante_final'] ?? '');
									$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
									$isImage = in_array($fileExt, ['jpg','jpeg','png','gif','webp'], true);
									$fileUrl = base_url('/assets/uploads/pago_gestor/' . $id . '/' . $fileName);
									$docTypeLabel = $docType;
									if ($docType === 'tramite_recibido') {
										$docTypeLabel = 'Tramite Entregado por Gestor';
									} elseif ($docType === 'acuse_recibo_cliente') {
										$docTypeLabel = 'Acuse de Recibo del Cliente';
									} elseif ($docType === 'otro') {
										$docTypeLabel = 'Otro';
									}
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
									<?php if ($docTypeLabel !== ''): ?>
										<span class="badge badge-info"><?= esc($docTypeLabel) ?></span>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						<?php else: ?>
							<div class="text-muted">Sin evidencias registradas.</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="dropzone-column">
				<div class="dropzone-sticky">
					<div class="sgl-soft-panel">
						<p class="sgl-soft-panel-title">Siguiente paso</p>
						<p class="text-muted">Cuando termines de subir las evidencias finales, puedes abrir directamente Pago al Gestor o Cobro al Cliente segun tus permisos.</p>
						<div class="d-flex flex-column" style="gap:8px;">
							<?php if ($canNavigatePagoGestor): ?>
								<a href="<?= site_url('/deskapp/tramitesn/ver_seccion_pago_gestor/' . (int) ($id ?? 0)) ?>" class="btn btn-success btn-sm sgl-btn-pill">
									<i class="fas fa-arrow-right"></i> Ir a Pago al Gestor
								</a>
							<?php endif; ?>
							<?php if ($canNavigateCobroCliente): ?>
								<a href="<?= site_url('/deskapp/tramitesn/ver_seccion_cobro_cliente/' . (int) ($id ?? 0)) ?>" class="btn btn-outline-success btn-sm sgl-btn-pill">
									<i class="fas fa-arrow-right"></i> Ir a Cobro al Cliente
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>