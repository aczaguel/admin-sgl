<?php
/**
 * Tramite Unified Layout - Step 1: Información General
 *
 * Partial que renderiza el paso 1 con layout de 3 carriles:
 * - Izquierdo: formulario de datos base del expediente
 * - Centro: dropzone de documentos y galería
 * - Derecho: bitácora/notas del expediente
 *
 * @var array $prototypeStep1Form       Formulario datos base con canEdit, values, options, urls
 * @var array $prototypeStep1DocsForm   Documentos del paso 1 con canView, canUpload, canDelete
 * @var array $prototypeEvidenceForm    Bitácora general con canView, canAdd, items
 * @var int   $prototypeTramiteId       ID del trámite
 */

// --- Permission & data extraction ---
$canEdit = !empty($prototypeStep1Form['canEdit']);
$blockedReason = trim((string) ($prototypeStep1Form['blockedReason'] ?? ''));
$csrfName = $prototypeStep1Form['csrfName'] ?? csrf_token();
$csrfHash = $prototypeStep1Form['csrfHash'] ?? csrf_hash();
$values = $prototypeStep1Form['values'] ?? [];
$options = $prototypeStep1Form['options'] ?? [];
$urls = $prototypeStep1Form['urls'] ?? [];
$tramiteId = (int) ($prototypeTramiteId ?? 0);

// Docs
$docsCanView = !empty($prototypeStep1DocsForm['canView']);
$docsCanUpload = !empty($prototypeStep1DocsForm['canUpload']);
$docsCanDelete = !empty($prototypeStep1DocsForm['canDelete']);
$docsBlockedReason = trim((string) ($prototypeStep1DocsForm['blockedReason'] ?? ''));
$docsDeleteBlockedReason = trim((string) ($prototypeStep1DocsForm['deleteBlockedReason'] ?? ''));
$docsUrls = $prototypeStep1DocsForm['urls'] ?? [];
$documents = $prototypeStep1DocsForm['documents'] ?? [];
$docOptions = $prototypeStep1DocsForm['options'] ?? [];
$docSummary = $prototypeStep1DocsForm['summary'] ?? [];

// Notes / Bitácora
$evidenceCanView = !empty($prototypeEvidenceForm['canView']);
$evidenceCanAdd = !empty($prototypeEvidenceForm['canAdd']);
$evidenceBlockedReason = trim((string) ($prototypeEvidenceForm['blockedReason'] ?? ''));
$evidenceUrls = $prototypeEvidenceForm['urls'] ?? [];
$evidenceItems = !empty($prototypeEvidenceForm['items']) && is_array($prototypeEvidenceForm['items'])
    ? array_values($prototypeEvidenceForm['items'])
    : [];
$evidenceCsrfName = $prototypeEvidenceForm['csrfName'] ?? $csrfName;
$evidenceCsrfHash = $prototypeEvidenceForm['csrfHash'] ?? $csrfHash;

// Save URL
$saveUrl = $urls['updateSave'] ?? ('/deskapp/tramitesn/update_save/' . $tramiteId);
$uploadUrl = $docsUrls['upload'] ?? ('/deskapp/tramitesn/upload_step1_doc/' . $tramiteId);
$deleteUrl = $docsUrls['delete'] ?? '/deskapp/tramitesn/delete_step1_doc';
$notesUrl = $evidenceUrls['create'] ?? ('/deskapp/tramitesn/prototype_evidencias_add/' . $tramiteId);

