<?php
$tipos = is_array($tipos_documentos_resumen ?? null) ? $tipos_documentos_resumen : [];
$clasificacionActiva = !empty($documentos_clasificacion_activa);
$totalTipos = count($tipos);
$tiposConDocumentos = 0;
$totalObligatorios = 0;
$totalOpcionales = 0;

foreach ($tipos as $tipo) {
    $obligatorios = is_array($tipo['documentos_obligatorios'] ?? null) ? $tipo['documentos_obligatorios'] : [];
    $opcionales = is_array($tipo['documentos_opcionales'] ?? null) ? $tipo['documentos_opcionales'] : [];
    if ($obligatorios !== [] || $opcionales !== []) {
        $tiposConDocumentos++;
    }
    $totalObligatorios += count($obligatorios);
    $totalOpcionales += count($opcionales);
}
?>

<style>
    .tramite-tipo-visual {
        margin-bottom: 24px;
    }

    .tramite-tipo-visual__hero {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) repeat(3, minmax(140px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .tramite-tipo-visual__intro,
    .tramite-tipo-visual__stat,
    .tramite-tipo-card {
        border: 1px solid #dbe3ef;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
    }

    .tramite-tipo-visual__intro {
        padding: 18px 20px;
    }

    .tramite-tipo-visual__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #075985;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .tramite-tipo-visual__intro h3 {
        margin: 0 0 6px;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
    }

    .tramite-tipo-visual__intro p {
        margin: 0;
        color: #475569;
        line-height: 1.55;
        font-size: 13px;
    }

    .tramite-tipo-visual__stat {
        padding: 16px;
    }

    .tramite-tipo-visual__stat-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .tramite-tipo-visual__stat-value {
        font-size: 28px;
        line-height: 1;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .tramite-tipo-visual__stat-help {
        font-size: 12px;
        color: #64748b;
    }

    .tramite-tipo-visual__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 14px;
    }

    .tramite-tipo-visual__notice {
        margin: 0 0 18px;
        padding: 13px 15px;
        border-radius: 14px;
        border: 1px solid #fde68a;
        background: linear-gradient(180deg, #fffbeb 0%, #fff7d6 100%);
        color: #92400e;
        font-size: 13px;
        line-height: 1.5;
    }

    .tramite-tipo-card {
        padding: 16px;
    }

    .tramite-tipo-card.is-empty {
        background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%);
    }

    .tramite-tipo-card__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 10px;
    }

    .tramite-tipo-card__kicker {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .tramite-tipo-card__title {
        margin: 0;
        font-size: 16px;
        line-height: 1.35;
        color: #0f172a;
        font-weight: 800;
    }

    .tramite-tipo-card__count {
        flex-shrink: 0;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 800;
    }

    .tramite-tipo-card__description {
        margin: 0 0 12px;
        color: #475569;
        font-size: 13px;
        line-height: 1.5;
        min-height: 38px;
    }

    .tramite-tipo-card__docs-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .tramite-tipo-card__docs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tramite-tipo-card__section {
        margin-bottom: 12px;
    }

    .tramite-tipo-card__section:last-child {
        margin-bottom: 0;
    }

    .tramite-tipo-card__doc {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 10px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.3;
    }

    .tramite-tipo-card__doc.is-optional {
        background: #f1f5f9;
        color: #334155;
    }

    .tramite-tipo-card__empty {
        border: 1px dashed #fdba74;
        border-radius: 12px;
        padding: 12px;
        background: rgba(255, 247, 237, 0.9);
        color: #9a3412;
        font-size: 13px;
        line-height: 1.45;
    }

    @media (max-width: 992px) {
        .tramite-tipo-visual__hero {
            grid-template-columns: 1fr 1fr;
        }

        .tramite-tipo-visual__intro {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 640px) {
        .tramite-tipo-visual__hero {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="tramite-tipo-visual">
    <div class="tramite-tipo-visual__hero">
        <div class="tramite-tipo-visual__intro">
            <div class="tramite-tipo-visual__eyebrow">
                <i class="fas fa-sitemap"></i>
                Mapa documental
            </div>
            <h3>Documentos base por tipo de tramite</h3>
            <p>Esta vista resume la relacion oficial entre cada tipo de tramite y los documentos obligatorios o base definidos en el catalogo. El CRUD inferior sigue siendo la fuente de mantenimiento.</p>
        </div>

        <div class="tramite-tipo-visual__stat">
            <div class="tramite-tipo-visual__stat-label">Tipos</div>
            <div class="tramite-tipo-visual__stat-value"><?= (int) $totalTipos ?></div>
            <div class="tramite-tipo-visual__stat-help">Tipos de tramite visibles en catalogo.</div>
        </div>

        <div class="tramite-tipo-visual__stat">
            <div class="tramite-tipo-visual__stat-label">Con documentos</div>
            <div class="tramite-tipo-visual__stat-value"><?= (int) $tiposConDocumentos ?></div>
            <div class="tramite-tipo-visual__stat-help">Tipos que ya tienen base documental ligada.</div>
        </div>

        <div class="tramite-tipo-visual__stat">
            <div class="tramite-tipo-visual__stat-label">Base</div>
            <div class="tramite-tipo-visual__stat-value"><?= (int) $totalObligatorios ?></div>
            <div class="tramite-tipo-visual__stat-help">Documentos obligatorios o base ligados.</div>
        </div>
    </div>

    <?php if (!$clasificacionActiva): ?>
        <div class="tramite-tipo-visual__notice">
            La clasificacion avanzada aun no esta activada en la tabla tra_tipo_documentos. Por ahora todas las relaciones existentes se muestran como documentos base u obligatorios.
        </div>
    <?php elseif ($totalOpcionales > 0): ?>
        <div class="tramite-tipo-visual__notice">
            La clasificacion avanzada esta activa. En cada tarjeta se separan documentos base y documentos opcionales; hoy hay <?= (int) $totalOpcionales ?> relaciones opcionales capturadas.
        </div>
    <?php endif; ?>

    <?php if ($tipos === []): ?>
        <div class="tramite-tipo-card is-empty">
            <div class="tramite-tipo-card__empty">No se encontraron tipos de tramite o relaciones documentales para mostrar.</div>
        </div>
    <?php else: ?>
        <div class="tramite-tipo-visual__grid">
            <?php foreach ($tipos as $tipo): ?>
                <?php $obligatorios = is_array($tipo['documentos_obligatorios'] ?? null) ? $tipo['documentos_obligatorios'] : []; ?>
                <?php $opcionales = is_array($tipo['documentos_opcionales'] ?? null) ? $tipo['documentos_opcionales'] : []; ?>
                <article class="tramite-tipo-card<?= $obligatorios === [] && $opcionales === [] ? ' is-empty' : '' ?>">
                    <div class="tramite-tipo-card__head">
                        <div>
                            <div class="tramite-tipo-card__kicker">Tipo #<?= (int) ($tipo['id'] ?? 0) ?></div>
                            <h4 class="tramite-tipo-card__title"><?= esc((string) ($tipo['tipo_tramite'] ?? 'Tipo sin nombre')) ?></h4>
                        </div>
                        <div class="tramite-tipo-card__count"><?= count($obligatorios) ?> base / <?= count($opcionales) ?> opc</div>
                    </div>

                    <p class="tramite-tipo-card__description">
                        <?= esc(trim((string) ($tipo['descripcion'] ?? '')) !== '' ? (string) $tipo['descripcion'] : 'Sin descripcion operativa capturada.') ?>
                    </p>

                    <div class="tramite-tipo-card__section">
                        <div class="tramite-tipo-card__docs-label">
                            <i class="fas fa-file-alt"></i>
                            Documentos base
                        </div>

                        <?php if ($obligatorios !== []): ?>
                            <div class="tramite-tipo-card__docs">
                                <?php foreach ($obligatorios as $documento): ?>
                                    <span class="tramite-tipo-card__doc">
                                        <i class="fas fa-check-circle"></i>
                                        <?= esc((string) ($documento['documento'] ?? 'Documento')) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="tramite-tipo-card__empty">Este tipo no tiene documentos base ligados.</div>
                        <?php endif; ?>
                    </div>

                    <?php if ($clasificacionActiva || $opcionales !== []): ?>
                        <div class="tramite-tipo-card__section">
                            <div class="tramite-tipo-card__docs-label">
                                <i class="fas fa-layer-group"></i>
                                Documentos opcionales
                            </div>

                            <?php if ($opcionales !== []): ?>
                                <div class="tramite-tipo-card__docs">
                                    <?php foreach ($opcionales as $documento): ?>
                                        <span class="tramite-tipo-card__doc is-optional">
                                            <i class="fas fa-plus-circle"></i>
                                            <?= esc((string) ($documento['documento'] ?? 'Documento')) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="tramite-tipo-card__empty">Sin documentos opcionales capturados para este tipo.</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>