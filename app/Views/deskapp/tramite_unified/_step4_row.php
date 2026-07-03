<?php
/**
 * Step 4 — Pago a Gestor (Accordion, initially collapsed)
 *
 * Three-rail layout inside accordion: form + costs | documents | notes
 * Implements readonly mode based on server-side permission flags.
 * No inline style attributes — all styling via tul-* CSS classes.
 *
 * Expected view variables:
 * - $prototypeStep4Form       (canView, canEdit, canUploadDocs, canDeleteDocs, blockedReason, uploadBlockedReason, deleteBlockedReason, csrfName, csrfHash, tramiteId, fileBaseUrl, url, urls, options, docs, values)
 * - $prototypeStep4NotesForm  (canView, canAdd, blockedReason, csrfName, csrfHash, tramiteId, urls, items)
 * - $prototypeTramiteId       (int)
 */

// --- Form permissions & data ---
$canView       = !empty($prototypeStep4Form['canView']);
$canEdit       = !empty($prototypeStep4Form['canEdit']);
$canUploadDocs = !empty($prototypeStep4Form['canUploadDocs']);
$canDeleteDocs = !empty($prototypeStep4Form['canDeleteDocs']);
$blockedReason        = trim((string) ($prototypeStep4Form['blockedReason'] ?? ''));
$uploadBlockedReason  = trim((string) ($prototypeStep4Form['uploadBlockedReason'] ?? ''));
$deleteBlockedReason  = trim((string) ($prototypeStep4Form['deleteBlockedReason'] ?? ''));
$tramiteId     = (int) ($prototypeTramiteId ?? 0);
$csrfName      = $prototypeStep4Form['csrfName'] ?? csrf_token();
$csrfHash      = $prototypeStep4Form['csrfHash'] ?? csrf_hash();
$documents     = $prototypeStep4Form['docs'] ?? [];
$fileBaseUrl   = rtrim((string) ($prototypeStep4Form['fileBaseUrl'] ?? ''), '/');

// --- Form values ---
$values = $prototypeStep4Form['values'] ?? [];

// --- Options ---
$pagoGestorStatusOptions   = $prototypeStep4Form['options']['pagoGestorStatus'] ?? [];
$statusDoctosGestorOptions = $prototypeStep4Form['options']['statusDoctosGestor'] ?? [];
$reembolsoStatusOptions    = $prototypeStep4Form['options']['reembolsoStatus'] ?? [];
$comprobanteFinalOptions   = $prototypeStep4Form['options']['comprobanteFinal'] ?? [];

// --- Notes permissions & data ---
$notesCanView      = !empty($prototypeStep4NotesForm['canView']);
$notesCanAdd       = !empty($prototypeStep4NotesForm['canAdd']);
$notesBlockedReason = trim((string) ($prototypeStep4NotesForm['blockedReason'] ?? ''));
$notesItems        = $prototypeStep4NotesForm['items'] ?? [];
$notesCsrfName     = $prototypeStep4NotesForm['csrfName'] ?? $csrfName;
$notesCsrfHash     = $prototypeStep4NotesForm['csrfHash'] ?? $csrfHash;

