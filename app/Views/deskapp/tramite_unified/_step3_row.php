<style>
.tul-approved-ribbon {
    display: flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(90deg, #d1fae5, #ecfdf5);
    border: 1px solid #6ee7b7;
    border-radius: 8px;
    padding: 10px 14px;
    color: #065f46;
    font-weight: 700;
    font-size: 13px;
    margin-top: 12px;
}
.tul-btn--aprobar-evidencias {
    width: 100%;
    margin-top: 12px;
    background: #0f766e;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background 0.18s;
}
.tul-btn--aprobar-evidencias:hover {
    background: #0d6561;
}
.tul-gate-badge--waiting {
    background: #fef3c7;
    border: 1px solid #fcd34d;
    color: #92400e;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
</style>
<?php
/**
 * Step 3 Partial — Evidencias Finales
 *
 * Three-rail layout: status indicators | evidence upload | bitácora
 * Implements readonly mode based on server-side permission flags.
 *
 * Expected view variables:
 * - $prototypeStep3Form       (canUpload, canDelete, blockedReason, deleteBlockedReason,
 *                              csrfName, csrfHash, urls, tramiteId,
 *                              options, docs, hasTramiteRecibido, hasAcuseRecibo)
 * - $prototypeEvidenceForm    (canView, canAdd, blockedReason, csrfName, csrfHash,
 *                              tramiteId, urls, items)
 * - $prototypeTramiteId       (int)
 */

// --- Step 3 permissions & data ---
$canUpload             = !empty($prototypeStep3Form['canUpload']);
$canDelete             = !empty($prototypeStep3Form['canDelete']);
$blockedReason         = trim((string) ($prototypeStep3Form['blockedReason'] ?? ''));
$deleteBlockedReason   = trim((string) ($prototypeStep3Form['deleteBlockedReason'] ?? ''));
$tramiteId             = (int) ($prototypeTramiteId ?? 0);
$csrfName              = $prototypeStep3Form['csrfName'] ?? csrf_token();
$csrfHash              = $prototypeStep3Form['csrfHash'] ?? csrf_hash();
$documents             = $prototypeStep3Form['docs'] ?? [];
$hasTramiteRecibido    = !empty($prototypeStep3Form['hasTramiteRecibido']);
$hasAcuseRecibo        = !empty($prototypeStep3Form['hasAcuseRecibo']);

// --- Evidence/Notes permissions & data (shared bitácora with step 1) ---
$evidenceCanView = !empty($prototypeEvidenceForm['canView']);
$evidenceCanAdd  = !empty($prototypeEvidenceForm['canAdd']);
$evidenceItems   = $prototypeEvidenceForm['items'] ?? [];
$evidenceUrls    = $prototypeEvidenceForm['urls'] ?? [];

// --- Approve button data ---
$canAprobarEvidencias = !empty($prototypeStep3Form['canAprobarEvidencias']);
$evidenciasAprobadas  = !empty($prototypeStep3Form['evidenciasAprobadas']);
$aprobarUrl           = (string) ($prototypeStep3Form['aprobarEvidenciasUrl'] ?? '');
$csrfName             = $prototypeStep3Form['csrfName'] ?? csrf_token();
$csrfHash             = $prototypeStep3Form['csrfHash'] ?? csrf_hash();

// --- Endpoint URLs ---
$uploadUrl = base_url('deskapp/tramitesn/upload_pago_gestor/' . $tramiteId);
$notesUrl  = base_url('deskapp/tramitesn/prototype_evidencias_add/' . $tramiteId);
?>

<?php
$tulLocked = !empty($tulStep3Locked);
$tulLockReason = (string) ($tulStep3LockReason ?? '');
?>
<section class="tul-step-row tul-step-row--3<?= $tulLocked ? ' tul-step-row--locked' : '' ?>" data-step-row="3">
    <header class="tul-step-row__header">
        <h3 class="tul-step-row__title">Paso 3 — Evidencias Finales</h3>
        <?php if ($tulLocked): ?>
            <span class="tul-lock-badge"><i class="icon-lock"></i> Bloqueado</span>
        <?php elseif (!$canUpload): ?>
            <span class="tul-readonly-badge">
                <i class="icon-lock"></i> Solo lectura
            </span>
        <?php endif; ?>
    </header>

    <?php if ($tulLocked): ?>
        <div class="tul-lock-notice">
            <span class="tul-lock-notice__icon"><i class="icon-lock"></i></span>
            <span class="tul-lock-notice__text"><?= esc($tulLockReason) ?></span>
        </div>
    <?php else: ?>
    <div class="tul-three-rail">
        <!-- Carril izquierdo: Indicadores de status -->
        <div class="tul-rail tul-rail--form" data-rail="form">
            <?php if (!$canUpload && $blockedReason !== ''): ?>
                <div class="tul-blocked-notice"><?= esc($blockedReason) ?></div>
            <?php endif; ?>

            <div class="tul-status-indicators">
                <h4 class="tul-status-indicators__title">Estado de evidencias</h4>

                <div class="tul-status-item <?= $hasTramiteRecibido ? 'tul-status-item--done' : 'tul-status-item--pending' ?>">
                    <span class="tul-status-item__icon">
                        <i class="<?= $hasTramiteRecibido ? 'icon-check-circle' : 'icon-clock' ?>"></i>
                    </span>
                    <span class="tul-status-item__label">Trámite Recibido</span>
                    <span class="tul-status-item__value"><?= $hasTramiteRecibido ? 'Cargado' : 'Pendiente' ?></span>
                </div>

                <div class="tul-status-item <?= $hasAcuseRecibo ? 'tul-status-item--done' : 'tul-status-item--pending' ?>">
                    <span class="tul-status-item__icon">
                        <i class="<?= $hasAcuseRecibo ? 'icon-check-circle' : 'icon-clock' ?>"></i>
                    </span>
                    <span class="tul-status-item__label">Acuse Recibo Cliente</span>
                    <span class="tul-status-item__value"><?= $hasAcuseRecibo ? 'Cargado' : 'Pendiente' ?></span>
                </div>
            </div>

            <?php if ($hasTramiteRecibido && $hasAcuseRecibo): ?>
                <div class="tul-gate-badge tul-gate-badge--complete">
                    <i class="icon-check"></i> Cierre documental completo
                </div>
            <?php else: ?>
                <div class="tul-gate-badge tul-gate-badge--pending">
                    <i class="icon-info"></i> Cierre documental pendiente
                </div>
            <?php endif; ?>

            <?php if ($evidenciasAprobadas): ?>
                <div class="tul-approved-ribbon">
                    <i class="icon-check-circle"></i>
                    <span>Evidencias aprobadas — fase financiera desbloqueada</span>
                </div>
            <?php elseif ($canAprobarEvidencias): ?>
                <button
                    type="button"
                    class="tul-btn tul-btn--approve tul-btn--aprobar-evidencias"
                    data-tul-aprobar-evidencias
                    data-tul-tramite-id="<?= (int) $tramiteId ?>"
                    data-tul-url="<?= esc($aprobarUrl, 'attr') ?>"
                    data-csrf-name="<?= esc($csrfName, 'attr') ?>"
                    data-csrf-hash="<?= esc($csrfHash, 'attr') ?>">
                    <i class="icon-check"></i> Aprobar Evidencias Finales
                </button>
            <?php elseif ($hasTramiteRecibido && $hasAcuseRecibo): ?>
                <div class="tul-gate-badge tul-gate-badge--waiting">
                    <i class="icon-clock"></i> Pendiente de aprobación por supervisor
                </div>
            <?php endif; ?>
        </div>

        <!-- Carril centro: Dropzones de evidencia y galería -->
        <div class="tul-rail tul-rail--docs" data-rail="docs">
            <?php if ($canUpload && !$evidenciasAprobadas): ?>
                <!-- Dropzone: Trámite Recibido -->
                <div class="tul-dropzone"
                     data-tul-dropzone
                     data-tul-step="3"
                     data-tul-upload-url="<?= esc($uploadUrl, 'attr') ?>">
                    <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">
                    <input type="hidden" name="comprobante_final" value="tramite_recibido">
                    <input type="file" class="tul-dropzone__input" data-tul-file-input multiple>
                    <div class="tul-dropzone__content">
                        <span class="tul-dropzone__label">Trámite Recibido</span>
                        <span class="tul-dropzone__hint">Arrastra o haz clic para subir uno o más archivos</span>
                    </div>
                    <div class="tul-dropzone__progress" data-tul-upload-progress hidden></div>
                </div>

                <!-- Dropzone: Acuse Recibo Cliente -->
                <div class="tul-dropzone"
                     data-tul-dropzone
                     data-tul-step="3"
                     data-tul-upload-url="<?= esc($uploadUrl, 'attr') ?>">
                    <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">
                    <input type="hidden" name="comprobante_final" value="acuse_recibo_cliente">
                    <input type="file" class="tul-dropzone__input" data-tul-file-input multiple>
                    <div class="tul-dropzone__content">
                        <span class="tul-dropzone__label">Acuse Recibo Cliente</span>
                        <span class="tul-dropzone__hint">Arrastra o haz clic para subir uno o más archivos</span>
                    </div>
                    <div class="tul-dropzone__progress" data-tul-upload-progress hidden></div>
                </div>
            <?php endif; // end canUpload && !evidenciasAprobadas ?>

            <!-- Galería de documentos cargados -->
            <div class="tul-gallery" data-tul-gallery data-tul-step="3">
                <h4 class="tul-gallery__title">Evidencias registradas</h4>
                <?php if (!empty($documents)): ?>
                    <?php
                        $tulEvidenceLabels = [
                            'tramite_recibido' => 'Trámite Recibido',
                            'acuse_recibo_cliente' => 'Acuse de Recibo del Cliente',
                        ];
                    ?>
                    <?php foreach ($documents as $doc): ?>
                        <?php
                            $docFile = (string) ($doc['file'] ?? '');
                            $docTipo = (string) ($doc['comprobante_final'] ?? $doc['tipo'] ?? '');
                            $docLabel = $tulEvidenceLabels[$docTipo] ?? ($docTipo !== '' ? $docTipo : 'Evidencia');
                            $docUrl  = ($doc['url'] ?? '') !== '' ? $doc['url'] : '#';
                            $isImage = !empty($doc['is_image']);
                        ?>
                        <div class="tul-gallery__item" data-tul-doc data-tul-doc-file="<?= esc($docFile, 'attr') ?>">
                            <?php if ($isImage && $docUrl !== '#'): ?>
                                <img class="tul-gallery__item-preview" src="<?= esc($docUrl, 'attr') ?>" alt="<?= esc($docLabel, 'attr') ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="tul-gallery__item-info">
                                <span class="tul-gallery__item-name"><?= esc($docLabel) ?></span>
                                <?php if ($docUrl !== '#'): ?>
                                    <a class="tul-gallery__item-link"
                                       href="<?= esc($docUrl, 'attr') ?>"
                                       target="_blank"
                                       rel="noopener"><?= esc($docFile) ?></a>
                                <?php endif; ?>
                            </div>
                            <?php if ($canDelete && $docFile !== ''): ?>
                                <button type="button"
                                        class="tul-btn tul-btn--danger tul-btn--sm"
                                        data-tul-delete-btn
                                        data-tul-reload
                                        data-tul-delete-url="/deskapp/tramitesn/delete_pago_gestor"
                                        data-tul-tramite-id="<?= (int) $tramiteId ?>"
                                        data-tul-doc-file="<?= esc($docFile, 'attr') ?>">Eliminar</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="tul-gallery__empty">Sin evidencias registradas.</p>
                <?php endif; ?>

                <?php if (!$canDelete && $deleteBlockedReason !== '' && !empty($documents)): ?>
                    <div class="tul-blocked-notice"><?= esc($deleteBlockedReason) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Carril derecho: Bitácora general (compartida con paso 1) -->
        <div class="tul-rail tul-rail--notes" data-rail="notes">
            <?php if ($evidenceCanView): ?>
                <?php if ($evidenceCanAdd): ?>
                    <form class="tul-notes-form"
                          data-tul-notes
                          data-tul-notes-group="general"
                          data-tul-step="3"
                          data-tul-url="<?= esc($notesUrl, 'attr') ?>">
                        <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">
                        <label for="tul_step3_note_input">Agregar comentario</label>
                        <textarea
                            id="tul_step3_note_input"
                            name="comentario"
                            placeholder="Escribe un comentario operativo para el expediente"
                            data-tul-note-input
                        ></textarea>
                        <div class="tul-btn-row">
                            <button type="submit" class="tul-btn tul-btn--primary" data-tul-note-btn>Agregar</button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="tul-notes-list" data-tul-notes-list data-tul-notes-group="general"<?= empty($evidenceItems) ? ' hidden' : '' ?>>
                    <?php foreach ($evidenceItems as $noteItem): ?>
                        <div class="tul-notes-list__item">
                            <span class="tul-notes-list__meta">
                                <?= esc((string) ($noteItem['createdAtLabel'] ?? 'Sin fecha') . ' · ' . (string) ($noteItem['author'] ?? 'Sistema')) ?>
                            </span>
                            <p class="tul-notes-list__text"><?= esc((string) ($noteItem['text'] ?? $noteItem['comment'] ?? '')) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="tul-rail__empty">Bitácora no disponible.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</section>