// --- Composición del servicio (tipos principal + asociados) ---
$servicesForm        = !empty($prototypeStep1ServicesForm) && is_array($prototypeStep1ServicesForm) ? $prototypeStep1ServicesForm : [];
$svcCanManage        = !empty($servicesForm['canManageBase']);
$svcCanEditPrincipal = !empty($servicesForm['canEditPrincipal']);
$svcCanEditAsociado  = !empty($servicesForm['canEditAsociado']);
$svcCanDeleteAsociado= !empty($servicesForm['canDeleteAsociado']);
$svcBlockedReason    = trim((string) ($servicesForm['blockedReason'] ?? ''));
$svcTramiteId        = (int) ($servicesForm['tramiteId'] ?? $tramiteId);
$svcPrincipalId      = (int) ($servicesForm['principalTipoId'] ?? 0);
$svcUrls             = $servicesForm['urls'] ?? [];
$svcTraTipos         = $servicesForm['options']['traTipos'] ?? [];
$svcServices         = $servicesForm['services'] ?? [];
$svcCsrfName         = $servicesForm['csrfName'] ?? $csrfName;
$svcCsrfHash         = $servicesForm['csrfHash'] ?? $csrfHash;
$svcPrincipalUrl     = $svcUrls['principalUpdate'] ?? '/deskapp/tramitesn/principal/update_tipo';
$svcAddUrl           = $svcUrls['add'] ?? '/deskapp/tramitesn/services/add';
$svcUpdateUrl        = $svcUrls['update'] ?? '/deskapp/tramitesn/services/update';
$svcDeleteUrl        = $svcUrls['delete'] ?? '/deskapp/tramitesn/services/delete';

