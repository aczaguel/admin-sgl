<?php
/**
 * Tramite Unified Layout - Client View (Read-Only)
 *
 * Vista de cliente que muestra únicamente los pasos 1-3 en modo lectura.
 * Los pasos 4 (Pago a Gestor) y 5 (Cobro a Cliente) están ocultos.
 *
 * @var array $viewData  Variables del controller con datos y permisos por paso
 */
?>
<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<script>
(function() {
    if (document.body) {
        document.body.classList.add('tul-no-floating-menu');
    }
})();
</script>

<meta name="csrf-token-name" content="<?= csrf_token() ?>">
<meta name="csrf-token-hash" content="<?= csrf_hash() ?>">

<link rel="stylesheet" href="<?= base_url('/public/assets/src/styles/tramite_unified_layout.css') ?>?v=20250619y">

<?php
$tul_t = $viewData['prototypeReadOnlyTramite'] ?? null;
$tul_id = $tul_t['id'] ?? ($viewData['prototypeTramiteId'] ?? '');
$tul_folio = $tul_t['folio'] ?? '--';
$tul_tipo = $tul_t['tipo_principal_label'] ?? 'Sin tipo';
$tul_status = $tul_t['tra_status_label'] ?? 'Sin estatus';
$tul_cliente = $tul_t['cliente_name'] ?? 'Sin cliente';
$tul_gestor = $tul_t['gestor_name'] ?? 'Sin asignar';
$tul_createdAt = isset($tul_t['created_at']) && $tul_t['created_at']
    ? date('d/m/Y', strtotime((string) $tul_t['created_at']))
    : '—';
$tul_startedAt = isset($tul_t['started_at']) && $tul_t['started_at']
    ? date('d/m/Y', strtotime((string) $tul_t['started_at']))
    : 'Pendiente';
$tul_asociados = [];
foreach (($tul_t['associated_service_rows'] ?? []) as $tul_row) {
    $lbl = trim((string) ($tul_row['label'] ?? ''));
    if ($lbl !== '' && stripos($lbl, 'sin asociados') === false) {
        $tul_asociados[] = $lbl;
    }
}
$tul_asociadosText = $tul_asociados !== [] ? implode(', ', $tul_asociados) : 'Sin asociados';

// Fase actual para el indicador de progreso (solo fases 1-2 visibles en vista cliente)
$tul_newFormatStep = (int) ($tul_t['new_format_step'] ?? 0);
$tul_stepActual = $tul_newFormatStep >= 1 ? min($tul_newFormatStep, 3) : 1;

$tul_gateEvidencias = $tul_stepActual >= 3;

if ($tul_gateEvidencias) {
    $tul_faseActual = 2;
    $tul_faseLabel = 'Evidencias finales';
} else {
    $tul_faseActual = 1;
    $tul_faseLabel = 'Operación base';
}
?>

<div class="tul-client-back" style="padding: 8px 16px;">
    <a href="<?= site_url('deskapp/clientes/tramites') ?>" style="font-size: 0.875rem; color: #6b7280; text-decoration: none;">
        ← Regresar a mis trámites
    </a>
</div>

