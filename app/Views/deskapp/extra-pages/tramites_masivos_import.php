<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>
<?php $assets = base_url('/public/assets'); ?>
<style>
    .tmass-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:18px;box-shadow:0 10px 30px rgba(15,23,42,.05)}
    .tmass-title{font-size:1.05rem;font-weight:800;color:#0f172a;margin:0 0 8px 0}
    .tmass-subtitle{color:#64748b;font-size:.9rem;margin-bottom:18px}
    .tmass-loader{display:none;align-items:center;gap:12px;margin-top:16px;padding:14px 16px;border-radius:12px;border:1px solid #bfdbfe;background:linear-gradient(135deg,#eff6ff,#f8fafc);color:#1d4ed8;font-size:.9rem;font-weight:800}
    .tmass-loader.is-visible{display:flex}
    .tmass-loader-spinner{width:18px;height:18px;border-radius:999px;border:2px solid rgba(29,78,216,.2);border-top-color:#1d4ed8;animation:tmass-spin .8s linear infinite;flex:0 0 auto}
    .tmass-loader-subtitle{display:block;color:#475569;font-size:.78rem;font-weight:600}
    .tmass-summary{display:flex;flex-wrap:wrap;gap:10px;margin-top:12px}
    .tmass-badge{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:7px 12px;font-size:.78rem;font-weight:800;background:#eef2ff;color:#1e3a8a;border:1px solid #c7d2fe}
    .tmass-badge.is-success{background:#ecfdf5;color:#166534;border-color:#86efac}
    .tmass-badge.is-muted{background:#f8fafc;color:#334155;border-color:#e2e8f0}
    .tmass-table-wrap{margin-top:18px;display:none}
    .tmass-table th{font-size:.74rem;text-transform:uppercase;letter-spacing:.04em;background:#f8fafc;color:#334155;border-top:none}
    .tmass-table td{vertical-align:middle;font-size:.83rem}
    .tmass-table .tmass-col-observaciones{min-width:220px}
    .tmass-save-btn{min-width:122px;font-weight:700}
    .tmass-save-btn.is-saved{background:#16a34a;border-color:#16a34a;color:#fff}
    .tmass-row-ready{background:#fffdf7}
    .tmass-row-existing{background:#f8fbff}
    .tmass-row-error{background:#fff6f8}
    .tmass-row-saved{background:#f0fdf4}
    .tmass-status-row td{padding-top:0;border-top:none;background:transparent}
    .tmass-status{margin:0;padding:10px 12px;border-radius:10px;border:1px solid transparent;font-size:.82rem;font-weight:700}
    .tmass-status a{font-weight:800;text-decoration:underline}
    .tmass-status.is-ready{background:#fffbeb;border-color:#fde68a;color:#92400e}
    .tmass-status.is-existing{background:#eff6ff;border-color:#93c5fd;color:#1d4ed8}
    .tmass-status.is-existing a{color:#1d4ed8}
    .tmass-status.is-error{background:#fff1f2;border-color:#fda4af;color:#be123c}
    .tmass-status.is-error a{color:#be123c}
    .tmass-status.is-saved{background:#ecfdf5;border-color:#86efac;color:#166534}
    .tmass-status.is-saved a{color:#166534}
    .tmass-empty{padding:20px;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc;color:#64748b;text-align:center}
    @keyframes tmass-spin{to{transform:rotate(360deg)}}
    @media (max-width: 991px){
        .tmass-actions{display:flex;justify-content:flex-start}
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="main-container">
    <div class="pd-20 card-box mb-30">
        <h4 class="tmass-title">Importación Masiva de Trámites</h4>
        <p class="tmass-subtitle">Cada fila se revisa por separado. Puedes guardar una por una y ver el motivo cuando una fila no sea válida.</p>

        <div class="tmass-card">
            <form id="tramitesMasivosForm" enctype="multipart/form-data">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-8">
                        <label>Archivo CSV</label>
                        <input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv" required>
                    </div>
                    <div class="form-group col-md-4 text-md-right">
                        <button type="button" class="btn btn-primary" id="btnPreviewMassive">
                            <i class="fas fa-search"></i> Leer archivo
                        </button>
                    </div>
                </div>
            </form>

            <div id="tmassLoader" class="tmass-loader" aria-live="polite">
                <span class="tmass-loader-spinner" aria-hidden="true"></span>
                <span>
                    Procesando datos...
                    <span class="tmass-loader-subtitle">Esto puede tardar unos segundos dependiendo del tamaño del archivo.</span>
                </span>
            </div>

            <div id="tmassSummary" class="tmass-summary" style="display:none;"></div>

            <div id="tmassTableWrap" class="tmass-table-wrap table-responsive">
                <table class="table table-bordered tmass-table" id="tmassTable"></table>
            </div>

            <div id="tmassEmpty" class="tmass-empty">Carga un CSV para ver las filas listas para revisión y guardado.</div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
<script>
    window.TRAMITES_MASIVOS_IMPORT = {
        previewUrl: '<?= site_url('/deskapp/tramites_masivos/preview') ?>',
        saveRowUrl: '<?= site_url('/deskapp/tramites_masivos/save_row') ?>',
        getEjecutivosUrlBase: '<?= site_url('/deskapp/tramites/getDependentData/ejecutivo') ?>',
        tipos: <?= json_encode($tipo_options ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        clientes: <?= json_encode($cliente_options ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        entidades: <?= json_encode($entidad_options ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    };
</script>
<script src="<?= $assets ?>/src/scripts/tramites_masivos_import.js?v=<?= time(); ?>"></script>
<?= $this->endSection() ?>