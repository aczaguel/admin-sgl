<?php
/**
 * Tramite Unified Layout - Orchestrator
 *
 * Vista principal que incluye los 5 step partials en layout de filas.
 * Pasos 1-3: fase operativa (expandidos).
 * Pasos 4-5: fase financiera (colapsados en acordeón).
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
$tul_asociados = [];
foreach (($tul_t['associated_service_rows'] ?? []) as $tul_row) {
    $lbl = trim((string) ($tul_row['label'] ?? ''));
    if ($lbl !== '' && stripos($lbl, 'sin asociados') === false) {
        $tul_asociados[] = $lbl;
    }
}
$tul_asociadosText = $tul_asociados !== [] ? implode(', ', $tul_asociados) : 'Sin asociados';

// --- Gates de revelado progresivo (status-driven) ---
// La visibilidad de cada fase se deriva del estatus real del trámite via la
// columna tra_status.new_format_step (expuesta por el controller). Esto permite
// cargar trámites históricos hacia atrás: si el estatus ya avanzó a un paso, ese
// paso (y los previos) YA ocurrieron en el flujo viejo y no deben re-bloquearse
// por la maquinaria nueva de aprobación/evidencias que esos trámites nunca tuvieron.
$tul_step1Complete = !empty($tul_t['step1_complete']);
$tul_step2Complete = !empty($tul_t['step2_complete']);
$tul_step3Complete = !empty($tul_t['step3_complete']);
$tul_hasTramiteRecibido = !empty($tul_t['has_tramite_recibido']);
$tul_hasAcuseRecibo = !empty($tul_t['has_acuse_recibo']);

$tul_traStatusId = (int) ($tul_t['tra_status_id'] ?? 0);

// new_format_step: 1 general, 2 gestión/derechos, 3 evidencias finales,
// 4 pago a gestor, 5 cobro a cliente, 10 concluido/cancelado (terminal).
// Se hace clamp a 5 para estados terminales, y default 1 si viene NULL/0.
$tul_newFormatStep = (int) ($tul_t['new_format_step'] ?? 0);
$tul_stepActual = $tul_newFormatStep >= 1 ? min($tul_newFormatStep, 5) : 1;

// El trámite ya alcanzó/rebasó la fase financiera cuando su estatus está en
// pago a gestor (paso 4) o posterior. El estatus ES la prueba de que la
// aprobación previa ya ocurrió en el flujo original.
$tul_isAprobado = $tul_stepActual >= 4;

// Evidencias finales (paso 3): visibles cuando el trámite alcanzó el paso 3 o
// posterior. Ya no dependen del botón "Aprobar trámite" del flujo nuevo.
$tul_gateEvidencias = $tul_stepActual >= 3;
// Fase financiera (pasos 4-5): visible cuando el estatus alcanzó el paso 4 o
// posterior. Ya no exige los registros de evidencias (tramite_recibido/acuse),
// que son parte de la maquinaria nueva inexistente en trámites históricos.
$tul_gateFinanciera = $tul_stepActual >= 4;

$viewData['tulStep3Locked'] = !$tul_gateEvidencias;
$viewData['tulStep3LockReason'] = 'Las evidencias finales se habilitan cuando el trámite avanza a esta etapa.';
$viewData['tulFinanceLocked'] = !$tul_gateFinanciera;
$viewData['tulFinanceLockReason'] = 'La fase financiera (Pago a Gestor y Cobro a Cliente) se habilita cuando el trámite avanza a esa etapa.';

// Fase actual para el indicador de progreso
if ($tul_gateFinanciera) {
    $tul_faseActual = 3;
    $tul_faseLabel = 'Fase financiera';
} elseif ($tul_gateEvidencias) {
    $tul_faseActual = 2;
    $tul_faseLabel = 'Evidencias finales';
} else {
    $tul_faseActual = 1;
    $tul_faseLabel = 'Operación base';
}

// --- Acciones globales: Cancelar / Concluir ---
if (!function_exists('has_permission')) {
    helper('permissions');
}
$tul_perms = session()->get('user_permissions');
$tul_roles = session()->get('user_roles');
$tul_isLockedTramite = in_array($tul_traStatusId, [20, 21], true);
$tul_canCancel = has_permission('important_cancelar_tramite', $tul_perms, $tul_roles) && !$tul_isLockedTramite;
$tul_canConclude = has_permission('important_concluir_tramite', $tul_perms, $tul_roles) && $tul_traStatusId === 28;
?>

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
    <div class="tul-detailbar__item tul-detailbar__item--phase">
        <span class="tul-detailbar__label">Fase</span>
        <span class="tul-detailbar__value tul-detailbar__value--phase">
            <span class="tul-phase-dot <?= $tul_faseActual >= 1 ? 'is-active' : '' ?>"></span>
            <span class="tul-phase-dot <?= $tul_faseActual >= 2 ? 'is-active' : '' ?>"></span>
            <span class="tul-phase-dot <?= $tul_faseActual >= 3 ? 'is-active' : '' ?>"></span>
            <?= esc($tul_faseLabel) ?>
        </span>
    </div>
</div>

<div class="tul-container">
    <!-- Fase Operativa: Pasos 1-3 (expandidos) -->
    <?= view('deskapp/tramite_unified/_step1_row', $viewData) ?>
    <?= view('deskapp/tramite_unified/_step2_row', $viewData) ?>
    <?= view('deskapp/tramite_unified/_step3_row', $viewData) ?>

    <!-- Divisor de fase -->
    <div class="tul-phase-divider">
        <span class="tul-phase-divider__label">Fase Financiera</span>
    </div>

    <!-- Fase Financiera: Pasos 4-5 (colapsados en acordeón) -->
    <?= view('deskapp/tramite_unified/_step4_row', $viewData) ?>
    <?= view('deskapp/tramite_unified/_step5_row', $viewData) ?>

    <?php if ($tul_canCancel || $tul_canConclude): ?>
        <div class="tul-actions-bar">
            <div class="tul-actions-bar__info">
                <span class="tul-actions-bar__label">Acciones del trámite</span>
                <span class="tul-actions-bar__hint">Estas acciones cambian el estatus final del expediente.</span>
            </div>
            <div class="tul-actions-bar__buttons">
                <?php if ($tul_canCancel): ?>
                    <button type="button"
                            class="tul-btn tul-btn--danger"
                            data-tul-cancel-tramite
                            data-tul-tramite-id="<?= (int) $tul_id ?>"
                            data-tul-url="<?= base_url('/deskapp/tramites/cancelar_tramite') ?>">
                        Cancelar Trámite
                    </button>
                <?php endif; ?>
                <?php if ($tul_canConclude): ?>
                    <button type="button"
                            class="tul-btn tul-btn--approve"
                            data-tul-conclude-tramite
                            data-tul-tramite-id="<?= (int) $tul_id ?>"
                            data-tul-url="<?= base_url('/deskapp/tramites/autorizar') ?>">
                        Concluir Trámite
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?= base_url('/public/assets/src/js/tramite_unified.js') ?>?v=20250619q"></script>

<?= $this->endSection() ?>
