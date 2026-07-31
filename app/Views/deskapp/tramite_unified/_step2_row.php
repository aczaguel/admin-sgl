<?php
/**
 * Tramite Unified Layout - Step 2 Row (Gestión y Derechos)
 *
 * Three-rail layout: form | comprobantes | approval
 * Implements readonly mode based on server-side permission flags.
 *
 * Expected view variables:
 * - $prototypeStep2Form        (canEdit, blockedReason, canUploadDocs, canDeleteDocs,
 *                               docsBlockedReason, deleteBlockedReason, csrfName, csrfHash,
 *                               currentStatusId, currentStep, isApprovedLock, isLockedStatus,
 *                               urls, options, values, docs)
 * - $prototypeCanApproveStep2  (bool)
 * - $prototypeTramiteId        (int)
 *
 * Endpoints:
 *   Gestor save   → POST /deskapp/tramitesn/update_gestor_save/{id}
 *   Derechos save → POST /deskapp/tramitesn/update_derechos_save/{id}
 *   Upload        → POST /deskapp/tramites/upload_comprobante/{id}
 *   Delete        → POST /deskapp/tramites/delete_comprobante/{id}
 *   Approve       → POST /deskapp/tramites/autorizar
 */

// --- Form permissions & data ---
$canEdit              = !empty($prototypeStep2Form['canEdit']);
$blockedReason        = trim((string) ($prototypeStep2Form['blockedReason'] ?? ''));
$canUploadDocs        = !empty($prototypeStep2Form['canUploadDocs']);
$canDeleteDocs        = !empty($prototypeStep2Form['canDeleteDocs']);
$docsBlockedReason    = trim((string) ($prototypeStep2Form['docsBlockedReason'] ?? ''));
$deleteBlockedReason  = trim((string) ($prototypeStep2Form['deleteBlockedReason'] ?? ''));
$tramiteId            = (int) ($prototypeTramiteId ?? 0);
$csrfName             = $prototypeStep2Form['csrfName'] ?? csrf_token();
$csrfHash             = $prototypeStep2Form['csrfHash'] ?? csrf_hash();
$canApprove           = !empty($prototypeCanApproveStep2);

$values               = $prototypeStep2Form['values'] ?? [];
$options              = $prototypeStep2Form['options'] ?? [];
$urls                 = $prototypeStep2Form['urls'] ?? [];
$docs                 = $prototypeStep2Form['docs'] ?? [];

// --- Approval stage logic ---
$currentStep          = (int) ($prototypeStep2Form['currentStep'] ?? 0);
$isApprovedLock       = !empty($prototypeStep2Form['isApprovedLock']);
$isLockedStatus       = !empty($prototypeStep2Form['isLockedStatus']);
$postApprovalStage    = $currentStep > 3 || $isApprovedLock;
$showApproveButton    = $canApprove && !$postApprovalStage && !$isLockedStatus;

// --- Vigencia computation ---
$vigenciaRaw          = trim((string) ($values['derechos_vigencia'] ?? ''));
$vigenciaInputValue   = '';
$vigenciaWarningText  = '';
$vigenciaIsUrgent     = false;

if ($vigenciaRaw !== '') {
    $vigenciaInputValue = str_replace(' ', 'T', substr($vigenciaRaw, 0, 16));
    try {
        $now = new DateTimeImmutable('now');
        $vigenciaDate = new DateTimeImmutable(str_replace('T', ' ', $vigenciaInputValue));
        $secondsRemaining = $vigenciaDate->getTimestamp() - $now->getTimestamp();
        $daysRemaining = (int) floor($secondsRemaining / 86400);
        if ($secondsRemaining < 0) {
            $vigenciaIsUrgent = true;
            $vigenciaWarningText = 'La referencia ya venció. Se requiere atención inmediata.';
        } elseif ($daysRemaining <= 15) {
            $vigenciaIsUrgent = true;
            $vigenciaWarningText = 'La referencia vence en ' . $daysRemaining . ' día(s). Se requiere premura.';
        }
    } catch (Throwable $e) {
        // Ignore invalid date
    }
}

