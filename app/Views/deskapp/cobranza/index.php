<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>
<link rel="stylesheet" href="<?= base_url('/public/assets/src/styles/wizard_modern.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= base_url('/public/assets/src/styles/tramite_update_view_nuevo.css?v=' . time()) ?>">
<style>
    :root {
        --cob-bg: linear-gradient(180deg, #f6f0e8 0%, #f9f7f2 48%, #ffffff 100%);
        --cob-surface: #fffdf9;
        --cob-ink: #1f2937;
        --cob-muted: #667085;
        --cob-border: rgba(148, 163, 184, 0.24);
        --cob-accent: #0f766e;
        --cob-accent-soft: #ccfbf1;
        --cob-danger: #b42318;
        --cob-danger-soft: #fee4e2;
        --cob-warning: #b54708;
        --cob-warning-soft: #ffedd5;
        --cob-success: #027a48;
        --cob-success-soft: #d1fadf;
        --cob-info: #155eef;
        --cob-info-soft: #dbeafe;
        --cob-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    }

    .cobranza-shell {
        background: var(--cob-bg);
        border-radius: 28px;
        padding: 28px;
        box-shadow: var(--cob-shadow);
        color: var(--cob-ink);
    }

    .cobranza-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(320px, 1fr);
        gap: 22px;
        margin-bottom: 24px;
    }

    .cobranza-panel {
        background: rgba(255, 253, 249, 0.9);
        border: 1px solid var(--cob-border);
        border-radius: 24px;
        padding: 22px;
        backdrop-filter: blur(10px);
    }

    .cobranza-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.08);
        color: var(--cob-accent);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .cobranza-title {
        margin: 14px 0 8px;
        font-size: 2rem;
        line-height: 1.1;
        font-weight: 800;
    }

    .cobranza-subtitle {
        margin: 0;
        max-width: 62ch;
        color: var(--cob-muted);
        line-height: 1.55;
        font-size: 0.98rem;
    }

    .cobranza-highlight-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .cobranza-stat-card {
        border-radius: 20px;
        padding: 18px;
        border: 1px solid var(--cob-border);
        background: #fff;
    }

    .cobranza-stat-card strong {
        display: block;
        font-size: 1.7rem;
        line-height: 1;
        margin-top: 8px;
    }

    .cobranza-stat-card span {
        display: block;
        color: var(--cob-muted);
        font-size: 0.9rem;
        margin-top: 6px;
    }

    .cobranza-stat-card.is-priority {
        background: linear-gradient(135deg, #fff7ed 0%, #fffbeb 100%);
    }

    .cobranza-stat-card.is-success {
        background: linear-gradient(135deg, #ecfdf3 0%, #f7fee7 100%);
    }

    .cobranza-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: end;
        margin-bottom: 24px;
    }

    .cobranza-field {
        display: grid;
        gap: 8px;
        min-width: 180px;
        flex: 1 1 220px;
    }

    .cobranza-field label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #475467;
    }

    .cobranza-field input,
    .cobranza-field select {
        height: 46px;
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.34);
        background: #fff;
        padding: 0 14px;
        color: var(--cob-ink);
        outline: none;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .cobranza-field input:focus,
    .cobranza-field select:focus {
        border-color: rgba(15, 118, 110, 0.5);
        box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.08);
    }

    .cobranza-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .cobranza-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 46px;
        padding: 0 18px;
        border-radius: 999px;
        border: 0;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .cobranza-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .cobranza-btn.is-primary {
        background: #0f766e;
        color: #fff;
        box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
    }

    .cobranza-btn.is-secondary {
        background: #fff;
        color: var(--cob-ink);
        border: 1px solid rgba(148, 163, 184, 0.34);
    }

    .cobranza-btn.is-danger-soft {
        background: linear-gradient(180deg, #fff5f4, #ffe7e4);
        color: var(--cob-danger);
        border: 1px solid rgba(180, 35, 24, 0.18);
        box-shadow: 0 10px 20px rgba(180, 35, 24, 0.08);
    }

    .cobranza-main {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.95fr);
        gap: 22px;
        align-items: start;
    }

    .cobranza-list-header,
    .cobranza-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 18px;
    }

    .cobranza-list-header h2,
    .cobranza-detail-header h2 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
    }

    .cobranza-list-header p,
    .cobranza-detail-header p {
        margin: 6px 0 0;
        color: var(--cob-muted);
        font-size: 0.9rem;
    }

    .cobranza-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .cobranza-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
        background: #fff;
        border: 1px solid rgba(148, 163, 184, 0.28);
        color: #344054;
        text-decoration: none;
    }

    .cobranza-list {
        display: grid;
        gap: 10px;
    }

    .cobranza-case {
        display: block;
        position: relative;
        padding: 14px;
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: #fff;
        color: inherit;
        text-decoration: none;
        transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .cobranza-case:hover {
        border-color: rgba(15, 118, 110, 0.4);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        transform: translateY(-1px);
        text-decoration: none;
    }

    .cobranza-case.is-active {
        border-color: rgba(15, 118, 110, 0.78);
        background: linear-gradient(180deg, #ffffff 0%, #f0fdfa 100%);
        box-shadow: 0 18px 32px rgba(15, 118, 110, 0.12), 0 0 0 3px rgba(15, 118, 110, 0.1);
        transform: translateX(4px);
        text-decoration: none;
        z-index: 2;
    }

    .cobranza-case.is-active::after {
        content: '';
        position: absolute;
        top: 50%;
        right: -16px;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-top: 12px solid transparent;
        border-bottom: 12px solid transparent;
        border-left: 16px solid rgba(15, 118, 110, 0.82);
        filter: drop-shadow(0 8px 10px rgba(15, 118, 110, 0.18));
    }

    .cobranza-case-top,
    .cobranza-case-bottom,
    .cobranza-detail-actions,
    .cobranza-detail-stats {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .cobranza-case-top h3,
    .cobranza-detail-header h2 {
        font-size: 0.98rem;
        margin: 2px 0 0;
        font-weight: 800;
    }

    .cobranza-case-top small,
    .cobranza-detail-overline {
        color: var(--cob-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .cobranza-case-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin: 10px 0;
    }

    .cobranza-meta-item {
        padding: 10px 11px;
        border-radius: 14px;
        background: #fcfcfb;
        border: 1px solid rgba(148, 163, 184, 0.12);
        font-size: 0.88rem;
        line-height: 1.3;
    }

    .cobranza-meta-item strong {
        display: block;
        font-size: 0.78rem;
        color: var(--cob-muted);
        margin-bottom: 4px;
        font-weight: 700;
    }

    .cobranza-tone {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .cobranza-tone.is-accent {
        color: var(--cob-accent);
        background: var(--cob-accent-soft);
    }

    .cobranza-tone.is-danger {
        color: var(--cob-danger);
        background: var(--cob-danger-soft);
    }

    .cobranza-tone.is-warning {
        color: var(--cob-warning);
        background: var(--cob-warning-soft);
    }

    .cobranza-tone.is-success {
        color: var(--cob-success);
        background: var(--cob-success-soft);
    }

    .cobranza-tone.is-info {
        color: var(--cob-info);
        background: var(--cob-info-soft);
    }

    .cobranza-detail-panel {
        position: sticky;
        top: 20px;
        scroll-margin-top: 90px;
    }

    .cobranza-detail-panel.is-selected {
        border-color: rgba(15, 118, 110, 0.28);
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
    }

    .cobranza-detail-panel.is-loading {
        opacity: 0.9;
    }

    .cobranza-detail-body {
        min-height: 220px;
    }

    .cobranza-detail-loader {
        min-height: 220px;
        display: grid;
        place-items: center;
        gap: 12px;
        text-align: center;
        color: var(--cob-muted);
    }

    .cobranza-detail-feedback {
        display: none;
        margin-bottom: 14px;
        padding: 12px 14px;
        border-radius: 14px;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .cobranza-detail-feedback.is-success {
        display: block;
        background: #ecfdf3;
        border: 1px solid rgba(2, 122, 72, 0.18);
        color: #166534;
    }

    .cobranza-detail-feedback.is-error {
        display: block;
        background: #fee4e2;
        border: 1px solid rgba(180, 35, 24, 0.18);
        color: #b42318;
    }

    .cobranza-proof-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .cobranza-proof-card {
        display: grid;
        gap: 8px;
        padding: 14px;
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.98));
        border: 1px solid rgba(148, 163, 184, 0.16);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .cobranza-proof-link {
        display: block;
        text-decoration: none;
    }

    .cobranza-proof-image,
    .cobranza-proof-file-icon {
        width: 100%;
        height: 120px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid rgba(148, 163, 184, 0.18);
    }

    .cobranza-proof-image {
        object-fit: cover;
    }

    .cobranza-proof-file-icon {
        display: grid;
        place-items: center;
        color: var(--cob-muted);
        font-weight: 700;
    }

    .cobranza-proof-meta {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .cobranza-proof-meta strong {
        font-size: 0.92rem;
        line-height: 1.35;
        word-break: break-word;
        color: var(--cob-ink);
    }

    .cobranza-proof-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .cobranza-proof-actions {
        margin-top: 2px;
    }

    .cobranza-detail-loader-spinner {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 3px solid rgba(148, 163, 184, 0.24);
        border-top-color: var(--cob-accent);
        animation: cobranza-spin 0.8s linear infinite;
    }

    @keyframes cobranza-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .cobranza-detail-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin: 18px 0;
    }

    .cobranza-detail-summary article {
        background: #fff;
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        padding: 14px;
    }

    .cobranza-detail-summary article span {
        display: block;
        color: var(--cob-muted);
        font-size: 0.78rem;
        margin-bottom: 6px;
    }

    .cobranza-timeline {
        display: grid;
        gap: 12px;
        margin-top: 18px;
    }

    .cobranza-timeline-item {
        position: relative;
        padding: 14px 14px 14px 18px;
        border-left: 3px solid rgba(148, 163, 184, 0.25);
        border-radius: 0 16px 16px 0;
        background: #fff;
    }

    .cobranza-timeline-item h4 {
        margin: 0 0 6px;
        font-size: 0.92rem;
        font-weight: 800;
    }

    .cobranza-timeline-item p {
        margin: 0;
        color: var(--cob-muted);
        font-size: 0.88rem;
        line-height: 1.45;
    }

    .cobranza-empty {
        padding: 36px 22px;
        border-radius: 20px;
        border: 1px dashed rgba(148, 163, 184, 0.45);
        text-align: center;
        color: var(--cob-muted);
        background: rgba(255, 255, 255, 0.6);
    }

    .cobranza-inline-note {
        padding: 14px 16px;
        border-radius: 16px;
        background: #fff7ed;
        border: 1px solid rgba(180, 83, 9, 0.16);
        color: #9a3412;
        font-size: 0.88rem;
        line-height: 1.45;
    }

    .cobranza-form-card {
        margin-top: 18px;
        padding: 18px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid rgba(148, 163, 184, 0.16);
    }

    .cobranza-form-card h3 {
        margin: 0 0 6px;
        font-size: 0.98rem;
        font-weight: 800;
    }

    .cobranza-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .cobranza-form-grid .cobranza-field {
        min-width: 0;
        flex: initial;
    }

    .cobranza-form-grid .is-full {
        grid-column: 1 / -1;
    }

    .cobranza-form-card textarea {
        min-height: 110px;
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.34);
        padding: 12px 14px;
        resize: vertical;
    }

    .cobranza-file-picker {
        display: grid;
        gap: 10px;
        padding: 16px;
        border-radius: 18px;
        border: 1px dashed rgba(15, 118, 110, 0.34);
        background:
            radial-gradient(circle at top left, rgba(204, 251, 241, 0.8), transparent 38%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.98));
        cursor: pointer;
        transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }

    .cobranza-file-picker:hover {
        border-color: rgba(15, 118, 110, 0.58);
        box-shadow: 0 14px 24px rgba(15, 118, 110, 0.1);
        transform: translateY(-1px);
    }

    .cobranza-file-picker.is-dragover {
        border-color: rgba(15, 118, 110, 0.76);
        background:
            radial-gradient(circle at top left, rgba(153, 246, 228, 0.95), transparent 42%),
            linear-gradient(180deg, rgba(240, 253, 250, 0.98), rgba(255, 255, 255, 0.98));
        box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.08), 0 16px 26px rgba(15, 118, 110, 0.14);
        transform: translateY(-1px) scale(1.01);
    }

    .cobranza-file-picker-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.12);
        color: var(--cob-accent);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .cobranza-file-picker-copy {
        display: grid;
        gap: 4px;
    }

    .cobranza-file-picker-copy strong {
        font-size: 0.96rem;
        color: var(--cob-ink);
        line-height: 1.3;
    }

    .cobranza-file-picker-copy small {
        color: var(--cob-muted);
        font-size: 0.83rem;
        line-height: 1.45;
    }

    .cobranza-file-picker-name {
        display: inline-flex;
        align-items: center;
        min-height: 42px;
        padding: 0 14px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(148, 163, 184, 0.22);
        color: #344054;
        font-size: 0.88rem;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cobranza-file-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .cobranza-file-input:focus + .cobranza-file-picker {
        border-color: rgba(15, 118, 110, 0.62);
        box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.08);
    }

    .cobranza-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid rgba(148, 163, 184, 0.18);
    }

    .cobranza-pagination-copy {
        color: var(--cob-muted);
        font-size: 0.9rem;
    }

    .cobranza-pagination-nav {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .cobranza-pagination-nav-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cobranza-page-link.is-active {
        background: rgba(10, 37, 64, 0.94);
        color: #fff;
        border-color: rgba(10, 37, 64, 0.94);
        pointer-events: none;
    }

    @media (max-width: 1080px) {
        .cobranza-hero,
        .cobranza-main {
            grid-template-columns: 1fr;
        }

        .cobranza-detail-panel {
            position: static;
        }

        .cobranza-case.is-active {
            transform: translateY(-1px);
        }

        .cobranza-case.is-active::after {
            display: none;
        }
    }

    @media (max-width: 720px) {
        .cobranza-shell {
            padding: 18px;
            border-radius: 20px;
        }

        .cobranza-highlight-grid,
        .cobranza-case-meta,
        .cobranza-detail-summary,
        .cobranza-form-grid {
            grid-template-columns: 1fr;
        }
    }
    .cobranza-legacy-block {
        margin-top: 18px;
    }

    .cobranza-legacy-block .sgl-step-form-ribbon .sgl-text {
        flex: 1 1 auto;
        min-width: 0;
    }

    .cobranza-legacy-block .sgl-step-form-ribbon .sgl-btn-icon {
        margin-left: auto;
        flex: 0 0 auto;
    }

    .cobranza-legacy-block .gallery-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .cobranza-legacy-block .file-preview {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 6px;
        background: #fff;
        display: inline-block;
        text-align: center;
    }

    .cobranza-legacy-block .file-preview p {
        margin: 4px 0 0;
        font-size: 10px;
        max-width: 100px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cobranza-legacy-block .file-preview img {
        width: 60px;
        height: 60px;
        object-fit: cover;
    }

    .cobranza-legacy-block .doc-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: .62rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #475467;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$basePath = site_url('deskapp/cobranza');
$selectedId = (int) ($selected_expediente['id'] ?? 0);
$pagination = $pagination ?? [
    'total_items' => count($items),
    'per_page' => 20,
    'current_page' => 1,
    'total_pages' => 1,
    'from' => empty($items) ? 0 : 1,
    'to' => count($items),
    'has_prev' => false,
    'has_next' => false,
];
$formatDate = static function (?string $value): string {
    if ($value === null || trim($value) === '') {
        return 'Sin fecha';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('d M Y H:i', $timestamp) : $value;
};
$buildUrl = static function (string $path, array $overrides = []) use ($filters, $active_cliente_id): string {
    $params = [];
    $bucket = $overrides['bucket'] ?? ($filters['bucket'] ?? 'all');
    $query = $overrides['q'] ?? ($filters['q'] ?? '');
    $clienteId = array_key_exists('cliente_id', $overrides) ? $overrides['cliente_id'] : $active_cliente_id;
    $page = array_key_exists('page', $overrides) ? (int) $overrides['page'] : (int) ($filters['page'] ?? 1);
    $fragment = trim((string) ($overrides['fragment'] ?? ''));

    if ($bucket !== 'all') {
        $params['bucket'] = $bucket;
    }
    if ($query !== '') {
        $params['q'] = $query;
    }
    if ($clienteId !== null && $clienteId !== '') {
        $params['cliente_id'] = $clienteId;
    }
    if ($page > 1) {
        $params['page'] = $page;
    }

    $url = empty($params) ? $path : $path . '?' . http_build_query($params);

    return $fragment === '' ? $url : $url . '#' . rawurlencode($fragment);
};
$currentPage = (int) ($pagination['current_page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
$pageStart = max(1, $currentPage - 2);
$pageEnd = min($totalPages, $pageStart + 4);
$pageStart = max(1, $pageEnd - 4);

$summaryCards = [
    ['label' => 'Expedientes activos', 'value' => $summary['active'] ?? 0, 'bucket' => 'all', 'class' => ''],
    ['label' => 'Mi cartera', 'value' => $summary['my_portfolio'] ?? 0, 'bucket' => 'my-portfolio', 'class' => ''],
    ['label' => 'Sin evidencia', 'value' => $summary['without_evidence'] ?? 0, 'bucket' => 'sin-evidencia', 'class' => ''],
    ['label' => 'Prioridad 8+ dias', 'value' => $summary['priority_overdue'] ?? 0, 'bucket' => 'aging-8-plus', 'class' => 'is-priority'],
    ['label' => 'Listos para apertura', 'value' => $summary['ready_to_open'] ?? 0, 'bucket' => 'listos-apertura', 'class' => ''],
    ['label' => 'Pago completo', 'value' => $summary['complete_payment'] ?? 0, 'bucket' => 'pago-completo', 'class' => 'is-success'],
];
$cobranzaSchemaReady = !empty($cobranza_schema_ready);
?>

<div class="main-container cobranza-page">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
<div class="cobranza-shell">
    <section class="cobranza-hero">
        <div class="cobranza-panel">
            <span class="cobranza-eyebrow">Centro de cobranza</span>
            <h1 class="cobranza-title">Cartera operativa separada del wizard</h1>
            <p class="cobranza-subtitle">
                Este primer slice concentra la cartera real que ya puede entrar a cobranza, con prioridad, evidencia y contexto operativo en una sola pantalla.
            </p>

            <?php if (!$cobranzaSchemaReady): ?>
                <div class="cobranza-inline-note" style="margin-top:16px;">
                    El esquema del modulo de cobranza aun no existe en la base. Antes de operar expedientes debes aplicar create_cobranza_tables.sql.
                </div>
            <?php endif; ?>

            <div class="cobranza-chip-row" style="margin-top:18px;">
                <span class="cobranza-chip">En seguimiento: <?= esc((string) ($summary['in_follow_up'] ?? 0)) ?></span>
                <span class="cobranza-chip">Pago parcial: <?= esc((string) ($summary['partial_payment'] ?? 0)) ?></span>
                <span class="cobranza-chip">Cliente activo: <?= esc($active_cliente_id ? (string) $active_cliente_id : 'Todos') ?></span>
            </div>
        </div>

        <div class="cobranza-highlight-grid">
            <?php foreach ($summaryCards as $card): ?>
                <a class="cobranza-stat-card <?= esc($card['class']) ?>" href="<?= esc($buildUrl($basePath, ['bucket' => $card['bucket']])) ?>">
                    <?= esc($card['label']) ?>
                    <strong><?= esc((string) $card['value']) ?></strong>
                    <span>Ver expedientes relacionados</span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <form method="get" action="<?= esc($basePath) ?>" class="cobranza-toolbar cobranza-panel">
        <div class="cobranza-field">
            <label for="cobranza-q">Buscar</label>
            <input id="cobranza-q" type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="ID, folio, contrato, cliente, serie o placas">
        </div>

        <div class="cobranza-field">
            <label for="cobranza-bucket">Vista</label>
            <select id="cobranza-bucket" name="bucket">
                <?php foreach (($available_buckets ?? []) as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= ($filters['bucket'] ?? 'all') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!empty($clientes)): ?>
            <div class="cobranza-field">
                <label for="cobranza-cliente">Cliente</label>
                <select id="cobranza-cliente" name="cliente_id">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= esc((string) $cliente['id']) ?>" <?= (int) ($active_cliente_id ?? 0) === (int) $cliente['id'] ? 'selected' : '' ?>><?= esc((string) $cliente['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="cobranza-actions">
            <button type="submit" class="cobranza-btn is-primary">Aplicar filtros</button>
            <a href="<?= esc($basePath) ?>" class="cobranza-btn is-secondary">Limpiar</a>
        </div>
    </form>

    <section class="cobranza-main">
        <div class="cobranza-panel">
            <div class="cobranza-list-header">
                <div>
                    <h2>Cartera visible</h2>
                    <p>
                        <?= esc((string) ($pagination['from'] ?? 0)) ?>-
                        <?= esc((string) ($pagination['to'] ?? 0)) ?> de
                        <?= esc((string) ($pagination['total_items'] ?? count($items))) ?> expediente(s).
                    </p>
                </div>
                <div class="cobranza-chip-row">
                    <span class="cobranza-chip">Sin evidencia: <?= esc((string) ($summary['without_evidence'] ?? 0)) ?></span>
                    <span class="cobranza-chip">Listos para apertura: <?= esc((string) ($summary['ready_to_open'] ?? 0)) ?></span>
                </div>
            </div>

            <?php if (empty($items)): ?>
                <div class="cobranza-empty">
                    No hay expedientes para la combinacion actual de filtros.
                </div>
            <?php else: ?>
                <div class="cobranza-list">
                    <?php foreach ($items as $item): ?>
                        <a href="<?= esc($buildUrl($item['detail_url'], ['page' => $currentPage, 'fragment' => 'cobranza-detalle'])) ?>" class="cobranza-case <?= $selectedId === (int) $item['id'] ? 'is-active' : '' ?>" data-cobranza-detail-link data-tramite-id="<?= esc((string) $item['id']) ?>">
                            <div class="cobranza-case-top">
                                <div>
                                    <small><?= esc($item['folio']) ?></small>
                                    <h3><?= esc($item['cliente_nombre']) ?></h3>
                                </div>
                                <span class="cobranza-tone is-<?= esc($item['attention_tone']) ?>"><?= esc($item['attention_label']) ?></span>
                            </div>

                            <p style="margin:12px 0 0;color:#475467;">
                                <?= esc($item['contrato'] !== '' ? $item['contrato'] : 'Sin contrato') ?> · <?= esc($item['unidad'] !== '' ? $item['unidad'] : 'Unidad no especificada') ?>
                            </p>

                            <div class="cobranza-case-meta">
                                <div class="cobranza-meta-item">
                                    <strong>Ejecutivo interno</strong>
                                    <?= esc($item['owner_name']) ?>
                                </div>
                                <div class="cobranza-meta-item">
                                    <strong>Ejecutivo cliente</strong>
                                    <?= esc($item['cliente_ejecutivo_nombre']) ?>
                                </div>
                                <div class="cobranza-meta-item">
                                    <strong>Etapa actual</strong>
                                    <span class="cobranza-tone is-<?= esc($item['stage_tone']) ?>"><?= esc($item['stage_label']) ?></span>
                                </div>
                                <div class="cobranza-meta-item">
                                    <strong>Evidencia</strong>
                                    <?= esc((string) $item['evidence_total']) ?> archivo(s), <?= esc((string) $item['evidence_partial_count']) ?> parcial(es), <?= esc((string) $item['evidence_complete_count']) ?> completo(s)
                                </div>
                            </div>

                            <div class="cobranza-case-bottom">
                                <span class="cobranza-chip">Antiguedad: <?= esc((string) $item['aging_days']) ?> dias</span>
                                <span class="cobranza-chip">Ultima actividad: <?= esc($formatDate($item['latest_evidence_at'] ?? $item['updated_at'])) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                    <div class="cobranza-pagination">
                        <div class="cobranza-pagination-copy">
                            Pagina <?= esc((string) $currentPage) ?> de <?= esc((string) $totalPages) ?>.
                        </div>
                        <div class="cobranza-pagination-nav">
                            <?php if (!empty($pagination['has_prev'])): ?>
                                <a class="cobranza-btn is-secondary" href="<?= esc($buildUrl($basePath, ['page' => ((int) ($pagination['current_page'] ?? 1)) - 1])) ?>">Anterior</a>
                            <?php endif; ?>
                            <div class="cobranza-pagination-nav-group">
                                <?php if ($pageStart > 1): ?>
                                    <a class="cobranza-btn is-secondary cobranza-page-link" href="<?= esc($buildUrl($basePath, ['page' => 1])) ?>">1</a>
                                    <?php if ($pageStart > 2): ?>
                                        <span class="cobranza-chip">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($page = $pageStart; $page <= $pageEnd; $page++): ?>
                                    <a class="cobranza-btn is-secondary cobranza-page-link <?= $page === $currentPage ? 'is-active' : '' ?>" href="<?= esc($buildUrl($basePath, ['page' => $page])) ?>"><?= esc((string) $page) ?></a>
                                <?php endfor; ?>

                                <?php if ($pageEnd < $totalPages): ?>
                                    <?php if ($pageEnd < ($totalPages - 1)): ?>
                                        <span class="cobranza-chip">...</span>
                                    <?php endif; ?>
                                    <a class="cobranza-btn is-secondary cobranza-page-link" href="<?= esc($buildUrl($basePath, ['page' => $totalPages])) ?>"><?= esc((string) $totalPages) ?></a>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($pagination['has_next'])): ?>
                                <a class="cobranza-btn is-secondary" href="<?= esc($buildUrl($basePath, ['page' => ((int) ($pagination['current_page'] ?? 1)) + 1])) ?>">Siguiente</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <aside id="cobranza-detalle" class="cobranza-panel cobranza-detail-panel <?= !empty($selected_expediente) ? 'is-selected' : '' ?>" data-cobranza-detail-panel data-detail-url="<?= esc((string) ($selected_expediente['detail_url'] ?? '')) ?>" data-tramite-id="<?= esc((string) ($selected_expediente['id'] ?? 0)) ?>">
            <div class="cobranza-detail-feedback" data-cobranza-detail-feedback></div>
            <div class="cobranza-detail-body" data-cobranza-detail-body>
                <?= $this->include('deskapp/cobranza/_detail') ?>
            </div>
        </aside>
    </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
<script>
    (() => {
        const detailPanel = document.querySelector('[data-cobranza-detail-panel]');
        const detailBody = detailPanel?.querySelector('[data-cobranza-detail-body]');
        const detailFeedback = detailPanel?.querySelector('[data-cobranza-detail-feedback]');
        if (!detailPanel || !detailBody || typeof window.fetch !== 'function') {
            return;
        }

        let activeRequest = null;
        const loaderMarkup = `
            <div class="cobranza-detail-loader">
                <div class="cobranza-detail-loader-spinner" aria-hidden="true"></div>
                <div>
                    <strong>Cargando expediente...</strong><br>
                    <span>Estamos trayendo el detalle sin recargar toda la cartera.</span>
                </div>
            </div>
        `;

        const showFeedback = (message, tone = 'success') => {
            if (!detailFeedback) {
                return;
            }

            detailFeedback.textContent = message || '';
            detailFeedback.className = 'cobranza-detail-feedback ' + (tone === 'error' ? 'is-error' : 'is-success');
        };

        const clearFeedback = () => {
            if (!detailFeedback) {
                return;
            }

            detailFeedback.textContent = '';
            detailFeedback.className = 'cobranza-detail-feedback';
        };

        const normalizeUrl = (url) => (url || '').split('#')[0];

        const getCurrentDetailUrl = () => normalizeUrl(detailPanel.dataset.detailUrl || '');

        const getCurrentTramiteId = () => detailPanel.dataset.tramiteId || '';

        const cleanupDetachedModals = () => {
            document.querySelectorAll('[data-cobranza-detached-modal="1"]').forEach((modal) => {
                if (modal.parentElement === document.body) {
                    modal.remove();
                }
            });

            document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
                backdrop.remove();
            });

            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
        };

        const syncDetachedModals = () => {
            cleanupDetachedModals();

            detailBody.querySelectorAll('[data-cobranza-detached-modal="1"]').forEach((modal) => {
                document.body.appendChild(modal);
            });
        };

        const setActiveCard = (tramiteId) => {
            document.querySelectorAll('[data-cobranza-detail-link]').forEach((link) => {
                link.classList.toggle('is-active', String(link.dataset.tramiteId || '') === String(tramiteId || ''));
            });
        };

        const loadDetail = (targetUrl, options = {}) => {
            const preserveScroll = options.preserveScroll === true;
            const pushState = options.pushState !== false;
            const activeTramiteId = options.tramiteId || getCurrentTramiteId();

            if (activeRequest && typeof activeRequest.abort === 'function') {
                activeRequest.abort();
            }

            const abortController = typeof window.AbortController === 'function' ? new window.AbortController() : null;
            activeRequest = abortController;

            const previousHtml = detailBody.innerHTML;
            detailPanel.classList.add('is-loading', 'is-selected');
            detailPanel.setAttribute('aria-busy', 'true');
            detailBody.innerHTML = loaderMarkup;

            return window.fetch(targetUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                signal: abortController ? abortController.signal : undefined
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('No se pudo cargar el detalle.');
                    }

                    return response.text();
                })
                .then((html) => {
                    detailBody.innerHTML = html;
                    syncDetachedModals();
                    detailPanel.dataset.detailUrl = normalizeUrl(targetUrl);
                    detailPanel.dataset.tramiteId = String(activeTramiteId || '');
                    setActiveCard(activeTramiteId);
                    if (pushState && window.history && typeof window.history.pushState === 'function') {
                        window.history.pushState({ cobranzaDetail: true }, '', targetUrl);
                    }
                    if (!preserveScroll) {
                        detailPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                })
                .catch((error) => {
                    if (error && error.name === 'AbortError') {
                        return;
                    }

                    detailBody.innerHTML = previousHtml;
                    throw error;
                })
                .finally(() => {
                    if (activeRequest === abortController) {
                        activeRequest = null;
                    }
                    detailPanel.classList.remove('is-loading');
                    detailPanel.removeAttribute('aria-busy');
                });
        };

            syncDetachedModals();

        document.addEventListener('click', (event) => {
            const link = event.target.closest('[data-cobranza-detail-link]');
            if (!link) {
                return;
            }

            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            event.preventDefault();

            const targetUrl = link.getAttribute('href');
            if (!targetUrl) {
                return;
            }

            clearFeedback();
            loadDetail(targetUrl, { tramiteId: link.dataset.tramiteId || '' })
                .catch(() => {
                    window.location.href = targetUrl;
                });
        });

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('[data-cobranza-ajax-form]');
            if (!form) {
                return;
            }

            event.preventDefault();

            const confirmMessage = form.getAttribute('data-confirm');
            if (confirmMessage && !window.confirm(confirmMessage)) {
                return;
            }

            const submitter = event.submitter instanceof HTMLElement ? event.submitter : form.querySelector('[type="submit"]');
            const previousLabel = submitter ? submitter.innerHTML : '';
            if (submitter) {
                submitter.setAttribute('disabled', 'disabled');
                submitter.innerHTML = 'Guardando...';
            }

            clearFeedback();

            window.fetch(form.action, {
                method: (form.method || 'POST').toUpperCase(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new window.FormData(form)
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || payload.success === false) {
                        const errors = payload.errors && typeof payload.errors === 'object'
                            ? Object.values(payload.errors).join(' ')
                            : '';
                        throw new Error(errors || payload.message || 'No se pudo completar la accion.');
                    }

                    return payload;
                })
                .then((payload) => {
                    const successRedirect = form.getAttribute('data-success-redirect');
                    if (successRedirect) {
                        showFeedback(payload.message || 'Cambio guardado.', 'success');
                        window.setTimeout(() => {
                            window.location.href = successRedirect;
                        }, 250);
                        return;
                    }

                    const currentUrl = getCurrentDetailUrl();
                    if (!currentUrl) {
                        showFeedback(payload.message || 'Cambio guardado.', 'success');
                        return;
                    }

                    return loadDetail(currentUrl, { tramiteId: getCurrentTramiteId(), pushState: false, preserveScroll: true })
                        .then(() => {
                            showFeedback(payload.message || 'Cambio guardado.', 'success');
                        });
                })
                .catch((error) => {
                    showFeedback(error.message || 'No se pudo completar la accion.', 'error');
                })
                .finally(() => {
                    if (submitter) {
                        submitter.removeAttribute('disabled');
                        submitter.innerHTML = previousLabel;
                    }
                });
        });

        document.addEventListener('change', (event) => {
            const input = event.target.closest('.cobranza-file-input');
            if (!input) {
                return;
            }

            const field = input.closest('.cobranza-field');
            const fileNameNode = field ? field.querySelector('[data-cobranza-file-name]') : null;
            if (!fileNameNode) {
                return;
            }

            const selectedFile = input.files && input.files[0] ? input.files[0].name : 'Sin archivo seleccionado';
            fileNameNode.textContent = selectedFile;
        });

        const updatePickerDragState = (picker, isActive) => {
            if (!picker) {
                return;
            }

            picker.classList.toggle('is-dragover', Boolean(isActive));
        };

        document.addEventListener('dragenter', (event) => {
            const picker = event.target.closest('[data-cobranza-file-picker]');
            if (!picker) {
                return;
            }

            event.preventDefault();
            updatePickerDragState(picker, true);
        });

        document.addEventListener('dragover', (event) => {
            const picker = event.target.closest('[data-cobranza-file-picker]');
            if (!picker) {
                return;
            }

            event.preventDefault();
            updatePickerDragState(picker, true);
        });

        document.addEventListener('dragleave', (event) => {
            const picker = event.target.closest('[data-cobranza-file-picker]');
            if (!picker) {
                return;
            }

            const relatedTarget = event.relatedTarget;
            if (relatedTarget instanceof Node && picker.contains(relatedTarget)) {
                return;
            }

            updatePickerDragState(picker, false);
        });

        document.addEventListener('drop', (event) => {
            const picker = event.target.closest('[data-cobranza-file-picker]');
            if (!picker) {
                return;
            }

            event.preventDefault();
            updatePickerDragState(picker, false);

            const input = picker.parentElement ? picker.parentElement.querySelector('.cobranza-file-input') : null;
            const files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : null;
            if (!input || !files || files.length === 0) {
                return;
            }

            try {
                input.files = files;
            } catch (error) {
                return;
            }

            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    })();
</script>
<?= $this->endSection() ?>