<?php
/**
 * Step 5 — Cobro a Cliente (Accordion)
 *
 * Partial con estructura de acordeón colapsado por defecto.
 * Tres carriles: formulario de cobro | documentos/upload/gallery | notas.
 *
 * @var array  $prototypeStep5Form       Datos del formulario de cobro paso 5
 * @var array  $prototypeStep5NotesForm   Datos de notas internas paso 5
 * @var int    $prototypeTramiteId        ID del trámite
 */

$canView       = $prototypeStep5Form['canView'] ?? false;
$canEdit       = $prototypeStep5Form['canEdit'] ?? false;
$canUploadDocs = $prototypeStep5Form['canUploadDocs'] ?? false;
$canDeleteDocs = $prototypeStep5Form['canDeleteDocs'] ?? false;
$blockedReason = $prototypeStep5Form['blockedReason'] ?? '';
$uploadBlockedReason = $prototypeStep5Form['uploadBlockedReason'] ?? '';
$deleteBlockedReason = $prototypeStep5Form['deleteBlockedReason'] ?? '';
$tramiteId     = $prototypeTramiteId ?? 0;
$csrfName      = $prototypeStep5Form['csrfName'] ?? csrf_token();
$csrfHash      = $prototypeStep5Form['csrfHash'] ?? csrf_hash();
$documents     = $prototypeStep5Form['docs'] ?? [];

// Notes
$notesCanView = $prototypeStep5NotesForm['canView'] ?? false;
$notesCanAdd  = $prototypeStep5NotesForm['canAdd'] ?? false;
$notesBlockedReason = $prototypeStep5NotesForm['blockedReason'] ?? '';
$notesItems   = $prototypeStep5NotesForm['items'] ?? [];

// Form values
$values = $prototypeStep5Form['values'] ?? [];

// Options
$cobroStatusOptions   = $prototypeStep5Form['options']['cobroStatus'] ?? [];
$cobroCorrectoOptions = $prototypeStep5Form['options']['cobroCorrecto'] ?? [];