// Separar principal de asociados
$svcPrincipalLabel = 'Sin tipo principal';
$svcAsociados = [];
foreach ($svcServices as $svcRow) {
    $svcRowTipoId = (int) ($svcRow['tra_tipos_id'] ?? 0);
    $svcRowLabel  = (string) ($svcRow['label'] ?? ('Tipo #' . $svcRowTipoId));
    if (!empty($svcRow['is_principal']) || $svcRowTipoId === $svcPrincipalId) {
        $svcPrincipalLabel = $svcRowLabel;
    } else {
        $svcAsociados[] = [
            'asociado_id' => (int) ($svcRow['asociado_id'] ?? 0),
            'tra_tipos_id' => $svcRowTipoId,
            'label' => $svcRowLabel,
        ];
    }
}
?>
<section class="tul-step-row tul-step-row--1" data-step-row="1">
    <header class="tul-step-row__header">
        <h3 class="tul-step-row__title">Paso 1 — Información General</h3>
        <?php if (!$canEdit): ?>
            <span class="tul-readonly-badge">
                <i class="icon-lock"></i> Solo lectura
            </span>
        <?php endif; ?>
    </header>

    <div class="tul-three-rail">
        <!-- Carril izquierdo: Formulario datos base -->
        <div class="tul-rail tul-rail--form" data-rail="form">
            <?php if (!$canEdit && $blockedReason !== ''): ?>
                <div class="tul-blocked-notice"><?= esc($blockedReason) ?></div>
            <?php endif; ?>

            <form data-tul-save data-tul-step="1" data-tul-url="<?= esc($saveUrl, 'attr') ?>" data-tul-reload<?= !$canEdit ? ' data-tul-readonly' : '' ?>>
                <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">
                <input type="hidden" name="current_step" value="<?= esc((string) ($values['current_step'] ?? '1'), 'attr') ?>">

                <div class="tul-field-grid">
                    <div class="tul-field">
                        <label for="tul_step1_folio">Folio</label>
                        <input type="text" id="tul_step1_folio" value="<?= esc((string) ($values['folio'] ?? ''), 'attr') ?>" data-tul-input="folio" readonly>
                        <input type="hidden" name="folio" value="<?= esc((string) ($values['folio'] ?? ''), 'attr') ?>">
                    </div>

                    <div class="tul-field">
                        <label for="tul_step1_cli_directo_id">Cliente</label>
                        <select id="tul_step1_cli_directo_id" name="cli_directo_id" data-tul-input="cli_directo_id"<?= !$canEdit ? ' disabled' : '' ?>>
                            <option value="">Seleccione un cliente</option>
                            <?php foreach (($options['cliente'] ?? []) as $optVal => $optLabel): ?>
                                <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['cli_directo_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step1_cli_directo_ejecutivo_id">Ejecutivo</label>
                        <select id="tul_step1_cli_directo_ejecutivo_id" name="cli_directo_ejecutivo_id" data-tul-input="cli_directo_ejecutivo_id"<?= !$canEdit ? ' disabled' : '' ?>>
                            <option value="">Seleccione un ejecutivo</option>
                            <?php foreach (($options['ejecutivo'] ?? []) as $optVal => $optLabel): ?>
                                <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['cli_directo_ejecutivo_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step1_contrato">Contrato</label>
                        <input type="text" id="tul_step1_contrato" name="contrato" value="<?= esc((string) ($values['contrato'] ?? ''), 'attr') ?>" data-tul-input="contrato"<?= !$canEdit ? ' disabled' : '' ?>>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step1_unidad">Unidad</label>
                        <input type="text" id="tul_step1_unidad" name="unidad" value="<?= esc((string) ($values['unidad'] ?? ''), 'attr') ?>" data-tul-input="unidad"<?= !$canEdit ? ' disabled' : '' ?>>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step1_serie">Serie</label>
                        <input type="text" id="tul_step1_serie" name="serie" value="<?= esc((string) ($values['serie'] ?? ''), 'attr') ?>" data-tul-input="serie"<?= !$canEdit ? ' disabled' : '' ?>>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step1_placas">Placas</label>
                        <input type="text" id="tul_step1_placas" name="placas" value="<?= esc((string) ($values['placas'] ?? ''), 'attr') ?>" data-tul-input="placas"<?= !$canEdit ? ' disabled' : '' ?>>
                    </div>

                    <div class="tul-field">
                        <label for="tul_step1_entidad_id">Entidad</label>
                        <select id="tul_step1_entidad_id" name="entidad_id" data-tul-input="entidad_id"<?= !$canEdit ? ' disabled' : '' ?>>
                            <option value="">Seleccione una entidad</option>
                            <?php foreach (($options['entidad'] ?? []) as $optVal => $optLabel): ?>
                                <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (string) $optVal === (string) ($values['entidad_id'] ?? '') ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tul-field tul-field--wide">
                        <label for="tul_step1_observaciones">Observaciones</label>
                        <textarea id="tul_step1_observaciones" name="observaciones" data-tul-input="observaciones"<?= !$canEdit ? ' disabled' : '' ?>><?= esc((string) ($values['observaciones'] ?? '')) ?></textarea>
                    </div>
                </div>

                <?php if ($canEdit): ?>
                    <div class="tul-btn-row">
                        <button type="submit" class="tul-btn tul-btn--primary" data-tul-save-btn>Guardar datos base</button>
                    </div>
                <?php endif; ?>

                <div class="tul-form-feedback" data-tul-feedback hidden></div>
            </form>

            <?php if ($svcCanManage || !empty($svcServices)): ?>
                <div class="tul-services"
                     data-tul-services
                     data-tul-tramite-id="<?= (int) $svcTramiteId ?>"
                     data-tul-principal-id="<?= (int) $svcPrincipalId ?>"
                     data-tul-svc-principal-url="<?= esc($svcPrincipalUrl, 'attr') ?>"
                     data-tul-svc-add-url="<?= esc($svcAddUrl, 'attr') ?>"
                     data-tul-svc-update-url="<?= esc($svcUpdateUrl, 'attr') ?>"
                     data-tul-svc-delete-url="<?= esc($svcDeleteUrl, 'attr') ?>">
                    <input type="hidden" name="<?= esc($svcCsrfName, 'attr') ?>" value="<?= esc($svcCsrfHash, 'attr') ?>" data-tul-svc-csrf>

                    <h4 class="tul-services__title">Composición del servicio</h4>

                    <?php if (!$svcCanManage && $svcBlockedReason !== ''): ?>
                        <div class="tul-blocked-notice"><?= esc($svcBlockedReason) ?></div>
                    <?php endif; ?>

                    <!-- Tipo principal -->
                    <div class="tul-services__principal">
                        <span class="tul-services__sublabel">Tipo principal</span>
                        <?php if ($svcCanEditPrincipal): ?>
                            <div class="tul-services__row">
                                <select class="tul-services__select" data-tul-svc-principal-select>
                                    <?php foreach ($svcTraTipos as $optVal => $optLabel): ?>
                                        <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (int) $optVal === $svcPrincipalId ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="tul-btn tul-btn--primary tul-btn--sm" data-tul-svc-principal-btn>Cambiar</button>
                            </div>
                        <?php else: ?>
                            <span class="tul-services__principal-name"><?= esc($svcPrincipalLabel) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Tipos asociados -->
                    <div class="tul-services__assoc">
                        <span class="tul-services__sublabel">Tipos asociados</span>
                        <div class="tul-services__list" data-tul-svc-list>
                            <?php foreach ($svcAsociados as $assoc): ?>
                                <div class="tul-services__item" data-tul-svc-item data-asociado-id="<?= (int) $assoc['asociado_id'] ?>">
                                    <?php if ($svcCanEditAsociado): ?>
                                        <select class="tul-services__select" data-tul-svc-item-select>
                                            <?php foreach ($svcTraTipos as $optVal => $optLabel): ?>
                                                <option value="<?= esc((string) $optVal, 'attr') ?>"<?= (int) $optVal === (int) $assoc['tra_tipos_id'] ? ' selected' : '' ?>><?= esc((string) $optLabel) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <span class="tul-services__item-name"><?= esc($assoc['label']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($svcCanDeleteAsociado): ?>
                                        <button type="button" class="tul-btn tul-btn--danger tul-btn--sm" data-tul-svc-delete-btn title="Eliminar tipo asociado">&times;</button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($svcAsociados)): ?>
                                <p class="tul-services__empty" data-tul-svc-empty>Sin tipos asociados.</p>
                            <?php endif; ?>
                        </div>

                        <?php if ($svcCanEditAsociado): ?>
                            <div class="tul-services__add">
                                <select class="tul-services__select" data-tul-svc-add-select>
                                    <option value="">Agregar tipo asociado…</option>
                                    <?php foreach ($svcTraTipos as $optVal => $optLabel): ?>
                                        <?php if ((int) $optVal !== $svcPrincipalId): ?>
                                            <option value="<?= esc((string) $optVal, 'attr') ?>"><?= esc((string) $optLabel) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="tul-btn tul-btn--primary tul-btn--sm" data-tul-svc-add-btn>Agregar</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Carril centro: Documentos -->
        <div class="tul-rail tul-rail--docs" data-rail="docs">
            <?php if ($docsCanView): ?>
                <?php if ($docsCanUpload): ?>
                    <div class="tul-dropzone" data-tul-dropzone data-tul-step="1" data-tul-upload-url="<?= esc($uploadUrl, 'attr') ?>">
                        <input type="hidden" name="<?= esc($csrfName, 'attr') ?>" value="<?= esc($csrfHash, 'attr') ?>">

                        <div class="tul-field">
                            <label for="tul_step1_doc_type">Tipo de documento</label>
                            <select id="tul_step1_doc_type" name="documento_id" data-tul-doc-type>
                                <option value="">Selecciona un documento</option>
                                <?php foreach (($docOptions['documentTypes'] ?? []) as $optVal => $optLabel): ?>
                                    <?php $docMeta = $docOptions['documentTypeMeta'][$optVal] ?? []; ?>
                                    <option value="<?= esc((string) $optVal, 'attr') ?>" data-doc-name="<?= esc((string) ($docMeta['documento_nombre'] ?? $optLabel), 'attr') ?>"><?= esc((string) $optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="tul-dropzone__area">
                            <input type="file" class="tul-dropzone__input" data-tul-file-input>
                            <span class="tul-dropzone__label">Arrastra aquí o haz clic para seleccionar archivo</span>
                        </div>

                        <div class="tul-btn-row">
                            <button type="button" class="tul-btn tul-btn--primary" data-tul-upload-btn>Subir documento</button>
                        </div>

                        <div class="tul-form-feedback" data-tul-upload-feedback hidden></div>
                    </div>
                <?php else: ?>
                    <?php if ($docsBlockedReason !== ''): ?>
                        <div class="tul-blocked-notice"><?= esc($docsBlockedReason) ?></div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Galería de documentos -->
                <div class="tul-gallery" data-tul-gallery data-tul-step="1">
                    <h4 class="tul-gallery__title">Documentos cargados</h4>
                    <?php if (!empty($documents) && is_array($documents)): ?>
                        <?php foreach ($documents as $docItem): ?>
                            <?php
                                $docId = (int) ($docItem['documento_id'] ?? 0);
                                $hasFile = !empty($docItem['has_file']);
                                $fileName = (string) ($docItem['file'] ?? '');
                                $fileUrl = (string) ($docItem['file_url'] ?? '');
                                $docName = (string) ($docItem['documento_nombre'] ?? 'Documento');
                                $isRequired = !empty($docItem['is_required']);
                                $statusLabel = (string) ($docItem['status_label'] ?? ($hasFile ? 'Cargado' : 'Pendiente'));
                                // Per-file list (comma-separated multi-upload split in the controller).
                                $docFiles = $docItem['files'] ?? [];
                                if (empty($docFiles) && $hasFile && $fileUrl !== '') {
                                    $docFiles = [['name' => $fileName, 'url' => $fileUrl]];
                                }
                            ?>
                            <div class="tul-gallery__item" data-tul-doc data-tul-doc-id="<?= $docId ?>">
                                <div class="tul-gallery__item-info">
                                    <span class="tul-gallery__item-name"><?= esc($docName) ?></span>
                                    <?php foreach ($docFiles as $docFile): ?>
                                        <?php
                                            $singleName = (string) ($docFile['name'] ?? '');
                                            $singleUrl = (string) ($docFile['url'] ?? '');
                                        ?>
                                        <?php if ($singleName !== '' && $singleUrl !== ''): ?>
                                            <a class="tul-gallery__item-link" href="<?= esc($singleUrl, 'attr') ?>" target="_blank" rel="noreferrer"><?= esc($singleName) ?></a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <span class="tul-gallery__item-meta"><?= esc(($isRequired ? 'Obligatorio' : 'Opcional') . ' · ' . $statusLabel) ?></span>
                                </div>
                                <?php if ($docsCanDelete && $hasFile && $docId > 0 && $fileName !== ''): ?>
                                    <button type="button" class="tul-btn tul-btn--danger tul-btn--sm" data-tul-delete-btn data-tul-delete-url="<?= esc($deleteUrl, 'attr') ?>" data-tul-doc-file="<?= esc($fileName, 'attr') ?>">Eliminar</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="tul-gallery__empty">Sin documentos cargados.</p>
                    <?php endif; ?>
                </div>

                <?php if (!$docsCanDelete && $docsDeleteBlockedReason !== ''): ?>
                    <div class="tul-blocked-notice"><?= esc($docsDeleteBlockedReason) ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Carril derecho: Bitácora / Notas -->
        <div class="tul-rail tul-rail--notes" data-rail="notes">
            <?php if ($evidenceCanView): ?>
                <h4 class="tul-notes__title">Bitácora del expediente</h4>

                <?php if ($evidenceCanAdd): ?>
                    <form class="tul-notes__compose" data-tul-notes data-tul-notes-group="general" data-tul-step="1" data-tul-url="<?= esc($notesUrl, 'attr') ?>">
                        <input type="hidden" name="<?= esc($evidenceCsrfName, 'attr') ?>" value="<?= esc($evidenceCsrfHash, 'attr') ?>">
                        <textarea class="tul-notes__input" name="comment" placeholder="Escribe un comentario operativo..." data-tul-note-input></textarea>
                        <div class="tul-btn-row">
                            <button type="submit" class="tul-btn tul-btn--primary" data-tul-note-btn>Agregar nota</button>
                        </div>
                        <div class="tul-form-feedback" data-tul-notes-feedback hidden></div>
                    </form>
                <?php elseif ($evidenceBlockedReason !== ''): ?>
                    <div class="tul-blocked-notice"><?= esc($evidenceBlockedReason) ?></div>
                <?php endif; ?>

                <div class="tul-notes__list" data-tul-notes-list data-tul-notes-group="general"<?= empty($evidenceItems) ? ' hidden' : '' ?>>
                    <?php foreach ($evidenceItems as $noteItem): ?>
                        <div class="tul-notes__item">
                            <span class="tul-notes__item-meta"><?= esc((string) ($noteItem['createdAtLabel'] ?? 'Sin fecha') . ' · ' . (string) ($noteItem['author'] ?? 'Sistema')) ?></span>
                            <span class="tul-notes__item-body"><?= esc((string) ($noteItem['comment'] ?? '')) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($evidenceItems)): ?>
                    <p class="tul-notes__empty" data-tul-notes-empty data-tul-notes-group="general">No hay comentarios registrados.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
