<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>
<?php $assets = base_url('/public/assets'); ?>
<?php if (!empty($css_files)) {
	foreach ($css_files as $file) { ?>
		<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
	<?php }
} ?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= $assets ?>/src/styles/dropzone.css">
<link rel="stylesheet" href="<?= $assets ?>/src/styles/wizard_modern.css?v=<?= time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
	.sgl-step-form-ribbon{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:#f8f9fa;border:1px solid #e9ecef;margin:0 0 10px 0}
	.sgl-step-form-ribbon .sgl-icon{width:22px;height:22px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-size:11px}
	.sgl-step-form-ribbon .sgl-text{font-size:11px;font-weight:700;color:#495057;line-height:1.2}
	.sgl-soft-panel{background:#fff;border:1px solid #e9ecef;border-radius:12px;padding:12px 12px 8px 12px}
	.sgl-soft-panel-title{font-size:.82rem;font-weight:700;color:#374151;margin:0 0 8px 0}
	.sgl-info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px}
	.sgl-info-item{background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;padding:8px 10px}
	.sgl-info-label{font-size:.64rem;letter-spacing:.04em;text-transform:uppercase;color:#6b7280;font-weight:800;margin-bottom:2px}
	.sgl-info-value{font-size:.78rem;color:#111827;font-weight:600;word-break:break-word}
	.sgl-pill{border-radius:999px}
	.sgl-btn-icon{width:28px;height:28px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;padding:0}
	.form-dropzone-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:14px;align-items:start}
	.form-dropzone-grid .form-column{min-width:0}
	.form-dropzone-grid .dropzone-column{min-width:0}
	.form-dropzone-grid .dropzone-sticky{position:sticky;top:10px}
	@media (max-width:1200px){.form-dropzone-grid{grid-template-columns:1fr}}
	.dropzone-title{font-size:.9rem;font-weight:800;color:#374151;margin:0 0 10px 0}
	.delete-notice{margin-top:8px;padding:8px 10px;border-radius:10px;background:#fff3cd;border:1px solid #ffe69c;color:#8a6d3b;font-size:.78rem;display:flex;align-items:center;gap:8px}
	.gallery-preview{display:flex;flex-wrap:wrap;gap:6px}
	.file-preview{border:1px solid #e5e7eb;border-radius:8px;padding:6px;background:#fff}
	.file-preview p{margin:4px 0 0 0}
	.file-preview .doc-badge{display:inline-flex;align-items:center;gap:4px;margin-top:4px;padding:2px 8px;border-radius:999px;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
	.file-preview .doc-badge.is-complete{background:#ecfdf5;border:1px solid #86efac;color:#166534}
	.file-preview .doc-badge.is-partial{background:#fff7ed;border:1px solid #fdba74;color:#9a3412}
	.file-preview .doc-badge.is-other{background:#f1f5f9;border:1px solid #e2e8f0;color:#64748b}
	.tramite-header-modern.sgl-header-compact{padding:12px 14px;border-radius:14px}
	.tramite-header-modern.sgl-header-compact .folio-badge,
	.tramite-header-modern.sgl-header-compact .status-badge{font-size:.78rem;padding:4px 10px;border-radius:999px}
	.tramite-header-modern.sgl-header-compact .timeline-info{gap:8px}
	.tramite-header-modern.sgl-header-compact .timeline-item{padding:8px 10px;border-radius:10px;min-height:54px}
	.tramite-header-modern.sgl-header-compact .timeline-icon{width:26px;height:26px;font-size:12px}
	.tramite-header-modern.sgl-header-compact .timeline-content h6{font-size:.62rem;letter-spacing:.04em;text-transform:uppercase;margin-bottom:4px}
	.tramite-header-modern.sgl-header-compact .timeline-content p{font-size:.78rem;margin-bottom:0}
	.tramite-header-modern.sgl-header-compact .badges-wrap .badge{font-size:.62rem;padding:.2rem .45rem}
	.sgl-money-group{border:1px solid #e2e8f0;border-radius:12px;padding:12px;background:#f8fafc;margin-bottom:12px}
	.sgl-money-title{font-size:.78rem;font-weight:800;color:#1f2937;margin-bottom:8px;display:flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:.04em}
	.sgl-money-group .mb-3.row{margin-bottom:.6rem !important}
	.sgl-status-row{display:flex;flex-wrap:wrap;gap:6px}
	.sgl-status-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:.7rem;font-weight:800;background:#ecfdf5;border:1px solid #86efac;color:#166534}
	.sgl-status-chip.is-muted{background:#f1f5f9;border-color:#e2e8f0;color:#64748b}
	.sgl-swal-confirm{border-radius:16px;padding:10px 0}
	.sgl-swal-title{font-size:1.05rem;font-weight:800;color:#0f172a}
	.sgl-swal-confirm-btn{border-radius:999px !important;padding:8px 16px !important;font-weight:700}
	.sgl-swal-cancel-btn{border-radius:999px !important;padding:8px 16px !important;font-weight:700}
</style>

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
	// El paso 6 no debe depender de `editar_tramite`; usa permisos propios de cierre.
	$isReadOnlyMode = empty($can_edit_final_form) && empty($can_upload_final_docs);
	$detailRoles = $user_roles ?? ($session->get('user_roles') ?? []);
	$detailPerms = $user_permissions ?? ($session->get('user_permissions') ?? []);
	$canSectionPagoGestor = !empty($can_section_pago_gestor) || has_permission('section_pago_gestor', $detailPerms, $detailRoles);
	$canSectionFinalCostos = !empty($can_section_final_costos) || has_permission('section_final_costos', $detailPerms, $detailRoles);
	$canQuickDocumentos = has_permission('quick_action_documentos', $detailPerms, $detailRoles);
	$canQuickBitacora = has_permission('quick_action_bitacora', $detailPerms, $detailRoles);
	$canQuickPagosDerecho = has_permission('quick_action_pagos_derecho', $detailPerms, $detailRoles);
	$canQuickPagoGestor = has_permission('quick_action_pago_gestor', $detailPerms, $detailRoles);
	$canQuickEvidenciasFinales = has_permission('quick_action_evidencias_finales', $detailPerms, $detailRoles);
	$canQuickCobrosCliente = has_permission('quick_action_cobros_cliente', $detailPerms, $detailRoles);
	$canListCobroCliente = has_permission('list_cobro_cliente', $detailPerms, $detailRoles);
	$canSeePagoGestorBtn = $canQuickPagoGestor && $canSectionPagoGestor;
	$canSeeEvidenciasFinalesBtn = $canQuickEvidenciasFinales && $canSectionFinalCostos;
	$canSeeCobroClienteBtn = $canSectionFinalCostos && ($canQuickCobrosCliente || $canListCobroCliente);
	$canSeeStatusQuickActions = !empty($tra_status_id) && in_array((int) $tra_status_id, [23, 27, 28, 20, 21], true);
	$canSeeAnyQuickAction = $canQuickDocumentos
		|| $canQuickBitacora
		|| $canQuickPagosDerecho
		|| ($canSeeStatusQuickActions && ($canSeePagoGestorBtn || $canSeeEvidenciasFinalesBtn || $canSeeCobroClienteBtn));
?>

<div class="main-container <?= $isReadOnlyMode ? 'sgl-readonly-mode' : '' ?>">
	<div class="pd-20 card-box mb-30 sgl-page-tight">
		<?php $headerContrato = $contrato ?? ($final_campos['contrato']['value'] ?? null); ?>
		<div class="tramite-header-modern sgl-header-compact">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<div class="d-flex align-items-center" style="gap:8px;flex-wrap:wrap;">
					<div class="status-badge status-id">
						<i class="fas fa-hashtag"></i>
						ID: <?= isset($id) && $id !== '' ? esc((string) $id) : '--' ?>
					</div>
					<div class="folio-badge">
						<i class="fas fa-file-alt"></i>
						Folio: <?= isset($folio) ? esc($folio) : '--' ?>
					</div>
					<div class="folio-badge">
						<i class="fas fa-file-signature"></i>
						Contrato: <?= ($headerContrato !== null && $headerContrato !== '') ? esc($headerContrato) : '--' ?>
					</div>
				</div>
				<div class="status-badge">
					<i class="fas fa-circle"></i>
					<?= isset($tra_status) ? esc($tra_status) : '--' ?>
				</div>
			</div>
			<div class="timeline-info">
				<div class="timeline-item">
					<div class="timeline-icon"><i class="fas fa-calendar-plus"></i></div>
					<div class="timeline-content">
						<h6>Fecha Creacion</h6>
						<p><?= isset($created_at) ? date('d/m/Y H:i', strtotime($created_at)) : '--' ?></p>
					</div>
				</div>
				<div class="timeline-item">
					<div class="timeline-icon"><i class="fas fa-layer-group"></i></div>
					<div class="timeline-content">
						<h6>Tipos Ligados</h6>
						<div class="badges-wrap">
							<?php if (!empty($servicios_asociados) && is_array($servicios_asociados)): ?>
								<?php foreach (array_slice($servicios_asociados, 0, 3) as $srv): ?>
									<span class="badge badge-secondary badge-pill sgl-pill"><?= esc($srv['label'] ?? '') ?></span>
								<?php endforeach; ?>
							<?php else: ?>
								<span class="badge badge-light">Sin tipos</span>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="timeline-item">
					<div class="timeline-icon"><i class="fas fa-user-tie"></i></div>
					<div class="timeline-content">
						<h6>Gestor</h6>
						<p><?= isset($gestor) ? esc($gestor) : '--' ?></p>
					</div>
				</div>
				<div class="timeline-item">
					<div class="timeline-icon"><i class="fas fa-building"></i></div>
					<div class="timeline-content">
						<h6>Empresa Gestora</h6>
						<p><?= isset($empresa_gestora) ? esc($empresa_gestora) : '--' ?></p>
					</div>
				</div>
				<div class="timeline-item">
					<div class="timeline-icon"><i class="fas fa-user"></i></div>
					<div class="timeline-content">
						<h6>Cliente</h6>
						<p><?= isset($cliente) ? esc($cliente) : '--' ?></p>
					</div>
				</div>
			</div>
		</div>

		<?php if (has_permission('important_concluir_tramite', $session->get('user_permissions'), $session->get('user_roles'))): ?>
			<?php if ((int) ($tra_status_id ?? 0) === 28): ?>
				<div class="header-actions" style="margin-top:10px;display:flex;flex-wrap:wrap;gap:8px;">
					<button type="button" class="btn btn-sm btn-success sgl-btn-pill" onclick="concluirTramite(<?= (int) $id ?>, 20)">
						<i class="fas fa-check-circle"></i> Concluir Tramite
					</button>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if (!empty($canSeeAnyQuickAction)): ?>
			<div class="quick-actions-ribbon" style="margin-top:12px;">
				<div class="ribbon-title">
					<i class="fas fa-bolt"></i>
					<span>Detalle rapido</span>
				</div>
				<div class="ribbon-buttons">
					<?php if ($canQuickDocumentos): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-documentos">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
								<i class="fas fa-folder-open"></i>
							</div>
							<span class="ribbon-label">Documentos</span>
							<?= perm_audit_tag('quick_action_documentos', $session) ?>
						</button>
					<?php endif; ?>

					<?php if ($canQuickBitacora): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-bitacora">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
								<i class="fas fa-history"></i>
							</div>
							<span class="ribbon-label">Bitacora</span>
							<?= perm_audit_tag('quick_action_bitacora', $session) ?>
						</button>
					<?php endif; ?>

					<?php if ($canQuickPagosDerecho): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pagos-derecho">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
								<i class="fas fa-receipt"></i>
							</div>
							<span class="ribbon-label">Pagos Derecho</span>
							<?= perm_audit_tag('quick_action_pagos_derecho', $session) ?>
						</button>
					<?php endif; ?>

					<?php if ($canSeePagoGestorBtn): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pago-gestor">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
								<i class="fas fa-hand-holding-usd"></i>
							</div>
							<span class="ribbon-label">Pago Gestor</span>
							<?= perm_audit_tag('quick_action_pago_gestor', $session) ?>
						</button>
					<?php endif; ?>

					<?php if ($canSeeEvidenciasFinalesBtn): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-evidencias-finales">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
								<i class="fas fa-check-double"></i>
							</div>
							<span class="ribbon-label">Evidencias Finales</span>
							<?= perm_audit_tag('quick_action_evidencias_finales', $session) ?>
						</button>
					<?php endif; ?>

					<?php if ($canSeeCobroClienteBtn): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-cobro-cliente">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
								<i class="fas fa-money-check-alt"></i>
							</div>
							<span class="ribbon-label">Cobros Cliente</span>
							<?= perm_audit_tag('quick_action_cobros_cliente', $session) ?>
							<?= perm_audit_tag('list_cobro_cliente', $session) ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="sgl-step-center mt-3">
			<div class="sgl-step-form-ribbon <?= !empty($step1_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="1">
				<div class="sgl-icon"><i class="<?= !empty($step1_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
				<div class="sgl-text">Paso 1: Datos del tramite</div>
				<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso1" aria-expanded="false" aria-controls="collapsePaso1">
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

			<div class="sgl-step-form-ribbon mt-3 <?= !empty($step2_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="2">
				<div class="sgl-icon"><i class="<?= !empty($step2_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
				<div class="sgl-text">Paso 2: Gestor y Empresa</div>
				<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso2" aria-expanded="false" aria-controls="collapsePaso2">
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

			<div class="sgl-step-form-ribbon mt-3 <?= !empty($step3_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="3">
				<div class="sgl-icon"><i class="<?= !empty($step3_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
				<div class="sgl-text">Paso 3: Pagos de Derechos</div>
				<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso3" aria-expanded="false" aria-controls="collapsePaso3">
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
					<p class="sgl-soft-panel-title">Documentos de derechos</p>
					<div class="gallery-preview" id="documentos-container-readonly">
						<?php if (!empty($pago_derechos_db) && is_array($pago_derechos_db)): ?>
							<?php foreach ($pago_derechos_db as $doc): ?>
								<?php
									$fileName = (string) ($doc['file'] ?? '');
									$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
									$isImage = in_array($fileExt, ['jpg','jpeg','png','gif','webp'], true);
									$fileUrl = base_url('/assets/uploads/pago_derechos/' . $id . '/' . $fileName);
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
								</div>
							<?php endforeach; ?>
						<?php else: ?>
							<div class="text-muted">Sin documentos registrados.</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php if (!empty($can_section_pago_gestor)): ?>
				<?php
					$hasTramiteRecibido = !empty($has_comprobante_tramite_recibido);
					$hasAcuseRecibo = !empty($has_comprobante_acuse_recibo);
					$hasFacturaGestor = !empty($has_factura_gestor);
					$hasComprobantePago = !empty($has_comprobante_pago);
					$pagoCompleto = $hasFacturaGestor && $hasComprobantePago;
				?>
				<div class="sgl-step-form-ribbon mt-3" data-ribbon-step="4" data-has-tramite-recibido="<?= $hasTramiteRecibido ? '1' : '0' ?>" data-has-acuse-recibo="<?= $hasAcuseRecibo ? '1' : '0' ?>">
					<div class="sgl-icon"><i class="fas fa-cloud-upload-alt"></i></div>
					<div class="sgl-text">Paso 4: Evidencias Finales</div>
					<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso4" aria-expanded="false" aria-controls="collapsePaso4">
						<i class="fas fa-chevron-down"></i>
					</button>
				</div>

				<div id="collapsePaso4" class="collapse">
					<div class="sgl-soft-panel mt-3">
						<p class="sgl-soft-panel-title">Evidencias finales del tramite</p>
						<div class="sgl-status-row" style="margin-top:8px;">
							<span class="sgl-status-chip <?= $hasTramiteRecibido ? '' : 'is-muted' ?>">Tramite Entregado por Gestor</span>
							<span class="sgl-status-chip <?= $hasAcuseRecibo ? '' : 'is-muted' ?>">Acuse de Recibo del Cliente</span>
							<?php if ($hasTramiteRecibido && $hasAcuseRecibo): ?>
								<span class="sgl-status-chip">Evidencias finales completas</span>
							<?php endif; ?>
						</div>
					</div>

					<div class="sgl-soft-panel mt-3">
						<p class="sgl-soft-panel-title">Documentos de evidencias finales</p>
						<div class="gallery-preview" id="gestor-container-readonly">
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
								<div class="text-muted">Sin documentos registrados.</div>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="sgl-step-form-ribbon mt-3" data-ribbon-step="5" data-has-tramite-recibido="<?= $hasTramiteRecibido ? '1' : '0' ?>" data-has-acuse-recibo="<?= $hasAcuseRecibo ? '1' : '0' ?>">
					<div class="sgl-icon"><i class="fas fa-credit-card"></i></div>
					<div class="sgl-text">Paso 5: Pago a Gestor</div>
					<button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#collapsePaso5" aria-expanded="false" aria-controls="collapsePaso5">
						<i class="fas fa-chevron-down"></i>
					</button>
				</div>

				<div id="collapsePaso5" class="collapse">
					<div class="sgl-soft-panel mt-3">
						<p class="sgl-soft-panel-title">Datos de pago a gestor</p>
						<div class="sgl-info-grid">
							<?php if (!empty($pago_gestor_campos) && is_array($pago_gestor_campos)): ?>
								<?php foreach ($pago_gestor_campos as $name => $cfg): ?>
									<?php
										if (!is_array($cfg)) {
											continue;
										}
										$type = $cfg['type'] ?? 'text';
										if ($type === 'hidden') {
											continue;
										}
										$label = $cfg['label'] ?? ucfirst((string) $name);
										$val = $cfg['value'] ?? '';
										$display = ($val === null || $val === '') ? '--' : $val;
									?>
									<div class="sgl-info-item">
										<div class="sgl-info-label"><?= esc($label) ?></div>
										<div class="sgl-info-value"><?= esc($display) ?></div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
						<div class="sgl-status-row" style="margin-top:8px;">
							<span class="sgl-status-chip <?= $hasFacturaGestor ? '' : 'is-muted' ?>">Factura del Gestor</span>
							<span class="sgl-status-chip <?= $hasComprobantePago ? '' : 'is-muted' ?>">Comprobante de Pago</span>
							<?php if ($pagoCompleto): ?>
								<span class="sgl-status-chip">Pago completado</span>
							<?php endif; ?>
						</div>
					</div>

					<div class="sgl-soft-panel mt-3">
						<p class="sgl-soft-panel-title">Documentos de pago a gestor</p>
						<div class="gallery-preview" id="gestor-pago-container-readonly">
							<?php if (!empty($pago_gestor_pago_db) && is_array($pago_gestor_pago_db)): ?>
								<?php foreach ($pago_gestor_pago_db as $doc): ?>
									<?php
										$fileName = (string) ($doc['file'] ?? '');
										$docType = (string) ($doc['comprobante_final'] ?? '');
										$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
										$isImage = in_array($fileExt, ['jpg','jpeg','png','gif','webp'], true);
										$fileUrl = base_url('/assets/uploads/pago_gestor/' . $id . '/' . $fileName);
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
				</div>
			<?php endif; ?>
		</div>

		<div class="sgl-step-center mt-3">
			<div class="sgl-soft-panel">
				<div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:8px;">
					<h5 class="sgl-soft-panel-title" style="margin:0;"><i class="fas fa-receipt"></i> Paso 6: Cobro a Cliente</h5>
					<div class="d-flex flex-wrap align-items-center" style="gap:8px;">
						<div id="cobro-status-chips" class="sgl-status-row"></div>
						<a href="<?= site_url('/deskapp/tramitesn/ver_seccion_evidencias_finales/' . (int) ($id ?? 0)) ?>" class="btn btn-sm btn-outline-secondary sgl-btn-pill">
							<i class="fas fa-arrow-left"></i> Ver paso 4
						</a>
					</div>
				</div>
				<div class="form-dropzone-grid">
					<div class="form-column">
						<div class="sgl-step-form-ribbon" data-form-id="finalForm" aria-live="polite">
							<div class="sgl-icon"><i class="fas fa-check"></i></div>
							<div class="sgl-text">Datos completos</div>
						</div>
						<?php
							$prefix_form = 'final';
							$form_action = '/deskapp/tramitesn/update_final_save/' . $id;
							$form_id = 'finalForm';
							$cancel_url = '/deskapp/tramitesn/cobro_cliente';
							$submit_permission = 'editar_final';
							$field_values = $final_campos ?? [];
							echo render_full_form($prefix_form, $form_action, $form_id, $cancel_url, $submit_permission, $field_values, $session, $tra_status_id, $reembolso_status_id, $cobro_status_id, 5);
						?>
					</div>

					<div class="dropzone-column">
						<div class="dropzone-sticky">
							<h5 class="dropzone-title">
								<i class="fas fa-cloud-upload-alt"></i> Documentos
								<?= perm_audit_tag('can_upload_dropzone_cobro_cliente', $session) ?>
							</h5>
							<?php if (!$isReadOnlyMode && !empty($can_upload_final_docs) && !empty($can_upload_dropzone_cobro_cliente)): ?>
								<div class="form-group mb-2">
									<label for="cobro_correcto" class="mb-1" style="font-size:.78rem;font-weight:800;color:#374151;">Cobro correcto</label>
									<select id="cobro_correcto" class="form-control form-control-sm">
										<option value="parcial">Parcial</option>
										<option value="completo">Completo</option>
										<option value="otro" selected>Otro</option>
									</select>
								</div>
								<div class="dropzone-container">
									<form class="dropzone dropzone-cliente dropzone-compact" id="miDropzoneCliente">
										<div class="dz-default dz-message">
											<button class="dz-button" type="button">
												<img src="/public/assets/src/images/upload.svg" class="dz-icon" alt="Subir Archivo">
												<p class="dz-text">Arrastra archivos aqui o haz clic</p>
												<p class="dz-subtext">Documentos de cobro al cliente</p>
											</button>
										</div>
									</form>
								</div>
								<button id="btnSubirCliente" class="btnSubir">
									<i class="fas fa-cloud-upload-alt"></i> Subir Archivos
								</button>
								<div class="delete-notice">
									<i class="fas fa-info-circle"></i>
									<h6>Para eliminar un archivo, solicitalo al administrador</h6>
								</div>
								<div class="gallery-preview" id="cliente-container"></div>
							<?php else: ?>
								<div class="gallery-preview" id="cliente-container"></div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if ($canQuickDocumentos): ?>
	<div class="modal fade" id="modal-documentos" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
					<h4 class="modal-title"><i class="fas fa-folder-open"></i> Documentos</h4>
					<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
				</div>
				<div class="modal-body"><div class="pd-20"><?= !empty($output_docs) ? $output_docs : '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay documentos disponibles</div>' ?></div></div>
				<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php if ($canQuickBitacora): ?>
	<div class="modal fade" id="modal-bitacora" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
					<h4 class="modal-title"><i class="fas fa-history"></i> Bitacora</h4>
					<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
				</div>
				<div class="modal-body"><div class="pd-20"><?= !empty($output_bitacora) ? $output_bitacora : '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay bitacora disponible</div>' ?></div></div>
				<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php if ($canQuickPagosDerecho): ?>
	<div class="modal fade" id="modal-pagos-derecho" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #0f172a;">
					<h4 class="modal-title"><i class="fas fa-receipt"></i> Pagos Derecho</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
				</div>
				<div class="modal-body"><div class="pd-20"><?= !empty($output_derechos) ? $output_derechos : '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay pagos de derecho disponibles</div>' ?></div></div>
				<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php if ($canSeeStatusQuickActions && $canSeePagoGestorBtn): ?>
	<div class="modal fade" id="modal-pago-gestor" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #0f172a;">
					<h4 class="modal-title"><i class="fas fa-hand-holding-usd"></i> Pago Gestor</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
				</div>
				<div class="modal-body"><div class="pd-20"><?= !empty($output_pago_gestor) ? $output_pago_gestor : '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay registros de pago a gestor</div>' ?></div></div>
				<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php if ($canSeeStatusQuickActions && $canSeeCobroClienteBtn): ?>
	<div class="modal fade" id="modal-cobro-cliente" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
					<h4 class="modal-title"><i class="fas fa-money-check-alt"></i> Cobros al Cliente</h4>
					<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
				</div>
				<div class="modal-body"><div class="pd-20"><?= !empty($output_cobro_cliente) ? $output_cobro_cliente : '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay registros de cobros al cliente</div>' ?></div></div>
				<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php if ($canSeeStatusQuickActions && $canSeeEvidenciasFinalesBtn): ?>
	<div class="modal fade" id="modal-evidencias-finales" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
					<h4 class="modal-title"><i class="fas fa-check-double"></i> Evidencias Finales</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
				</div>
				<div class="modal-body"><div class="pd-20"><?= !empty($outputevidencias_finales) ? $outputevidencias_finales : '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay evidencias finales disponibles</div>' ?></div></div>
				<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
<script>
	const tramite_id = <?= (int) ($id ?? 0) ?>;
</script>
<link rel="stylesheet" href="<?php echo base_url(); ?>/public/assets/src/plugins/sweetalert2/sweetalert2.css">
<script src="<?php echo base_url(); ?>/public/assets/src/plugins/sweetalert2/sweetalert2.all.js"></script>
<?php
	if (!empty($js_files)) {
		foreach ($js_files as $file) { ?>
			<script src="<?php echo $file . '?v=' . time(); ?>"></script>
		<?php }
	}
?>
<script src="<?= $assets ?>/src/scripts/dropzone.js"></script>
<script src="<?= $assets ?>/src/scripts/tramitesn_cobro_cliente.js?v=<?= time(); ?>"></script>
<?= $this->endSection() ?>
