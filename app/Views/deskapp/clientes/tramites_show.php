<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>

<style>
    :root {
        --cs-ink: #1f2933;
        --cs-muted: #6b7280;
        --cs-surface: #ffffff;
        --cs-panel: #f3efe7;
        --cs-accent: #0f766e;
        --cs-border: #e7e1d6;
        --cs-shadow: 0 14px 32px rgba(15, 23, 42, 0.12);
    }

    .client-tramite-show {
        background: radial-gradient(circle at top right, #f7efe1 0%, #f9fafb 55%, #ffffff 100%);
        min-height: 100vh;
    }

    .client-tramite-show .empty-state {
        padding: 18px;
        border: 1px dashed var(--cs-border);
        border-radius: 12px;
        color: var(--cs-muted);
        text-align: center;
        background: #ffffff;
    }

    .client-tramite-show .page-header {
        border-radius: 18px;
        background: linear-gradient(120deg, #ffffff 0%, #fdf6ea 100%);
        border: 1px solid var(--cs-border);
        box-shadow: var(--cs-shadow);
        padding: 18px 20px;
    }

    .client-tramite-show .hero {
        border-radius: 18px;
        background: linear-gradient(120deg, #0f766e 0%, #1f2933 100%);
        color: #ffffff;
        padding: 18px 20px;
        position: relative;
        overflow: hidden;
    }

    .client-tramite-show .hero-title {
        margin: 0 0 4px;
        font-weight: 800;
        letter-spacing: 0.2px;
    }

    .client-tramite-show .hero-subtitle {
        font-size: 12px;
        opacity: 0.85;
        margin: 0 0 10px;
    }

    .client-tramite-show .hero::after {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
        border-radius: 999px;
    }

    .client-tramite-show .hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .client-tramite-show .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: 700;
    }

    .client-tramite-show .hero-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 999px;
        padding: 6px 14px;
    }

    .client-tramite-show .hero-semaforo {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 800;
        border-radius: 999px;
        padding: 6px 14px;
        color: #ffffff;
        background: rgba(15, 23, 42, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.28);
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.20);
        white-space: nowrap;
    }

    .client-tramite-show .hero-semaforo.is-green { background: rgba(34, 197, 94, 0.85); border-color: rgba(34, 197, 94, 0.95); }
    .client-tramite-show .hero-semaforo.is-yellow { background: rgba(245, 158, 11, 0.88); border-color: rgba(245, 158, 11, 0.96); }
    .client-tramite-show .hero-semaforo.is-red { background: rgba(239, 68, 68, 0.88); border-color: rgba(239, 68, 68, 0.96); }
    .client-tramite-show .hero-semaforo.is-violet { background: rgba(124, 58, 237, 0.88); border-color: rgba(124, 58, 237, 0.96); }
    .client-tramite-show .hero-semaforo.is-neutral { background: rgba(148, 163, 184, 0.35); border-color: rgba(255, 255, 255, 0.22); }

    .client-tramite-show .docs-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .client-tramite-show .doc-card {
        width: 118px;
        border: 1px solid var(--cs-border);
        border-radius: 12px;
        padding: 10px;
        background: #ffffff;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
        text-align: center;
    }

    .client-tramite-show .doc-thumb {
        width: 78px;
        height: 78px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #f8fafc;
    }

    .client-tramite-show .doc-name {
        margin-top: 8px;
        font-size: 10px;
        color: var(--cs-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .client-tramite-show .doc-badge {
        display: inline-block;
        margin-top: 6px;
        font-size: 10px;
        font-weight: 800;
        color: #1d4ed8;
        background: rgba(37, 99, 235, 0.12);
        border: 1px solid rgba(37, 99, 235, 0.16);
        border-radius: 999px;
        padding: 2px 8px;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .client-tramite-show .hero-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .client-tramite-show .hero-tile {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .client-tramite-show .hero-tile span {
        display: block;
        font-size: 11px;
        opacity: 0.8;
    }

    .client-tramite-show .hero-tile strong {
        font-size: 13px;
        font-weight: 700;
    }

    .client-tramite-show .quick-actions-ribbon {
        margin-bottom: 18px;
        padding: 12px 16px;
        border-radius: 16px;
        border: 1px solid var(--cs-border);
        background: #ffffff;
        box-shadow: var(--cs-shadow);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .client-tramite-show .ribbon-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        color: var(--cs-ink);
        white-space: nowrap;
        padding-right: 12px;
        border-right: 2px solid #e8ecf1;
    }

    .client-tramite-show .ribbon-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        flex: 1;
    }

    .client-tramite-show .ribbon-btn {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        padding: 10px 12px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid var(--cs-border);
        border-radius: 14px;
        min-width: 190px;
        max-width: 320px;
        text-align: left;
        position: relative;
        overflow: hidden;
        white-space: normal;
        word-break: break-word;
    }

    .client-tramite-show .ribbon-btn.is-disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .client-tramite-show .ribbon-btn.is-disabled::before {
        display: none;
    }

    .client-tramite-show .ribbon-btn::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(135deg, var(--cs-accent) 0%, #2563eb 100%);
        transform: scaleX(0);
        transition: transform 0.2s ease;
    }

    .client-tramite-show .ribbon-btn:hover::before {
        transform: scaleX(1);
    }

    .client-tramite-show .ribbon-icon {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 16px;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.18);
    }

    .client-tramite-show .ribbon-label {
        font-size: 11px;
        font-weight: 700;
        color: #424242;
        line-height: 1.2;
        display: block;
    }

    .client-tramite-show .ribbon-sub {
        font-size: 11px;
        font-weight: 700;
        color: var(--cs-ink);
        display: block;
        margin-top: 2px;
        max-width: 240px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .client-tramite-show .ribbon-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .client-tramite-show .card-box {
        border: 1px solid var(--cs-border);
        border-radius: 16px;
        background: var(--cs-surface);
        box-shadow: var(--cs-shadow);
    }

    .client-tramite-show .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }

    .client-tramite-show .exec-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .client-tramite-show .exec-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.12);
        color: var(--cs-accent);
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid rgba(15, 118, 110, 0.18);
        white-space: nowrap;
    }

    .client-tramite-show .info-tile {
        border: 1px solid var(--cs-border);
        border-radius: 14px;
        padding: 14px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
        position: relative;
        overflow: hidden;
        min-height: 78px;
    }

    .client-tramite-show .info-tile::after {
        content: '';
        position: absolute;
        top: -44px;
        right: -44px;
        width: 120px;
        height: 120px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(15, 118, 110, 0.14) 0%, rgba(15, 118, 110, 0) 70%);
    }

    .client-tramite-show .info-tile span {
        display: block;
        font-size: 11px;
        color: var(--cs-muted);
        text-transform: uppercase;
        letter-spacing: 0.07em;
        font-weight: 800;
    }

    .client-tramite-show .info-tile strong {
        display: block;
        margin-top: 6px;
        font-size: 15px;
        color: var(--cs-ink);
        font-weight: 800;
        line-height: 1.2;
        position: relative;
        z-index: 1;
        word-break: break-word;
    }

    .client-tramite-show .info-tile strong.is-empty {
        color: var(--cs-muted);
        font-weight: 700;
    }

    .client-tramite-show .timeline-subtitle {
        margin-top: 6px;
        font-size: 12px;
        color: var(--cs-muted);
    }

    .client-tramite-show .ux-top-grid {
        margin-top: 12px;
        margin-bottom: 12px;
        display: grid;
        grid-template-columns: 1.05fr 0.95fr;
        gap: 12px;
        align-items: stretch;
    }

    @media (max-width: 991px) {
        .client-tramite-show .ux-top-grid {
            grid-template-columns: 1fr;
        }
    }

    .client-tramite-show .timeline-progress {
        margin-top: 0;
        margin-bottom: 0;
        border: 1px solid var(--cs-border);
        border-radius: 14px;
        padding: 12px 14px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f5ef 100%);
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
    }

    .client-tramite-show .timeline-progress-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .client-tramite-show .timeline-progress-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--cs-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .client-tramite-show .timeline-progress-value {
        font-size: 16px;
        font-weight: 800;
        color: var(--cs-ink);
    }

    .client-tramite-show .timeline-progress-track {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .client-tramite-show .timeline-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #0f766e 0%, #2563eb 55%, #f59e0b 100%);
        transition: width 0.3s ease;
    }

    .client-tramite-show .timeline-progress-foot {
        margin-top: 8px;
        font-size: 12px;
        color: var(--cs-muted);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .client-tramite-show .timeline {
        position: relative;
        padding-left: 36px;
        display: grid;
        gap: 14px;
    }

    .client-tramite-show .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 10px;
        bottom: 10px;
        width: 6px;
        border-radius: 999px;
        background: linear-gradient(180deg, #0f766e 0%, #38bdf8 50%, #f59e0b 100%);
        opacity: 0.5;
    }

    .client-tramite-show .timeline-item {
        position: relative;
        padding: 14px 16px 14px 18px;
        border-radius: 16px;
        border: 1px solid var(--cs-border);
        background: #ffffff;
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08);
        display: grid;
        gap: 8px;
    }

    .client-tramite-show .timeline-item::before {
        content: '';
        position: absolute;
        left: -32px;
        top: 20px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #94a3b8;
        box-shadow: 0 0 0 6px rgba(148, 163, 184, 0.2);
    }

    .client-tramite-show .timeline-item.is-done::before {
        background: #22c55e;
        box-shadow: 0 0 0 6px rgba(34, 197, 94, 0.2);
    }

    .client-tramite-show .timeline-item.is-current::before {
        background: #f59e0b;
        box-shadow: 0 0 0 6px rgba(245, 158, 11, 0.25);
    }

    .client-tramite-show .timeline-item.is-pending::before {
        background: #94a3b8;
        box-shadow: 0 0 0 6px rgba(148, 163, 184, 0.2);
    }

    .client-tramite-show .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .client-tramite-show .timeline-step {
        font-size: 11px;
        font-weight: 700;
        color: var(--cs-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .client-tramite-show .timeline-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--cs-ink);
    }

    .client-tramite-show .timeline-chip {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.2);
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .client-tramite-show .timeline-chip.is-done {
        background: rgba(34, 197, 94, 0.2);
        color: #166534;
    }

    .client-tramite-show .timeline-chip.is-current {
        background: rgba(245, 158, 11, 0.2);
        color: #92400e;
    }

    .client-tramite-show .timeline-chip.is-pending {
        background: rgba(148, 163, 184, 0.2);
        color: #334155;
    }

    .client-tramite-show .timeline-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        font-size: 12px;
        color: var(--cs-muted);
    }

    .client-tramite-show .timeline-detail {
        font-size: 13px;
        color: var(--cs-ink);
    }

    .client-tramite-show .realtime-card {
        margin-top: 0;
        border: 1px solid var(--cs-border);
        border-radius: 14px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        padding: 14px;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
    }

    .client-tramite-show .realtime-card.is-highlight {
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.14) 0%, rgba(37, 99, 235, 0.12) 60%, #ffffff 100%);
        border-color: rgba(15, 118, 110, 0.25);
    }

    .client-tramite-show .realtime-kicker {
        font-size: 11px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--cs-muted);
        margin-bottom: 6px;
    }

    .client-tramite-show .realtime-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--cs-ink);
        margin-bottom: 4px;
    }

    .client-tramite-show .realtime-meta {
        font-size: 12px;
        color: var(--cs-muted);
    }

    .client-tramite-show .activity-feed {
        display: grid;
        gap: 10px;
    }

    .client-tramite-show .activity-sequence {
        position: relative;
        padding-left: 34px;
    }

    .client-tramite-show .activity-sequence::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 4px;
        bottom: 4px;
        width: 6px;
        border-radius: 999px;
        background: linear-gradient(180deg, #0f766e 0%, #22c55e 45%, #3b82f6 100%);
        opacity: 0.35;
    }

    .client-tramite-show .activity-item {
        position: relative;
        border: 1px solid var(--cs-border);
        border-radius: 14px;
        background: #ffffff;
        padding: 12px 12px 12px 14px;
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .client-tramite-show .activity-item::before {
        content: '';
        position: absolute;
        left: -28px;
        top: 16px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #94a3b8;
        box-shadow: 0 0 0 5px rgba(148, 163, 184, 0.25);
    }

    .client-tramite-show .activity-item::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        border-radius: 14px 0 0 14px;
        background: #94a3b8;
    }

    .client-tramite-show .activity-item.status_change::before,
    .client-tramite-show .activity-item.status_change::after {
        background: #2563eb;
        box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.2);
    }

    .client-tramite-show .activity-item.upload::before,
    .client-tramite-show .activity-item.upload::after {
        background: #10b981;
        box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2);
    }

    .client-tramite-show .activity-item.update::before,
    .client-tramite-show .activity-item.update::after {
        background: #f59e0b;
        box-shadow: 0 0 0 5px rgba(245, 158, 11, 0.22);
    }

    .client-tramite-show .activity-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }

    .client-tramite-show .activity-chip {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-radius: 999px;
        padding: 3px 8px;
        color: #0f172a;
        background: #e2e8f0;
    }

    .client-tramite-show .activity-chip.status_change {
        background: rgba(59, 130, 246, 0.2);
        color: #1d4ed8;
    }

    .client-tramite-show .activity-chip.upload {
        background: rgba(16, 185, 129, 0.2);
        color: #047857;
    }

    .client-tramite-show .activity-chip.update {
        background: rgba(245, 158, 11, 0.2);
        color: #92400e;
    }

    .client-tramite-show .activity-text {
        font-size: 13px;
        color: var(--cs-ink);
        font-weight: 600;
        margin-bottom: 6px;
    }

    .client-tramite-show .activity-change {
        font-size: 12px;
        color: var(--cs-muted);
    }

    .client-tramite-show .activity-change strong {
        color: var(--cs-ink);
    }

    .client-tramite-show .activity-values {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        font-size: 12px;
        color: var(--cs-ink);
    }

    .client-tramite-show .activity-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 3px 9px;
        font-weight: 700;
        background: #f1f5f9;
        color: #0f172a;
    }

    .client-tramite-show .activity-sub {
        padding-top: 10px;
        margin-top: 10px;
        border-top: 1px dashed rgba(231, 225, 214, 0.9);
        display: grid;
        gap: 8px;
    }

    .client-tramite-show .activity-sub-row {
        display: grid;
        grid-template-columns: 82px 1fr;
        gap: 10px;
        align-items: start;
        font-size: 12px;
        color: var(--cs-ink);
    }

    .client-tramite-show .activity-sub-time {
        font-weight: 800;
        color: var(--cs-muted);
        white-space: nowrap;
    }

    .client-tramite-show .activity-sub-title {
        font-weight: 700;
        color: var(--cs-ink);
        line-height: 1.2;
    }

    .client-tramite-show .activity-sub-meta {
        margin-top: 2px;
        color: var(--cs-muted);
        font-size: 11px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .client-tramite-show .status-ribbon-list {
        position: relative;
        margin-top: 8px;
        padding-left: 32px;
        display: grid;
        gap: 10px;
    }

    .client-tramite-show .status-ribbon-list::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 6px;
        bottom: 6px;
        width: 5px;
        border-radius: 999px;
        background: linear-gradient(180deg, #2563eb 0%, #0ea5e9 100%);
        opacity: 0.35;
    }

    .client-tramite-show .status-ribbon {
        position: relative;
        border-radius: 14px;
        border: 1px solid var(--cs-border);
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        padding: 11px 12px;
        box-shadow: 0 8px 15px rgba(15, 23, 42, 0.06);
    }

    .client-tramite-show .status-ribbon::before {
        content: '';
        position: absolute;
        left: -26px;
        top: 14px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #2563eb;
        box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.2);
    }

    .client-tramite-show .status-ribbon-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        color: var(--cs-muted);
        margin-bottom: 4px;
    }

    .client-tramite-show .status-ribbon-title {
        font-size: 13px;
        color: var(--cs-ink);
        font-weight: 700;
    }

    @media (max-width: 767px) {
        .client-tramite-show .timeline {
            padding-left: 30px;
        }

        .client-tramite-show .timeline-item::before {
            left: -28px;
        }

        .client-tramite-show .timeline-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .client-tramite-show .timeline-progress-value {
            font-size: 14px;
        }

        .client-tramite-show .activity-sequence,
        .client-tramite-show .status-ribbon-list {
            padding-left: 28px;
        }

        .client-tramite-show .activity-item::before,
        .client-tramite-show .status-ribbon::before {
            left: -22px;
        }

        .client-tramite-show .ribbon-btn {
            min-width: 100%;
            max-width: 100%;
        }

        .client-tramite-show .ribbon-sub {
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
        }
    }

    .client-tramite-show .cost-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .client-tramite-show .cost-tile {
        border-radius: 14px;
        border: 1px solid var(--cs-border);
        padding: 14px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
        position: relative;
        overflow: hidden;
        min-height: 84px;
    }

    .client-tramite-show .cost-tile::after {
        content: '';
        position: absolute;
        top: -44px;
        right: -44px;
        width: 120px;
        height: 120px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.14) 0%, rgba(37, 99, 235, 0) 70%);
    }

    .client-tramite-show .cost-tile span {
        display: block;
        font-size: 11px;
        color: var(--cs-muted);
        text-transform: uppercase;
        letter-spacing: 0.07em;
        font-weight: 800;
    }

    .client-tramite-show .cost-tile strong {
        display: block;
        margin-top: 6px;
        font-size: 15px;
        color: var(--cs-ink);
        font-weight: 800;
        position: relative;
        z-index: 1;
    }

    .client-tramite-show .cost-tile.is-total {
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.14) 0%, rgba(245, 158, 11, 0.12) 65%, #ffffff 100%);
        border-color: rgba(15, 118, 110, 0.22);
    }

    .client-tramite-show .cost-tile.is-total::after {
        background: radial-gradient(circle, rgba(15, 118, 110, 0.18) 0%, rgba(15, 118, 110, 0) 70%);
    }

    .client-tramite-show .cost-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .client-tramite-show .cost-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 800;
        border: 1px solid rgba(37, 99, 235, 0.18);
        white-space: nowrap;
    }

    @media (max-width: 480px) {
        .client-tramite-show .cost-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    $formatDate = static function ($value) {
        if (empty($value)) {
            return 'Pendiente';
        }
        $ts = strtotime($value);
        if (!$ts) {
            return (string) $value;
        }
        return date('d/m/Y H:i', $ts);
    };
    $formatDateShort = static function ($value) {
        if (empty($value)) {
            return 'Pendiente';
        }
        $ts = strtotime($value);
        if (!$ts) {
            return (string) $value;
        }
        return date('d/m/Y H:i', $ts);
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
    $normalizeAuditValue = static function ($value) {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return '—';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}(?:[T\s]\d{2}:\d{2}(?::\d{2})?)?$/', $text)) {
            $ts = strtotime($text);
            if ($ts) {
                return date('d/m/Y H:i', $ts);
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
                <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px; position: relative; z-index: 1;">
                    <div>
                        <h4 class="hero-title">Resumen inteligente del trámite</h4>
                        <div class="hero-subtitle">Seguimiento operativo con línea de tiempo y bitácora real.</div>
                        <div class="hero-badges">
                            <span class="hero-badge"><i class="fa fa-hashtag"></i> <?= esc($folioValue) ?></span>
                            <span class="hero-badge"><i class="fa fa-file-signature"></i> <?= esc($contratoValue) ?></span>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap justify-content-end" style="gap: 8px;">
                            <div class="hero-status"><i class="fa fa-circle"></i> <?= esc($tramite['tra_status'] ?? 'N/A') ?></div>
                            <div class="hero-semaforo <?= esc($semaforo['class'] ?? 'is-neutral') ?>" title="Semáforo calculado desde el inicio real">
                                <i class="fa fa-traffic-light"></i>
                                <?= esc(($semaforo['label'] ?? 'Sin iniciar') . ' · ' . ($semaforo['scope'] ?? '—')) ?>
                                <?php if (($semaforo['days'] ?? null) !== null): ?>
                                    <span style="opacity:0.9; font-weight: 700;">· <?= esc((string) $semaforo['days']) ?> días</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hero-grid" style="position: relative; z-index: 1;">
                    <div class="hero-tile"><span>Inicio</span><strong><?= esc($formatDate($tramite['started_at'] ?? null)) ?></strong></div>
                    <div class="hero-tile"><span>Tipo de trámite</span><strong><?= esc($tramite['tipo_tramite'] ?? 'N/A') ?></strong></div>
                    <div class="hero-tile"><span>Cliente</span><strong><?= esc($tramite['cliente'] ?? 'N/A') ?></strong></div>
                    <div class="hero-tile"><span>Gestor</span><strong><?= esc($tramite['gestor'] ?? 'Sin asignar') ?></strong></div>
                    <div class="hero-tile"><span>Empresa gestora</span><strong><?= esc($tramite['empresa_gestora'] ?? 'Sin asignar') ?></strong></div>
                </div>
                <div class="mt-12 text-right" style="position: relative; z-index: 1;">
                    <a class="btn btn-outline-light" href="<?= site_url('/deskapp/clientes/tramites') ?>">
                        <i class="fa fa-arrow-left"></i> Volver al listado
                    </a>
                </div>
            </div>

            <div class="quick-actions-ribbon">
                <div class="ribbon-title">
                    <i class="fas fa-bolt"></i>
                    <span>Detalle rápido</span>
                </div>
                <div class="ribbon-buttons">
                    <?php
                        $docsTotal = (int) ($doc_status_docs_total ?? count($doc_status_docs ?? []));
                        $docsUploaded = (int) ($doc_status_docs_uploaded ?? 0);
                    ?>
                    <button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-documentos-cliente" title="Ver documentos del trámite">
                        <div class="ribbon-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <span class="ribbon-text">
                            <span class="ribbon-label">Documentos</span>
                            <span class="ribbon-sub">
                                <?= $docsTotal > 0
                                    ? esc((string) $docsUploaded . ' / ' . (string) $docsTotal . ' subidos')
                                    : 'Sin documentos'
                                ?>
                            </span>
                        </span>
                    </button>
                    <button type="button" class="ribbon-btn" title="<?= esc($tramite['cobro_status'] ?? 'N/A') ?>">
                        <div class="ribbon-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <span class="ribbon-text">
                            <span class="ribbon-label">Cobro</span>
                            <span class="ribbon-sub"><?= esc($tramite['cobro_status'] ?? 'N/A') ?></span>
                        </span>
                    </button>
                    <button type="button" class="ribbon-btn" title="<?= esc($tramite['gestor'] ?? 'Sin asignar') ?>">
                        <div class="ribbon-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span class="ribbon-text">
                            <span class="ribbon-label">Gestor asignado</span>
                            <span class="ribbon-sub"><?= esc($tramite['gestor'] ?? 'Sin asignar') ?></span>
                        </span>
                    </button>
                    <button type="button" class="ribbon-btn" title="<?= esc(($tramite['entidad'] ?? 'N/A') . ' · ' . ($tramite['municipio'] ?? 'N/A')) ?>">
                        <div class="ribbon-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <span class="ribbon-text">
                            <span class="ribbon-label">Entidad y municipio</span>
                            <span class="ribbon-sub"><?= esc(($tramite['entidad'] ?? 'N/A') . ' · ' . ($tramite['municipio'] ?? 'N/A')) ?></span>
                        </span>
                    </button>

                    <?php
                        $docUrl = (string) ($doc_gestor_entregado_url ?? '');
                        $docFile = (string) (($doc_gestor_entregado['file'] ?? '') ?: '');
                        $docWhen = $doc_gestor_entregado['created_at'] ?? null;
                        $docAvailable = $docUrl !== '';
                        $docTitle = $docAvailable
                            ? ('Ver documento: ' . $docFile)
                            : 'Aún no se ha subido el trámite al sistema';
                        $docSub = $docAvailable
                            ? ($docFile !== '' ? $docFile : 'Documento disponible')
                            : 'Aún no disponible';
                    ?>
                    <?php if ($docAvailable): ?>
                        <a class="ribbon-btn" href="<?= esc($docUrl) ?>" target="_blank" rel="noopener" title="<?= esc($docTitle) ?>">
                            <div class="ribbon-icon" style="background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%);">
                                <i class="fas fa-file-download"></i>
                            </div>
                            <span class="ribbon-text">
                                <span class="ribbon-label">Documento entregado por gestor</span>
                                <span class="ribbon-sub"><?= esc($docSub) ?></span>
                                <?php if (!empty($docWhen)): ?>
                                    <span class="ribbon-label" style="margin-top:2px;">Subido: <?= esc($formatDateShort($docWhen)) ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <div class="ribbon-btn is-disabled" title="<?= esc($docTitle) ?>" aria-disabled="true">
                            <div class="ribbon-icon" style="background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);">
                                <i class="fas fa-file"></i>
                            </div>
                            <span class="ribbon-text">
                                <span class="ribbon-label">Documento entregado por gestor</span>
                                <span class="ribbon-sub"><?= esc($docSub) ?></span>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

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

            <div class="card-box pd-20 mb-20">
                <?php
                    $auditTop = ($audit_log[0] ?? null);
                    $auditTitle = '';
                    if (!empty($auditTop)) {
                        $auditTitle = trim((string) ($auditTop['username'] ?? ''));
                    }
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

                    <?php
                        $execInterno = trim((string) (($tramite['firstname'] ?? '') . ' ' . ($tramite['lastname'] ?? '')));
                        [$v, $empty] = $formatTileValue($execInterno);
                    ?>
                    <div class="info-tile"><span>Ejecutivo interno</span><strong class="<?= $empty ? 'is-empty' : '' ?>"><?= esc($v) ?></strong></div>

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
                                'titulo' => 'Gestion con gestor',
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
                                        <?= esc($latestActivity['username'] ?? 'Sistema') ?> · <?= esc($formatDateShort($latestActivity['created_at'] ?? null)) ?>
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
                                                                    <span><?= esc($event['username'] ?? 'Sistema') ?></span>
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

<?= $this->endSection() ?>
