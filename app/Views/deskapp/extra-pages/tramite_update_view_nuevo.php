<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>

<?php $assets = base_url('/public/assets'); ?>
<?php if (!empty($css_files)) {
	foreach ($css_files as $file) { ?>
		<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
	<?php }
} ?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css">
<link rel="stylesheet" href="<?= $assets ?>/src/plugins/sweetalert2/sweetalert2.css">
<link rel="stylesheet" href="<?= $assets ?>/src/styles/dropzone.css">
<link rel="stylesheet" href="<?= $assets ?>/src/styles/wizard_modern.css?v=<?= time(); ?>">
<link rel="stylesheet" href="<?= $assets ?>/src/styles/jquery.steps.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
	.sgl-step-form-ribbon{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:#f8f9fa;border:1px solid #e9ecef;margin:0 0 10px 0;transition:background .2s,border-color .2s}
	.sgl-step-form-ribbon .sgl-icon{width:22px;height:22px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-size:11px}
	.sgl-step-form-ribbon .sgl-text{font-size:11px;font-weight:700;color:#495057;line-height:1.2}
	.sgl-step-form-ribbon .btn{padding:.2rem .45rem;font-size:.7rem;border-radius:6px}
	.sgl-soft-panel{background:#fff;border:1px solid #e9ecef;border-radius:12px;padding:12px 12px 8px 12px}
	.sgl-soft-panel-title{font-size:.82rem;font-weight:700;color:#374151;margin:0 0 8px 0}
	.wizard-section{padding:6px 0}
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

	.sgl-readonly-mode .sgl-step-form-ribbon{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:#f8f9fa;border:1px solid #e9ecef;margin:0 0 10px 0;transition:background .2s,border-color .2s}
	.sgl-readonly-mode .sgl-step-form-ribbon .sgl-icon{width:22px;height:22px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-size:11px}
	.sgl-readonly-mode .sgl-step-form-ribbon .sgl-text{font-size:11px;font-weight:700;color:#495057;line-height:1.2}
	.sgl-readonly-mode .sgl-step-form-ribbon .btn{padding:.2rem .45rem;font-size:.7rem;border-radius:6px}
	.sgl-step-form-ribbon.is-complete{background:rgba(16,185,129,0.08);border-color:rgba(16,185,129,0.25)}
	.sgl-step-form-ribbon.is-complete .sgl-icon{background:linear-gradient(135deg,#10B981 0%,#06B6D4 100%);color:#fff}
	.sgl-step-form-ribbon.is-incomplete{background:rgba(245,158,11,0.10);border-color:rgba(245,158,11,0.25)}
	.sgl-step-form-ribbon.is-incomplete .sgl-icon{background:#F59E0B;color:#fff}
	.sgl-approval-wrap{padding:0 6px}


	.sgl-associated-row{display:flex;align-items:center;justify-content:space-between;gap:10px}
	.sgl-associated-row{flex-wrap:wrap;align-items:flex-start}
	.sgl-associated-row .actions{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-left:auto}
	.sgl-associated-row .tipo-label{display:block}
	.sgl-add-row{flex-wrap:wrap}
	.sgl-add-row .sgl-inline-status{flex:1 0 100%;margin-top:4px;font-size:.72rem}
	.sgl-inline-status{display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
	.sgl-pill{border-radius:999px}
	.sgl-step-center{max-width:100%;margin:0 auto}
	.sgl-readonly-mode .sgl-soft-panel{background:#fff;border:1px solid #e9ecef;border-radius:12px;padding:12px 12px 8px 12px}
	.sgl-readonly-mode .sgl-soft-panel-title{font-size:.82rem;font-weight:700;color:#374151;margin:0 0 8px 0}
	.sgl-readonly-mode .sgl-info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px}
	.sgl-readonly-mode .sgl-info-item{background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;padding:8px 10px}
	.sgl-readonly-mode .sgl-info-label{font-size:.64rem;letter-spacing:.04em;text-transform:uppercase;color:#6b7280;font-weight:800;margin-bottom:2px}
	.sgl-readonly-mode .sgl-info-value{font-size:.78rem;color:#111827;font-weight:600;word-break:break-word}
	.sgl-highlight-box{background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);border-radius:14px;padding:12px 12px 10px 12px}
	.sgl-linked-fields{background:rgba(14,165,233,0.06);border:1px solid rgba(14,165,233,0.22);border-radius:14px;padding:12px 12px 4px 12px;margin-bottom:10px}
	.sgl-linked-fields .sgl-linked-title{font-size:.8rem;font-weight:800;color:#374151;margin:0 0 8px 0}
	.sgl-btn-mini{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 12px;font-weight:800;font-size:.8rem;border:1px solid rgba(15,23,42,0.18);background:rgba(15,23,42,0.08);color:#202342}
		/* Tipos ligados: aún más compacto */
		.sgl-card-tight .card-body{padding:10px}
		.sgl-card-tight hr{margin:.5rem 0}
		.sgl-card-tight small{font-size:.72rem}
	.sgl-btn-mini:hover{background:rgba(15,23,42,0.12);color:#202342}
	.sgl-btn-mini:focus{box-shadow:none}
	.sgl-btn-mini i{font-size:.8rem}
	.tramite-header-modern .mini-action{color:#fff}
	.tramite-header-modern.sgl-header-compact{padding:12px 14px;border-radius:14px}
	.tramite-header-modern.sgl-header-compact .folio-badge,
	.tramite-header-modern.sgl-header-compact .status-badge{font-size:.78rem;padding:4px 10px;border-radius:999px}
	.tramite-header-modern.sgl-header-compact .timeline-info{gap:8px}
	.tramite-header-modern.sgl-header-compact .timeline-item{padding:8px 10px;border-radius:10px;min-height:54px}
	.tramite-header-modern.sgl-header-compact .timeline-icon{width:26px;height:26px;font-size:12px}
	.tramite-header-modern.sgl-header-compact .timeline-content h6{font-size:.62rem;letter-spacing:.04em;text-transform:uppercase;margin-bottom:4px}
	.tramite-header-modern.sgl-header-compact .timeline-content p{font-size:.78rem;margin-bottom:0}
	.tramite-header-modern.sgl-header-compact .badges-wrap .badge{font-size:.62rem;padding:.2rem .45rem}
	.tramite-header-modern.sgl-header-compact .header-actions .btn{font-size:.72rem;padding:.25rem .55rem}
	.tramite-header-modern.sgl-header-compact .timeline-item{padding:6px 8px;min-height:50px}
	.tramite-header-modern.sgl-header-compact .timeline-icon{width:24px;height:24px;font-size:11px}
	.tramite-header-modern.sgl-header-compact .timeline-content h6{font-size:.58rem}
	.tramite-header-modern.sgl-header-compact .timeline-content p{font-size:.74rem}
	.sgl-cost-panel{background:#fff;border:1px solid #e9ecef;border-radius:12px;padding:10px 12px}
	.sgl-cost-title{font-size:.78rem;font-weight:800;color:#374151;margin:0 0 8px 0;display:flex;align-items:center;gap:6px}
	.sgl-cost-list{display:flex;flex-direction:column;gap:8px}
	.sgl-cost-item{display:flex;align-items:center;gap:8px;padding:8px 10px;border:1px solid #e9ecef;border-radius:10px;background:#f8f9fa}
	.sgl-cost-name{font-size:.78rem;font-weight:700;color:#374151;flex:1}
	.sgl-cost-input{max-width:140px}
	.sgl-cost-save{white-space:nowrap}
	.sgl-cost-save .fa-save{margin:0}
	.sgl-cost-icon{display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;font-size:.7rem;color:#16a34a}
	.sgl-cost-total{display:flex;align-items:center;justify-content:space-between;margin-top:8px;padding:8px 10px;border-radius:10px;background:#eef2ff;border:1px solid #e0e7ff}
	.sgl-cost-total .label{font-size:.7rem;font-weight:800;color:#4c51bf;text-transform:uppercase;letter-spacing:.04em}
	.sgl-cost-total .value{font-size:.9rem;font-weight:800;color:#1e293b}
	.sgl-cost-status{font-size:.68rem;font-weight:700;color:#16a34a;display:none}
	.sgl-cost-row-status{display:none;font-size:.68rem;font-weight:700;color:#16a34a}
	.sgl-cost-item.is-saved{border-color:#34d399;background:#ecfdf5;box-shadow:0 6px 14px rgba(16,185,129,.18)}
	.sgl-cost-item.is-saved .sgl-cost-row-status{display:inline-flex}
	.sgl-cost-item.is-saved .sgl-cost-save{background:#16a34a;border-color:#16a34a}
	.sgl-cost-item.is-saved .sgl-cost-save i::before{content:"\f00c"}
	.sgl-cost-item.is-error{border-color:#f87171;background:#fef2f2;box-shadow:0 6px 14px rgba(220,38,38,.12)}
	.sgl-cost-item.is-error .sgl-cost-row-status{display:inline-flex;color:#dc2626}
	.sgl-cost-item.is-error .sgl-cost-save{background:#dc2626;border-color:#dc2626}
	.sgl-cost-item.is-error .sgl-cost-icon{color:#dc2626}
	.sgl-gasto-panel{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:10px 12px;margin-top:6px}
	.sgl-gasto-panel .panel-title{font-size:.78rem;font-weight:800;color:#374151;margin:0 0 8px 0}
	.sgl-total-display{display:flex;align-items:center;justify-content:space-between;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;font-weight:800;font-size:.86rem;color:#0f172a}
	@keyframes sglPulse{
		0%{transform:scale(1)}
		50%{transform:scale(1.03)}
		100%{transform:scale(1)}
	}
	.sgl-cost-item.is-saved{animation:sglPulse .45s ease-in-out}
	.sgl-btn-pill{border-radius:999px;font-weight:700;letter-spacing:.01em}
	.sgl-btn-pill.btn-danger,
	.sgl-btn-pill.btn-warning,
	.sgl-btn-pill.btn-success{box-shadow:0 4px 10px rgba(17,24,39,.12)}
	.header-actions .btn.sgl-btn-pill,
	#pagoGestorFormCustom [data-submit="pago-gestor"],
	#wizard.wizard-modern > .actions ul li.sgl-action-guardar a,
	#wizard.wizard-modern > .actions a[href="#finish"]{border-radius:999px !important}
	.header-actions{gap:12px !important}
	#pagoGestorFormCustom [data-submit="pago-gestor"]{margin-top:10px;padding:.4rem 1rem}
	.sgl-saldo-info{margin-top:8px;padding:8px 10px;border-radius:10px;border:1px solid #e2e8f0;background:#f8fafc;font-size:.78rem;font-weight:700;color:#334155}
	.sgl-saldo-info.is-sgl{border-color:#cbd5e1;background:#eff6ff;color:#1e3a8a}
	.sgl-saldo-info.is-gestor{border-color:#fecaca;background:#fef2f2;color:#991b1b}
	.sgl-saldo-info.is-even{border-color:#bbf7d0;background:#f0fdf4;color:#166534}
	.sgl-btn-icon{width:28px;height:28px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;padding:0}

	/* jQuery Steps: acciones compactas (similar al update original) */
	#wizard.wizard-modern > .actions{margin-top:10px}
	#wizard.wizard-modern > .actions ul{display:flex;justify-content:flex-start;gap:8px;align-items:center}
	#wizard.wizard-modern > .actions ul li{margin:0}
	#wizard.wizard-modern > .actions ul li a{border-radius:999px;font-weight:700;font-size:.82rem;padding:.35rem .85rem}
	#wizard.wizard-modern > .actions a[href="#previous"].disabled{display:none}
	#wizard.wizard-modern > .actions a[href="#previous"]{background:#fff;color:#6c757d;border:1px solid rgba(233,236,239,.95)}
	#wizard.wizard-modern > .actions a[href="#next"],
	#wizard.wizard-modern > .actions a[href="#finish"]{background:linear-gradient(90deg,#10B981 0%,#06B6D4 100%);border:0;color:#fff}
	#wizard.wizard-modern > .actions a[href="#next"]:hover,
	#wizard.wizard-modern > .actions a[href="#finish"]:hover{opacity:.95}
	#wizard.wizard-modern > .actions ul li.sgl-action-guardar{margin-left:auto}

	/* Wizard steps: reducir tamaño de tabs */
	#wizard.wizard-modern > .steps{padding:.6rem .9rem}
	#wizard.wizard-modern .steps ul li{min-height:unset}
	#wizard.wizard-modern .steps ul li a{gap:.2rem;padding:.15rem 0;line-height:1.1}
	#wizard.wizard-modern .steps .step-number{width:34px;height:34px;font-size:.9rem;border-width:2px}
	#wizard.wizard-modern .steps .step-title{font-size:.72rem}
	#wizard.wizard-modern .steps ul li.current .step-number{box-shadow:none}
	#wizard.wizard-modern .steps .step-title{color:#fff;font-weight:600}
	#wizard.wizard-modern .steps ul li.current .step-title{color:#fff;font-weight:700}

	/* Dropzone: boton quitar como tache superior derecho */
	.dropzone-documentos .dz-preview{position:relative}
	.dropzone-documentos .dz-preview .dz-remove{position:absolute;top:4px;right:4px;width:20px;height:20px;border-radius:999px;background:#dc3545;color:#fff !important;font-size:0 !important;line-height:20px;text-align:center;border:0;padding:0;z-index:999;display:flex;align-items:center;justify-content:center;text-decoration:none !important}
	.dropzone-documentos .dz-preview .dz-remove::before{content:'x';font-size:14px;line-height:1;display:block}
	.sgl-final-doc-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
	@media (max-width: 768px){.sgl-final-doc-grid{grid-template-columns:1fr}}
	.sgl-final-doc-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:10px}
	.sgl-final-doc-title{font-size:.8rem;font-weight:800;color:#374151;margin:0 0 8px 0}
	.sgl-important-select{border:2px solid #16a34a !important;box-shadow:0 0 0 .15rem rgba(34,197,94,.25)}
	.sgl-status-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:.7rem;font-weight:800;border:1px solid transparent;white-space:nowrap}
	.sgl-status-chip.is-muted{background:#f1f5f9;border-color:#e2e8f0;color:#64748b}
	.sgl-status-chip.is-success{background:#ecfdf5;border-color:#86efac;color:#166534}
	.sgl-status-row{display:flex;flex-wrap:wrap;gap:6px;margin-top:6px}
	.dropzone-gestor .dz-preview .dz-remove{display:none !important}
</style>

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
	$isReadOnlyMode = in_array((int) ($tra_status_id ?? 0), [23, 28, 20, 21], true);
	$formatValue = static function ($cfg) {
		$value = $cfg['value'] ?? '';
		$type = $cfg['type'] ?? 'text';
		if ($type === 'select') {
			$options = $cfg['options'] ?? [];
			if (is_array($options) && array_key_exists($value, $options)) {
				return $options[$value];
			}
		}
		if ($value === null || $value === '') {
			return '--';
		}
		return $value;
	};
	$folioValue = $folio ?? ($fields['folio']['value'] ?? null);
	$contratoValue = $fields['contrato']['value'] ?? ($contrato ?? null);
?>

<div class="main-container <?= $isReadOnlyMode ? 'sgl-readonly-mode' : '' ?>">
	<div class="pd-20 card-box mb-30 sgl-page-tight">
		<div class="tramite-header-modern sgl-header-compact">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<div class="d-flex align-items-center" style="gap:8px;flex-wrap:wrap;">
					<div class="folio-badge">
						<i class="fas fa-file-alt"></i>
						Folio: <?= $folioValue ? esc($folioValue) : '--' ?>
					</div>
					<div class="folio-badge">
						<i class="fas fa-file-signature"></i>
						Contrato: <?= $contratoValue ? esc($contratoValue) : '--' ?>
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
						<h6>Fecha Creación</h6>
						<p><?= isset($created_at) ? date('d/m/Y H:i', strtotime($created_at)) : '--' ?></p>
					</div>
				</div>
				<div class="timeline-item">
					<div class="timeline-icon"><i class="fas fa-layer-group"></i></div>
					<div class="timeline-content">
						<h6>Tipos Ligados</h6>
						<div class="badges-wrap" id="headerTiposLigados">
							<?php if (!empty($servicios_asociados) && is_array($servicios_asociados)): ?>
								<?php foreach (array_slice($servicios_asociados, 0, 3) as $srv): ?>
									<span class="badge badge-secondary badge-pill sgl-pill"><?= esc($srv['label'] ?? '') ?></span>
								<?php endforeach; ?>
								<?php if (count($servicios_asociados) > 3): ?>
									<span class="badge badge-secondary badge-pill sgl-pill">+<?= count($servicios_asociados) - 3 ?></span>
								<?php endif; ?>
							<?php else: ?>
								<span class="badge badge-secondary badge-pill sgl-pill">N/A</span>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="timeline-item">
					<div class="timeline-icon"><i class="fas fa-play-circle"></i></div>
					<div class="timeline-content">
						<h6>Fecha Inicio</h6>
						<p><?= isset($started_at) ? date('d/m/Y H:i', strtotime($started_at)) : '--' ?></p>
					</div>
				</div>
				<div class="timeline-item">
					<div class="timeline-icon"><i class="fas fa-file-contract"></i></div>
					<div class="timeline-content">
						<h6>Tipo de Trámite</h6>
						<p>
							<span id="principalTipoText"><?= isset($tipo_tramite) ? esc($tipo_tramite) : '--' ?></span>
							<?php if (!empty($can_edit_principal) && empty($isReadOnlyMode)): ?>
								<a href="#" class="mini-action" data-toggle="modal" data-target="#modalEditPrincipalTipo">
									<i class="fas fa-pen"></i> Cambiar
								</a>
							<?php endif; ?>
						</p>
					</div>
				</div>
				<div class="timeline-item">
					<div class="timeline-icon"><i class="fas fa-user-tie"></i></div>
					<div class="timeline-content">
						<h6>Gestor Asignado</h6>
						<p><?= isset($gestor) ? esc($gestor) : '--' ?></p>
					</div>
				</div>
				<div class="timeline-item">
					<div class="timeline-icon"><i class="fas fa-building"></i></div>
					<div class="timeline-content">
						<h6>Cliente</h6>
						<p><?= isset($cliente) ? esc($cliente) : '--' ?></p>
					</div>
				</div>
			</div>

			<?php if (has_permission('important_cancelar_tramite', $user_permissions ?? [], $user_roles ?? [])): ?>
				<div class="header-actions" style="margin-top:10px;display:flex;flex-wrap:wrap;">
					<?php if (($tra_status_id ?? 0) == 11): ?>
						<button type="button" class="btn btn-sm btn-warning sgl-btn-pill" onclick="changeStatusTramite(<?= (int) $id ?>, 29)">
							<i class="fas fa-file-invoice"></i> Es solo Cotizacion
						</button>
					<?php endif; ?>
					<?php if (!in_array((int) ($tra_status_id ?? 0), [20, 21], true)): ?>
						<button type="button" class="btn btn-sm btn-danger sgl-btn-pill" data-toggle="modal" data-target="#Medium-modal">
							<i class="fas fa-times-circle"></i> Cancelar Tramite
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Liston de Acciones Rapidas -->
		<div class="quick-actions-ribbon">
			<div class="ribbon-title">
				<i class="fas fa-bolt"></i>
				<span>Acciones Rapidas</span>
			</div>
			<div class="ribbon-buttons">
				<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-documentos">
					<div class="ribbon-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
						<i class="fas fa-folder-open"></i>
					</div>
					<span class="ribbon-label">Documentos</span>
				</button>

				<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-bitacora">
					<div class="ribbon-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
						<i class="fas fa-history"></i>
					</div>
					<span class="ribbon-label">Bitacora</span>
				</button>

				<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pagos-derecho">
					<div class="ribbon-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
						<i class="fas fa-receipt"></i>
					</div>
					<span class="ribbon-label">Pagos Derecho</span>
				</button>

				<?php if (isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21]) && has_permission('section_pago_gestor', $user_permissions ?? [], $user_roles ?? [])) : ?>
					<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pago-gestor">
						<div class="ribbon-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
							<i class="fas fa-hand-holding-usd"></i>
						</div>
						<span class="ribbon-label">Pago Gestor</span>
					</button>
				<?php endif; ?>

				<?php if (isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
					<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-cobro-cliente">
						<div class="ribbon-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
							<i class="fas fa-money-check-alt"></i>
						</div>
						<span class="ribbon-label">Cobros Cliente</span>
					</button>
				<?php endif; ?>

				<?php if (isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
					<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-evidencias-finales">
						<div class="ribbon-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
							<i class="fas fa-check-double"></i>
						</div>
						<span class="ribbon-label">Evidencias</span>
					</button>
				<?php endif; ?>
			</div>
		</div>

		<?php if (!$isReadOnlyMode): ?>
		<?php
			$principalTipoIdAudit = (int) ($principal_tipo_id ?? 0);
			$principalTipoLabelAudit = $tra_tipos_options[$principalTipoIdAudit] ?? '';
		?>
		<div id="audit-info" class="alert debug-info-container" style="display:none;background:#f8fafc;border:1px dashed #94a3b8;border-radius:10px;padding:10px 12px;margin-bottom:12px;">
			<div style="font-weight:700;color:#0f172a;margin-bottom:6px;">Audit</div>
			<div><strong>Tipo principal:</strong> <?= esc($principalTipoLabelAudit !== '' ? $principalTipoLabelAudit : 'N/A') ?> (ID <?= (int) $principalTipoIdAudit ?>)</div>
			<div style="margin-top:6px;"><strong>Tramites asociados:</strong></div>
			<?php if (!empty($servicios_asociados) && is_array($servicios_asociados)): ?>
				<ul style="margin:6px 0 0 18px;">
					<?php foreach ($servicios_asociados as $srv): ?>
						<li><?= esc($srv['label'] ?? 'N/A') ?> (ID <?= (int) ($srv['tra_tipos_id'] ?? 0) ?>)</li>
					<?php endforeach; ?>
				</ul>
			<?php else: ?>
				<div class="text-muted">Sin tramites asociados.</div>
			<?php endif; ?>
		</div>
		<form id="tramiteNuevoForm" method="post" action="<?= site_url('/deskapp/tramitesn/update_save/' . $id) ?>">
			<?= csrf_field() ?>
			<input type="hidden" id="current_step" name="current_step" value="1">
			<div id="tramiteNuevoMessage" class="alert mt-2" style="display:none;"></div>
			<div id="wizard" class="wizard-modern">
				<h3>Datos del Trámite</h3>
				<section>
					<div class="wizard-section" data-step="1">
					<div class="sgl-step-form-ribbon <?= !empty($step1_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="1">
						<div class="sgl-icon"><i class="<?= !empty($step1_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
						<div class="sgl-text">
							<?= !empty($step1_complete) ? 'Datos completos' : 'Completa los datos generales del trámite' ?>
						</div>
				</div>

					<div class="row" style="margin-left:-6px;margin-right:-6px;">
						<div class="col-lg-4" style="padding-left:6px;padding-right:6px;">

				<!-- Tipos de trámite ligados (tra_tramite_asociado) -->
				<div class="card mb-3 sgl-card-tight">
					<div class="card-body">
						<div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:12px;">
							<div>
								<h5 class="mb-1">Tipos de trámite ligados</h5>
								<small class="text-muted">Incluye el tipo principal y los asociados.</small>
							</div>
							<div id="tiposLigadosBadges" class="d-flex flex-wrap" style="gap:8px;">
								<?php if (!empty($servicios_asociados) && is_array($servicios_asociados)): ?>
									<?php foreach ($servicios_asociados as $srv): ?>
										<span class="badge badge-success badge-pill sgl-pill">✓ <?= esc($srv['label'] ?? '') ?></span>
									<?php endforeach; ?>
								<?php else: ?>
									<span class="badge badge-secondary badge-pill sgl-pill">Sin tipos ligados</span>
								<?php endif; ?>
							</div>
						</div>

						<hr>

						<div class="sgl-highlight-box mb-2">
							<div class="row align-items-end">
								<div class="col-12 mb-1">
									<label for="tra_tipos_add" class="form-label mb-1">Agregar Tipo de Trámite</label>
									<div class="d-flex align-items-center sgl-add-row" style="gap:10px;">
										<div class="d-flex flex-wrap" style="gap:8px;flex:1;align-items:center;">
											<select id="tra_tipos_add" class="form-control form-control-sm" style="min-width:160px;flex:1;">
												<option value="">Seleccione...</option>
												<?php if (!empty($tra_tipos_options) && is_array($tra_tipos_options)): ?>
													<?php foreach ($tra_tipos_options as $tipoId => $tipoLabel): ?>
														<option value="<?= (int) $tipoId ?>"><?= esc($tipoLabel) ?></option>
													<?php endforeach; ?>
												<?php endif; ?>
											</select>
											<button type="button" id="btnAgregarTipo" class="sgl-btn-mini" style="white-space:nowrap;">
												<i class="fas fa-plus"></i> Agregar
											</button>
										</div>
										<span class="sgl-inline-status" id="tiposMsg"></span>
									</div>
								</div>
							</div>
						</div>

						<div class="mt-3" id="tiposAsociadosList">
							<?php if (!empty($servicios_asociados) && is_array($servicios_asociados)): ?>
								<?php foreach ($servicios_asociados as $srv): ?>
									<?php $isPrincipalTipo = (int) ($srv['tra_tipos_id'] ?? 0) === (int) ($principal_tipo_id ?? 0); ?>
									<div class="card mb-2" data-asociado-id="<?= (int) ($srv['asociado_id'] ?? 0) ?>" data-tipo-id="<?= (int) ($srv['tra_tipos_id'] ?? 0) ?>">
										<div class="card-body py-2 sgl-associated-row">
											<div>
												<strong class="tipo-label"><?= esc($srv['label'] ?? '') ?></strong>
												<small class="text-muted d-block">Asociado</small>
											</div>
											<div class="actions">
												<?php if (!empty($can_edit_asociado) && !$isPrincipalTipo): ?>
													<button type="button" class="btn btn-sm btn-outline-primary btnCambiarAsociado" data-toggle="modal" data-target="#modalEditAsociadoTipo" title="Cambiar">
														<i class="fas fa-pen"></i>
													</button>
												<?php endif; ?>
												<?php if (!empty($can_delete_asociado) && !$isPrincipalTipo): ?>
													<button type="button" class="btn btn-sm btn-outline-danger btnEliminarAsociado" data-toggle="modal" data-target="#modalDeleteAsociado" title="Eliminar">
														<i class="fas fa-trash"></i>
													</button>
												<?php endif; ?>
												<span class="badge badge-success badge-pill sgl-pill" title="Ligado">✓</span>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							<?php else: ?>
								<div class="text-muted">No hay tipos asociados. Agrega uno arriba.</div>
							<?php endif; ?>
						</div>

						<div class="mt-2">
							<div id="tiposPendientes" class="d-grid" style="gap:10px;"></div>
						</div>
					</div>
				</div>
						</div>

						<div class="col-lg-8" style="padding-left:6px;padding-right:6px;">

				<div class="sgl-soft-panel mt-3">
					<p class="sgl-soft-panel-title">Datos del trámite</p>
					<div class="row">
						<?php if (!empty($fields) && is_array($fields)): ?>
							<?php
								$clienteCfg = $fields['cli_directo_id'] ?? null;
								$clienteEjCfg = $fields['cli_directo_ejecutivo_id'] ?? null;
								$renderClienteGroup = is_array($clienteCfg) || is_array($clienteEjCfg);
							?>

							<?php if ($renderClienteGroup): ?>
								<div class="col-12">
									<div class="sgl-linked-fields">
										<p class="sgl-linked-title mb-0">Cliente y Ejecutivo (conectados)</p>
										<div class="row">
											<?php
												$groupFields = [
													'cli_directo_id' => $clienteCfg,
													'cli_directo_ejecutivo_id' => $clienteEjCfg,
												];
											?>
											<?php foreach ($groupFields as $name => $cfg): ?>
												<?php if (empty($cfg) || !is_array($cfg)) continue; ?>
												<?php
													$type = $cfg['type'] ?? 'text';
													if ($type === 'hidden') {
														echo '<input type="hidden" name="'.$name.'" value="'.esc($cfg['value'] ?? '', 'attr').'">';
														continue;
													}
													$required = !empty($cfg['required']);
												?>
														<div class="col-md-6 mb-2">
													<div class="form-group">
														<label for="<?= esc($name) ?>"><?= esc($cfg['label'] ?? ucfirst($name)) ?><?= $required ? ' *' : '' ?></label>
														<?php if ($type === 'select'): ?>
																<select class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $required ? 'required' : '' ?>>
																<option value="">Seleccione...</option>
																<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
																	<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
																		<option value="<?= esc($optValue, 'attr') ?>" <?= ($cfg['value'] ?? null) == $optValue ? 'selected' : '' ?>><?= esc($optLabel) ?></option>
																	<?php endforeach; ?>
																<?php endif; ?>
															</select>
														<?php else: ?>
																<input type="<?= esc($type) ?>" class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($cfg['value'] ?? '', 'attr') ?>" <?= $required ? 'required' : '' ?>>
														<?php endif; ?>
														<div class="invalid-feedback">Campo obligatorio.</div>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							<?php endif; ?>

							<?php foreach ($fields as $name => $cfg): ?>
								<?php
									if ($name === 'cli_directo_id' || $name === 'cli_directo_ejecutivo_id') {
										continue;
									}
									$type = $cfg['type'] ?? 'text';
									if ($type === 'hidden') {
										echo '<input type="hidden" name="'.$name.'" value="'.esc($cfg['value'] ?? '', 'attr').'">';
										continue;
									}
									$required = !empty($cfg['required']);
								?>
								<div class="col-lg-3 col-md-4 col-sm-6 mb-2">
									<div class="form-group">
										<label for="<?= esc($name) ?>"><?= esc($cfg['label'] ?? ucfirst($name)) ?><?= $required ? ' *' : '' ?></label>
										<?php if ($type === 'textarea'): ?>
											<textarea class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" rows="2" <?= $required ? 'required' : '' ?>><?= esc($cfg['value'] ?? '') ?></textarea>
										<?php elseif ($type === 'select'): ?>
											<select class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $required ? 'required' : '' ?>>
												<option value="">Seleccione...</option>
												<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
													<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
														<option value="<?= esc($optValue, 'attr') ?>" <?= ($cfg['value'] ?? null) == $optValue ? 'selected' : '' ?>><?= esc($optLabel) ?></option>
													<?php endforeach; ?>
												<?php endif; ?>
											</select>
										<?php else: ?>
											<input type="<?= esc($type) ?>" class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($cfg['value'] ?? '', 'attr') ?>" <?= $required ? 'required' : '' ?>>
										<?php endif; ?>
										<div class="invalid-feedback">Campo obligatorio.</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>

				</div>
					</div>
				</div>
				</section>

				<h3>Gestor y Empresa</h3>
				<section>
					<div class="wizard-section" data-step="2">
						<div class="sgl-step-center">
							<div class="sgl-step-form-ribbon <?= !empty($step2_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="2">
								<div class="sgl-icon"><i class="<?= !empty($step2_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
								<div class="sgl-text">
									<?= !empty($step2_complete) ? 'Datos completos' : 'Asigna la empresa gestora y el gestor responsable' ?>
								</div>
							</div>
							<div class="sgl-soft-panel mt-3">
								<p class="sgl-soft-panel-title">Gestor y Empresa</p>
								<div class="row">
								<?php if (!empty($gestor_campos) && is_array($gestor_campos)): ?>
									<?php foreach ($gestor_campos as $name => $cfg): ?>
										<?php $required = !empty($cfg['required']); ?>
										<div class="col-lg-6 col-md-6 col-sm-12 mb-2">
											<div class="form-group">
												<label for="<?= esc($name) ?>"><?= esc($cfg['label'] ?? ucfirst($name)) ?><?= $required ? ' *' : '' ?></label>
												<select
													class="form-control form-control-sm"
													id="<?= esc($name) ?>"
													name="<?= esc($name) ?>"
													<?= $required ? 'required' : '' ?>>
													<option value="">Seleccione...</option>
													<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
														<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
															<option value="<?= esc($optValue, 'attr') ?>" <?= ($cfg['value'] ?? null) == $optValue ? 'selected' : '' ?>>
																<?= esc($optLabel) ?>
															</option>
														<?php endforeach; ?>
													<?php endif; ?>
												</select>
												<div class="invalid-feedback">Campo obligatorio.</div>
											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						</div>
						</div>
				</div>
				</section>

				<h3>Pagos de Derechos</h3>
				<section>
					<div class="wizard-section" data-step="3">
					<div class="sgl-step-center">
						<div class="sgl-step-form-ribbon <?= !empty($step3_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="3">
							<div class="sgl-icon"><i class="<?= !empty($step3_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
							<div class="sgl-text">
								<?= !empty($step3_complete) ? 'Datos completos' : 'Configura los pagos de derechos y su evidencia' ?>
							</div>
						</div>
						<div class="form-dropzone-grid">
							<div class="form-column">
								<div class="row">
								<?php if (!empty($derechos_campos) && is_array($derechos_campos)): ?>
									<?php foreach ($derechos_campos as $name => $cfg): ?>
										<?php
											$type = $cfg['type'] ?? 'text';
											$required = !empty($cfg['required']);
										?>
										<div class="col-lg-3 col-md-4 col-sm-6 mb-2">
											<div class="form-group">
												<label for="<?= esc($name) ?>"><?= esc($cfg['label'] ?? ucfirst($name)) ?><?= $required ? ' *' : '' ?></label>
												<?php if ($type === 'select'): ?>
													<select
													class="form-control form-control-sm"
													id="<?= esc($name) ?>"
													name="<?= esc($name) ?>"
													<?= $required ? 'required' : '' ?>>
													<option value="">Seleccione...</option>
													<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
														<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
															<option value="<?= esc($optValue, 'attr') ?>" <?= ($cfg['value'] ?? null) == $optValue ? 'selected' : '' ?>>
																<?= esc($optLabel) ?>
															</option>
														<?php endforeach; ?>
													<?php endif; ?>
												</select>
											<?php else: ?>
												<input
													type="<?= $type === 'datetime' ? 'datetime-local' : esc($type) ?>"
													class="form-control form-control-sm"
													id="<?= esc($name) ?>"
													name="<?= esc($name) ?>"
													value="<?= esc($cfg['value'] ?? '', 'attr') ?>"
													<?= $required ? 'required' : '' ?>>
											<?php endif; ?>
											<div class="invalid-feedback">Campo obligatorio.</div>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php
								$paso3_completo = !empty($derechos_campos['derechos_tramite']['value'])
									&& !empty($derechos_campos['derechos_revol_cliente']['value'])
									&& !empty($derechos_campos['derechos_refer_banc']['value']);
								$arr_status = [
									11 => 1, 22 => 2, 25 => 3, 26 => 3, 27 => 3,
									23 => 4, 28 => 5, 20 => 6, 21 => 7, 29 => 1
								];
								$step_actual = isset($arr_status[$tra_status_id]) ? $arr_status[$tra_status_id] : 1;
							?>

							<?php if (is_array($user_roles ?? null) && in_array('Super Admin', $user_roles, true)): ?>
								<div class="debug-info-container" style="display: none;">
									<div class="alert" style="background:#fff3cd;border:1px solid #ffeeba;border-radius:8px;">
										<h6 style="margin:0 0 8px 0;font-weight:700;">
											<i class="fas fa-bug"></i> Debug paso3
										</h6>
										<div style="font-size:12px;line-height:1.4;">
											<div><strong>tra_status_id:</strong> <?= esc($tra_status_id ?? '') ?></div>
											<div><strong>step_actual:</strong> <?= esc($step_actual ?? '') ?></div>
											<div><strong>paso3_completo:</strong> <?= !empty($paso3_completo) ? 'true' : 'false' ?></div>
											<div><strong>can_important_pasar_a_pagos:</strong> <?= has_permission('important_pasar_a_pagos', $user_permissions ?? [], $user_roles ?? []) ? 'true' : 'false' ?></div>
											<div><strong>can_boton_aprobar_tramite:</strong> <?= puede_editar_modulo($user_roles ?? [], $tra_status_id ?? 0, 'boton_aprobar_tramite', $reembolso_status_id ?? 0, $cobro_status_id ?? 0, 3) ? 'true' : 'false' ?></div>
											<div><strong>user_roles_count:</strong> <?= isset($user_roles) && is_array($user_roles) ? count($user_roles) : 0 ?></div>
											<div><strong>user_perms_count:</strong> <?= isset($user_permissions) && is_array($user_permissions) ? count($user_permissions) : 0 ?></div>
										</div>
									</div>
								</div>
							<?php endif; ?>

							<?php if (has_permission('important_pasar_a_pagos', $user_permissions ?? [], $user_roles ?? [])): ?>
								<?php if ($step_actual <= 3 && puede_editar_modulo($user_roles ?? [], $tra_status_id, 'boton_aprobar_tramite', $reembolso_status_id, $cobro_status_id, 3)): ?>
									<div class="sgl-approval-wrap" id="approvalWrap">
										<div class="alert alert-info approval-ready" id="approvalReady" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border: 2px solid #4caf50; border-radius: 12px; padding: 20px; display: <?= $paso3_completo ? 'flex' : 'none' ?>; align-items: center; gap: 20px; box-shadow: 0 4px 6px rgba(76, 175, 80, 0.15);">
											<div style="flex-shrink: 0;">
												<i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50;"></i>
											</div>
											<div style="flex: 1;">
												<h5 style="margin: 0 0 8px 0; color: #2e7d32; font-weight: 700;">
													<i class="fas fa-clipboard-check"></i> Informacion Completa
												</h5>
												<p style="margin: 0 0 15px 0; color: #1b5e20; font-size: 0.95rem;">
													Los datos de pago de derechos estan completos. El tramite esta listo para ser aprobado y continuar al siguiente paso.
												</p>
												<button type="button"
													class="btn-wizard btn-success btn-lg approval-button"
													onclick="confirmAprobarTramite(<?= (int) $id ?>);">
													<i class="fas fa-check-double"></i> Aprobar Tramite
												</button>
											</div>
										</div>
										<div class="alert alert-warning approval-pending" id="approvalPending" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border: 2px solid #ff9800; border-radius: 12px; padding: 20px; display: <?= $paso3_completo ? 'none' : 'flex' ?>; align-items: center; gap: 20px; box-shadow: 0 4px 6px rgba(255, 152, 0, 0.15);">
											<div style="flex-shrink: 0;">
												<i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ff9800;"></i>
											</div>
											<div style="flex: 1;">
												<h5 style="margin: 0 0 8px 0; color: #e65100; font-weight: 700;">
													<i class="fas fa-info-circle"></i> Informacion Incompleta
												</h5>
												<p style="margin: 0; color: #bf360c; font-size: 0.95rem;">
													Para aprobar el tramite, primero debes completar y guardar los siguientes campos obligatorios:
												</p>
												<ul id="approvalMissingList" style="margin: 10px 0 0 0; color: #bf360c; font-size: 0.9rem;">
													<?php if (empty($derechos_campos['derechos_tramite']['value'])): ?>
														<li><strong>Monto pago de derechos</strong></li>
													<?php endif; ?>
													<?php if (empty($derechos_campos['derechos_revol_cliente']['value'])): ?>
														<li><strong>Forma de Pago</strong></li>
													<?php endif; ?>
													<?php if (empty($derechos_campos['derechos_refer_banc']['value'])): ?>
														<li><strong>Referencia Bancaria</strong></li>
													<?php endif; ?>
												</ul>
											</div>
										</div>
									<?php endif; ?>
									</div>
							<?php endif; ?>
								</div>
							</div>
							<div class="dropzone-column">
								<div class="dropzone-sticky">
									<h5 class="dropzone-title">
										<i class="fas fa-cloud-upload-alt"></i> Documentos de Derechos
									</h5>
									<?php if (!empty($can_section_pago_derechos)): ?>
										<?php if (!empty($can_upload_derechos)): ?>
											<div class="dropzone-container">
												<div class="dropzone dropzone-documentos dropzone-compact" id="miDropzone">
													<div class="dz-default dz-message">
														<button class="dz-button" type="button">
															<img src="/public/assets/src/images/upload.svg" class="dz-icon" alt="Subir Archivo">
															<p class="dz-text">Arrastra archivos aqui o haz clic</p>
															<p class="dz-subtext">Documentos de pago de derechos</p>
														</button>
													</div>
												</div>
											</div>
											<button id="btnSubirDocumentos" class="btnSubir">
												<i class="fas fa-cloud-upload-alt"></i> Subir Archivos
											</button>
											<div class="delete-notice">
												<i class="fas fa-info-circle"></i>
												<h6>Para eliminar un archivo, solicitalo al administrador</h6>
											</div>
										<?php endif; ?>
										<!-- Galeria de Imagenes -->
										<div class="gallery-preview" id="documentos-container">
											<?php if (!empty($pago_derechos_db) && is_array($pago_derechos_db)): ?>
												<?php foreach ($pago_derechos_db as $doc): ?>
													<?php
														$fileName = (string) ($doc['file'] ?? '');
														$docType = (string) ($doc['doc_type'] ?? ($doc['comprobante_final'] ?? ''));
														$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
														$isImage = in_array($fileExt, ['jpg','jpeg','png','gif','webp'], true);
														$fileUrl = base_url('/assets/uploads/pago_derechos/' . $id . '/' . $fileName);
													?>
													<div class="file-preview" data-file="<?= esc($fileName) ?>" data-doc-type="<?= esc($docType) ?>" style="border:1px solid #ddd;border-radius:5px;padding:5px;background-color:#f9f9f9;display:inline-block;margin:4px;text-align:center;">
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
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				</div>
				</section>
			</div>
		</form>
		<?php else: ?>
			<div class="sgl-step-center">
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

				<?php if (!empty($can_section_pago_gestor)): ?>
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
									<form id="pagoGestorFormCustom" method="post" action="<?= site_url('/deskapp/tramites/update_pago_gestor/' . $id) ?>">
									<?= csrf_field() ?>
									<div class="row">
										<?php
											$gastoGroup = ['impuesto_gestoria', 'gestoria_comision', 'costo_paqueteria', 'gestor_total_pago'];
											$primaryGroup = ['gestor_name', 'num_factura_gestor', 'deposito_gestor'];
											$statusGroup = ['pago_gestor_st_id', 'reembolso_status_id'];
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
								</h5>
								<div class="form-group mb-2">
									<label for="pagoGestorComprobanteFinal" class="mb-1">Tipo de comprobante final</label>
									<select id="pagoGestorComprobanteFinal" class="form-control form-control-sm sgl-important-select">
										<option value="tramite_recibido">Tramite Entregado por Gestor</option>
										<option value="acuse_recibo_cliente">Acuse de Recibo del Cliente</option>
										<option value="otro" selected>Otro</option>
									</select>
									<small class="text-muted">Se guarda con cada archivo que subas.</small>
								</div>
								<?php if (!empty($can_upload_pago_gestor)): ?>
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
		<?php endif; ?>
	</div>
</div>

<?php if (has_permission('important_cancelar_tramite', $user_permissions ?? [], $user_roles ?? [])): ?>
	<div class="modal fade" id="Medium-modal" tabindex="-1" role="dialog" aria-labelledby="cancelTramiteLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="cancelTramiteLabel">Cancelar Tramite</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="motivo" class="form-label">Motivo de Cancelacion</label>
						<textarea class="form-control" id="motivo" rows="3" required></textarea>
						<div id="cancelError" class="text-danger small mt-2" style="display:none;"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
					<button type="button" class="btn btn-primary" id="saveCancelBtn">Continuar</button>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<!-- Modal: Cambiar tipo principal -->
<div class="modal fade" id="modalEditPrincipalTipo" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Cambiar tipo de trámite principal</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<label class="form-label">Nuevo tipo</label>
				<select id="principalTipoSelect" class="form-control">
					<option value="">Seleccione...</option>
					<?php if (!empty($tra_tipos_options) && is_array($tra_tipos_options)): ?>
						<?php foreach ($tra_tipos_options as $tipoId => $tipoLabel): ?>
							<option value="<?= (int) $tipoId ?>"><?= esc($tipoLabel) ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<small class="text-muted d-block mt-2">Este cambio actualiza el tipo principal del trámite.</small>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-primary" id="btnGuardarPrincipalTipo">Guardar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Cambiar tipo asociado -->
<div class="modal fade" id="modalEditAsociadoTipo" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Cambiar tipo de trámite asociado</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="asociadoIdInput" value="">
				<label class="form-label">Nuevo tipo</label>
				<select id="asociadoTipoSelect" class="form-control">
					<option value="">Seleccione...</option>
					<?php if (!empty($tra_tipos_options) && is_array($tra_tipos_options)): ?>
						<?php foreach ($tra_tipos_options as $tipoId => $tipoLabel): ?>
							<option value="<?= (int) $tipoId ?>"><?= esc($tipoLabel) ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<small class="text-muted d-block mt-2">No permite duplicados dentro del mismo trámite.</small>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-primary" id="btnGuardarAsociadoTipo">Guardar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Eliminar asociado -->
<div class="modal fade" id="modalDeleteAsociado" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Eliminar tipo asociado</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="deleteAsociadoIdInput" value="">
				<p class="mb-1">¿Seguro que deseas eliminar este tipo asociado?</p>
				<small class="text-muted d-block">Este cambio solo afecta a los tipos asociados. El tipo principal del trámite se mantiene.</small>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-danger" id="btnConfirmDeleteAsociado">Eliminar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Comprobante final guardado -->
<div class="modal fade" id="modalComprobanteFinal" tabindex="-1" role="dialog" aria-labelledby="modalComprobanteFinalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalComprobanteFinalLabel">Comprobante final guardado</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				El archivo se registro como: <strong id="comprobanteFinalText">tramite_recibido</strong>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
			</div>
		</div>
	</div>
</div>

<!-- MODALS DE ACCIONES RAPIDAS -->

<div class="modal fade" id="modal-documentos" tabindex="-1" role="dialog" aria-labelledby="modalDocumentosLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
				<h4 class="modal-title" id="modalDocumentosLabel">
					<i class="fas fa-folder-open"></i> Documentos
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
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

<div class="modal fade" id="modal-bitacora" tabindex="-1" role="dialog" aria-labelledby="modalBitacoraLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
				<h4 class="modal-title" id="modalBitacoraLabel">
					<i class="fas fa-history"></i> Bitacora
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_bitacora)) {
							echo $output_bitacora;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay registros en la bitacora</div>';
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

<div class="modal fade" id="modal-pagos-derecho" tabindex="-1" role="dialog" aria-labelledby="modalPagosDerechoLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
				<h4 class="modal-title" id="modalPagosDerechoLabel">
					<i class="fas fa-receipt"></i> Pagos de Derecho
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
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

<?php if (isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
<div class="modal fade" id="modal-pago-gestor" tabindex="-1" role="dialog" aria-labelledby="modalPagoGestorLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
				<h4 class="modal-title" id="modalPagoGestorLabel">
					<i class="fas fa-hand-holding-usd"></i> Pago al Gestor
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_pago_gestor)) {
							echo $output_pago_gestor;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay informacion de pago al gestor</div>';
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

<?php if (isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
<div class="modal fade" id="modal-cobro-cliente" tabindex="-1" role="dialog" aria-labelledby="modalCobroClienteLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
				<h4 class="modal-title" id="modalCobroClienteLabel">
					<i class="fas fa-money-check-alt"></i> Cobros al Cliente
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
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

<?php if (isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
<div class="modal fade" id="modal-evidencias-finales" tabindex="-1" role="dialog" aria-labelledby="modalEvidenciasFinalesLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
				<h4 class="modal-title" id="modalEvidenciasFinalesLabel">
					<i class="fas fa-check-double"></i> Evidencias Finales
				</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
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

<!-- FIN MODALS DE ACCIONES RAPIDAS -->

<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
<script src="<?= $assets ?>/src/plugins/sweetalert2/sweetalert2.all.js"></script>
<?php
	if (!empty($js_files)) {
		foreach ($js_files as $file) { ?>
			<script src="<?php echo $file . "?v=" . time(); ?>"></script>
		<?php }
	}
?>
<script src="<?= $assets ?>/src/scripts/dropzone.js"></script>
<script>
	if (window.Dropzone) {
		window.Dropzone.autoDiscover = false;
	}
</script>
<script src="<?= $assets ?>/src/plugins/jquery-steps/jquery.steps.js"></script>
<script>
	<?php
		$principalTipoId = 0;
		if (isset($fields) && is_array($fields) && isset($fields['tra_tipos_id']) && is_array($fields['tra_tipos_id'])) {
			$principalTipoId = (int) ($fields['tra_tipos_id']['value'] ?? 0);
		}
	?>

	window.SGL_TRAMITESN_UPDATE_V2 = {
		maxStep: 3,
		csrfName: '<?= csrf_token() ?>',
		csrfHash: '<?= csrf_hash() ?>',
		tramiteId: <?= (int) ($id ?? 0) ?>,
		tiposOptions: <?= json_encode($tra_tipos_options ?? []) ?>,
		tiposExistentes: <?= json_encode($servicios_tipos_ids ?? []) ?>,
		principalTipoId: <?= (int) $principalTipoId ?>,
		statusId: <?= (int) ($tra_status_id ?? 0) ?>,
		isLocked: <?= in_array((int) ($tra_status_id ?? 0), [20, 21], true) ? 'true' : 'false' ?>,
		stepActual: <?= (int) ($step ?? ($step_actual ?? 0)) ?>,
		canEditAsociado: <?= (!empty($can_edit_asociado) && !in_array((int) ($tra_status_id ?? 0), [20, 21], true)) ? 'true' : 'false' ?>,
		canDeleteAsociado: <?= (!empty($can_delete_asociado) && !in_array((int) ($tra_status_id ?? 0), [20, 21], true)) ? 'true' : 'false' ?>,
		canEditPagoGestor: <?= (!empty($can_edit_pago_gestor) && !in_array((int) ($tra_status_id ?? 0), [20, 21], true)) ? 'true' : 'false' ?>,
		swalSrc: '<?= $assets ?>/src/plugins/sweetalert2/sweetalert2.all.js',
		urls: {
			servicesAdd: '<?= site_url('/deskapp/tramitesn/services/add') ?>',
			servicesUpdate: '<?= site_url('/deskapp/tramitesn/services/update') ?>',
			servicesDelete: '<?= site_url('/deskapp/tramitesn/services/delete') ?>',
			principalUpdateTipo: '<?= site_url('/deskapp/tramitesn/principal/update_tipo') ?>',
			updateSave: '<?= site_url('/deskapp/tramitesn/update_save/' . $id) ?>',
			updateGestorSave: '<?= site_url('/deskapp/tramitesn/update_gestor_save/' . $id) ?>',
			updateDerechosSave: '<?= site_url('/deskapp/tramitesn/update_derechos_save/' . $id) ?>',
			uploadComprobante: '<?= site_url('/deskapp/tramites/upload_comprobante/' . $id) ?>',
			deleteComprobante: '<?= site_url('/deskapp/tramites/delete_comprobante') ?>',
			getGestoresByEmpresaId: '<?= site_url('/deskapp/tramites/getGestoresByEmpresaId') ?>',
			updatePagoGestor: '<?= site_url('/deskapp/tramites/update_pago_gestor/' . $id) ?>',
			uploadPagoGestor: '<?= site_url('/deskapp/tramites/upload_pago_gestor/' . $id) ?>',
			deletePagoGestor: '<?= site_url('/deskapp/tramites/delete_pago_gestor') ?>',
			getServiceCosts: '<?= site_url('/deskapp/tramitesn/get_service_costs_by_tramite/' . $id) ?>',
			updateServiceCost: '<?= site_url('/deskapp/tramitesn/update_service_cost') ?>',
			uploadFinalDocBase: '<?= site_url('/deskapp/tramitesn/upload_final_doc') ?>/',
			deleteFinalDoc: '<?= site_url('/deskapp/tramitesn/delete_final_doc') ?>'
		}
	};
</script>
<script src="<?= $assets ?>/src/scripts/tramitesn_update_v2.js?v=<?= time(); ?>"></script>
<script>
	console.log('tramitesn_update_v2 loaded flag', window.__SGL_TRAMITESN_LOADED);
	document.addEventListener('DOMContentLoaded', function () {
		console.log('[HTML FinalDocs] presence check', {
			path: window.location.pathname,
			hasBtn16: !!document.getElementById('btnSubirFinalDoc16'),
			hasBtn17: !!document.getElementById('btnSubirFinalDoc17'),
			hasInput16: !!document.getElementById('nativeFinalDoc16'),
			hasInput17: !!document.getElementById('nativeFinalDoc17'),
			hasCont16: !!document.getElementById('final-doc-16-container'),
			hasCont17: !!document.getElementById('final-doc-17-container')
		});
	});
</script>
<script>
	// final docs inline script removido: ahora lo maneja tramitesn_update_v2.js
</script>
<script>
	// fallback final docs removido
</script>
<script>
	(function () {
		function initCostFallback() {
			if (window.__SGL_TRAMITESN_COSTS_BOUND) return;
			var cfg = window.SGL_TRAMITESN_UPDATE_V2 || {};
			var urls = cfg.urls || {};
			var list = document.getElementById('gestor_costos_tipo_servicio');
			var totalEl = document.getElementById('costo_tramite_total');
			var totalInput = document.getElementById('costo_tramite');
			if (!list || !totalEl) return;

			function formatNumber(n) {
				var num = parseFloat(n);
				if (isNaN(num)) num = 0;
				return num.toFixed(2);
			}

			function updateTotal() {
				var total = 0;
				list.querySelectorAll('input[data-cost-id]').forEach(function (input) {
					var v = parseFloat(input.value) || 0;
					total += v;
				});
				var totalStr = formatNumber(total);
				totalEl.textContent = '$' + totalStr;
				if (totalInput) totalInput.value = totalStr;
				var impuestoEl = document.getElementById('impuesto_gestoria');
				var comisionEl = document.getElementById('gestoria_comision');
				var paqueteriaEl = document.getElementById('costo_paqueteria');
				var depositoEl = document.getElementById('deposito_gestor');
				var saldoInput = document.getElementById('col_a_favor');
				var saldoInfo = document.getElementById('saldoPendienteInfo');
				var reembolsoSelect = document.getElementById('reembolso_status_id');
				var totalPago = document.getElementById('gestor_total_pago');
				var totalPagoText = document.getElementById('gasto_total_text');
				var breakdownText = document.getElementById('gasto_total_breakdown');
				var impuesto = parseFloat(impuestoEl ? impuestoEl.value : 0) || 0;
				var comision = parseFloat(comisionEl ? comisionEl.value : 0) || 0;
				var paqueteria = parseFloat(paqueteriaEl ? paqueteriaEl.value : 0) || 0;
				var deposito = parseFloat(depositoEl ? depositoEl.value : 0) || 0;
				var gastoTotal = total + impuesto + comision + paqueteria;
				var saldo = total - deposito;
				var saldoAbs = Math.abs(saldo);
				if (totalPago) totalPago.value = formatNumber(gastoTotal);
				if (totalPagoText) totalPagoText.textContent = '$' + formatNumber(gastoTotal);
				if (breakdownText) {
					breakdownText.textContent =
						'Costos: $' + formatNumber(total) +
						' + Honorarios: $' + formatNumber(impuesto) +
						' + Gratificacion: $' + formatNumber(comision) +
						' + Paqueteria: $' + formatNumber(paqueteria);
				}
				if (saldoInput) {
					saldoInput.value = formatNumber(saldo);
					saldoInput.setAttribute('readonly', 'readonly');
				}
				if (reembolsoSelect) {
					var targetStatus = Math.abs(saldo) > 0.0001 ? '22' : '24';
					if (String(reembolsoSelect.value) !== targetStatus) {
						reembolsoSelect.value = targetStatus;
						reembolsoSelect.dispatchEvent(new Event('change', { bubbles: true }));
					}
				}
				if (saldoInfo) {
					saldoInfo.classList.remove('is-sgl', 'is-gestor', 'is-even');
					if (saldo > 0.0001) {
						saldoInfo.classList.add('is-gestor');
						saldoInfo.textContent = 'Saldo pendiente a favor del Gestor: $' + formatNumber(saldoAbs);
					} else if (saldo < -0.0001) {
						saldoInfo.classList.add('is-sgl');
						saldoInfo.textContent = 'Saldo pendiente a favor SGL: $' + formatNumber(saldoAbs);
					} else {
						saldoInfo.classList.add('is-even');
						saldoInfo.textContent = 'Sin saldo pendiente';
					}
				}
			}

			function bindInputs() {
				list.querySelectorAll('input[data-cost-id]').forEach(function (input) {
					input.addEventListener('input', updateTotal);
					input.addEventListener('keyup', function (e) {
						console.log('fallback keyup costo_tramite', e.target && e.target.value);
						updateTotal();
					});
				});
			}

			function renderItems(data) {
				list.innerHTML = '';
				if (!Array.isArray(data) || data.length === 0) {
					list.innerHTML = '<div class="text-muted">No hay tramites asociados.</div>';
					updateTotal();
					return;
				}
				var canEdit = !!cfg.canEditPagoGestor;
				data.forEach(function (row) {
					var id = row.id;
					var name = row.tipo_tramite || ('Servicio #' + id);
					var val = (row.costo_tramite !== null && row.costo_tramite !== undefined) ? row.costo_tramite : 0;
					var item = document.createElement('div');
					item.className = 'sgl-cost-item';
					item.innerHTML =
						'<div class="sgl-cost-name">' + name + '</div>' +
						'<input type="number" class="form-control form-control-sm text-end sgl-cost-input" data-cost-id="' + id + '" value="' + val + '" ' + (canEdit ? '' : 'disabled') + ' />' +
						(canEdit
							? '<button type="button" class="btn btn-success btn-sm sgl-btn-pill sgl-cost-save" data-save-id="' + id + '" title="Guardar"><i class="fas fa-save"></i></button>'
							: '') +
						'<span class="sgl-cost-icon" aria-hidden="true"></span>' +
						'<span class="sgl-cost-row-status">Guardado</span>';
					list.appendChild(item);
				});
				bindInputs();
				updateTotal();
			}

			list.addEventListener('click', function (e) {
				var btn = e.target.closest('[data-save-id]');
				if (!btn || !urls.updateServiceCost) return;
				var id = btn.getAttribute('data-save-id');
				var input = list.querySelector('input[data-cost-id="' + id + '"]');
				if (!input) return;
				var fd = new FormData();
				fd.append('id', id);
				fd.append('costo_tramite', input.value || '0');
				if (cfg.csrfName && cfg.csrfHash) {
					fd.append(cfg.csrfName, cfg.csrfHash);
				}
				fetch(urls.updateServiceCost, {
					method: 'POST',
					body: fd,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (resp) { return resp.json(); })
					.then(function (data) {
						var rowStatus = list.querySelector('input[data-cost-id="' + id + '"]').parentElement.querySelector('.sgl-cost-row-status');
						var rowIcon = list.querySelector('input[data-cost-id="' + id + '"]').parentElement.querySelector('.sgl-cost-icon');
						var row = list.querySelector('input[data-cost-id="' + id + '"]').closest('.sgl-cost-item');
						if (data && data.status === 'success') {
							if (rowStatus) rowStatus.textContent = 'Guardado';
							if (rowIcon) rowIcon.innerHTML = '<i class="fas fa-check"></i>';
							if (row) row.classList.add('is-saved');
							setTimeout(function () {
								if (rowStatus) rowStatus.textContent = '';
								if (rowIcon) rowIcon.textContent = '';
								if (row) row.classList.remove('is-saved');
							}, 1400);
							return;
						}
						if (rowStatus) rowStatus.textContent = 'Error al guardar';
						if (rowIcon) rowIcon.innerHTML = '<i class="fas fa-times"></i>';
						if (row) row.classList.add('is-error');
						setTimeout(function () {
							if (rowStatus) rowStatus.textContent = '';
							if (rowIcon) rowIcon.textContent = '';
							if (row) row.classList.remove('is-error');
						}, 2000);
					})
					.catch(function () {
						var rowStatus = list.querySelector('input[data-cost-id="' + id + '"]').parentElement.querySelector('.sgl-cost-row-status');
						var rowIcon = list.querySelector('input[data-cost-id="' + id + '"]').parentElement.querySelector('.sgl-cost-icon');
						var row = list.querySelector('input[data-cost-id="' + id + '"]').closest('.sgl-cost-item');
						if (rowStatus) rowStatus.textContent = 'Error al guardar';
						if (rowIcon) rowIcon.innerHTML = '<i class="fas fa-times"></i>';
						if (row) row.classList.add('is-error');
						setTimeout(function () {
							if (rowStatus) rowStatus.textContent = '';
							if (rowIcon) rowIcon.textContent = '';
							if (row) row.classList.remove('is-error');
						}, 2000);
					});
			});

			['impuesto_gestoria', 'gestoria_comision', 'costo_paqueteria', 'deposito_gestor'].forEach(function (id) {
				var el = document.getElementById(id);
				if (el) {
					el.addEventListener('input', updateTotal);
					el.addEventListener('keyup', function (e) {
						console.log('fallback keyup gasto', id, e.target && e.target.value);
						updateTotal();
					});
				}
			});

			if (list.querySelector('.sgl-cost-item')) {
				bindInputs();
				updateTotal();
				return;
			}

			if (!urls.getServiceCosts) {
				bindInputs();
				updateTotal();
				return;
			}

			fetch(urls.getServiceCosts, {
				method: 'GET',
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			})
				.then(function (resp) { return resp.json(); })
				.then(renderItems)
				.catch(function () {
					list.innerHTML = '<div class="text-danger">Error al cargar costos.</div>';
				});
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', initCostFallback);
		} else {
			initCostFallback();
		}
	})();
</script>
<?= $this->endSection() ?>