<div class="tul-detailbar" data-tul-detailbar>
    <div class="tul-detailbar__item">
        <span class="tul-detailbar__label">Folio</span>
        <span class="tul-detailbar__value"><?= esc($tul_folio) ?></span>
    </div>
    <div class="tul-detailbar__item">
        <span class="tul-detailbar__label">ID</span>
        <span class="tul-detailbar__value"><?= esc((string) $tul_id) ?></span>
    </div>
    <div class="tul-detailbar__item">
        <span class="tul-detailbar__label">Tipo de trámite</span>
        <span class="tul-detailbar__value"><?= esc($tul_tipo) ?></span>
    </div>
    <div class="tul-detailbar__item">
        <span class="tul-detailbar__label">Asociados</span>
        <span class="tul-detailbar__value" title="<?= esc($tul_asociadosText, 'attr') ?>"><?= esc($tul_asociadosText) ?></span>
    </div>
    <div class="tul-detailbar__item">
        <span class="tul-detailbar__label">Estatus</span>
        <span class="tul-detailbar__value tul-detailbar__value--status"><?= esc($tul_status) ?></span>
    </div>
    <div class="tul-detailbar__item">
        <span class="tul-detailbar__label">Cliente</span>
        <span class="tul-detailbar__value"><?= esc($tul_cliente) ?></span>
    </div>
    <div class="tul-detailbar__item">
        <span class="tul-detailbar__label">Gestor</span>
        <span class="tul-detailbar__value"><?= esc($tul_gestor) ?></span>
    </div>
    <div class="tul-detailbar__item">
        <span class="tul-detailbar__label">Creación</span>
        <span class="tul-detailbar__value"><?= esc($tul_createdAt) ?></span>
    </div>
    <div class="tul-detailbar__item">
        <span class="tul-detailbar__label">Inicio</span>
        <span class="tul-detailbar__value"><?= esc($tul_startedAt) ?></span>
    </div>
    <div class="tul-detailbar__item tul-detailbar__item--phase">
        <span class="tul-detailbar__label">Fase</span>
        <span class="tul-detailbar__value tul-detailbar__value--phase">
            <span class="tul-phase-dot <?= $tul_faseActual >= 1 ? 'is-active' : '' ?>"></span>
            <span class="tul-phase-dot <?= $tul_faseActual >= 2 ? 'is-active' : '' ?>"></span>
            <?= esc($tul_faseLabel) ?>
        </span>
    </div>
</div>

<?php
$tul_clienteMode = $viewData['tulClienteMode'] ?? 'light';
?>
<div class="tul-container">
    <!-- Fase Operativa: Pasos 1-3 (expandidos, solo lectura) -->
    <?= view('deskapp/tramite_unified/_step1_row', $viewData) ?>
    <?= view('deskapp/tramite_unified/_step2_row', $viewData) ?>
    <?= view('deskapp/tramite_unified/_step3_row', $viewData) ?>

    <?php if ($tul_clienteMode === 'full'): ?>
    <!-- Paso 5 — Cobro y cierre (solo visible para Cliente Full) -->
    <script>document.addEventListener('DOMContentLoaded',function(){var s=document.querySelector('[data-step-row="5"]');if(s){s.classList.add('is-expanded');var b=s.querySelector('[data-accordion-body]');if(b)b.removeAttribute('aria-hidden');}});</script>
    <?php
    // Pass tulFinanceLocked=false so step 5 renders expanded for the client
    $step5ViewData = array_merge($viewData, ['tulFinanceLocked' => false, 'tulFinanceLockReason' => '']);
    echo view('deskapp/tramite_unified/_step5_row', $step5ViewData);
    ?>
    <?php endif; ?>
</div>

<!-- Document preview lightbox (shared across all steps) -->
<div class="tul-lightbox" id="tulLightbox" role="dialog" aria-modal="true" aria-label="Vista previa del documento">
    <div class="tul-lightbox__dialog">
        <div class="tul-lightbox__header">
            <span class="tul-lightbox__title" id="tulLightboxTitle"></span>
            <div class="tul-lightbox__actions">
                <a class="tul-lightbox__btn tul-lightbox__btn--download" id="tulLightboxDownload" href="#" download>
                    ⬇ Descargar
                </a>
                <button class="tul-lightbox__btn tul-lightbox__btn--close" id="tulLightboxClose" type="button">
                    ✕ Cerrar
                </button>
            </div>
        </div>
        <div class="tul-lightbox__body" id="tulLightboxBody">
        </div>
    </div>
</div>

<script src="<?= base_url('/public/assets/src/js/tramite_unified.js') ?>?v=20250619q"></script>

<?= $this->endSection() ?>
