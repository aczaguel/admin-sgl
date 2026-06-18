<?php
helper(['navigation', 'permissions']);

$session = $session ?? session();
$navigation = sgl_build_ribbon_navigation($session);
$sections = $navigation['sections'] ?? [];

if (empty($sections)) {
    return;
}
?>
<div class="sgl-ribbon-nav" data-sgl-ribbon data-ribbon-variant="<?= esc($navigation['variant'] ?? 'admin', 'attr') ?>">
    <div class="sgl-ribbon-brand-row">
        <a href="<?= esc(base_url('deskapp/dashboard'), 'attr') ?>" class="sgl-ribbon-brand" aria-label="Ir al dashboard principal">
            <img src="<?= esc(base_url('/public/assets/vendors/images/logo_sasgl_bicolor.png'), 'attr') ?>" alt="Logo SASGL" class="sgl-ribbon-brand-image">
            <span class="sgl-ribbon-brand-copy">
                <span class="sgl-ribbon-brand-kicker">Sistema SGL</span>
                <span class="sgl-ribbon-brand-name">Navegación principal</span>
            </span>
        </a>
    </div>
    <div class="sgl-ribbon-main-tabs" role="tablist" aria-label="Navegación principal del sistema">
        <?php foreach ($sections as $index => $section): ?>
            <?php $sectionId = 'sgl-ribbon-' . preg_replace('/[^a-z0-9\-]+/i', '-', (string) $section['key']); ?>
            <button
                type="button"
                class="sgl-ribbon-tab<?= !empty($section['active']) ? ' is-active' : '' ?>"
                id="<?= esc($sectionId . '-tab', 'attr') ?>"
                data-section="<?= esc((string) $section['key'], 'attr') ?>"
                role="tab"
                aria-selected="<?= !empty($section['active']) ? 'true' : 'false' ?>"
                aria-controls="<?= esc($sectionId . '-panel', 'attr') ?>"
                tabindex="<?= !empty($section['active']) || $index === 0 ? '0' : '-1' ?>"
            >
                <?php if (!empty($section['icon'])): ?>
                    <span class="sgl-ribbon-tab-icon" aria-hidden="true"><i class="<?= esc($section['icon'], 'attr') ?>"></i></span>
                <?php endif; ?>
                <span class="sgl-ribbon-tab-label"><?= esc((string) $section['label']) ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($sections as $section): ?>
        <?php $sectionId = 'sgl-ribbon-' . preg_replace('/[^a-z0-9\-]+/i', '-', (string) $section['key']); ?>
        <div
            class="sgl-ribbon-panel<?= !empty($section['active']) ? ' is-active' : '' ?>"
            id="<?= esc($sectionId . '-panel', 'attr') ?>"
            data-section-panel="<?= esc((string) $section['key'], 'attr') ?>"
            role="tabpanel"
            aria-labelledby="<?= esc($sectionId . '-tab', 'attr') ?>"
            <?= !empty($section['active']) ? '' : 'hidden' ?>
        >
            <?php foreach (($section['items'] ?? []) as $item): ?>
                <a href="<?= esc((string) ($item['url'] ?? '#'), 'attr') ?>" class="sgl-ribbon-link<?= !empty($item['active']) ? ' is-active' : '' ?>">
                    <?php if (!empty($item['icon'])): ?>
                        <span class="sgl-ribbon-link-icon" aria-hidden="true"><i class="<?= esc($item['icon'], 'attr') ?>"></i></span>
                    <?php endif; ?>
                    <span class="sgl-ribbon-link-text">
                        <?php if (!empty($item['group'])): ?>
                            <span class="sgl-ribbon-link-group"><?= esc((string) $item['group']) ?></span>
                        <?php endif; ?>
                        <span class="sgl-ribbon-link-label"><?= esc((string) $item['label']) ?></span>
                    </span>
                    <?php foreach (($item['perm_tags'] ?? []) as $permTag): ?>
                        <?= perm_audit_tag((string) $permTag, $session) ?>
                    <?php endforeach; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>