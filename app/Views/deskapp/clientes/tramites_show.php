<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>
<link rel="stylesheet" href="<?= base_url('/public/assets/src/styles/clientes_tramites_show.css?v=20260606-1') ?>">

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    $formatDateEs = static function ($value, bool $withTime = true): string {
        if (empty($value)) {
            return 'Pendiente';
        }
        $ts = strtotime((string) $value);
        if (!$ts) {
            return (string) $value;
        }

        $months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $mIndex = (int) date('n', $ts);
        $mon = $months[max(1, min(12, $mIndex)) - 1];
        $datePart = date('j', $ts) . ' ' . $mon . ' ' . date('Y', $ts);
        if (!$withTime) {
            return $datePart;
        }
        return $datePart . ', ' . date('H:i', $ts);
    };

    $formatDate = static function ($value) use ($formatDateEs) {
        return $formatDateEs($value, true);
    };
    $formatDateShort = static function ($value) use ($formatDateEs) {
        return $formatDateEs($value, true);
    };
    $formatTimeSeconds = static function ($value) {
        if (empty($value)) {
            return '—';
        }
        $ts = strtotime($value);
        if (!$ts) {
            return '—';
        }
        return date('H:i:s', $ts);
    };
    $minuteKey = static function ($value): ?string {
        if (empty($value)) {
            return null;
        }
        $ts = strtotime($value);
        if (!$ts) {
            return null;
        }
        return date('Y-m-d H:i', $ts);
    };
    $formatFieldLabel = static function (?string $field): string {
        $map = [
            'tra_status_id' => 'Estatus',
            'cobro_status_id' => 'Estatus de cobro',
            'numero_factura' => 'Número de factura',
            'numero_refactura' => 'Número de refactura',
            'gestor_id' => 'Gestor',
            'empresa_gestora_id' => 'Empresa gestora',
            'observaciones' => 'Observaciones',
            'finished_at' => 'Fecha de cierre',
            'started_at' => 'Fecha de inicio',
        ];
        if (!$field) {
            return 'Actualización';
        }
        return $map[$field] ?? ucwords(str_replace('_', ' ', $field));
    };
    $normalizeAuditValue = static function ($value) use ($formatDateEs) {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return '—';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}(?:[T\s]\d{2}:\d{2}(?::\d{2})?)?$/', $text)) {
            $ts = strtotime($text);
            if ($ts) {
                return $formatDateEs($text, true);
            }
        }
        return $text;
    };
    $actionLabel = static function (?string $action): string {
        $map = [
            'update' => 'Actualización',
            'status_change' => 'Cambio de estatus',
            'upload' => 'Carga de archivo',
            'insert' => 'Alta',
            'delete' => 'Baja',
        ];
        $key = strtolower(trim((string) $action));
        return $map[$key] ?? ucfirst($key ?: 'Evento');
    };
    $formatTileValue = static function ($value): array {
        $text = trim((string) ($value ?? ''));
        if ($text === '' || strtoupper($text) === 'N/A') {
            return ['N/A', true];
        }
        return [$text, false];
    };
    $folioValue = $tramite['folio'] ?? 'N/A';
    $contratoValue = $tramite['contrato'] ?? 'N/A';
    $tramiteIdValue = isset($tramite['id']) && $tramite['id'] !== '' ? (string) $tramite['id'] : 'N/A';

    $semaforoFromTramite = static function (array $tramite): array {
        $startedAt = $tramite['started_at'] ?? null;
        if (empty($startedAt) || !strtotime((string) $startedAt)) {
            return [
                'scope' => '—',
                'days' => null,
                'label' => 'Sin iniciar',
                'class' => 'is-neutral',
            ];
        }

        $startTs = strtotime((string) $startedAt);
        $days = (int) floor((time() - $startTs) / 86400);
        if ($days < 0) {
            $days = 0;
        }

        $entidadRaw = (string) ($tramite['entidad'] ?? '');
        $entidadNorm = strtoupper(trim($entidadRaw));
        if ($entidadNorm !== '') {
            $trans = @iconv('UTF-8', 'ASCII//TRANSLIT', $entidadNorm);
            if (is_string($trans) && $trans !== '') {
                $entidadNorm = $trans;
            }
            $entidadNorm = str_replace(['.', ',', '  '], [' ', ' ', ' '], $entidadNorm);
            $entidadNorm = trim(preg_replace('/\s+/', ' ', $entidadNorm));
        }

        $localSet = [
            'CIUDAD DE MEXICO',
            'CIUDAD DE MEXICO',
            'ESTADO DE MEXICO',
            'CDMX',
            'EDOMEX',
            'EDO MEX',
            'EDO. MEX',
            'EDO MEXICO',
        ];
        $isLocal = in_array($entidadNorm, $localSet, true);
        $scope = $isLocal ? 'Local' : 'Foráneo';

        if ($isLocal) {
            if ($days < 5) return ['scope' => $scope, 'days' => $days, 'label' => 'Verde', 'class' => 'is-green'];
            if ($days <= 7) return ['scope' => $scope, 'days' => $days, 'label' => 'Amarillo', 'class' => 'is-yellow'];
            if ($days <= 11) return ['scope' => $scope, 'days' => $days, 'label' => 'Rojo', 'class' => 'is-red'];
            return ['scope' => $scope, 'days' => $days, 'label' => 'Violeta', 'class' => 'is-violet'];
        }

        if ($days < 10) return ['scope' => $scope, 'days' => $days, 'label' => 'Verde', 'class' => 'is-green'];
        if ($days <= 12) return ['scope' => $scope, 'days' => $days, 'label' => 'Amarillo', 'class' => 'is-yellow'];
        if ($days <= 15) return ['scope' => $scope, 'days' => $days, 'label' => 'Rojo', 'class' => 'is-red'];
        return ['scope' => $scope, 'days' => $days, 'label' => 'Violeta', 'class' => 'is-violet'];
    };

    $semaforo = $semaforoFromTramite($tramite ?? []);
?>