// --- Endpoints ---
$saveUrl       = '/deskapp/tramitesn/update_pago_gestor/' . $tramiteId;
$uploadUrl     = '/deskapp/tramitesn/upload_pago_gestor/' . $tramiteId;
$deleteUrl     = $prototypeStep4Form['urls']['delete'] ?? '/deskapp/tramitesn/delete_pago_gestor';
$costsUrl      = '/deskapp/tramitesn/get_service_costs_by_tramite/' . $tramiteId;
$costUpdateUrl = '/deskapp/tramitesn/update_service_cost';
$notesUrl      = '/deskapp/tramitesn/prototype_step4_notes_add/' . $tramiteId;
?>
<?php $tulLocked = !empty($tulFinanceLocked); $tulLockReason = (string) ($tulFinanceLockReason ?? ''); ?>
<section class="tul-step-row tul-step-row--4 tul-accordion<?= $tulLocked ? ' tul-step-row--locked' : '' ?>" data-step-row="4" data-accordion>
    <header class="tul-step-row__header tul-accordion__trigger" data-accordion-trigger>
        <h3 class="tul-step-row__title">Paso 4 — Pago a Gestor</h3>
        <?php if ($tulLocked): ?>
            <span class="tul-lock-badge"><i class="icon-lock"></i> Bloqueado</span>
        <?php elseif (!$canEdit && $canView): ?>
            <span class="tul-readonly-badge">

                <i class="icon-lock"></i> Solo lectura
            </span>
        <?php endif; ?>
        <span class="tul-accordion__icon"></span>
    </header>

    <div class="tul-accordion__body" data-accordion-body aria-hidden="true">
        <?php if ($tulLocked): ?>
        <div class="tul-lock-notice">
            <span class="tul-lock-notice__icon"><i class="icon-lock"></i></span>
            <span class="tul-lock-notice__text"><?= esc($tulLockReason) ?></span>
        </div>
        <?php elseif ($canView): ?>
        <div class="tul-three-rail">

            <!-- Carril izquierdo: Formulario financiero + Costos por servicio -->
            <div class="tul-rail tul-rail--form" data-rail="form">
                <?php if (!$canEdit && $blockedReason !== ''): ?>
                    <div class="tul-blocked-notice"><?= esc($blockedReason) ?></div>
                <?php endif; ?>

                <form
                    data-tul-save
                    data-tul-step="4"
                    data-tul-url="<?= esc($saveUrl, 'attr') ?>"
                    data-tul-reload
                    data-tul-step4-finance
                    <?php if (!$canEdit): ?> data-tul-readonly<?php endif; ?>
                >
                    <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">

                    <div class="tul-field-grid">
                        <div class="tul-field">
                            <label for="tul_step4_num_factura_gestor">Número de factura</label>
                            <input
                                type="text"
                                id="tul_step4_num_factura_gestor"
                                name="num_factura_gestor"
                                value="<?= esc((string) ($values['num_factura_gestor'] ?? ''), 'attr') ?>"
                                <?= $canEdit ? '' : ' disabled' ?>
                            >
                        </div>

                        <div class="tul-field">
                            <label for="tul_step4_costo_tramite">Costo del trámite</label>
                            <input
                                type="number"
                                step="0.01"
                                id="tul_step4_costo_tramite"
                                name="costo_tramite"
                                value="<?= esc((string) ($values['costo_tramite'] ?? ''), 'attr') ?>"
                                <?= $canEdit ? '' : ' disabled' ?>
                            >
                        </div>

                        <div class="tul-field">
                            <label for="tul_step4_deposito_gestor">Depósito a gestor</label>
                            <input
                                type="number"
                                step="0.01"
                                id="tul_step4_deposito_gestor"
                                name="deposito_gestor"
                                value="<?= esc((string) ($values['deposito_gestor'] ?? ''), 'attr') ?>"
                                <?= $canEdit ? '' : ' disabled' ?>
                            >
                        </div>

                        <div class="tul-field">
                            <label for="tul_step4_col_a_favor">Saldo pendiente</label>
                            <input
                                type="number"
                                step="0.01"
                                id="tul_step4_col_a_favor"
                                name="col_a_favor"
                                value="<?= esc((string) ($values['col_a_favor'] ?? ''), 'attr') ?>"
                                data-tul-step4-out="saldo"
                                readonly
                            >
                        </div>

                        <div class="tul-field">
                            <label for="tul_step4_impuesto_gestoria">Honorarios de gestoría</label>
                            <input
                                type="number"
                                step="0.01"
                                id="tul_step4_impuesto_gestoria"
                                name="impuesto_gestoria"
                                value="<?= esc((string) ($values['impuesto_gestoria'] ?? ''), 'attr') ?>"
                                <?= $canEdit ? '' : ' disabled' ?>
                            >
                        </div>

                        <div class="tul-field">
                            <label for="tul_step4_gestoria_comision">Gratificación</label>
                            <input
                                type="number"
                                step="0.01"
                                id="tul_step4_gestoria_comision"
                                name="gestoria_comision"
                                value="<?= esc((string) ($values['gestoria_comision'] ?? ''), 'attr') ?>"
                                <?= $canEdit ? '' : ' disabled' ?>
                            >
                        </div>

                        <div class="tul-field">
                            <label for="tul_step4_costo_paqueteria">Costo paquetería</label>
                            <input
                                type="number"
                                step="0.01"
                                id="tul_step4_costo_paqueteria"
                                name="costo_paqueteria"
                                value="<?= esc((string) ($values['costo_paqueteria'] ?? ''), 'attr') ?>"
                                <?= $canEdit ? '' : ' disabled' ?>
                            >
                        </div>

                        <div class="tul-field">
                            <label for="tul_step4_gestor_total_pago">Pago total</label>
                            <input
                                type="number"
                                step="0.01"
                                id="tul_step4_gestor_total_pago"
                                name="gestor_total_pago"
                                value="<?= esc((string) ($values['gestor_total_pago'] ?? ''), 'attr') ?>"
                                data-tul-step4-out="total"
                                readonly
                            >
                        </div>

                        <div class="tul-field">
                            <label for="tul_step4_pago_gestor_st_id">Estatus del pago</label>
                            <select
                                id="tul_step4_pago_gestor_st_id"
                                name="pago_gestor_st_id"
                                <?= $canEdit ? '' : ' disabled' ?>
                            >
                                <?php foreach ($pagoGestorStatusOptions as $optVal => $optLabel): ?>
                                    <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['pago_gestor_st_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="tul-field">
                            <label for="tul_step4_status_doctos_gestor">Estatus de documentos</label>
                            <select
                                id="tul_step4_status_doctos_gestor"
                                name="status_doctos_gestor"
                                <?= $canEdit ? '' : ' disabled' ?>
                            >
                                <?php foreach ($statusDoctosGestorOptions as $optVal => $optLabel): ?>
                                    <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['status_doctos_gestor'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="tul-field">
                            <label for="tul_step4_reembolso_status_id">Estatus del reembolso</label>
                            <select
                                id="tul_step4_reembolso_status_id"
                                name="reembolso_status_id"
                                <?= $canEdit ? '' : ' disabled' ?>
                            >
                                <?php foreach ($reembolsoStatusOptions as $optVal => $optLabel): ?>
                                    <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['reembolso_status_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="tul-step4-saldo is-even" data-tul-step4-saldo>Sin saldo pendiente</div>
                    <div class="tul-step4-breakdown" data-tul-step4-breakdown></div>

                    <?php if ($canEdit): ?>
                        <div class="tul-btn-row">
                            <button type="submit" class="tul-btn tul-btn--primary" data-tul-save-btn>Guardar Pago a Gestor</button>
                        </div>
                    <?php endif; ?>

                    <div class="tul-form-feedback" data-tul-feedback hidden></div>
                </form>

                <!-- Service Costs Table -->
                <div class="tul-costs-panel" data-tul-costs data-tul-step="4" data-tul-costs-url="<?= esc($costsUrl, 'attr') ?>" data-tul-cost-update-url="<?= esc($costUpdateUrl, 'attr') ?>">
                    <h4 class="tul-costs-panel__title">Costo por trámite asociado</h4>
                    <p class="tul-costs-panel__hint">Especifica el costo de cada trámite asociado. La suma se toma como Costo del trámite.</p>
                    <div class="tul-costs-panel__header">
                        <span>Trámite asociado</span>
                        <span>Costo</span>
                    </div>
                    <div class="tul-costs-panel__list" data-tul-costs-list>
                        <span class="tul-inline-note">Cargando trámites asociados...</span>
                    </div>
                    <div class="tul-costs-panel__total">
                        <span>Total de costos</span>
                        <strong data-tul-costs-total>$0.00</strong>
                    </div>
                </div>
            </div>

            <!-- Carril centro: Documentos (factura_gestor / comprobante_pago) -->
            <div class="tul-rail tul-rail--docs" data-rail="docs">
                <?php if ($canUploadDocs): ?>
                    <div class="tul-dropzone" data-tul-dropzone data-tul-step="4" data-tul-upload-url="<?= esc($uploadUrl, 'attr') ?>">
                        <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">

                        <div class="tul-field">
                            <label for="tul_step4_doc_type">Tipo de comprobante</label>
                            <select id="tul_step4_doc_type" name="comprobante_final" data-tul-doc-type>
                                <option value="factura_gestor">Factura del gestor</option>
                                <option value="comprobante_pago">Comprobante de pago</option>
                                <?php foreach ($comprobanteFinalOptions as $optVal => $optLabel): ?>
                                    <?php if (!in_array((string) $optVal, ['factura_gestor', 'comprobante_pago'], true)): ?>
                                        <option value="<?= esc((string) $optVal, 'attr') ?>"><?= esc((string) $optLabel) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="tul-dropzone__area">
                            <input type="file" class="tul-dropzone__input" data-tul-file-input>
                            <span class="tul-dropzone__label">Arrastra aquí la factura o comprobante del gestor</span>
                            <span class="tul-dropzone__meta" data-tul-file-meta>Sin archivo seleccionado</span>
                        </div>
                        <div class="tul-dropzone__progress" data-tul-upload-progress hidden></div>

                        <div class="tul-btn-row">
                            <button type="button" class="tul-btn tul-btn--primary" data-tul-upload-btn>Subir comprobante</button>
                        </div>
                    </div>
                <?php elseif ($uploadBlockedReason !== ''): ?>
                    <div class="tul-blocked-notice"><?= esc($uploadBlockedReason) ?></div>
                <?php endif; ?>

                <!-- Gallery: documentos registrados -->
                <div class="tul-gallery" data-tul-gallery data-tul-step="4">
                    <h4 class="tul-gallery__title">Comprobantes registrados</h4>
                    <?php if (!empty($documents) && is_array($documents)): ?>
                        <?php foreach ($documents as $docRow): ?>
                            <?php
                                $docFile   = (string) ($docRow['file'] ?? '');
                                $docType   = (string) ($docRow['comprobante_final'] ?? '');
                                $docId     = (string) ($docRow['id'] ?? '');
                                $docUrl    = $fileBaseUrl !== '' && $docFile !== '' ? $fileBaseUrl . '/' . rawurlencode($docFile) : '#';
                                $docLabel  = (string) ($comprobanteFinalOptions[$docType] ?? ($docType !== '' ? $docType : 'Documento de pago'));
                            ?>
                            <div class="tul-gallery__item" data-tul-doc data-tul-doc-id="<?= esc($docId, 'attr') ?>">
                                <div class="tul-gallery__item-info">
                                    <a class="tul-gallery__item-link" href="<?= esc($docUrl, 'attr') ?>" target="_blank" rel="noreferrer" title="<?= esc($docFile, 'attr') ?>"><?= esc($docFile !== '' ? $docFile : 'Sin nombre') ?></a>
                                    <span class="tul-gallery__item-meta"><?= esc($docLabel) ?></span>
                                </div>
                                <?php if ($canDeleteDocs): ?>
                                    <button
                                        type="button"
                                        class="tul-btn tul-btn--danger tul-btn--small"
                                        data-tul-delete-btn
                                        data-tul-reload
                                        data-tul-delete-url="<?= esc($deleteUrl, 'attr') ?>"
                                        data-tul-tramite-id="<?= (int) $tramiteId ?>"
                                        data-tul-doc-file="<?= esc($docFile, 'attr') ?>"
                                    >Eliminar</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="tul-gallery__empty">Sin comprobantes de pago a gestor registrados.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Carril derecho: Notas internas paso 4 -->
            <div class="tul-rail tul-rail--notes" data-rail="notes">
                <?php if ($notesCanView): ?>
                    <div class="tul-notes-panel" data-tul-notes data-tul-step="4" data-tul-url="<?= esc($notesUrl, 'attr') ?>">
                        <h4 class="tul-notes-panel__title">Notas de Pago a Gestor</h4>

                        <?php if ($notesCanAdd): ?>
                            <form class="tul-notes-form" data-tul-note-form>
                                <input type="hidden" name="<?= esc($notesCsrfName, 'attr') ?>" value="<?= esc($notesCsrfHash, 'attr') ?>">
                                <label for="tul_step4_note_input">Agregar nota interna</label>
                                <textarea
                                    id="tul_step4_note_input"
                                    class="tul-notes-panel__input"
                                    name="comentario"
                                    placeholder="Escribe aquí una nota interna de seguimiento para Pago a Gestor"
                                    data-tul-note-input
                                ></textarea>
                                <div class="tul-btn-row">
                                    <button type="submit" class="tul-btn tul-btn--primary" data-tul-note-btn>Guardar nota interna</button>
                                </div>
                            </form>
                        <?php elseif ($notesBlockedReason !== ''): ?>
                            <div class="tul-blocked-notice"><?= esc($notesBlockedReason) ?></div>
                        <?php endif; ?>

                        <div class="tul-form-feedback" data-tul-note-feedback hidden></div>

                        <div class="tul-notes-panel__list" data-tul-note-list<?= empty($notesItems) ? ' hidden' : '' ?>>
                            <?php foreach ($notesItems as $noteItem): ?>
                                <div class="tul-note-item">
                                    <span class="tul-note-item__meta"><?= esc((string) ($noteItem['createdAtLabel'] ?? 'Sin fecha') . ' · ' . (string) ($noteItem['author'] ?? 'Sistema')) ?></span>
                                    <p class="tul-note-item__body"><?= esc((string) ($noteItem['comment'] ?? '')) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="tul-notes-panel__empty" data-tul-note-empty<?= empty($notesItems) ? '' : ' hidden' ?>>
                            Todavía no hay notas internas de Pago a Gestor registradas.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tul-rail__empty">Notas no disponibles.</div>
                <?php endif; ?>
            </div>

        </div>
        <?php else: ?>
            <!-- canView is false: step 4 content not rendered -->
            <div class="tul-rail__empty">Sin acceso a información de Pago a Gestor.</div>
        <?php endif; ?>
    </div>
</section>