// --- Endpoint URLs ---
$gestorSaveUrl      = $urls['updateGestorSave'] ?? ('/deskapp/tramitesn/update_gestor_save/' . $tramiteId);
$derechosSaveUrl    = $urls['updateDerechosSave'] ?? ('/deskapp/tramitesn/update_derechos_save/' . $tramiteId);
$gestoresByEmpresa  = $urls['getGestoresByEmpresaIdBase'] ?? ('/deskapp/tramitesn/get_gestores_by_empresa/');
$uploadUrl          = '/deskapp/tramites/upload_comprobante/' . $tramiteId;
$deleteUrl          = '/deskapp/tramites/delete_comprobante/' . $tramiteId;
$approveUrl         = '/deskapp/tramites/autorizar';
?>

<section class="tul-step-row tul-step-row--2" data-step-row="2">
    <header class="tul-step-row__header">
        <h3 class="tul-step-row__title">Paso 2 — Gestión y Derechos</h3>
        <?php if (!$canEdit): ?>
            <span class="tul-readonly-badge">
                <i class="icon-lock"></i> Solo lectura
            </span>
        <?php endif; ?>
    </header>

    <div class="tul-three-rail">
        <!-- Carril izquierdo: Formularios (Gestor + Derechos) -->
        <div class="tul-rail tul-rail--form" data-rail="form">
            <?php if (!$canEdit && $blockedReason !== ''): ?>
                <div class="tul-blocked-notice"><?= esc($blockedReason) ?></div>
            <?php endif; ?>

            <!-- Gestor Assignment Form -->
            <form
                data-tul-save
                data-tul-step="2"
                data-tul-url="<?= esc($gestorSaveUrl, 'attr') ?>"
                data-tul-gestores-url="<?= esc($gestoresByEmpresa, 'attr') ?>"
                data-tul-reload
                <?php if (!$canEdit): ?> data-tul-readonly<?php endif; ?>
            >
                <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">

                <fieldset class="tul-fieldset">
                    <legend class="tul-fieldset__legend">Asignación de Gestor</legend>

                    <div class="tul-field-group">
                        <div class="tul-field">
                            <label class="tul-label" for="tul_step2_empresa_gestora">Empresa gestora</label>
                            <select
                                class="tul-input"
                                id="tul_step2_empresa_gestora"
                                name="empresa_gestora_id"
                                <?= !$canEdit ? ' disabled' : '' ?>
                            >
                                <option value="">Seleccione una empresa</option>
                                <?php foreach (($options['empresaGestora'] ?? []) as $optVal => $optLabel): ?>
                                    <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['empresa_gestora_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="tul-field">
                            <label class="tul-label" for="tul_step2_gestor">Gestor</label>
                            <select
                                class="tul-input"
                                id="tul_step2_gestor"
                                name="gestor_id"
                                <?= !$canEdit ? ' disabled' : '' ?>
                            >
                                <option value="">Seleccione un gestor</option>
                                <?php foreach (($options['gestor'] ?? []) as $optVal => $optLabel): ?>
                                    <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['gestor_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="tul-field-help">La lista de gestores depende de la empresa seleccionada.</span>
                        </div>
                    </div>

                    <?php if ($canEdit): ?>
                        <div class="tul-btn-row">
                            <button type="submit" class="tul-btn tul-btn--primary" data-tul-save-btn>Guardar Gestor</button>
                        </div>
                    <?php endif; ?>
                </fieldset>
            </form>

            <!-- Derechos Payment Form -->
            <form
                data-tul-save
                data-tul-step="2"
                data-tul-url="<?= esc($derechosSaveUrl, 'attr') ?>"
                data-tul-reload
                <?php if (!$canEdit): ?> data-tul-readonly<?php endif; ?>
            >
                <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">

                <fieldset class="tul-fieldset">
                    <legend class="tul-fieldset__legend">Pago de Derechos</legend>

                    <div class="tul-field-group">
                        <div class="tul-field">
                            <label class="tul-label" for="tul_step2_derechos_tramite">Monto pago de derechos</label>
                            <input
                                class="tul-input"
                                id="tul_step2_derechos_tramite"
                                type="number"
                                step="0.01"
                                min="0"
                                name="derechos_tramite"
                                value="<?= esc((string) ($values['derechos_tramite'] ?? ''), 'attr') ?>"
                                <?= !$canEdit ? ' disabled' : '' ?>
                            >
                        </div>

                        <div class="tul-field">
                            <label class="tul-label" for="tul_step2_derechos_pago_sitio">Pago</label>
                            <select
                                class="tul-input"
                                id="tul_step2_derechos_pago_sitio"
                                name="derechos_pago_sitio"
                                <?= !$canEdit ? ' disabled' : '' ?>
                            >
                                <option value="">Seleccione una opción</option>
                                <?php foreach (($options['derechosPagoSitio'] ?? []) as $optVal => $optLabel): ?>
                                    <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['derechos_pago_sitio'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="tul-field<?= $vigenciaIsUrgent ? ' tul-field--urgent' : '' ?>">
                            <label class="tul-label" for="tul_step2_derechos_vigencia">Fecha vigencia</label>
                            <input
                                class="tul-input"
                                id="tul_step2_derechos_vigencia"
                                type="datetime-local"
                                name="derechos_vigencia"
                                value="<?= esc($vigenciaInputValue, 'attr') ?>"
                                <?= !$canEdit ? ' disabled' : '' ?>
                            >
                            <?php if ($vigenciaWarningText !== ''): ?>
                                <span class="tul-field-warning"><?= esc($vigenciaWarningText) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="tul-field">
                            <label class="tul-label" for="tul_step2_derechos_revol_cliente">Forma de pago</label>
                            <select
                                class="tul-input"
                                id="tul_step2_derechos_revol_cliente"
                                name="derechos_revol_cliente"
                                <?= !$canEdit ? ' disabled' : '' ?>
                            >
                                <option value="">Seleccione una opción</option>
                                <?php foreach (($options['derechosRevolCliente'] ?? []) as $optVal => $optLabel): ?>
                                    <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['derechos_revol_cliente'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="tul-field tul-field--wide">
                            <label class="tul-label" for="tul_step2_derechos_refer_banc">Referencia bancaria</label>
                            <input
                                class="tul-input"
                                id="tul_step2_derechos_refer_banc"
                                type="text"
                                name="derechos_refer_banc"
                                maxlength="100"
                                value="<?= esc((string) ($values['derechos_refer_banc'] ?? ''), 'attr') ?>"
                                <?= !$canEdit ? ' disabled' : '' ?>
                            >
                        </div>
                    </div>

                    <?php if ($canEdit): ?>
                        <div class="tul-btn-row">
                            <button type="submit" class="tul-btn tul-btn--primary" data-tul-save-btn>Guardar Derechos</button>
                        </div>
                    <?php endif; ?>
                </fieldset>
            </form>
        </div>

        <!-- Carril centro: Comprobantes (Upload + Galería) -->
        <div class="tul-rail tul-rail--docs" data-rail="docs">
            <h4 class="tul-rail__title">Comprobantes de pago</h4>

            <?php if (!$canUploadDocs && $docsBlockedReason !== ''): ?>
                <div class="tul-blocked-notice"><?= esc($docsBlockedReason) ?></div>
            <?php endif; ?>

            <?php if ($canUploadDocs): ?>
                <div
                    class="tul-dropzone"
                    data-tul-dropzone
                    data-tul-step="2"
                    data-tul-upload-url="<?= esc($uploadUrl, 'attr') ?>"
                >
                    <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">
                    <input type="file" class="tul-dropzone__input" data-tul-file-input>
                    <span class="tul-dropzone__label">Arrastra comprobantes o haz clic para seleccionar</span>
                    <div class="tul-dropzone__progress" data-tul-upload-progress hidden>Subiendo...</div>
                </div>
            <?php endif; ?>

            <div class="tul-gallery" data-tul-gallery data-tul-step="2">
                <?php if (!empty($docs) && is_array($docs)): ?>
                    <?php foreach ($docs as $doc): ?>
                        <?php
                            $docFile = (string) ($doc['file'] ?? '');
                            $docId   = (string) ($doc['id'] ?? $docFile);
                            $docName = $docFile !== '' ? $docFile : 'Archivo';
                            $docUrl  = ($doc['url'] ?? '') !== '' ? $doc['url'] : '#';
                            $isImage = !empty($doc['is_image']);
                        ?>
                        <div class="tul-gallery__item" data-tul-doc data-tul-doc-id="<?= esc($docId, 'attr') ?>">
                            <?php if ($isImage && $docUrl !== '#'): ?>
                                <img class="tul-gallery__item-preview" src="<?= esc($docUrl, 'attr') ?>" alt="<?= esc($docName, 'attr') ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="tul-gallery__item-info">
                                <?php if ($docUrl !== '#'): ?>
                                    <a class="tul-gallery__item-link" href="<?= esc($docUrl, 'attr') ?>" target="_blank" rel="noreferrer"><?= esc($docName) ?></a>
                                <?php else: ?>
                                    <span class="tul-gallery__item-name"><?= esc($docName) ?></span>
                                <?php endif; ?>
                                <span class="tul-gallery__item-meta">Comprobante de derechos</span>
                            </div>

                            <?php if ($canDeleteDocs): ?>
                                <button
                                    type="button"
                                    class="tul-btn tul-btn--danger tul-btn--small"
                                    data-tul-delete-btn
                                    data-tul-delete-url="<?= esc($deleteUrl, 'attr') ?>"
                                    data-tul-doc-id="<?= esc($docId, 'attr') ?>"
                                    data-tul-doc-file="<?= esc($docFile, 'attr') ?>"
                                    title="Eliminar comprobante"
                                >&times;</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="tul-gallery__empty">Sin comprobantes cargados.</p>
                <?php endif; ?>
            </div>

            <?php if (!$canDeleteDocs && $deleteBlockedReason !== ''): ?>
                <div class="tul-blocked-notice tul-blocked-notice--subtle"><?= esc($deleteBlockedReason) ?></div>
            <?php endif; ?>
        </div>

        <!-- Carril derecho: Aprobación -->
        <div class="tul-rail tul-rail--notes" data-rail="notes">
            <?php if ($showApproveButton): ?>
                <div class="tul-approval-card">
                    <div class="tul-approval-card__head">
                        <span class="tul-approval-card__icon"><i class="icon-check-circle"></i></span>
                        <div>
                            <h4 class="tul-approval-card__title">Listo para aprobar</h4>
                            <p class="tul-approval-card__copy">Gestor y derechos capturados. Al aprobar, el trámite pasa a la fase financiera (Pago a Gestor y Cobro a Cliente).</p>
                        </div>
                    </div>
                    <form
                        class="tul-approval-form"
                        data-tul-save
                        data-tul-step="2"
                        data-tul-url="<?= esc($approveUrl, 'attr') ?>"
                        data-tul-reload
                    >
                        <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">
                        <input type="hidden" name="tramite_id" value="<?= (int) $tramiteId ?>">
                        <input type="hidden" name="status_id" value="23">
                        <button type="submit" class="tul-btn tul-btn--approve" data-tul-save-btn>
                            <i class="icon-check"></i> Aprobar trámite
                        </button>
                    </form>
                </div>
            <?php elseif ($postApprovalStage): ?>
                <div class="tul-approval-status tul-approval-status--done">
                    <span class="tul-approval-status__icon"><i class="icon-check-circle"></i></span>
                    <div>
                        <h4 class="tul-rail__title">Trámite autorizado</h4>
                        <p class="tul-rail__info">El trámite ya fue autorizado y avanzó a la fase financiera.</p>
                    </div>
                </div>
            <?php elseif ($isLockedStatus): ?>
                <h4 class="tul-rail__title">Estado de aprobación</h4>
                <p class="tul-rail__info">El trámite está concluido o cancelado. La autorización ya no aplica.</p>
            <?php else: ?>
                <h4 class="tul-rail__title">Estado de aprobación</h4>
                <p class="tul-rail__info">Este perfil no dispone de permisos para aprobar el trámite en esta etapa.</p>
            <?php endif; ?>

            <?php if ($vigenciaWarningText !== ''): ?>
                <div class="tul-vigencia-alert<?= $vigenciaIsUrgent ? ' tul-vigencia-alert--urgent' : '' ?>">
                    <strong class="tul-vigencia-alert__title">Vigencia</strong>
                    <p class="tul-vigencia-alert__text"><?= esc($vigenciaWarningText) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
