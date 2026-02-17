<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>
<?php $assets = base_url('/public/assets'); ?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
	.flotilla-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px}
	.flotilla-title{font-size:1rem;font-weight:800;color:#1f2937;margin:0 0 10px 0}
	.flotilla-summary{display:flex;flex-wrap:wrap;gap:10px;margin-top:10px}
	.flotilla-badge{border-radius:999px;padding:6px 12px;font-size:.78rem;font-weight:800;background:#f1f5f9;color:#0f172a;border:1px solid #e2e8f0}
	.flotilla-badge.is-success{background:#ecfdf5;color:#166534;border-color:#86efac}
	.flotilla-badge.is-danger{background:#fef2f2;color:#991b1b;border-color:#fecaca}
	.flotilla-table{width:100%;font-size:.82rem}
	.flotilla-table th{background:#f8fafc;font-weight:800}
	.flotilla-table td,.flotilla-table th{padding:6px 8px;border:1px solid #e5e7eb}
	.flotilla-errors{max-height:220px;overflow:auto}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="main-container">
	<div class="pd-20 card-box mb-30">
		<h4 class="mb-2">Importar Flotilla (Paso 1)</h4>
		<p class="text-muted mb-3">Carga un CSV y selecciona el cliente para crear los tramites basicos.</p>

		<div class="flotilla-card">
			<form id="flotillaForm" enctype="multipart/form-data">
				<div class="form-row">
					<div class="form-group col-md-5">
						<label>Archivo CSV</label>
						<input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv" required>
					</div>
					<div class="form-group col-md-5">
						<label>Cliente (cli_directo)</label>
						<select class="form-control" name="cli_directo_id" id="cli_directo_id" required>
							<option value="">Seleccione...</option>
							<?php if (!empty($cli_directo_options) && is_array($cli_directo_options)): ?>
								<?php foreach ($cli_directo_options as $id => $label): ?>
									<option value="<?= (int) $id ?>"><?= esc($label) ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
					<div class="form-group col-md-2">
						<label>Nombre Flotilla</label>
						<input type="text" class="form-control" name="flotilla_nombre" id="flotilla_nombre" placeholder="Flotilla 1">
					</div>
				</div>
				<div class="d-flex" style="gap:10px;">
					<button type="button" class="btn btn-primary" id="btnPreview">Previsualizar</button>
					<button type="button" class="btn btn-success" id="btnImport" disabled>Importar</button>
				</div>
			</form>

			<div id="flotillaSummary" class="flotilla-summary" style="display:none;"></div>

			<div id="flotillaErrors" class="mt-3" style="display:none;">
				<h6>Errores detectados</h6>
				<div class="table-responsive">
					<table class="flotilla-table" id="errorTable"></table>
				</div>
			</div>

			<div id="flotillaPreview" class="mt-3" style="display:none;">
				<h6>Vista previa (max 50 filas)</h6>
				<div class="table-responsive">
					<table class="flotilla-table" id="previewTable"></table>
				</div>
			</div>
		</div>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
<script>
	window.FLOTILLA_IMPORT = {
		previewUrl: '<?= site_url('/deskapp/flotillas/preview') ?>',
			importUrl: '<?= site_url('/deskapp/flotillas/import') ?>',
			tramiteBaseUrl: '<?= site_url('/deskapp/tramitesn/update/') ?>'
	};
</script>
<script src="<?= $assets ?>/src/scripts/flotillas_import.js?v=<?= time(); ?>"></script>
<?= $this->endSection() ?>