<div class="main-container client-tramite-show">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="hero mb-20">
                <div class="d-flex flex-wrap justify-content-between align-items-center hero-head">
                    <div>
                        <h4 class="hero-title">Resumen inteligente del trámite</h4>
                        <div class="hero-subtitle">Seguimiento operativo con línea de tiempo y bitácora real.</div>
                        <div class="hero-badges">
                            <span class="hero-badge hero-badge-featured">
                                <i class="fa fa-fingerprint"></i>
                                <span class="hero-badge-label">Id del trámite</span>
                                <span class="hero-badge-value">#<?= esc($tramiteIdValue) ?></span>
                            </span>
                            <span class="hero-badge"><i class="fa fa-hashtag"></i> <?= esc($folioValue) ?></span>
                            <span class="hero-badge"><i class="fa fa-file-signature"></i> <?= esc($contratoValue) ?></span>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap justify-content-end hero-action-row">
                            <div class="hero-status"><i class="fa fa-circle"></i> <?= esc($tramite['tra_status'] ?? 'N/A') ?></div>
                            <div class="hero-semaforo <?= esc($semaforo['class'] ?? 'is-neutral') ?>" title="Semáforo calculado desde el inicio real">
                                <i class="fa fa-traffic-light"></i>
                                <?= esc(($semaforo['label'] ?? 'Sin iniciar') . ' · ' . ($semaforo['scope'] ?? '—')) ?>
                                <?php if (($semaforo['days'] ?? null) !== null): ?>
                                    <span class="hero-days">· <?= esc((string) $semaforo['days']) ?> días</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hero-grid hero-layered">
                    <div class="hero-tile"><span>Inicio</span><strong><?= esc($formatDate($tramite['started_at'] ?? null)) ?></strong></div>
                    <div class="hero-tile"><span>Tipo de trámite</span><strong><?= esc($tramite['tipo_tramite'] ?? 'N/A') ?></strong></div>
                    <div class="hero-tile"><span>Cliente</span><strong><?= esc($tramite['cliente'] ?? 'N/A') ?></strong></div>
                    <div class="hero-tile"><span>Gestor</span><strong><?= esc($tramite['gestor'] ?? 'Sin asignar') ?></strong></div>
                    <div class="hero-tile"><span>Empresa gestora</span><strong><?= esc($tramite['empresa_gestora'] ?? 'Sin asignar') ?></strong></div>
                </div>
                <div class="mt-12 text-right hero-layered">
                    <a class="btn btn-outline-light" href="<?= site_url('/deskapp/clientes/tramites') ?>">
                        <i class="fa fa-arrow-left"></i> Volver al listado
                    </a>
                </div>
            </div>

            <div id="quick-access" class="quick-actions-ribbon">
                <div class="ribbon-title">
                    <i class="fas fa-bolt"></i>
                    <span>Acciones Rápidas</span>
                </div>
                <div class="ribbon-buttons">

                    <!-- Documentos de derechos -->
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-documentos-cliente">
                        <div class="ribbon-icon sgl-ribbon-icon--documentos">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <span class="ribbon-label">Documentos</span>
                    </button>

                    <!-- Bitácora / notas del proceso -->
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-bitacora-cliente">
                        <div class="ribbon-icon sgl-ribbon-icon--bitacora">
                            <i class="fas fa-history"></i>
                        </div>
                        <span class="ribbon-label">Bitácora</span>
                    </button>

                    <!-- Pagos de derecho -->
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pagos-derecho-cliente">
                        <div class="ribbon-icon sgl-ribbon-icon--pagos-derecho">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <span class="ribbon-label">Pagos Derecho</span>
                    </button>

                    <!-- Evidencias finales -->
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-evidencias-finales-cliente">
                        <div class="ribbon-icon sgl-ribbon-icon--evidencias-finales">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <span class="ribbon-label">Evidencias Finales</span>
                    </button>

                    <!-- Cobros al cliente -->
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-cobro-cliente-detalle">
                        <div class="ribbon-icon sgl-ribbon-icon--cobro-cliente">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <span class="ribbon-label">Cobros Cliente</span>
                    </button>

                </div>
            </div>

              <!-- ═══════════════════════════════════════════════════
                  WIZARD DE PASOS 1–5 — Vista cliente (solo lectura)
                  ═══════════════════════════════════════════════════ -->
            <?php
                /* ─── Cálculo de completitud de cada paso ─── */

                // Paso 1: siempre completo (el trámite existe)
                $_wiz1Done = true;

                // Paso 2: gestor asignado
                $_wiz2Done = !empty($tramite['gestor_id']) && ((int)($tramite['gestor_id'] ?? 0)) > 0;

                // Paso 3: pago de derechos (documentos subidos o línea de captura registrada)
                $_wiz3Done = ($doc_status_docs_uploaded ?? 0) > 0 || !empty($tramite['linea_captura']) || !empty($tra_evidencias);

                // Paso 4: evidencias finales (tramite_recibido + acuse_recibo_cliente)
                $_pgEvidencias  = [];
                $_wizHasTramRec = false;
                $_wizHasAcuse   = false;
                foreach ($pago_gestor_docs ?? [] as $_pg) {
                    $_pgTipo = trim((string)($_pg['comprobante_final'] ?? ''));
                    if (in_array($_pgTipo, ['tramite_recibido', 'acuse_recibo_cliente'], true)) {
                        $_pgEvidencias[] = $_pg;
                        if ($_pgTipo === 'tramite_recibido')     $_wizHasTramRec = true;
                        if ($_pgTipo === 'acuse_recibo_cliente') $_wizHasAcuse   = true;
                    }
                }
                $_wiz4Done = $_wizHasTramRec || $_wizHasAcuse || !empty($_pgEvidencias);

                // Paso 5: cobro y cierre (tramite finalizado)
                $_wiz5Done = !empty($tramite['finished_at'])
                    || (!empty($tramite['cobro_status_id']) && ((int)($tramite['cobro_status_id'] ?? 0)) > 1);

                // Paso actual = primer paso sin completar
                $_wizDoneArr = [$_wiz1Done, $_wiz2Done, $_wiz3Done, $_wiz4Done, $_wiz5Done];
                $_wizCurrent = 0;
                foreach ($_wizDoneArr as $_wi => $_wd) {
                    if (!$_wd) { $_wizCurrent = $_wi + 1; break; }
                }
                if ($_wizCurrent === 0) $_wizCurrent = 6; // todos completos

                // Retorna la clase CSS del paso según su estado
                $__stepCls = static function(int $n, array $done, int $cur): string {
                    if ($done[$n - 1]) return 'is-done';
                    if ($n === $cur)   return 'is-current';
                    return 'is-locked';
                };

                // Retorna la etiqueta del chip
                $__stepChip = static function(string $cls): string {
                    return ['is-done' => 'Completado', 'is-current' => 'En progreso', 'is-locked' => 'Pendiente'][$cls] ?? 'Pendiente';
                };

                // Etiqueta del tipo de doc de pago_gestor
                $__pgDocType = static function(?string $t): string {
                    $map = [
                        'tramite_recibido'    => 'Trámite entregado',
                        'acuse_recibo_cliente' => 'Acuse de recibo',
                        'otro'                => 'Otro',
                        'factura_gestor'      => 'Factura del gestor',
                        'comprobante_pago'    => 'Comprobante de pago',
                    ];
                    $k = trim((string)($t ?? ''));
                    return $map[$k] ?? ($k !== '' ? $k : 'Documento');
                };
            ?>
            <div class="cs-wizard">
                <div class="cs-wizard-head">
                    <i class="fas fa-route"></i> Progreso del trámite — Pasos 1 al 5
                    <?php if ($_wizCurrent === 6): ?>
                        <span style="font-size:11px;background:rgba(34,197,94,0.15);color:#166534;border:1px solid rgba(34,197,94,0.25);border-radius:999px;padding:3px 10px;">
                            <i class="fas fa-check-circle"></i> Todos los pasos completados
                        </span>
                    <?php else: ?>
                        <span style="font-size:11px;background:rgba(245,158,11,0.12);color:#92400e;border:1px solid rgba(245,158,11,0.22);border-radius:999px;padding:3px 10px;">
                            Paso actual: <?= esc((string)$_wizCurrent) ?> de 5
                        </span>
                    <?php endif; ?>
                </div>

                <?php
                    $_s1cls = $__stepCls(1, $_wizDoneArr, $_wizCurrent);
                    $_s2cls = $__stepCls(2, $_wizDoneArr, $_wizCurrent);
                    $_s3cls = $__stepCls(3, $_wizDoneArr, $_wizCurrent);
                    $_s4cls = $__stepCls(4, $_wizDoneArr, $_wizCurrent);
                    $_s5cls = $__stepCls(5, $_wizDoneArr, $_wizCurrent);
                ?>

                <!-- ── Paso 1: Datos del trámite ── -->
                <div class="cs-step step-1 <?= esc($_s1cls) ?>">
                    <button type="button" class="cs-step-ribbon" data-toggle="collapse" data-target="#csWizStep1" aria-expanded="true" aria-controls="csWizStep1">
                        <span class="cs-step-num">1</span>
                        <span class="cs-step-text">
                            <span class="cs-step-label">Paso 1</span>
                            <span class="cs-step-title">Datos del trámite</span>
                        </span>
                        <span class="cs-step-chip"><?= esc($__stepChip($_s1cls)) ?></span>
                        <i class="fas fa-chevron-down cs-step-chevron"></i>
                    </button>
                    <div id="csWizStep1" class="collapse show cs-step-body">
                        <div class="info-grid mt-10">
                            <?php
                                $_s1rows = [
                                    ['Tipo de trámite',  $tramite['tipo_tramite'] ?? null],
                                    ['Folio',            $tramite['folio']        ?? null],
                                    ['Contrato',         $tramite['contrato']     ?? null],
                                    ['Unidad',           $tramite['unidad']       ?? null],
                                    ['Serie / VIN',      $tramite['serie']        ?? null],
                                    ['Placas',           $tramite['placas']       ?? null],
                                    ['Entidad',          $tramite['entidad']      ?? null],
                                    ['Municipio',        $tramite['municipio']    ?? null],
                                    ['Fecha de registro', $formatDate($tramite['created_at'] ?? null)],
                                ];
                                foreach ($_s1rows as [$_lbl, $_val]):
                                    [$_v, $_empty] = $formatTileValue($_val);
                            ?>
                                <div class="info-tile">
                                    <span><?= esc($_lbl) ?></span>
                                    <strong class="<?= $_empty ? 'is-empty' : '' ?>"><?= esc($_v) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- ── Paso 2: Asignación de gestor ── -->
                <div class="cs-step step-2 <?= esc($_s2cls) ?>">
                    <button type="button" class="cs-step-ribbon" data-toggle="collapse" data-target="#csWizStep2" aria-expanded="<?= $_s2cls !== 'is-locked' ? 'true' : 'false' ?>" aria-controls="csWizStep2">
                        <span class="cs-step-num">2</span>
                        <span class="cs-step-text">
                            <span class="cs-step-label">Paso 2</span>
                            <span class="cs-step-title">Asignación de gestor</span>
                        </span>
                        <span class="cs-step-chip"><?= esc($__stepChip($_s2cls)) ?></span>
                        <i class="fas fa-chevron-down cs-step-chevron"></i>
                    </button>
                    <div id="csWizStep2" class="collapse <?= $_s2cls !== 'is-locked' ? 'show' : '' ?> cs-step-body">
                        <div class="info-grid mt-10">
                            <?php
                                $_gestor   = trim((string)($tramite['gestor']          ?? ''));
                                $_empresa  = trim((string)($tramite['empresa_gestora'] ?? ''));
                                $_s2rows = [
                                    ['Gestor asignado',  $_gestor  !== '' ? $_gestor  : null],
                                    ['Empresa gestora',  $_empresa !== '' ? $_empresa : null],
                                    ['Inicio de proceso', $formatDate($tramite['started_at'] ?? null)],
                                    ['Estatus del paso', $_s2cls === 'is-done' ? 'Asignado' : 'Pendiente de asignación'],
                                ];
                                foreach ($_s2rows as [$_lbl, $_val]):
                                    [$_v, $_empty] = $formatTileValue($_val);
                            ?>
                                <div class="info-tile">
                                    <span><?= esc($_lbl) ?></span>
                                    <strong class="<?= $_empty ? 'is-empty' : '' ?>"><?= esc($_v) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- ── Paso 3: Pago de derechos y documentos ── -->
                <div class="cs-step step-3 <?= esc($_s3cls) ?>">
                    <button type="button" class="cs-step-ribbon" data-toggle="collapse" data-target="#csWizStep3" aria-expanded="<?= $_s3cls !== 'is-locked' ? 'true' : 'false' ?>" aria-controls="csWizStep3">
                        <span class="cs-step-num">3</span>
                        <span class="cs-step-text">
                            <span class="cs-step-label">Paso 3</span>
                            <span class="cs-step-title">Pago de derechos y documentos</span>
                        </span>
                        <span class="cs-step-chip"><?= esc($__stepChip($_s3cls)) ?></span>
                        <i class="fas fa-chevron-down cs-step-chevron"></i>
                    </button>
                    <div id="csWizStep3" class="collapse <?= $_s3cls !== 'is-locked' ? 'show' : '' ?> cs-step-body">
                        <?php if (!empty($tramite['linea_captura'])): ?>
                            <div class="info-grid mt-10">
                                <div class="info-tile">
                                    <span>Línea de captura</span>
                                    <strong><?= esc($tramite['linea_captura']) ?></strong>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($doc_status_docs ?? [])): ?>
                            <div class="cs-step-docs">
                                <div class="cs-step-docs-title">
                                    <i class="fas fa-paperclip"></i>
                                    Documentos de derechos
                                    <span style="font-weight:700;color:var(--cs-ink);">(<?= esc((string)($doc_status_docs_uploaded ?? 0)) ?>/<?= esc((string)($doc_status_docs_total ?? 0)) ?> subidos)</span>
                                </div>
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                    <?php foreach ($doc_status_docs as $_d3):
                                        $_url3    = (string)($_d3['url']             ?? '');
                                        $_name3   = trim((string)($_d3['documento_nombre'] ?? ''));
                                        $_file3   = basename((string)($_d3['file']   ?? ''));
                                        $_status3 = trim((string)($_d3['status_nombre'] ?? ''));
                                        $_label3  = $_name3 !== '' ? $_name3 : 'Documento';
                                        $_ext3    = strtolower(pathinfo($_file3, PATHINFO_EXTENSION));
                                        $_isImg3  = in_array($_ext3, ['jpg','jpeg','png','gif','webp'], true);
                                        $_icoC3   = $_isImg3 ? 'fas fa-image' : 'fas fa-file-alt';
                                        $_icoBg3  = $_url3 !== '' ? '#0f766e' : '#94a3b8';
                                    ?>
                                        <?php if ($_url3 !== ''): ?>
                                            <a class="cs-doc-pill" href="<?= esc($_url3) ?>" target="_blank" rel="noopener" title="<?= esc($_label3 . ($_file3 !== '' ? ' — ' . $_file3 : '')) ?>">
                                                <span class="cs-doc-icon" style="background:<?= esc($_icoBg3) ?>"><i class="<?= esc($_icoC3) ?>"></i></span>
                                                <span>
                                                    <span style="display:block;font-size:10px;color:#6b7280;"><?= esc($_status3 !== '' ? $_status3 : 'Subido') ?></span>
                                                    <?= esc($_label3) ?>
                                                </span>
                                            </a>
                                        <?php else: ?>
                                            <span class="cs-doc-pill is-pending" title="<?= esc($_label3) ?>">
                                                <span class="cs-doc-icon" style="background:<?= esc($_icoBg3) ?>"><i class="<?= esc($_icoC3) ?>"></i></span>
                                                <span>
                                                    <span style="display:block;font-size:10px;color:#6b7280;">Pendiente</span>
                                                    <?= esc($_label3) ?>
                                                </span>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state mt-10">Sin documentos de derechos registrados aún.</div>
                        <?php endif; ?>

                        <!-- Notas del proceso (tra_evidencias) -->
                        <?php if (!empty($tra_evidencias)): ?>
                            <div class="cs-step-docs" style="margin-top:20px;">
                                <div class="cs-step-docs-title"><i class="fas fa-clipboard-list"></i> Registro de seguimiento del proceso</div>
                                <div style="display:flex;flex-direction:column;gap:8px;margin-top:10px;">
                                    <?php foreach ($tra_evidencias as $_ev):
                                        $_evDate  = $_ev['created_at'] ?? '';
                                        $_evNote  = trim((string)($_ev['comentario'] ?? ''));
                                        $_evCosto = (int)($_ev['costo'] ?? 0);
                                        $_evUrl   = (string)($_ev['url'] ?? '');
                                        $_evFile  = basename((string)($_ev['file'] ?? ''));
                                        $_evFolio = trim((string)($_ev['folio_tramite'] ?? ''));
                                    ?>
                                        <div style="background:#f8fafc;border-left:3px solid #7c3aed;border-radius:6px;padding:10px 12px;font-size:13px;">
                                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                                                <span style="color:#7c3aed;font-weight:600;">
                                                    <i class="fas fa-circle" style="font-size:7px;vertical-align:middle;margin-right:4px;"></i>
                                                    <?= esc(!empty($_evDate) ? $formatDateShort($_evDate) : '—') ?>
                                                    <?php if ($_evFolio !== ''): ?>
                                                        <span style="font-weight:400;color:#64748b;margin-left:6px;">Folio: <?= esc($_evFolio) ?></span>
                                                    <?php endif; ?>
                                                </span>
                                                <?php if ($_evCosto > 0): ?>
                                                    <span style="background:#ede9fe;color:#7c3aed;border-radius:20px;padding:2px 8px;font-size:12px;font-weight:600;">
                                                        $<?= esc(number_format($_evCosto, 0)) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($_evNote !== ''): ?>
                                                <div style="margin-top:5px;color:#374151;line-height:1.5;"><?= esc($_evNote) ?></div>
                                            <?php endif; ?>
                                            <?php if ($_evUrl !== ''): ?>
                                                <div style="margin-top:6px;">
                                                    <a href="<?= esc($_evUrl) ?>" target="_blank" rel="noopener"
                                                       style="font-size:12px;color:#2563eb;text-decoration:none;">
                                                        <i class="fas fa-paperclip"></i> <?= esc($_evFile !== '' ? $_evFile : 'Ver archivo') ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Paso 4: Evidencias finales de entrega ── -->
                <div class="cs-step step-4 <?= esc($_s4cls) ?>">
                    <button type="button" class="cs-step-ribbon" data-toggle="collapse" data-target="#csWizStep4" aria-expanded="<?= $_s4cls !== 'is-locked' ? 'true' : 'false' ?>" aria-controls="csWizStep4">
                        <span class="cs-step-num">4</span>
                        <span class="cs-step-text">
                            <span class="cs-step-label">Paso 4</span>
                            <span class="cs-step-title">Evidencias finales de entrega</span>
                        </span>
                        <span class="cs-step-chip"><?= esc($__stepChip($_s4cls)) ?></span>
                        <i class="fas fa-chevron-down cs-step-chevron"></i>
                    </button>
                    <div id="csWizStep4" class="collapse <?= $_s4cls !== 'is-locked' ? 'show' : '' ?> cs-step-body">
                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
                            <span class="cs-confirm-chip <?= $_wizHasTramRec ? 'ok' : 'pending' ?>">
                                <i class="fas <?= $_wizHasTramRec ? 'fa-check-circle' : 'fa-clock' ?>"></i>
                                Trámite entregado por gestor
                            </span>
                            <span class="cs-confirm-chip <?= $_wizHasAcuse ? 'ok' : 'pending' ?>">
                                <i class="fas <?= $_wizHasAcuse ? 'fa-check-circle' : 'fa-clock' ?>"></i>
                                Acuse de recibo del cliente
                            </span>
                        </div>
                        <?php if (!empty($_pgEvidencias)): ?>
                            <div class="cs-step-docs">
                                <div class="cs-step-docs-title"><i class="fas fa-paperclip"></i> Documentos de evidencia</div>
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                    <?php foreach ($_pgEvidencias as $_d4):
                                        $_url4  = (string)($_d4['url']              ?? '');
                                        $_file4 = basename((string)($_d4['file']    ?? ''));
                                        $_tipo4 = $__pgDocType($_d4['comprobante_final'] ?? null);
                                        $_ext4  = strtolower(pathinfo($_file4, PATHINFO_EXTENSION));
                                        $_isImg4 = in_array($_ext4, ['jpg','jpeg','png','gif','webp'], true);
                                        $_icoC4  = $_isImg4 ? 'fas fa-image' : 'fas fa-file-alt';
                                        $_icoBg4 = $_url4 !== '' ? '#7c3aed' : '#94a3b8';
                                    ?>
                                        <?php if ($_url4 !== ''): ?>
                                            <a class="cs-doc-pill" href="<?= esc($_url4) ?>" target="_blank" rel="noopener" title="<?= esc($_tipo4 . ($_file4 !== '' ? ' — ' . $_file4 : '')) ?>">
                                                <span class="cs-doc-icon" style="background:<?= esc($_icoBg4) ?>"><i class="<?= esc($_icoC4) ?>"></i></span>
                                                <span>
                                                    <span style="display:block;font-size:10px;color:#6b7280;"><?= esc($_tipo4) ?></span>
                                                    <?= esc($_file4 !== '' ? $_file4 : 'Documento') ?>
                                                </span>
                                            </a>
                                        <?php else: ?>
                                            <span class="cs-doc-pill is-pending">
                                                <span class="cs-doc-icon" style="background:<?= esc($_icoBg4) ?>"><i class="<?= esc($_icoC4) ?>"></i></span>
                                                <span>
                                                    <span style="display:block;font-size:10px;color:#6b7280;"><?= esc($_tipo4) ?></span>
                                                    Pendiente
                                                </span>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state mt-10">Aún no hay evidencias de entrega registradas.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Paso 5: Cobro y cierre ── -->
                <div class="cs-step step-5 <?= esc($_s5cls) ?>">
                    <button type="button" class="cs-step-ribbon" data-toggle="collapse" data-target="#csWizStep5" aria-expanded="<?= $_s5cls !== 'is-locked' ? 'true' : 'false' ?>" aria-controls="csWizStep5">
                        <span class="cs-step-num">5</span>
                        <span class="cs-step-text">
                            <span class="cs-step-label">Paso 5</span>
                            <span class="cs-step-title">Cobro y cierre del trámite</span>
                        </span>
                        <span class="cs-step-chip"><?= esc($__stepChip($_s5cls)) ?></span>
                        <i class="fas fa-chevron-down cs-step-chevron"></i>
                    </button>
                    <div id="csWizStep5" class="collapse <?= $_s5cls !== 'is-locked' ? 'show' : '' ?> cs-step-body">
                        <div class="info-grid mt-10">
                            <?php
                                $_s6rows = [
                                    ['Estatus de cobro',    $tramite['cobro_status']       ?? null],
                                    ['Número de factura',   $tramite['numero_factura']     ?? null],
                                    ['Número de refactura', $tramite['numero_refactura']   ?? null],
                                    ['Fecha de cierre',     $formatDate($tramite['finished_at'] ?? null)],
                                    ['Total del trámite',   (float)($tramite['costo_total'] ?? 0) > 0
                                                                ? '$' . number_format((float)$tramite['costo_total'], 2)
                                                                : null],
                                ];
                                foreach ($_s6rows as [$_lbl, $_val]):
                                    [$_v, $_empty] = $formatTileValue($_val);
                            ?>
                                <div class="info-tile">
                                    <span><?= esc($_lbl) ?></span>
                                    <strong class="<?= $_empty ? 'is-empty' : '' ?>"><?= esc($_v) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($tra_cobros)): ?>
                            <div class="cs-step-docs" style="margin-top:16px;">
                                <div class="cs-step-docs-title"><i class="fas fa-file-invoice"></i> Documentos de cobro</div>
                                <div class="docs-grid" style="margin-top:10px;">
                                    <?php foreach ($tra_cobros as $_cs6co):
                                        $_cs6Url  = (string)($_cs6co['url']  ?? '');
                                        $_cs6File = basename((string)($_cs6co['file'] ?? ''));
                                        $_cs6Ext  = strtolower(pathinfo($_cs6File, PATHINFO_EXTENSION));
                                        $_cs6IsImg = in_array($_cs6Ext, ['jpg','jpeg','png','gif','webp'], true);
                                        $_cs6IsPdf = $_cs6Ext === 'pdf';
                                        $_cs6IsXml = $_cs6Ext === 'xml';
                                        $_cs6Costo = (int)($_cs6co['costo'] ?? 0);
                                        $_cs6Date  = $_cs6co['created_at'] ?? '';
                                    ?>
                                        <div class="doc-card">
                                            <?php if ($_cs6Url !== ''): ?>
                                                <a href="<?= esc($_cs6Url) ?>" target="_blank" rel="noopener">
                                                    <?php if ($_cs6IsImg): ?>
                                                        <img class="doc-thumb" src="<?= esc($_cs6Url) ?>" alt="<?= esc($_cs6File) ?>">
                                                    <?php elseif ($_cs6IsPdf): ?>
                                                        <div class="doc-thumb d-flex align-items-center justify-content-center"><i class="fas fa-file-pdf" style="font-size:32px;color:#ef4444;"></i></div>
                                                    <?php elseif ($_cs6IsXml): ?>
                                                        <div class="doc-thumb d-flex align-items-center justify-content-center"><i class="fas fa-file-code" style="font-size:32px;color:#f59e0b;"></i></div>
                                                    <?php else: ?>
                                                        <div class="doc-thumb d-flex align-items-center justify-content-center"><i class="far fa-file-alt" style="font-size:32px;color:#6b7280;"></i></div>
                                                    <?php endif; ?>
                                                </a>
                                            <?php else: ?>
                                                <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                    <?php if ($_cs6IsPdf): ?>
                                                        <i class="fas fa-file-pdf" style="font-size:32px;color:#ef4444;"></i>
                                                    <?php elseif ($_cs6IsXml): ?>
                                                        <i class="fas fa-file-code" style="font-size:32px;color:#f59e0b;"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="doc-name" title="<?= esc($_cs6File) ?>"><?= esc($_cs6File !== '' ? $_cs6File : 'Documento') ?></div>
                                            <?php if ($_cs6Costo > 0): ?>
                                                <div class="doc-badge">$<?= esc(number_format($_cs6Costo, 0)) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($_cs6Date)): ?>
                                                <div class="doc-badge" style="color:#64748b;"><?= esc($formatDateShort($_cs6Date)) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /cs-wizard -->

            <div class="modal fade" id="modal-documentos-cliente" tabindex="-1" role="dialog" aria-labelledby="modalDocumentosClienteLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDocumentosClienteLabel"><i class="fas fa-folder-open"></i> Documentos del trámite</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <?php
                                $docs = $doc_status_docs ?? [];
                            ?>

                            <?php if (!empty($docs) && is_array($docs)): ?>
                                <div class="docs-grid">
                                    <?php foreach ($docs as $doc): ?>
                                        <?php
                                            $fileName = (string) ($doc['file'] ?? '');
                                            $url = (string) ($doc['url'] ?? '');
                                            $docName = trim((string) ($doc['documento_nombre'] ?? ''));
                                            $docStatus = trim((string) ($doc['status_nombre'] ?? ''));
                                            $docId = $doc['documento_id'] ?? null;
                                            $ext = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                                            $mainLabel = $docName !== '' ? $docName : ($docId !== null ? ('Documento #' . (string) $docId) : 'Documento');
                                            $badgeStatus = $docStatus !== '' ? $docStatus : ($url !== '' ? 'Subido' : 'Pendiente');
                                            $badge = $mainLabel . ' · ' . $badgeStatus;
                                        ?>
                                        <div class="doc-card">
                                            <?php if ($url !== ''): ?>
                                                <a href="<?= esc($url) ?>" target="_blank" rel="noopener" title="<?= esc($mainLabel) ?><?= $fileName !== '' ? esc(' — ' . $fileName) : '' ?>">
                                                    <?php if ($isImage): ?>
                                                        <img class="doc-thumb" src="<?= esc($url) ?>" alt="<?= esc($mainLabel) ?>">
                                                    <?php else: ?>
                                                        <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                            <i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </a>
                                            <?php else: ?>
                                                <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                    <i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="doc-name" title="<?= esc($mainLabel) ?><?= $fileName !== '' ? esc(' — ' . $fileName) : '' ?>"><?= esc($mainLabel) ?></div>
                                            <?php if ($badge !== ''): ?>
                                                <div class="doc-badge" title="<?= esc($badge) ?>"><?= esc($badge) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">Aún no hay documentos cargados para este trámite.</div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ Modal: Bitácora / Notas del proceso (tra_evidencias) ══ -->
            <div class="modal fade" id="modal-bitacora-cliente" tabindex="-1" role="dialog" aria-labelledby="modalBitacoraClienteLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalBitacoraClienteLabel"><i class="fas fa-history"></i> Bitácora del trámite</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($tra_evidencias)): ?>
                                <div style="display:flex;flex-direction:column;gap:10px;">
                                    <?php foreach ($tra_evidencias as $_bev):
                                        $_bevDate  = $_bev['created_at'] ?? '';
                                        $_bevNote  = trim((string)($_bev['comentario'] ?? ''));
                                        $_bevCosto = (int)($_bev['costo'] ?? 0);
                                        $_bevUrl   = (string)($_bev['url'] ?? '');
                                        $_bevFile  = basename((string)($_bev['file'] ?? ''));
                                        $_bevFolio = trim((string)($_bev['folio_tramite'] ?? ''));
                                    ?>
                                        <div style="background:#f8fafc;border-left:4px solid #7c3aed;border-radius:6px;padding:12px 14px;">
                                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;margin-bottom:6px;">
                                                <span style="font-size:12px;color:#7c3aed;font-weight:600;">
                                                    <i class="fas fa-clock"></i>
                                                    <?= esc(!empty($_bevDate) ? $formatDateShort($_bevDate) : '—') ?>
                                                    <?php if ($_bevFolio !== ''): ?>
                                                        <span style="font-weight:400;color:#64748b;margin-left:8px;">Folio: <?= esc($_bevFolio) ?></span>
                                                    <?php endif; ?>
                                                </span>
                                                <?php if ($_bevCosto > 0): ?>
                                                    <span style="background:#ede9fe;color:#7c3aed;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;">
                                                        $<?= esc(number_format($_bevCosto, 0)) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($_bevNote !== ''): ?>
                                                <p style="margin:0;color:#374151;font-size:13px;line-height:1.5;"><?= esc($_bevNote) ?></p>
                                            <?php endif; ?>
                                            <?php if ($_bevUrl !== ''): ?>
                                                <div style="margin-top:8px;">
                                                    <a href="<?= esc($_bevUrl) ?>" target="_blank" rel="noopener" style="font-size:12px;color:#2563eb;">
                                                        <i class="fas fa-paperclip"></i> <?= esc($_bevFile !== '' ? $_bevFile : 'Ver archivo') ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">Aún no hay registros en la bitácora de este trámite.</div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ Modal: Pagos de Derecho ══ -->
            <div class="modal fade" id="modal-pagos-derecho-cliente" tabindex="-1" role="dialog" aria-labelledby="modalPagosDerechoClienteLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalPagosDerechoClienteLabel"><i class="fas fa-receipt"></i> Pagos de Derecho</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <?php
                                $_lcap = trim((string)($tramite['linea_captura'] ?? ''));
                                $_lmonto = trim((string)($tramite['monto_linea_captura'] ?? ''));
                                $_lvencimiento = trim((string)($tramite['fecha_vencimiento_lc'] ?? ''));
                            ?>
                            <?php if ($_lcap !== ''): ?>
                                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px 16px;margin-bottom:16px;">
                                    <div style="font-size:12px;color:#16a34a;font-weight:600;margin-bottom:4px;"><i class="fas fa-barcode"></i> Línea de captura</div>
                                    <div style="font-size:15px;font-weight:700;letter-spacing:.5px;font-family:monospace;"><?= esc($_lcap) ?></div>
                                    <?php if ($_lmonto !== ''): ?>
                                        <div style="margin-top:6px;font-size:13px;color:#374151;">Monto: <strong>$<?= esc($_lmonto) ?></strong></div>
                                    <?php endif; ?>
                                    <?php if ($_lvencimiento !== ''): ?>
                                        <div style="font-size:13px;color:#374151;">Vencimiento: <strong><?= esc($formatDateShort($_lvencimiento)) ?></strong></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($tra_pago_derechos)): ?>
                                <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">Comprobantes de pago</div>
                                <div style="display:flex;flex-direction:column;gap:8px;">
                                    <?php foreach ($tra_pago_derechos as $_pd):
                                        $_pdUrl   = (string)($_pd['url']       ?? '');
                                        $_pdFile  = basename((string)($_pd['file']  ?? ''));
                                        $_pdNote  = trim((string)($_pd['comentario'] ?? ''));
                                        $_pdCosto = (int)($_pd['costo'] ?? 0);
                                        $_pdDate  = $_pd['created_at'] ?? '';
                                        $_pdExt   = strtolower(pathinfo($_pdFile, PATHINFO_EXTENSION));
                                        $_pdIsImg = in_array($_pdExt, ['jpg','jpeg','png','gif','webp'], true);
                                    ?>
                                        <div style="background:#f8fafc;border-left:3px solid #4facfe;border-radius:6px;padding:10px 12px;font-size:13px;">
                                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;">
                                                <span style="color:#0369a1;font-size:12px;font-weight:600;">
                                                    <i class="fas fa-clock"></i> <?= esc(!empty($_pdDate) ? $formatDateShort($_pdDate) : '—') ?>
                                                </span>
                                                <?php if ($_pdCosto > 0): ?>
                                                    <span style="background:#e0f2fe;color:#0369a1;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;">
                                                        $<?= esc(number_format($_pdCosto, 0)) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($_pdNote !== ''): ?>
                                                <div style="margin-top:5px;color:#374151;"><?= esc($_pdNote) ?></div>
                                            <?php endif; ?>
                                            <?php if ($_pdUrl !== ''): ?>
                                                <div style="margin-top:6px;">
                                                    <a href="<?= esc($_pdUrl) ?>" target="_blank" rel="noopener" style="font-size:12px;color:#2563eb;">
                                                        <i class="fas fa-<?= $_pdIsImg ? 'image' : 'file-alt' ?>"></i> <?= esc($_pdFile !== '' ? $_pdFile : 'Ver comprobante') ?>
                                                    </a>
                                                </div>
                                            <?php elseif ($_pdFile !== ''): ?>
                                                <div style="margin-top:6px;font-size:12px;color:#94a3b8;">
                                                    <i class="fas fa-file"></i> <?= esc($_pdFile) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($_lcap === ''): ?>
                                <div class="empty-state">Aún no hay información de pagos de derecho registrada.</div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ Modal: Entrega del Gestor / Pago Gestor ══ -->
            <div class="modal fade" id="modal-entrega-gestor-cliente" tabindex="-1" role="dialog" aria-labelledby="modalEntregaGestorClienteLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEntregaGestorClienteLabel"><i class="fas fa-hand-holding-usd"></i> Documentos del Gestor</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <?php
                                $_pgTypeLbl = [
                                    'tramite_recibido'     => 'Trámite recibido',
                                    'acuse_recibo_cliente' => 'Acuse de recibo',
                                    'otro'                 => 'Documento del gestor',
                                    'factura_gestor'       => 'Factura del gestor',
                                    'comprobante_pago'     => 'Comprobante de pago',
                                ];
                            ?>
                            <?php if (!empty($pago_gestor_docs)): ?>
                                <div class="docs-grid">
                                    <?php foreach ($pago_gestor_docs as $_mgd):
                                        $_mgUrl  = (string)($_mgd['url'] ?? '');
                                        $_mgFile = basename((string)($_mgd['file'] ?? ''));
                                        $_mgTipo = (string)($_mgd['comprobante_final'] ?? '');
                                        $_mgLbl  = $_pgTypeLbl[$_mgTipo] ?? ($_mgTipo !== '' ? ucfirst($_mgTipo) : 'Documento del gestor');
                                        $_mgWhen = $_mgd['created_at'] ?? null;
                                        $_mgExt  = strtolower(pathinfo($_mgFile, PATHINFO_EXTENSION));
                                        $_mgIsImg = in_array($_mgExt, ['jpg','jpeg','png','gif','webp'], true);
                                    ?>
                                        <div class="doc-card">
                                            <?php if ($_mgUrl !== ''): ?>
                                                <a href="<?= esc($_mgUrl) ?>" target="_blank" rel="noopener" title="<?= esc($_mgLbl) ?>">
                                                    <?php if ($_mgIsImg): ?>
                                                        <img class="doc-thumb" src="<?= esc($_mgUrl) ?>" alt="<?= esc($_mgLbl) ?>">
                                                    <?php else: ?>
                                                        <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                            <i class="far fa-file-alt" style="font-size:32px;color:#43b89c;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </a>
                                            <?php else: ?>
                                                <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                    <i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="doc-name"><?= esc($_mgLbl) ?></div>
                                            <?php if ($_mgFile !== ''): ?>
                                                <div class="doc-badge"><?= esc($_mgFile) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($_mgWhen)): ?>
                                                <div class="doc-badge" style="color:#64748b;"><?= esc($formatDateShort($_mgWhen)) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">Aún no hay documentos del gestor registrados.</div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ Modal: Evidencias Finales ══ -->
            <div class="modal fade" id="modal-evidencias-finales-cliente" tabindex="-1" role="dialog" aria-labelledby="modalEvidenciasFinalesClienteLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEvidenciasFinalesClienteLabel"><i class="fas fa-check-double"></i> Evidencias Finales</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <?php
                                $_efList = array_filter($pago_gestor_docs ?? [], static function($_d) {
                                    return in_array(trim((string)($_d['comprobante_final'] ?? '')), ['tramite_recibido','acuse_recibo_cliente'], true);
                                });
                            ?>
                            <?php if (!empty($_efList)): ?>
                                <div class="docs-grid">
                                    <?php foreach ($_efList as $_efd):
                                        $_efUrl  = (string)($_efd['url'] ?? '');
                                        $_efFile = basename((string)($_efd['file'] ?? ''));
                                        $_efTipo = (string)($_efd['comprobante_final'] ?? '');
                                        $_efLbl  = $_efTipo === 'tramite_recibido' ? 'Trámite entregado por gestor' : 'Acuse de recibo del cliente';
                                        $_efWhen = $_efd['created_at'] ?? null;
                                        $_efExt  = strtolower(pathinfo($_efFile, PATHINFO_EXTENSION));
                                        $_efIsImg = in_array($_efExt, ['jpg','jpeg','png','gif','webp'], true);
                                    ?>
                                        <div class="doc-card">
                                            <?php if ($_efUrl !== ''): ?>
                                                <a href="<?= esc($_efUrl) ?>" target="_blank" rel="noopener" title="<?= esc($_efLbl) ?>">
                                                    <?php if ($_efIsImg): ?>
                                                        <img class="doc-thumb" src="<?= esc($_efUrl) ?>" alt="<?= esc($_efLbl) ?>">
                                                    <?php else: ?>
                                                        <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-check-circle" style="font-size:32px;color:#16a34a;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </a>
                                            <?php else: ?>
                                                <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-clock" style="font-size:32px;color:#94a3b8;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="doc-name"><?= esc($_efLbl) ?></div>
                                            <?php if ($_efFile !== ''): ?>
                                                <div class="doc-badge"><?= esc($_efFile) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($_efWhen)): ?>
                                                <div class="doc-badge" style="color:#64748b;"><?= esc($formatDateShort($_efWhen)) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">Las evidencias finales aún no han sido registradas.</div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ Modal: Cobros al Cliente ══ -->
            <div class="modal fade" id="modal-cobro-cliente-detalle" tabindex="-1" role="dialog" aria-labelledby="modalCobroClienteDetalleLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCobroClienteDetalleLabel"><i class="fas fa-money-check-alt"></i> Cobros al Cliente</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($tra_cobros)): ?>
                                <div class="docs-grid">
                                    <?php foreach ($tra_cobros as $_co):
                                        $_coUrl    = (string)($_co['url']           ?? '');
                                        $_coFile   = basename((string)($_co['file'] ?? ''));
                                        $_coNote   = trim((string)($_co['comentario']    ?? ''));
                                        $_coCosto  = (int)($_co['costo']            ?? 0);
                                        $_coCorrecto = trim((string)($_co['cobro_correcto'] ?? ''));
                                        $_coDate   = $_co['created_at'] ?? '';
                                        $_coExt    = strtolower(pathinfo($_coFile, PATHINFO_EXTENSION));
                                        $_coIsImg  = in_array($_coExt, ['jpg','jpeg','png','gif','webp'], true);
                                        $_coIsPdf  = $_coExt === 'pdf';
                                        $_coIsXml  = $_coExt === 'xml';
                                    ?>
                                        <div class="doc-card">
                                            <?php if ($_coUrl !== ''): ?>
                                                <a href="<?= esc($_coUrl) ?>" target="_blank" rel="noopener" title="<?= esc($_coFile) ?>">
                                                    <?php if ($_coIsImg): ?>
                                                        <img class="doc-thumb" src="<?= esc($_coUrl) ?>" alt="<?= esc($_coFile) ?>">
                                                    <?php elseif ($_coIsPdf): ?>
                                                        <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-file-pdf" style="font-size:32px;color:#ef4444;"></i>
                                                        </div>
                                                    <?php elseif ($_coIsXml): ?>
                                                        <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-file-code" style="font-size:32px;color:#f59e0b;"></i>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                            <i class="far fa-file-alt" style="font-size:32px;color:#6b7280;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </a>
                                            <?php else: ?>
                                                <div class="doc-thumb d-flex align-items-center justify-content-center">
                                                    <i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="doc-name" title="<?= esc($_coFile) ?>"><?= esc($_coFile !== '' ? $_coFile : 'Documento') ?></div>
                                            <?php if ($_coCosto > 0): ?>
                                                <div class="doc-badge">$<?= esc(number_format($_coCosto, 0)) ?></div>
                                            <?php endif; ?>
                                            <?php if ($_coNote !== ''): ?>
                                                <div class="doc-badge" style="color:#374151;"><?= esc($_coNote) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($_coDate)): ?>
                                                <div class="doc-badge" style="color:#64748b;"><?= esc($formatDateShort($_coDate)) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">Aún no hay documentos de cobro registrados para este trámite.</div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
                <?php
                    $auditTop = ($audit_log[0] ?? null);
                    $auditTitle = 'Equipo SGL';
                    $auditChipText = !empty($auditTop)
                        ? ('Última actualización: ' . $formatDateShort($auditTop['created_at'] ?? null))
                        : 'Sin actualizaciones';
                ?>
                <div class="exec-head">
                    <h5 class="text-blue h5" style="margin:0;">Resumen ejecutivo</h5>
                    <span class="exec-chip" title="<?= esc($auditTitle) ?>"><i class="fas fa-history"></i> <?= esc($auditChipText) ?></span>
                </div>
                <div class="info-grid mt-10">
                    <?php [$v, $empty] = $formatTileValue($tramite['cliente_directo'] ?? null); ?>
                    <div class="info-tile"><span>Cliente directo</span><strong class="<?= $empty ? 'is-empty' : '' ?>"><?= esc($v) ?></strong></div>

                    <?php [$v, $empty] = $formatTileValue($tramite['ejecutivo_cliente'] ?? null); ?>
                    <div class="info-tile"><span>Ejecutivo cliente</span><strong class="<?= $empty ? 'is-empty' : '' ?>"><?= esc($v) ?></strong></div>

                    <?php [$v, $empty] = $formatTileValue($tramite['unidad'] ?? null); ?>
                    <div class="info-tile"><span>Unidad</span><strong class="<?= $empty ? 'is-empty' : '' ?>"><?= esc($v) ?></strong></div>

                    <?php [$v, $empty] = $formatTileValue($tramite['serie'] ?? null); ?>
                    <div class="info-tile"><span>Serie</span><strong class="<?= $empty ? 'is-empty' : '' ?>"><?= esc($v) ?></strong></div>

                    <?php [$v, $empty] = $formatTileValue($tramite['placas'] ?? null); ?>
                    <div class="info-tile"><span>Placas</span><strong class="<?= $empty ? 'is-empty' : '' ?>"><?= esc($v) ?></strong></div>

                    <?php [$v, $empty] = $formatTileValue($tramite['entidad'] ?? null); ?>
                    <div class="info-tile"><span>Entidad</span><strong class="<?= $empty ? 'is-empty' : '' ?>"><?= esc($v) ?></strong></div>

                    <?php [$v, $empty] = $formatTileValue($tramite['municipio'] ?? null); ?>
                    <div class="info-tile"><span>Municipio</span><strong class="<?= $empty ? 'is-empty' : '' ?>"><?= esc($v) ?></strong></div>

                    <?php [$v, $empty] = $formatTileValue($tramite['cobro_status'] ?? null); ?>
                    <div class="info-tile"><span>Cobro</span><strong class="<?= $empty ? 'is-empty' : '' ?>"><?= esc($v) ?></strong></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="card-box pd-20 mb-20">
                        <h5 class="text-blue h5">Línea de tiempo del trámite</h5>
                        <div class="timeline-subtitle">Avance y eventos reales del proceso.</div>
                        <?php
                            $normalizeStageTitle = static function ($rawTitle, string $fallback): string {
                                $title = trim((string) $rawTitle);
                                if ($title === '') {
                                    return $fallback;
                                }

                                if (preg_match('/^paso\s*\d+\s*$/i', $title)) {
                                    return $fallback;
                                }

                                $title = preg_replace('/^paso\s*\d+\s*[-–—:]\s*/i', '', $title);
                                $title = preg_replace('/^paso\s*\d+\s*/i', '', $title);
                                $title = trim((string) $title);

                                return $title !== '' ? $title : $fallback;
                            };

                            $paso1 = $milestones[0] ?? ['titulo' => 'Registro inicial', 'fecha' => null, 'detalle' => 'Registro inicial'];
                            $paso2 = $milestones[1] ?? ['titulo' => 'Inicio del proceso', 'fecha' => null, 'detalle' => 'Inicio del proceso'];
                            $paso3 = $milestones[2] ?? ['titulo' => 'Último movimiento', 'fecha' => null, 'detalle' => 'Último movimiento'];
                            $paso4 = [
                                'titulo' => 'Gestion de trámite',
                                'fecha' => $milestones[3]['fecha'] ?? null,
                                'detalle' => 'Se esta trabajando en el tramite por parte del gestor.',
                            ];
                            $paso5 = [
                                'titulo' => 'Cierre y costos',
                                'fecha' => $milestones[4]['fecha'] ?? null,
                                'detalle' => 'Desglose de costos del tramite.',
                            ];

                            $stages = [
                                [
                                    'titulo' => $normalizeStageTitle($paso1['titulo'] ?? null, 'Registro inicial'),
                                    'fecha' => $paso1['fecha'] ?? null,
                                    'detalle' => $paso1['detalle'] ?? 'Registro inicial',
                                ],
                                [
                                    'titulo' => $normalizeStageTitle($paso2['titulo'] ?? null, 'Inicio del proceso'),
                                    'fecha' => $paso2['fecha'] ?? null,
                                    'detalle' => $paso2['detalle'] ?? 'Inicio del proceso',
                                ],
                                [
                                    'titulo' => $normalizeStageTitle($paso3['titulo'] ?? null, 'Último movimiento'),
                                    'fecha' => $paso3['fecha'] ?? null,
                                    'detalle' => $paso3['detalle'] ?? 'Ultimo movimiento',
                                ],
                                [
                                    'titulo' => $normalizeStageTitle($paso4['titulo'] ?? null, 'Gestión con gestor'),
                                    'fecha' => $paso4['fecha'] ?? null,
                                    'detalle' => $paso4['detalle'] ?? 'En progreso',
                                ],
                                [
                                    'titulo' => $normalizeStageTitle($paso5['titulo'] ?? null, 'Cierre y costos'),
                                    'fecha' => $paso5['fecha'] ?? null,
                                    'detalle' => $paso5['detalle'] ?? 'Pendiente',
                                ],
                            ];

                            $firstPendingIndex = null;
                            $completedCount = 0;
                            foreach ($stages as $index => $stage) {
                                if (!empty($stage['fecha'])) {
                                    $completedCount++;
                                }
                                if ($firstPendingIndex === null && empty($stage['fecha'])) {
                                    $firstPendingIndex = $index;
                                }
                            }

                            $totalStages = count($stages);
                            $progressPercent = $totalStages > 0 ? (int) round(($completedCount / $totalStages) * 100) : 0;
                            $currentStageNumber = $firstPendingIndex !== null ? $firstPendingIndex + 1 : $totalStages;
                            $currentStageTitle = $firstPendingIndex !== null
                                ? ($stages[$firstPendingIndex]['titulo'] ?? 'En progreso')
                                : 'Proceso concluido';

                            $statusForStage = static function (int $index, array $stage, ?int $firstPendingIndex): array {
                                if (!empty($stage['fecha'])) {
                                    return ['class' => 'is-done', 'label' => 'Completado'];
                                }
                                if ($firstPendingIndex === $index) {
                                    return ['class' => 'is-current', 'label' => 'En progreso'];
                                }
                                return ['class' => 'is-pending', 'label' => 'Pendiente'];
                            };

                            $activityFeed = array_slice($audit_log ?? [], 0, 12);
                            $latestActivity = $activityFeed[0] ?? null;

                            $activityGroups = [];
                            $lastGroupKey = null;
                            foreach ($activityFeed as $event) {
                                $key = $minuteKey($event['created_at'] ?? null) ?? ('no-date-' . count($activityGroups));
                                if ($lastGroupKey !== $key) {
                                    $activityGroups[] = [
                                        'minute_key' => $key,
                                        'timestamp' => $event['created_at'] ?? null,
                                        'events' => [],
                                    ];
                                    $lastGroupKey = $key;
                                }
                                $activityGroups[count($activityGroups) - 1]['events'][] = $event;
                            }

                            $pickGroupAction = static function (array $events): string {
                                $action = 'update';
                                foreach ($events as $ev) {
                                    $a = strtolower((string) ($ev['action'] ?? 'update'));
                                    if ($a === 'status_change') {
                                        return 'status_change';
                                    }
                                    if ($a === 'upload') {
                                        $action = 'upload';
                                    }
                                }
                                return $action;
                            };
                        ?>
                        <div class="ux-top-grid">
                            <div class="timeline-progress">
                                <div class="timeline-progress-head">
                                    <div class="timeline-progress-title">Avance temporal</div>
                                    <div class="timeline-progress-value"><?= esc($progressPercent) ?>%</div>
                                </div>
                                <div class="timeline-progress-track" role="progressbar" aria-valuenow="<?= esc($progressPercent) ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="timeline-progress-fill" style="width: <?= esc($progressPercent) ?>%;"></div>
                                </div>
                                <div class="timeline-progress-foot">
                                    <span>Etapa actual: <?= esc($currentStageTitle) ?></span>
                                    <span><?= esc((string) $completedCount) ?> de <?= esc((string) $totalStages) ?> etapas completadas</span>
                                </div>
                            </div>

                            <div class="realtime-card is-highlight">
                                <div class="realtime-kicker">Acción actual del trámite</div>
                                <div class="realtime-title">
                                    <?= esc($latestActivity['description'] ?? 'Sin movimientos recientes registrados') ?>
                                </div>
                                <div class="realtime-meta">
                                    <?php if (!empty($latestActivity)): ?>
                                        Equipo SGL · <?= esc($formatDateShort($latestActivity['created_at'] ?? null)) ?>
                                    <?php else: ?>
                                        Se mostrará aquí la última acción en cuanto exista actividad en la bitácora.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="timeline">
                            <?php foreach ($stages as $index => $stage): ?>
                                <?php $status = $statusForStage($index, $stage, $firstPendingIndex); ?>
                                <div class="timeline-item <?= esc($status['class']) ?>">
                                    <div class="timeline-header">
                                        <div>
                                            <div class="timeline-step">Etapa <?= $index + 1 ?></div>
                                            <div class="timeline-title"><?= esc($stage['titulo']) ?></div>
                                        </div>
                                        <span class="timeline-chip <?= esc($status['class']) ?>"><?= esc($status['label']) ?></span>
                                    </div>
                                    <div class="timeline-meta">
                                        <span><i class="fa fa-calendar"></i> <?= esc($formatDate($stage['fecha'] ?? null)) ?></span>
                                    </div>
                                    <div class="timeline-detail"><?= esc($stage['detalle']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="realtime-card">
                            <div class="d-flex justify-content-between align-items-center mb-10" style="gap: 8px;">
                                <h6 style="margin:0; font-weight: 700; color: var(--cs-ink);">Bitácora real del trámite</h6>
                                <span style="font-size:12px; color: var(--cs-muted);">Últimos <?= esc((string) count($activityFeed)) ?> eventos</span>
                            </div>

                            <?php if (!empty($activityGroups)): ?>
                                <div class="activity-feed activity-sequence">
                                    <?php foreach ($activityGroups as $group): ?>
                                        <?php
                                            $events = $group['events'] ?? [];
                                            $groupAction = $pickGroupAction($events);
                                            $groupTitle = count($events) > 1
                                                ? ('Cambios agrupados (' . count($events) . ' eventos)')
                                                : 'Movimiento registrado';
                                        ?>
                                        <div class="activity-item <?= esc($groupAction) ?>">
                                            <div class="activity-head">
                                                <span class="activity-chip <?= esc($groupAction) ?>"><?= esc($actionLabel($groupAction)) ?></span>
                                                <span style="font-size:11px; color: var(--cs-muted);">
                                                    <?= esc($formatDateShort($group['timestamp'] ?? null)) ?>
                                                </span>
                                            </div>

                                            <div class="activity-text"><?= esc($groupTitle) ?></div>

                                            <?php if (!empty($events)): ?>
                                                <div class="activity-sub">
                                                    <?php foreach ($events as $event): ?>
                                                        <?php
                                                            $fieldName = $formatFieldLabel($event['field_name'] ?? null);
                                                            $oldValue = $normalizeAuditValue($event['old_value'] ?? null);
                                                            $newValue = $normalizeAuditValue($event['new_value'] ?? null);
                                                            $eventTitle = $event['description'] ?? 'Movimiento registrado';
                                                            if (stripos($eventTitle, 'Campo ') === 0) {
                                                                $eventTitle = $fieldName . ' actualizado';
                                                            }
                                                        ?>
                                                        <div class="activity-sub-row">
                                                            <div class="activity-sub-time"><?= esc($formatTimeSeconds($event['created_at'] ?? null)) ?></div>
                                                            <div>
                                                                <div class="activity-sub-title"><?= esc($eventTitle) ?></div>
                                                                <div class="activity-sub-meta">
                                                                    <span class="activity-pill"><?= esc($fieldName) ?></span>
                                                                    <span><?= esc($oldValue) ?> → <?= esc($newValue) ?></span>
                                                                    <span>·</span>
                                                                    <span>Equipo SGL</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state" style="margin-top: 6px;">Este trámite aún no tiene eventos de auditoría registrados.</div>
                            <?php endif; ?>
                        </div>

                        <div class="realtime-card">
                            <div class="d-flex justify-content-between align-items-center mb-10" style="gap: 8px;">
                                <h6 style="margin:0; font-weight: 700; color: var(--cs-ink);">Secuencia de cambios de estatus</h6>
                                <span style="font-size:12px; color: var(--cs-muted);">Histórico real</span>
                            </div>
                            <?php if (!empty($status_timeline ?? [])): ?>
                                <?php
                                    $statusEvents = array_slice($status_timeline, 0, 10);
                                    $statusGroups = [];
                                    $lastStatusKey = null;
                                    foreach ($statusEvents as $statusEvent) {
                                        $key = $minuteKey($statusEvent['timestamp'] ?? null) ?? ('no-date-' . count($statusGroups));
                                        if ($lastStatusKey !== $key) {
                                            $statusGroups[] = [
                                                'timestamp' => $statusEvent['timestamp'] ?? null,
                                                'events' => [],
                                            ];
                                            $lastStatusKey = $key;
                                        }
                                        $statusGroups[count($statusGroups) - 1]['events'][] = $statusEvent;
                                    }
                                ?>
                                <div class="status-ribbon-list">
                                    <?php foreach ($statusGroups as $group): ?>
                                        <?php $events = $group['events'] ?? []; ?>
                                        <div class="status-ribbon">
                                            <div class="status-ribbon-head">
                                                <span><i class="fa fa-flag"></i> Cambio de estatus</span>
                                                <span><?= esc($formatDateShort($group['timestamp'] ?? null)) ?></span>
                                            </div>
                                            <?php if (count($events) <= 1): ?>
                                                <div class="status-ribbon-title"><?= esc($events[0]['descripcion'] ?? 'Cambio de estatus') ?></div>
                                            <?php else: ?>
                                                <div class="status-ribbon-title">Cambios agrupados (<?= esc((string) count($events)) ?>)</div>
                                                <div class="activity-sub" style="border-top: 0; padding-top: 8px; margin-top: 6px;">
                                                    <?php foreach ($events as $ev): ?>
                                                        <div class="activity-sub-row" style="grid-template-columns: 82px 1fr;">
                                                            <div class="activity-sub-time"><?= esc($formatTimeSeconds($ev['timestamp'] ?? null)) ?></div>
                                                            <div class="activity-sub-title" style="font-weight: 700;">
                                                                <?= esc($ev['descripcion'] ?? 'Cambio de estatus') ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state" style="margin-top: 6px;">No hay cambios de estatus registrados en auditoría para este trámite.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card-box pd-20 mb-20">
                        <div class="cost-head">
                            <h5 class="text-blue h5" style="margin:0;">Desglose de costos</h5>
                            <span class="cost-chip"><i class="fas fa-calculator"></i> Total: $<?= number_format((float) ($tramite['costo_total'] ?? 0), 2) ?></span>
                        </div>
                        <?php
                            $montoDerechos = (float) ($tramite['costo_gestoria'] ?? 0);
                            $honorarios = (float) ($tramite['costo_pago_cliente'] ?? ($tramite['costo_gestoria'] ?? 0));
                            $comisiones = (float) ($tramite['comision_derechos'] ?? 0);
                            $iva = (float) ($tramite['iva'] ?? ($tramite['impuesto_gestoria'] ?? 0));
                            $sumaCalculada = $montoDerechos + $honorarios + $comisiones + $iva;
                            $totalFinal = (float) ($tramite['costo_total'] ?? 0);
                            if ($totalFinal <= 0) {
                                $totalFinal = $sumaCalculada;
                            }
                        ?>
                        <div class="cost-grid">
                            <div class="cost-tile">
                                <span>Costos de tramites</span>
                                <strong>$<?= number_format($montoDerechos, 2) ?></strong>
                            </div>
                            <div class="cost-tile">
                                <span>Honorarios</span>
                                <strong>$<?= number_format($honorarios, 2) ?></strong>
                            </div>
                            <div class="cost-tile">
                                <span>Comisiones</span>
                                <strong>$<?= number_format($comisiones, 2) ?></strong>
                            </div>
                            <div class="cost-tile">
                                <span>IVA</span>
                                <strong>$<?= number_format($iva, 2) ?></strong>
                            </div>
                            <div class="cost-tile is-total">
                                <span>Suma total</span>
                                <strong>$<?= number_format($totalFinal, 2) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    /* ── Botones del ribbon con clase sgl-scroll-to ── */
    document.querySelectorAll('.ribbon-btn.sgl-scroll-to').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-target-id');
            if (!targetId) { return; }

            var collapseEl = document.getElementById(targetId);
            if (!collapseEl) { return; }

            // Expandir usando Bootstrap collapse
            if (typeof $ !== 'undefined') {
                $(collapseEl).collapse('show');
            } else {
                collapseEl.classList.add('show');
            }

            // Scroll al encabezado del paso (el botón cs-step-ribbon que lo precede)
            var stepWrapper = collapseEl.closest ? collapseEl.closest('.cs-step') : null;
            var scrollTarget = stepWrapper || collapseEl;
            setTimeout(function () {
                scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 220);
        });
    });
}());
</script>

<?= $this->endSection() ?>
