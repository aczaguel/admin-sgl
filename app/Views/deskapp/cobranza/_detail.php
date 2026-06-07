<?php
$selected_expediente = $selected_expediente ?? null;
$cobranzaSchemaReady = !empty($cobranzaSchemaReady ?? $cobranza_schema_ready ?? false);
$cobroStatusOptions = $cobro_status_options ?? [];
$canEditCobroClienteData = !empty($can_edit_cobro_cliente_data);
$canUploadCobroClienteFiles = !empty($can_upload_cobro_cliente_files);
$canConcludeTramite = !empty($can_conclude_tramite);
$hasPendingPagoConciliation = (int) ($selected_expediente['pago_summary']['pending_count'] ?? 0) > 0;
$formatDate = static function (?string $value): string {
    if ($value === null || trim($value) === '') {
        return 'Sin fecha';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('d M Y H:i', $timestamp) : $value;
};
$costoTotalCobro = (float) ($selected_expediente['costo_total'] ?? 0);
if ($costoTotalCobro <= 0 && !empty($selected_expediente)) {
    $costoTotalCobro = (float) ($selected_expediente['costo_gestoria'] ?? 0)
        + (float) ($selected_expediente['costo_pago_cliente'] ?? 0)
        + (float) ($selected_expediente['comision_derechos'] ?? 0)
        + (float) ($selected_expediente['iva'] ?? 0);
}
$detailSession = session();
$detailRoles = $detailSession->get('user_roles') ?? [];
$detailPerms = $detailSession->get('user_permissions') ?? [];
$canHeaderHistorialActividad = has_permission('tramite_detalle_quick_actions_historial_actividad_ver', $detailPerms, $detailRoles);
$canQuickDocumentos = has_permission('quick_action_documentos', $detailPerms, $detailRoles);
$canQuickBitacora = has_permission('quick_action_bitacora', $detailPerms, $detailRoles);
$canQuickPagosDerecho = has_permission('quick_action_pagos_derecho', $detailPerms, $detailRoles);
$canQuickPagoGestor = has_permission('quick_action_pago_gestor', $detailPerms, $detailRoles);
$canQuickEvidenciasFinales = has_permission('quick_action_evidencias_finales', $detailPerms, $detailRoles);
$canQuickCobrosCliente = has_permission('quick_action_cobros_cliente', $detailPerms, $detailRoles);
$canListCobroCliente = has_permission('list_cobro_cliente', $detailPerms, $detailRoles);
$canSectionPagoGestor = has_permission('section_pago_gestor', $detailPerms, $detailRoles);
$canSectionFinalCostos = has_permission('section_final_costos', $detailPerms, $detailRoles);
$canSeePagoGestorBtn = $canQuickPagoGestor && $canSectionPagoGestor;
$canSeeEvidenciasFinalesBtn = $canQuickEvidenciasFinales && $canSectionFinalCostos;
$canSeeCobroClienteBtn = $canSectionFinalCostos && ($canQuickCobrosCliente || $canListCobroCliente);
$hasLegacySteps = !empty($selected_expediente['readonly_step1'])
    || !empty($selected_expediente['readonly_step2'])
    || !empty($selected_expediente['readonly_step3'])
    || !empty($selected_expediente['pago_gestor_resumen'])
    || !empty($selected_expediente['servicios_asociados']);
$docStatusDocs = $selected_expediente['doc_status_docs'] ?? [];
$renderPreviewCard = static function (array $file): void {
    $fileUrl = (string) ($file['file_url'] ?? '#');
    $fileName = (string) ($file['file'] ?? 'Archivo');
    $isImage = !empty($file['is_image']);
    ?>
    <div class="file-preview">
        <a href="<?= esc($fileUrl) ?>" target="_blank" rel="noopener noreferrer">
            <?php if ($isImage): ?>
                <img src="<?= esc($fileUrl) ?>" alt="<?= esc($fileName) ?>" class="img-thumbnail">
            <?php else: ?>
                <i class="far fa-file cobranza-file-fallback-icon"></i>
            <?php endif; ?>
        </a>
        <p><?= esc($fileName) ?></p>
        <?php if (!empty($file['doc_label'])): ?>
            <span class="doc-badge"><?= esc((string) $file['doc_label']) ?></span>
        <?php endif; ?>
    </div>
    <?php
};
?>

<?php if (!empty($selected_expediente)): ?>
    <div class="cobranza-detail-header">
        <div>
            <span class="cobranza-detail-overline">Expediente seleccionado</span>
            <h2><?= esc($selected_expediente['folio']) ?> · <?= esc($selected_expediente['cliente_nombre']) ?></h2>
            <p><?= esc($selected_expediente['contrato'] !== '' ? $selected_expediente['contrato'] : 'Sin contrato') ?></p>
        </div>
        <span class="cobranza-tone is-<?= esc($selected_expediente['stage_tone']) ?>"><?= esc($selected_expediente['stage_label']) ?></span>
    </div>

    <div class="cobranza-detail-actions">
        <a class="cobranza-btn is-primary" href="<?= esc($selected_expediente['tramite_url']) ?>">Abrir tramite</a>
        <?php if ($canConcludeTramite && (int) ($selected_expediente['tramite_status_id'] ?? 0) === SGL_TRA_STATUS_COBRO_CLIENTE && !$hasPendingPagoConciliation): ?>
            <form method="post" action="<?= esc(site_url('deskapp/tramites/change_status')) ?>" class="cobranza-inline-form" data-cobranza-ajax-form data-confirm="Se concluira este tramite y saldra de la cartera de cobranza." data-success-redirect="<?= esc(site_url('deskapp/cobranza')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="tramite_id" value="<?= esc((string) ($selected_expediente['id'] ?? 0)) ?>">
                <input type="hidden" name="status_id" value="20">
                <button type="submit" class="cobranza-btn is-secondary">Concluir tramite</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($canConcludeTramite && (int) ($selected_expediente['tramite_status_id'] ?? 0) === SGL_TRA_STATUS_COBRO_CLIENTE && $hasPendingPagoConciliation): ?>
        <div class="cobranza-inline-note cobranza-inline-note--mb-lg cobranza-inline-note--warning">
            Este tramite todavia tiene pagos pendientes de conciliacion. Confirma o resuelve esos pagos antes de concluirlo.
        </div>
    <?php endif; ?>

    <?php if ($cobranzaSchemaReady && !empty($selected_expediente['can_open_expediente'])): ?>
        <form method="post" action="<?= esc(site_url('deskapp/cobranza/expediente/' . (int) $selected_expediente['id'] . '/abrir')) ?>" class="cobranza-form-card cobranza-form-card--spaced">
            <?= csrf_field() ?>
            <h3>Abrir expediente de cobranza</h3>
            <p class="cobranza-copy">Este tramite ya cumple condicion para operar cobranza. Al abrirlo se creara o reactivara su expediente y se registrara la apertura en timeline.</p>
            <div class="cobranza-actions cobranza-actions--spaced">
                <button type="submit" class="cobranza-btn is-primary">Abrir expediente</button>
            </div>
        </form>
    <?php elseif ($cobranzaSchemaReady && !empty($selected_expediente['has_active_expediente'])): ?>
        <div class="cobranza-inline-note cobranza-inline-note--mb-lg cobranza-inline-note--success">
            Expediente activo: <?= esc($selected_expediente['expediente_status_nombre'] !== '' ? $selected_expediente['expediente_status_nombre'] : 'Abierto') ?>.
            <?php if (!empty($selected_expediente['fecha_proximo_seguimiento'])): ?>
                Siguiente seguimiento: <?= esc($formatDate($selected_expediente['fecha_proximo_seguimiento'])) ?>.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="cobranza-detail-summary">
        <article>
            <span>Ejecutivo interno</span>
            <strong><?= esc($selected_expediente['owner_name']) ?></strong>
        </article>
        <article>
            <span>Ejecutivo cliente</span>
            <strong><?= esc($selected_expediente['cliente_ejecutivo_nombre']) ?></strong>
        </article>
        <article>
            <span>Estatus tramite</span>
            <strong><?= esc($selected_expediente['tramite_status_nombre'] !== '' ? $selected_expediente['tramite_status_nombre'] : 'Sin estatus') ?></strong>
        </article>
        <article>
            <span>Estatus cobro</span>
            <strong><?= esc($selected_expediente['cobro_status_nombre'] !== '' ? $selected_expediente['cobro_status_nombre'] : 'Pendiente') ?></strong>
        </article>
        <article>
            <span>Estatus expediente</span>
            <strong><?= esc($selected_expediente['expediente_status_nombre'] !== '' ? $selected_expediente['expediente_status_nombre'] : 'Sin abrir') ?></strong>
        </article>
        <article>
            <span>Ultima evidencia</span>
            <strong><?= esc($formatDate($selected_expediente['latest_evidence_at'])) ?></strong>
        </article>
        <article>
            <span>Antiguedad operativa</span>
            <strong><?= esc((string) $selected_expediente['aging_days']) ?> dias</strong>
        </article>
        <article>
            <span>Ultimo contacto de cobranza</span>
            <strong><?= esc($formatDate($selected_expediente['fecha_ultimo_contacto'] ?? null)) ?></strong>
        </article>
        <article>
            <span>Proximo seguimiento</span>
            <strong><?= esc($formatDate($selected_expediente['fecha_proximo_seguimiento'] ?? null)) ?></strong>
        </article>
    </div>

    <div class="cobranza-detail-stats">
        <span class="cobranza-chip">Archivos: <?= esc((string) $selected_expediente['evidence_total']) ?></span>
        <span class="cobranza-chip">Parciales: <?= esc((string) $selected_expediente['evidence_partial_count']) ?></span>
        <span class="cobranza-chip">Completos: <?= esc((string) $selected_expediente['evidence_complete_count']) ?></span>
        <?php if (!empty($selected_expediente['promesa_activa'])): ?>
            <span class="cobranza-chip">Promesa activa: <?= esc($formatDate($selected_expediente['promesa_activa']['fecha_promesa'] ?? null)) ?></span>
        <?php endif; ?>
        <?php if (!empty($selected_expediente['pago_summary']['count'])): ?>
            <span class="cobranza-chip">Pagos reportados: <?= esc((string) $selected_expediente['pago_summary']['count']) ?></span>
            <span class="cobranza-chip">Pendientes conciliacion: <?= esc((string) ($selected_expediente['pago_summary']['pending_count'] ?? 0)) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($canHeaderHistorialActividad || $canQuickDocumentos || $canQuickBitacora || $canQuickPagosDerecho || $canSeePagoGestorBtn || $canSeeEvidenciasFinalesBtn || $canSeeCobroClienteBtn): ?>
        <div class="quick-actions-ribbon cobranza-legacy-block">
            <div class="ribbon-title">
                <i class="fas fa-bolt"></i>
                <span>Detalle rapido</span>
            </div>
            <div class="ribbon-buttons">
                <?php if ($canQuickDocumentos): ?>
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-documentos-cobranza">
                        <div class="ribbon-icon sgl-ribbon-icon--documentos">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <span class="ribbon-label">Documentos</span>
                    </button>
                <?php endif; ?>
                <?php if ($canQuickBitacora || $canHeaderHistorialActividad): ?>
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-bitacora-cobranza">
                        <div class="ribbon-icon sgl-ribbon-icon--bitacora">
                            <i class="fas fa-history"></i>
                        </div>
                        <span class="ribbon-label">Bitacora</span>
                    </button>
                <?php endif; ?>
                <?php if ($canQuickPagosDerecho): ?>
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pagos-derecho-cobranza">
                        <div class="ribbon-icon sgl-ribbon-icon--pagos-derecho">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <span class="ribbon-label">Pagos Derecho</span>
                    </button>
                <?php endif; ?>
                <?php if ($canSeePagoGestorBtn): ?>
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pago-gestor-cobranza">
                        <div class="ribbon-icon sgl-ribbon-icon--pago-gestor">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <span class="ribbon-label">Pago Gestor</span>
                    </button>
                <?php endif; ?>
                <?php if ($canSeeEvidenciasFinalesBtn): ?>
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-evidencias-finales-cobranza">
                        <div class="ribbon-icon sgl-ribbon-icon--evidencias-finales">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <span class="ribbon-label">Evidencias Finales</span>
                    </button>
                <?php endif; ?>
                <?php if ($canSeeCobroClienteBtn): ?>
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-cobro-cliente-cobranza">
                        <div class="ribbon-icon sgl-ribbon-icon--cobro-cliente">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <span class="ribbon-label">Cobros Cliente</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($hasLegacySteps): ?>
        <div class="cobranza-legacy-block">
            <div class="sgl-step-form-ribbon <?= !empty($selected_expediente['step1_complete']) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="1">
                <div class="sgl-icon"><i class="<?= !empty($selected_expediente['step1_complete']) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
                <div class="sgl-text">Paso 1: Datos del tramite</div>
                <button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#cobranzaCollapsePaso1" aria-expanded="false" aria-controls="cobranzaCollapsePaso1">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div id="cobranzaCollapsePaso1" class="collapse">
                <?php if (!empty($selected_expediente['servicios_asociados'])): ?>
                    <div class="sgl-soft-panel mt-3">
                        <p class="sgl-soft-panel-title">Tipos de tramite ligados</p>
                        <div class="d-flex flex-wrap cobranza-badge-row">
                            <?php foreach ($selected_expediente['servicios_asociados'] as $srv): ?>
                                <span class="badge badge-success badge-pill sgl-pill">✓ <?= esc((string) ($srv['label'] ?? '')) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="sgl-soft-panel mt-3">
                    <p class="sgl-soft-panel-title">Datos del tramite</p>
                    <div class="sgl-info-grid">
                        <?php foreach (($selected_expediente['readonly_step1'] ?? []) as $item): ?>
                            <div class="sgl-info-item">
                                <div class="sgl-info-label"><?= esc((string) ($item['label'] ?? '')) ?></div>
                                <div class="sgl-info-value"><?= esc((string) (($item['value'] ?? '') !== '' ? $item['value'] : '--')) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="sgl-step-form-ribbon mt-3 <?= !empty($selected_expediente['step2_complete']) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="2">
                <div class="sgl-icon"><i class="<?= !empty($selected_expediente['step2_complete']) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
                <div class="sgl-text">Paso 2: Gestor y Empresa</div>
                <button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#cobranzaCollapsePaso2" aria-expanded="false" aria-controls="cobranzaCollapsePaso2">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div id="cobranzaCollapsePaso2" class="collapse">
                <div class="sgl-soft-panel mt-3">
                    <p class="sgl-soft-panel-title">Gestor y Empresa</p>
                    <div class="sgl-info-grid">
                        <?php foreach (($selected_expediente['readonly_step2'] ?? []) as $item): ?>
                            <div class="sgl-info-item">
                                <div class="sgl-info-label"><?= esc((string) ($item['label'] ?? '')) ?></div>
                                <div class="sgl-info-value"><?= esc((string) (($item['value'] ?? '') !== '' ? $item['value'] : '--')) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="sgl-step-form-ribbon mt-3 <?= !empty($selected_expediente['step3_complete']) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="3">
                <div class="sgl-icon"><i class="<?= !empty($selected_expediente['step3_complete']) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
                <div class="sgl-text">Paso 3: Pagos de Derechos</div>
                <button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#cobranzaCollapsePaso3" aria-expanded="false" aria-controls="cobranzaCollapsePaso3">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div id="cobranzaCollapsePaso3" class="collapse">
                <div class="sgl-soft-panel mt-3">
                    <p class="sgl-soft-panel-title">Datos de pagos de derechos</p>
                    <div class="sgl-info-grid">
                        <?php foreach (($selected_expediente['readonly_step3'] ?? []) as $item): ?>
                            <div class="sgl-info-item">
                                <div class="sgl-info-label"><?= esc((string) ($item['label'] ?? '')) ?></div>
                                <div class="sgl-info-value"><?= esc((string) (($item['value'] ?? '') !== '' ? $item['value'] : '--')) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="sgl-soft-panel mt-3">
                    <p class="sgl-soft-panel-title">Documentos de derechos</p>
                    <div class="gallery-preview">
                        <?php if (!empty($selected_expediente['pago_derechos_db'])): ?>
                            <?php foreach ($selected_expediente['pago_derechos_db'] as $file): ?>
                                <?php $renderPreviewCard($file); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted">Sin documentos registrados.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="sgl-step-form-ribbon mt-3" data-ribbon-step="4">
                <div class="sgl-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="sgl-text">Paso 4: Evidencias Finales</div>
                <button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#cobranzaCollapsePaso4" aria-expanded="false" aria-controls="cobranzaCollapsePaso4">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div id="cobranzaCollapsePaso4" class="collapse">
                <div class="sgl-soft-panel mt-3">
                    <p class="sgl-soft-panel-title">Evidencias finales del tramite</p>
                    <div class="sgl-status-row cobranza-status-row--spaced">
                        <span class="sgl-status-chip <?= !empty($selected_expediente['has_comprobante_tramite_recibido']) ? '' : 'is-muted' ?>">Tramite Entregado por Gestor</span>
                        <span class="sgl-status-chip <?= !empty($selected_expediente['has_comprobante_acuse_recibo']) ? '' : 'is-muted' ?>">Acuse de Recibo del Cliente</span>
                        <?php if (!empty($selected_expediente['has_comprobante_tramite_recibido']) && !empty($selected_expediente['has_comprobante_acuse_recibo'])): ?>
                            <span class="sgl-status-chip">Evidencias finales completas</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="sgl-soft-panel mt-3">
                    <p class="sgl-soft-panel-title">Documentos de evidencias finales</p>
                    <div class="gallery-preview">
                        <?php if (!empty($selected_expediente['pago_gestor_evidencias_db'])): ?>
                            <?php foreach ($selected_expediente['pago_gestor_evidencias_db'] as $file): ?>
                                <?php $renderPreviewCard($file); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted">Sin documentos registrados.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="sgl-step-form-ribbon mt-3" data-ribbon-step="5">
                <div class="sgl-icon"><i class="fas fa-credit-card"></i></div>
                <div class="sgl-text">Paso 5: Pago a Gestor</div>
                <button class="btn btn-sm btn-outline-secondary sgl-btn-icon" type="button" data-toggle="collapse" data-target="#cobranzaCollapsePaso5" aria-expanded="false" aria-controls="cobranzaCollapsePaso5">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div id="cobranzaCollapsePaso5" class="collapse">
                <div class="sgl-soft-panel mt-3">
                    <p class="sgl-soft-panel-title">Datos de pago a gestor</p>
                    <div class="sgl-info-grid">
                        <?php foreach (($selected_expediente['pago_gestor_resumen'] ?? []) as $item): ?>
                            <div class="sgl-info-item">
                                <div class="sgl-info-label"><?= esc((string) ($item['label'] ?? '')) ?></div>
                                <div class="sgl-info-value"><?= esc((string) (($item['value'] ?? '') !== '' ? $item['value'] : '--')) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="sgl-status-row cobranza-status-row--spaced">
                        <span class="sgl-status-chip <?= !empty($selected_expediente['has_factura_gestor']) ? '' : 'is-muted' ?>">Factura del Gestor</span>
                        <span class="sgl-status-chip <?= !empty($selected_expediente['has_comprobante_pago']) ? '' : 'is-muted' ?>">Comprobante de Pago</span>
                        <?php if (!empty($selected_expediente['has_factura_gestor']) && !empty($selected_expediente['has_comprobante_pago'])): ?>
                            <span class="sgl-status-chip">Pago completado</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="sgl-soft-panel mt-3">
                    <p class="sgl-soft-panel-title">Documentos de pago a gestor</p>
                    <div class="gallery-preview">
                        <?php if (!empty($selected_expediente['pago_gestor_pago_db'])): ?>
                            <?php foreach ($selected_expediente['pago_gestor_pago_db'] as $file): ?>
                                <?php $renderPreviewCard($file); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted">Sin documentos de pago a gestor registrados.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($canQuickDocumentos): ?>
        <div class="modal fade" id="modal-documentos-cobranza" tabindex="-1" role="dialog" aria-hidden="true" data-cobranza-detached-modal="1">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header cobranza-modal-header--documentos">
                        <h4 class="modal-title"><i class="fas fa-folder-open"></i> Documentos</h4>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="cobranza-legacy-block">
                            <?php if (!empty($docStatusDocs)): ?>
                                <div class="gallery-preview">
                                    <?php foreach ($docStatusDocs as $doc): ?>
                                        <div class="file-preview">
                                            <?php if (!empty($doc['file_url'])): ?>
                                                <a href="<?= esc((string) $doc['file_url']) ?>" target="_blank" rel="noopener noreferrer">
                                                    <?php if (!empty($doc['is_image'])): ?>
                                                        <img src="<?= esc((string) $doc['file_url']) ?>" alt="<?= esc((string) ($doc['documento_nombre'] ?? 'Documento')) ?>" class="img-thumbnail">
                                                    <?php else: ?>
                                                        <i class="far fa-file cobranza-file-fallback-icon"></i>
                                                    <?php endif; ?>
                                                </a>
                                            <?php else: ?>
                                                <i class="far fa-file cobranza-file-fallback-icon"></i>
                                            <?php endif; ?>
                                            <p><?= esc((string) (($doc['documento_nombre'] ?? '') !== '' ? $doc['documento_nombre'] : 'Documento')) ?></p>
                                            <?php if (!empty($doc['status_nombre'])): ?>
                                                <span class="doc-badge"><?= esc((string) $doc['status_nombre']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($doc['file'])): ?>
                                                <span class="doc-badge cobranza-doc-badge--offset"><?= esc((string) $doc['file']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="cobranza-inline-note cobranza-inline-note--flush">No hay documentos del tramite disponibles en este expediente.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= esc($selected_expediente['tramite_url']) ?>" class="btn btn-primary">Abrir tramite completo</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($canQuickBitacora || $canHeaderHistorialActividad): ?>
        <div class="modal fade" id="modal-bitacora-cobranza" tabindex="-1" role="dialog" aria-hidden="true" data-cobranza-detached-modal="1">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header cobranza-modal-header--bitacora">
                        <h4 class="modal-title"><i class="fas fa-history"></i> Bitacora</h4>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($selected_expediente['timeline'])): ?>
                            <div class="cobranza-timeline">
                                <?php foreach ($selected_expediente['timeline'] as $event): ?>
                                    <article class="cobranza-timeline-item">
                                        <strong><?= esc((string) ($event['title'] ?? 'Movimiento registrado')) ?></strong>
                                        <p><?= esc((string) ($event['description'] ?? 'Sin detalle')) ?></p>
                                        <span class="cobranza-detail-overline"><?= esc($formatDate($event['timestamp'] ?? null)) ?></span>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="cobranza-inline-note cobranza-inline-note--flush">No hay registros de bitacora para este expediente.</div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <?php if ($canHeaderHistorialActividad): ?>
                            <a href="<?= esc(site_url('deskapp/tramites/audit_timeline/' . (int) ($selected_expediente['id'] ?? 0))) ?>" class="btn btn-primary">Abrir historial completo</a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($canQuickPagosDerecho): ?>
        <div class="modal fade" id="modal-pagos-derecho-cobranza" tabindex="-1" role="dialog" aria-hidden="true" data-cobranza-detached-modal="1">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header cobranza-modal-header--pagos-derecho">
                        <h4 class="modal-title"><i class="fas fa-receipt"></i> Pagos Derecho</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="cobranza-legacy-block">
                            <div class="sgl-soft-panel">
                                <p class="sgl-soft-panel-title">Datos de pagos de derechos</p>
                                <div class="sgl-info-grid">
                                    <?php foreach (($selected_expediente['readonly_step3'] ?? []) as $item): ?>
                                        <div class="sgl-info-item">
                                            <div class="sgl-info-label"><?= esc((string) ($item['label'] ?? '')) ?></div>
                                            <div class="sgl-info-value"><?= esc((string) (($item['value'] ?? '') !== '' ? $item['value'] : '--')) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="sgl-soft-panel mt-3">
                                <p class="sgl-soft-panel-title">Documentos de derechos</p>
                                <div class="gallery-preview">
                                    <?php if (!empty($selected_expediente['pago_derechos_db'])): ?>
                                        <?php foreach ($selected_expediente['pago_derechos_db'] as $file): ?>
                                            <?php $renderPreviewCard($file); ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted">Sin documentos registrados.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($canSeePagoGestorBtn): ?>
        <div class="modal fade" id="modal-pago-gestor-cobranza" tabindex="-1" role="dialog" aria-hidden="true" data-cobranza-detached-modal="1">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header cobranza-modal-header--pago-gestor">
                        <h4 class="modal-title"><i class="fas fa-hand-holding-usd"></i> Pago Gestor</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="cobranza-legacy-block">
                            <div class="sgl-soft-panel">
                                <p class="sgl-soft-panel-title">Datos de pago a gestor</p>
                                <div class="sgl-info-grid">
                                    <?php foreach (($selected_expediente['pago_gestor_resumen'] ?? []) as $item): ?>
                                        <div class="sgl-info-item">
                                            <div class="sgl-info-label"><?= esc((string) ($item['label'] ?? '')) ?></div>
                                            <div class="sgl-info-value"><?= esc((string) (($item['value'] ?? '') !== '' ? $item['value'] : '--')) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="sgl-soft-panel mt-3">
                                <p class="sgl-soft-panel-title">Documentos de pago a gestor</p>
                                <div class="gallery-preview">
                                    <?php if (!empty($selected_expediente['pago_gestor_pago_db'])): ?>
                                        <?php foreach ($selected_expediente['pago_gestor_pago_db'] as $file): ?>
                                            <?php $renderPreviewCard($file); ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted">Sin documentos de pago a gestor registrados.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($canSeeEvidenciasFinalesBtn): ?>
        <div class="modal fade" id="modal-evidencias-finales-cobranza" tabindex="-1" role="dialog" aria-hidden="true" data-cobranza-detached-modal="1">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header cobranza-modal-header--evidencias-finales">
                        <h4 class="modal-title"><i class="fas fa-check-double"></i> Evidencias Finales</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                    </div>
                    <div class="modal-body">
                        <div class="cobranza-legacy-block">
                            <div class="sgl-soft-panel">
                                <p class="sgl-soft-panel-title">Estatus de evidencias finales</p>
                                <div class="sgl-status-row">
                                    <span class="sgl-status-chip <?= !empty($selected_expediente['has_comprobante_tramite_recibido']) ? '' : 'is-muted' ?>">Tramite Entregado por Gestor</span>
                                    <span class="sgl-status-chip <?= !empty($selected_expediente['has_comprobante_acuse_recibo']) ? '' : 'is-muted' ?>">Acuse de Recibo del Cliente</span>
                                </div>
                            </div>
                            <div class="sgl-soft-panel mt-3">
                                <p class="sgl-soft-panel-title">Documentos de evidencias finales</p>
                                <div class="gallery-preview">
                                    <?php if (!empty($selected_expediente['pago_gestor_evidencias_db'])): ?>
                                        <?php foreach ($selected_expediente['pago_gestor_evidencias_db'] as $file): ?>
                                            <?php $renderPreviewCard($file); ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted">Sin documentos registrados.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($canSeeCobroClienteBtn): ?>
        <div class="modal fade" id="modal-cobro-cliente-cobranza" tabindex="-1" role="dialog" aria-hidden="true" data-cobranza-detached-modal="1">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header cobranza-modal-header--cobro-cliente">
                        <h4 class="modal-title"><i class="fas fa-money-check-alt"></i> Cobros Cliente</h4>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($selected_expediente['evidencia_cobro_txt'])): ?>
                            <div class="cobranza-inline-note cobranza-inline-note--md">
                                <?= esc((string) $selected_expediente['evidencia_cobro_txt']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($selected_expediente['cobro_cliente_files'])): ?>
                            <div class="cobranza-proof-grid">
                                <?php foreach ($selected_expediente['cobro_cliente_files'] as $file): ?>
                                    <article class="cobranza-proof-card">
                                        <a href="<?= esc((string) ($file['file_url'] ?? '#')) ?>" target="_blank" rel="noopener noreferrer" class="cobranza-proof-link">
                                            <?php if (!empty($file['is_image'])): ?>
                                                <img src="<?= esc((string) ($file['file_url'] ?? '')) ?>" alt="<?= esc((string) ($file['file'] ?? 'Archivo')) ?>" class="cobranza-proof-image">
                                            <?php else: ?>
                                                <div class="cobranza-proof-file-icon">Archivo</div>
                                            <?php endif; ?>
                                        </a>
                                        <div class="cobranza-proof-meta">
                                            <strong><?= esc((string) ($file['file'] ?? 'Archivo')) ?></strong>
                                            <div class="cobranza-proof-meta-row">
                                                <span class="cobranza-chip"><?= esc((string) ($file['cobro_correcto'] ?? 'otro')) ?></span>
                                                <span class="cobranza-detail-overline"><?= esc($formatDate($file['created_at'] ?? null)) ?></span>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="cobranza-inline-note cobranza-inline-note--flush">Aun no hay documentos de cobro registrados para este expediente.</div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($canEditCobroClienteData || $canUploadCobroClienteFiles || !empty($selected_expediente['cobro_cliente_files'])): ?>
        <div class="cobranza-form-card">
            <h3>Ajustes de cobro cliente</h3>
            <p class="cobranza-copy">Aqui puedes ajustar el equivalente operativo del paso 6: estatus, montos, evidencia descriptiva e imagenes de cobro.</p>

            <?php if ($canEditCobroClienteData): ?>
                <form method="post" action="<?= esc(site_url('deskapp/tramitesn/update_final_save/' . (int) $selected_expediente['id'])) ?>" class="cobranza-form-grid cobranza-form-grid--top" data-cobranza-ajax-form>
                    <?= csrf_field() ?>
                    <div class="cobranza-field">
                        <label for="id-give-cliente">ID Give Cliente</label>
                        <input id="id-give-cliente" type="text" name="id_give_cliente" value="<?= esc((string) ($selected_expediente['id_give_cliente'] ?? '')) ?>" required>
                    </div>

                    <div class="cobranza-field">
                        <label for="numero-factura">Numero de factura</label>
                        <input id="numero-factura" type="text" name="numero_factura" value="<?= esc((string) ($selected_expediente['numero_factura'] ?? '')) ?>" required>
                    </div>

                    <div class="cobranza-field">
                        <label for="numero-refactura">Numero de refactura</label>
                        <input id="numero-refactura" type="text" name="numero_refactura" value="<?= esc((string) ($selected_expediente['numero_refactura'] ?? '')) ?>">
                    </div>

                    <div class="cobranza-field">
                        <label for="cobro-status-id">Estatus del cobro</label>
                        <select id="cobro-status-id" name="cobro_status_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($cobroStatusOptions as $optionId => $optionLabel): ?>
                                <option value="<?= esc((string) $optionId) ?>" <?= (int) ($selected_expediente['cobro_status_id'] ?? 0) === (int) $optionId ? 'selected' : '' ?>><?= esc($optionLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="cobranza-field">
                        <label for="costo-gestoria">Sumatoria de derechos</label>
                        <input id="costo-gestoria" type="number" step="0.01" value="<?= esc(number_format((float) ($selected_expediente['costo_gestoria'] ?? 0), 2, '.', '')) ?>" disabled>
                        <input type="hidden" name="costo_gestoria_hidden" value="<?= esc(number_format((float) ($selected_expediente['costo_gestoria'] ?? 0), 2, '.', '')) ?>">
                    </div>

                    <div class="cobranza-field">
                        <label for="costo-pago-cliente">Honorarios del tramite</label>
                        <input id="costo-pago-cliente" type="number" step="0.01" min="0" name="costo_pago_cliente" value="<?= esc(number_format((float) ($selected_expediente['costo_pago_cliente'] ?? 0), 2, '.', '')) ?>" required>
                    </div>

                    <div class="cobranza-field">
                        <label for="comision-derechos">Comision de derechos</label>
                        <input id="comision-derechos" type="number" step="0.01" min="0" name="comision_derechos" value="<?= esc(number_format((float) ($selected_expediente['comision_derechos'] ?? 0), 2, '.', '')) ?>" required>
                    </div>

                    <div class="cobranza-field">
                        <label for="iva-cobro">IVA</label>
                        <input id="iva-cobro" type="number" step="0.01" min="0" name="iva" value="<?= esc(number_format((float) ($selected_expediente['iva'] ?? 0), 2, '.', '')) ?>">
                    </div>

                    <div class="cobranza-field">
                        <label>Costo total actual</label>
                        <input type="text" value="<?= esc('$' . number_format($costoTotalCobro, 2)) ?>" disabled>
                    </div>

                    <div class="cobranza-field is-full">
                        <label for="evidencia-cobro-txt">Evidencia de cobro</label>
                        <textarea id="evidencia-cobro-txt" name="evidencia_cobro_txt" placeholder="Describe el avance, faltantes o validaciones pendientes del cobro al cliente."><?= esc((string) ($selected_expediente['evidencia_cobro_txt'] ?? '')) ?></textarea>
                    </div>

                    <div class="cobranza-actions">
                        <button type="submit" class="cobranza-btn is-primary">Guardar ajustes</button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="cobranza-section-top">
                <div class="cobranza-detail-stats cobranza-detail-stats--tight">
                    <span class="cobranza-chip">ID: <?= esc((string) $selected_expediente['id']) ?></span>
                    <span class="cobranza-chip">Factura: <?= esc((string) ($selected_expediente['numero_factura'] !== '' ? $selected_expediente['numero_factura'] : 'Sin capturar')) ?></span>
                    <span class="cobranza-chip">Refactura: <?= esc((string) ($selected_expediente['numero_refactura'] !== '' ? $selected_expediente['numero_refactura'] : 'Sin capturar')) ?></span>
                </div>

                <?php if ($canUploadCobroClienteFiles): ?>
                    <form method="post" action="<?= esc(site_url('deskapp/tramitesn/upload_cobro_cliente/' . (int) $selected_expediente['id'])) ?>" class="cobranza-form-grid" enctype="multipart/form-data" data-cobranza-ajax-form>
                        <?= csrf_field() ?>
                        <div class="cobranza-field">
                            <label for="cobro-correcto">Tipo de evidencia</label>
                            <select id="cobro-correcto" name="cobro_correcto">
                                <option value="parcial">Pago parcial</option>
                                <option value="completo">Pago completo</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="cobranza-field is-full">
                            <label for="archivo-cobro">Imagen o comprobante</label>
                            <input id="archivo-cobro" class="cobranza-file-input" type="file" name="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" required>
                            <label for="archivo-cobro" class="cobranza-file-picker" data-cobranza-file-picker>
                                <span class="cobranza-file-picker-badge">Subir evidencia</span>
                                <span class="cobranza-file-picker-copy">
                                    <strong>Selecciona un archivo o arrastralo aqui</strong>
                                    <small>PNG, JPG, PDF, Word o Excel. Se vinculara a este expediente.</small>
                                </span>
                                <span class="cobranza-file-picker-name" data-cobranza-file-name>Sin archivo seleccionado</span>
                            </label>
                        </div>
                        <div class="cobranza-actions">
                            <button type="submit" class="cobranza-btn is-secondary">Subir evidencia</button>
                        </div>
                    </form>
                <?php endif; ?>

                <?php if (!empty($selected_expediente['cobro_cliente_files'])): ?>
                    <div class="cobranza-proof-grid">
                        <?php foreach ($selected_expediente['cobro_cliente_files'] as $file): ?>
                            <article class="cobranza-proof-card">
                                <a href="<?= esc((string) ($file['file_url'] ?? '#')) ?>" target="_blank" rel="noopener noreferrer" class="cobranza-proof-link">
                                    <?php if (!empty($file['is_image'])): ?>
                                        <img src="<?= esc((string) ($file['file_url'] ?? '')) ?>" alt="<?= esc((string) ($file['file'] ?? 'Archivo')) ?>" class="cobranza-proof-image">
                                    <?php else: ?>
                                        <div class="cobranza-proof-file-icon">Archivo</div>
                                    <?php endif; ?>
                                </a>
                                <div class="cobranza-proof-meta">
                                    <strong><?= esc((string) ($file['file'] ?? 'Archivo')) ?></strong>
                                    <div class="cobranza-proof-meta-row">
                                        <span class="cobranza-chip"><?= esc((string) ($file['cobro_correcto'] ?? 'otro')) ?></span>
                                        <span class="cobranza-detail-overline"><?= esc($formatDate($file['created_at'] ?? null)) ?></span>
                                    </div>
                                </div>
                                <?php if ($canUploadCobroClienteFiles): ?>
                                    <form method="post" action="<?= esc(site_url('deskapp/tramitesn/delete_cobro_cliente')) ?>" class="cobranza-proof-actions" data-cobranza-ajax-form data-confirm="Se eliminara esta evidencia de cobro.">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="tramite_id" value="<?= esc((string) $selected_expediente['id']) ?>">
                                        <input type="hidden" name="file" value="<?= esc((string) ($file['file'] ?? '')) ?>">
                                        <button type="submit" class="cobranza-btn is-danger-soft">Eliminar evidencia</button>
                                    </form>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="cobranza-empty cobranza-empty--top">Aun no hay imagenes o comprobantes de cobro cargados para este tramite.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($selected_expediente['promesa_activa'])): ?>
        <div class="cobranza-inline-note cobranza-inline-note--mt-lg cobranza-inline-note--orange">
            Promesa activa por $<?= esc(number_format((float) ($selected_expediente['promesa_activa']['monto_prometido'] ?? 0), 2)) ?>
            para <?= esc($formatDate($selected_expediente['promesa_activa']['fecha_promesa'] ?? null)) ?>
            vía <?= esc((string) ($selected_expediente['promesa_activa']['medio_pago_nombre'] ?? 'medio no especificado')) ?>.
        </div>
    <?php endif; ?>

    <?php if (!empty($selected_expediente['pago_summary']['count'])): ?>
        <div class="cobranza-inline-note cobranza-inline-note--mt-md cobranza-inline-note--success">
            Total reportado: $<?= esc(number_format((float) ($selected_expediente['pago_summary']['amount_total'] ?? 0), 2)) ?>.
            Pagos parciales: <?= esc((string) ($selected_expediente['pago_summary']['partial_count'] ?? 0)) ?>.
            Confirmados: <?= esc((string) ($selected_expediente['pago_summary']['confirmed_count'] ?? 0)) ?>.
            Último reporte: <?= esc($formatDate($selected_expediente['pago_summary']['latest_pago_reportado'] ?? null)) ?>.
        </div>
    <?php endif; ?>

    <?php if (!empty($selected_expediente['pagos_pendientes'])): ?>
        <div class="cobranza-form-card">
            <h3>Pagos pendientes de conciliacion</h3>
            <p class="cobranza-copy">Confirma cada pago reportado para sacarlo de revisión y mover el expediente al siguiente estado operativo.</p>

            <div class="cobranza-timeline cobranza-timeline--top">
                <?php foreach ($selected_expediente['pagos_pendientes'] as $pagoPendiente): ?>
                    <article class="cobranza-timeline-item cobranza-timeline-item--warning">
                        <span class="cobranza-tone is-warning"><?= esc($formatDate($pagoPendiente['fecha_pago_reportada'] ?? null)) ?></span>
                        <h4><?= esc(ucfirst((string) ($pagoPendiente['tipo_pago'] ?? 'Pago'))) ?> por $<?= esc(number_format((float) ($pagoPendiente['monto'] ?? 0), 2)) ?></h4>
                        <p>
                            Medio: <?= esc((string) ($pagoPendiente['medio_pago_nombre'] ?? 'medio no especificado')) ?>.
                            Referencia: <?= esc((string) ($pagoPendiente['referencia_pago'] ?? 'sin referencia')) ?>.
                        </p>

                        <form method="post" action="<?= esc(site_url('deskapp/cobranza/expediente/' . (int) $selected_expediente['id'] . '/pagos/' . (int) $pagoPendiente['id'] . '/confirmar')) ?>" class="cobranza-form-grid cobranza-form-grid--mid">
                            <?= csrf_field() ?>
                            <div class="cobranza-field">
                                <label for="fecha-confirmada-<?= (int) $pagoPendiente['id'] ?>">Fecha confirmada</label>
                                <input id="fecha-confirmada-<?= (int) $pagoPendiente['id'] ?>" type="datetime-local" name="fecha_pago_confirmada" value="<?= esc(date('Y-m-d\TH:i')) ?>" required>
                            </div>

                            <div class="cobranza-field">
                                <label for="seguimiento-confirmado-<?= (int) $pagoPendiente['id'] ?>">Siguiente seguimiento</label>
                                <input id="seguimiento-confirmado-<?= (int) $pagoPendiente['id'] ?>" type="datetime-local" name="fecha_proximo_seguimiento">
                            </div>

                            <div class="cobranza-field is-full">
                                <label for="observaciones-confirmado-<?= (int) $pagoPendiente['id'] ?>">Observaciones de conciliacion</label>
                                <textarea id="observaciones-confirmado-<?= (int) $pagoPendiente['id'] ?>" name="observaciones" placeholder="Confirmación de tesorería, banco, folio o saldo remanente"></textarea>
                            </div>

                            <input type="hidden" name="canal" value="interno">

                            <div class="cobranza-actions">
                                <button type="submit" class="cobranza-btn is-primary">Confirmar pago</button>
                            </div>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($cobranzaSchemaReady && !empty($selected_expediente['can_register_gestion'])): ?>
        <form method="post" action="<?= esc(site_url('deskapp/cobranza/expediente/' . (int) $selected_expediente['id'] . '/gestiones')) ?>" class="cobranza-form-card">
            <?= csrf_field() ?>
            <h3>Registrar gestion</h3>
            <p class="cobranza-copy">Registra el ultimo contacto y deja programado el siguiente seguimiento sin salir del centro de cobranza.</p>

            <div class="cobranza-form-grid">
                <div class="cobranza-field">
                    <label for="tipo-gestion">Tipo</label>
                    <select id="tipo-gestion" name="tipo">
                        <option value="seguimiento">Seguimiento</option>
                        <option value="promesa">Promesa</option>
                        <option value="comentario">Comentario interno</option>
                    </select>
                </div>

                <div class="cobranza-field">
                    <label for="canal-gestion">Canal</label>
                    <select id="canal-gestion" name="canal">
                        <option value="interno">Interno</option>
                        <option value="llamada">Llamada</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="correo">Correo</option>
                    </select>
                </div>

                <div class="cobranza-field">
                    <label for="resultado-gestion">Resultado</label>
                    <select id="resultado-gestion" name="resultado">
                        <option value="seguimiento_registrado">Seguimiento registrado</option>
                        <option value="contacto_exitoso">Contacto exitoso</option>
                        <option value="sin_contacto">Sin contacto</option>
                        <option value="promesa_registrada">Promesa registrada</option>
                    </select>
                </div>

                <div class="cobranza-field">
                    <label for="fecha-proximo-seguimiento">Proximo seguimiento</label>
                    <input id="fecha-proximo-seguimiento" type="datetime-local" name="fecha_proximo_seguimiento">
                </div>

                <div class="cobranza-field is-full">
                    <label for="siguiente-accion">Siguiente accion</label>
                    <input id="siguiente-accion" type="text" name="siguiente_accion" placeholder="Que haremos despues de esta gestion">
                </div>

                <div class="cobranza-field is-full">
                    <label for="comentarios-gestion">Comentarios</label>
                    <textarea id="comentarios-gestion" name="comentarios" placeholder="Resume el contacto, acuerdo o bloqueo encontrado" required></textarea>
                </div>
            </div>

            <div class="cobranza-actions cobranza-actions--spaced">
                <button type="submit" class="cobranza-btn is-primary">Guardar gestion</button>
            </div>
        </form>

        <form method="post" action="<?= esc(site_url('deskapp/cobranza/expediente/' . (int) $selected_expediente['id'] . '/promesas')) ?>" class="cobranza-form-card">
            <?= csrf_field() ?>
            <h3>Registrar promesa de pago</h3>
            <p class="cobranza-copy">La promesa vive como objeto propio del expediente y actualiza el siguiente seguimiento.</p>

            <div class="cobranza-form-grid">
                <div class="cobranza-field">
                    <label for="monto-prometido">Monto prometido</label>
                    <input id="monto-prometido" type="number" step="0.01" min="0" name="monto_prometido" required>
                </div>

                <div class="cobranza-field">
                    <label for="fecha-promesa">Fecha promesa</label>
                    <input id="fecha-promesa" type="datetime-local" name="fecha_promesa" required>
                </div>

                <div class="cobranza-field">
                    <label for="medio-promesa">Medio de pago</label>
                    <select id="medio-promesa" name="medio_pago">
                        <option value="transferencia">Transferencia</option>
                        <option value="deposito">Deposito</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>

                <div class="cobranza-field">
                    <label for="canal-promesa">Canal</label>
                    <select id="canal-promesa" name="canal">
                        <option value="interno">Interno</option>
                        <option value="llamada">Llamada</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="correo">Correo</option>
                    </select>
                </div>

                <div class="cobranza-field is-full">
                    <label for="observaciones-promesa">Observaciones</label>
                    <textarea id="observaciones-promesa" name="observaciones" placeholder="Condiciones acordadas, restricciones o contexto de la promesa"></textarea>
                </div>
            </div>

            <div class="cobranza-actions cobranza-actions--spaced">
                <button type="submit" class="cobranza-btn is-primary">Guardar promesa</button>
            </div>
        </form>

        <form method="post" action="<?= esc(site_url('deskapp/cobranza/expediente/' . (int) $selected_expediente['id'] . '/pagos')) ?>" class="cobranza-form-card">
            <?= csrf_field() ?>
            <h3>Registrar pago reportado</h3>
            <p class="cobranza-copy">Los pagos parciales y totales quedan registrados como objeto propio del expediente y pasan a revisión.</p>

            <div class="cobranza-form-grid">
                <div class="cobranza-field">
                    <label for="monto-pago">Monto</label>
                    <input id="monto-pago" type="number" step="0.01" min="0" name="monto" required>
                </div>

                <div class="cobranza-field">
                    <label for="tipo-pago">Tipo de pago</label>
                    <select id="tipo-pago" name="tipo_pago">
                        <option value="parcial">Parcial</option>
                        <option value="total">Total</option>
                    </select>
                </div>

                <div class="cobranza-field">
                    <label for="fecha-pago-reportada">Fecha reportada</label>
                    <input id="fecha-pago-reportada" type="datetime-local" name="fecha_pago_reportada" required>
                </div>

                <div class="cobranza-field">
                    <label for="medio-pago">Medio de pago</label>
                    <select id="medio-pago" name="medio_pago">
                        <option value="transferencia">Transferencia</option>
                        <option value="deposito">Deposito</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>

                <div class="cobranza-field is-full">
                    <label for="referencia-pago">Referencia de pago</label>
                    <input id="referencia-pago" type="text" name="referencia_pago" placeholder="Folio, SPEI, banco o referencia interna">
                </div>

                <div class="cobranza-field is-full">
                    <label for="observaciones-pago">Observaciones</label>
                    <textarea id="observaciones-pago" name="observaciones" placeholder="Que se reporto, quien lo envio y que queda por validar"></textarea>
                </div>
            </div>

            <input type="hidden" name="canal" value="interno">

            <div class="cobranza-actions cobranza-actions--spaced">
                <button type="submit" class="cobranza-btn is-primary">Guardar pago</button>
            </div>
        </form>
    <?php endif; ?>

    <div class="cobranza-section-top">
        <span class="cobranza-detail-overline">Timeline operativo</span>
        <?php if (empty($selected_expediente['timeline'])): ?>
            <div class="cobranza-empty cobranza-empty--md">
                Aun no hay eventos relevantes para este expediente.
            </div>
        <?php else: ?>
            <div class="cobranza-timeline">
                <?php foreach ($selected_expediente['timeline'] as $event): ?>
                    <article class="cobranza-timeline-item">
                        <span class="cobranza-tone is-<?= esc($event['tone']) ?>"><?= esc($formatDate($event['timestamp'] ?? null)) ?></span>
                        <h4><?= esc($event['title']) ?></h4>
                        <p><?= esc($event['description'] !== '' ? $event['description'] : 'Sin detalle adicional.') ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="cobranza-empty">
        Selecciona un expediente para ver contexto operativo y timeline.
    </div>
<?php endif; ?>