// Endpoints
$saveUrl    = '/deskapp/tramitesn/update_final_save/' . $tramiteId;
$uploadUrl  = '/deskapp/tramitesn/upload_cobro_cliente/' . $tramiteId;
$deleteUrl  = '/deskapp/tramitesn/delete_cobro_cliente';
$getFilesUrl = '/deskapp/tramitesn/getCobroClienteFiles/' . $tramiteId;
$notesUrl   = '/deskapp/tramitesn/prototype_step5_notes_add/' . $tramiteId;
?>
<?php $tulLocked = !empty($tulFinanceLocked); $tulLockReason = (string) ($tulFinanceLockReason ?? ''); ?>
<section class="tul-step-row tul-step-row--5 tul-accordion<?= $tulLocked ? ' tul-step-row--locked' : '' ?>" data-step-row="5" data-accordion>
    <header class="tul-step-row__header tul-accordion__trigger" data-accordion-trigger>
        <h3 class="tul-step-row__title">Paso 5 — Cobro a Cliente</h3>
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

            <!-- Carril izquierdo: Formulario de cobro -->
            <div class="tul-rail tul-rail--form" data-rail="form">
                <?php if (!$canEdit && $blockedReason): ?>
                    <div class="tul-blocked-notice"><?= esc($blockedReason) ?></div>
                <?php endif; ?>

                <form data-tul-save data-tul-step="5" data-tul-url="<?= esc($saveUrl, 'attr') ?>" data-tul-reload data-tul-step5-finance<?= !$canEdit ? ' data-tul-readonly' : '' ?>>
                    <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">
                    <input type="hidden" name="costo_gestoria_hidden" value="<?= esc((string) ($values['costo_gestoria_hidden'] ?? ''), 'attr') ?>">

                    <div class="tul-field">
                        <label for="tul_step5_id_give_cliente">ID que da el cliente</label>
                        <input id="tul_step5_id_give_cliente" type="text" name="id_give_cliente" value="<?= esc((string) ($values['id_give_cliente'] ?? ''), 'attr') ?>"<?= $canEdit ? '' : ' disabled' ?>>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step5_cobro_status_id">Estatus del cobro</label>
                        <select id="tul_step5_cobro_status_id" name="cobro_status_id"<?= $canEdit ? '' : ' disabled' ?>>
                            <?php foreach ($cobroStatusOptions as $optVal => $optLabel): ?>
                                <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['cobro_status_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step5_numero_factura">Número de factura</label>
                        <input id="tul_step5_numero_factura" type="text" name="numero_factura" value="<?= esc((string) ($values['numero_factura'] ?? ''), 'attr') ?>"<?= $canEdit ? '' : ' disabled' ?>>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step5_numero_refactura">Número de refactura</label>
                        <input id="tul_step5_numero_refactura" type="text" name="numero_refactura" value="<?= esc((string) ($values['numero_refactura'] ?? ''), 'attr') ?>"<?= $canEdit ? '' : ' disabled' ?>>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step5_evidencia_cobro_txt">Evidencia de cobro</label>
                        <textarea id="tul_step5_evidencia_cobro_txt" name="evidencia_cobro_txt"<?= $canEdit ? '' : ' disabled' ?>><?= esc((string) ($values['evidencia_cobro_txt'] ?? '')) ?></textarea>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step5_costo_gestoria">Sumatoria de derechos</label>
                        <input id="tul_step5_costo_gestoria" type="text" name="costo_gestoria" value="<?= esc((string) ($values['costo_gestoria'] ?? '0.00'), 'attr') ?>" disabled>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step5_costo_pago_cliente">Honorarios del trámite</label>
                        <input id="tul_step5_costo_pago_cliente" type="number" step="0.01" name="costo_pago_cliente" value="<?= esc((string) ($values['costo_pago_cliente'] ?? '0'), 'attr') ?>"<?= $canEdit ? '' : ' disabled' ?>>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step5_comision_derechos">Comisión de derechos</label>
                        <input id="tul_step5_comision_derechos" type="number" step="0.01" name="comision_derechos" value="<?= esc((string) ($values['comision_derechos'] ?? '0'), 'attr') ?>"<?= $canEdit ? '' : ' disabled' ?>>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step5_iva">IVA</label>
                        <input id="tul_step5_iva" type="number" step="0.01" name="iva" value="<?= esc((string) ($values['iva'] ?? '0.00'), 'attr') ?>"<?= $canEdit ? '' : ' disabled' ?>>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step5_costo_total">Costo total</label>
                        <input id="tul_step5_costo_total" type="text" name="costo_total" value="<?= esc((string) ($values['costo_total'] ?? '0.00'), 'attr') ?>" disabled>
                    </div>

                    <?php if ($canEdit): ?>
                        <div class="tul-btn-row">
                            <button type="submit" class="tul-btn tul-btn--primary" data-tul-save-btn>Guardar Cobro a Cliente</button>
                        </div>
                    <?php endif; ?>

                    <div class="tul-form-feedback" data-tul-feedback hidden></div>
                </form>
            </div>

            <!-- Carril centro: Documentos de cobro (upload / gallery / delete) -->
            <div class="tul-rail tul-rail--docs" data-rail="docs">
                <?php if ($canUploadDocs): ?>
                    <div class="tul-dropzone" data-tul-dropzone data-tul-step="5" data-tul-upload-url="<?= esc($uploadUrl, 'attr') ?>">
                        <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">

                        <div class="tul-field">
                            <label for="tul_step5_cobro_correcto">Tipo de soporte</label>
                            <select id="tul_step5_cobro_correcto" name="cobro_correcto" data-tul-doc-type>
                                <?php foreach ($cobroCorrectoOptions as $optVal => $optLabel): ?>
                                    <option value="<?= esc((string) $optVal, 'attr') ?>"><?= esc((string) $optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="tul-dropzone__area">
                            <input type="file" class="tul-dropzone__input" data-tul-file-input multiple>
                            <span class="tul-dropzone__label">Arrastra aquí o haz clic para seleccionar uno o más archivos</span>
                            <span class="tul-dropzone__meta" data-tul-file-meta>Sin archivo seleccionado</span>
                        </div>

                        <div class="tul-dropzone__progress" data-tul-upload-progress hidden></div>

                        <div class="tul-btn-row">
                            <button type="button" class="tul-btn tul-btn--primary" data-tul-upload-btn>Subir evidencia</button>
                        </div>
                    </div>
                <?php elseif ($uploadBlockedReason): ?>
                    <div class="tul-blocked-notice"><?= esc($uploadBlockedReason) ?></div>
                <?php endif; ?>

                <div class="tul-gallery" data-tul-gallery data-tul-step="5" data-tul-get-files-url="<?= esc($getFilesUrl, 'attr') ?>">
                    <h4 class="tul-gallery__title">Evidencias de cobro</h4>
                    <?php if (!empty($documents) && is_array($documents)): ?>
                        <?php foreach ($documents as $doc): ?>
                            <?php
                                $docFile = (string) ($doc['file'] ?? '');
                                $docType = (string) ($doc['cobro_correcto'] ?? 'otro');
                                $docId   = (string) ($doc['id'] ?? '');
                                $docUrl  = ($doc['url'] ?? '') !== '' ? $doc['url'] : '#';
                                $docLabel = (string) (($cobroCorrectoOptions[$docType] ?? '') ?: ($docType !== '' ? $docType : 'Soporte de cobro'));
                                $isImage = !empty($doc['is_image']);
                            ?>
                            <div class="tul-gallery__item" data-tul-doc data-tul-doc-id="<?= esc($docId, 'attr') ?>">
                                <?php if ($isImage && $docUrl !== '#'): ?>
                                    <img class="tul-gallery__item-preview" src="<?= esc($docUrl, 'attr') ?>" alt="<?= esc($docFile, 'attr') ?>" loading="lazy">
                                <?php endif; ?>
                                <div class="tul-gallery__item-info">
                                    <a class="tul-gallery__item-link" href="<?= esc($docUrl, 'attr') ?>" target="_blank" rel="noreferrer"><?= esc($docFile) ?></a>
                                    <span class="tul-gallery__item-meta"><?= esc($docLabel) ?></span>
                                </div>
                                <?php if ($canDeleteDocs): ?>
                                    <button type="button" class="tul-btn tul-btn--danger tul-btn--sm" data-tul-delete-btn data-tul-delete-url="<?= esc($deleteUrl, 'attr') ?>" data-tul-doc-id="<?= esc($docId, 'attr') ?>">Eliminar</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="tul-gallery__empty">Sin evidencias de cobro registradas.</div>
                    <?php endif; ?>
                </div>

                <?php if (!$canDeleteDocs && $deleteBlockedReason): ?>
                    <div class="tul-blocked-notice"><?= esc($deleteBlockedReason) ?></div>
                <?php endif; ?>
            </div>

            <!-- Carril derecho: Notas internas paso 5 -->
            <div class="tul-rail tul-rail--notes" data-rail="notes">
                <?php if ($notesCanView): ?>
                    <div class="tul-notes-panel" data-tul-notes data-tul-step="5" data-tul-url="<?= esc($notesUrl, 'attr') ?>">
                        <h4 class="tul-notes-panel__title">Notas de Cobro a Cliente</h4>

                        <?php if ($notesCanAdd): ?>
                            <form class="tul-notes-form" data-tul-note-form>
                                <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">
                                <label for="tul_step5_note_input">Agregar nota interna</label>
                                <textarea
                                    id="tul_step5_note_input"
                                    class="tul-notes-panel__input"
                                    name="comentario"
                                    placeholder="Escribe aquí una nota interna de seguimiento para Cobro a Cliente"
                                    data-tul-note-input
                                ></textarea>
                                <div class="tul-btn-row">
                                    <button type="submit" class="tul-btn tul-btn--primary" data-tul-note-btn>Guardar nota interna</button>
                                </div>
                            </form>
                        <?php elseif ($notesBlockedReason): ?>
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
                            Todavía no hay notas internas de Cobro a Cliente registradas.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tul-rail__empty">Notas no disponibles.</div>
                <?php endif; ?>
            </div>

        </div>
        <?php else: ?>
            <!-- canView is false: step 5 content not rendered -->
            <div class="tul-rail__empty">Sin acceso a información de Cobro a Cliente.</div>
        <?php endif; ?>
    </div>
</section>
