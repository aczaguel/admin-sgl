<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Buscar Bitacora</title>
	<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url() ?>/public/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url() ?>/public/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= base_url() ?>/public/assets/vendors/images/favicon-16x16.png">

	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/style.css">
	<style>
		.sgl-liston{
			display:flex;
			align-items:center;
			gap:10px;
			padding:10px 12px;
			border-radius:10px;
			font-weight:600;
			margin:10px 0 20px;
			border:1px solid #fecaca;
			background:#fff1f2;
			color:#991b1b;
		}
		.sgl-liston i{font-size:14px;}
	</style>
</head>
<body class="sidebar-shrink">
	<?= view('deskapp/includes/_header') ?>
	<?= view('deskapp/includes/_sidebar') ?>

	<?php helper('datetime_es'); ?>

	<div class="main-container">
		<div class="pd-ltr-20">
			<div class="card-box pd-20 height-100-p mb-30">
				<div class="row align-items-center">
					<div class="col-md-4">
						<img src="<?= base_url('public/assets/vendors/images/banner-img.png') ?>" alt="">
					</div>
					<div class="col-md-8">
						<h4 class="font-20 weight-500 mb-10 text-capitalize">
							<i class="fas fa-history text-primary"></i> Buscar Bitacora de Tramite
						</h4>
						<p class="font-18 max-width-600">
							Selecciona un tramite para ver el historial completo de bitacora
						</p>
					</div>
				</div>
			</div>

			<?php $flashError = session()->getFlashdata('error'); ?>
			<?php if ($flashError): ?>
				<div class="sgl-liston" id="bitacoraFlash"><i class="fas fa-exclamation-triangle"></i> <?= esc($flashError) ?></div>
			<?php else: ?>
				<div class="sgl-liston" id="bitacoraFlash" style="display:none;"></div>
			<?php endif; ?>

			<div class="row">
				<div class="col-xl-6 col-lg-8 col-md-10 mx-auto">
					<div class="card-box mb-30">
						<div class="pb-20 pt-20 pl-30 pr-30">
							<div class="wizard-content">
								<h5 class="text-center mb-30">Buscar Tramite</h5>

								<form id="searchBitacoraForm">
									<section>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Buscar por ID del Tramite:</label>
													<input type="number"
														name="tramite_id"
														id="tramite_id"
														class="form-control"
														placeholder="Ej: 108"
														min="1">
													<small class="form-text text-muted">
														Puedes encontrar el ID en la URL al editar un tramite
													</small>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12 text-center">
												<h6 class="mb-0 mt-20 mb-20">O</h6>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Buscar por Folio:</label>
													<input type="text"
														name="folio"
														id="folio"
														class="form-control text-uppercase"
														placeholder="Ej: 064474"
														style="text-transform: uppercase;">
													<small class="form-text text-muted">
														El folio se mostrara en mayusculas automaticamente
													</small>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12">
												<div class="form-group text-center mt-30">
													<button type="submit" class="btn btn-primary btn-lg">
														<i class="fas fa-search"></i> Buscar Bitacora
													</button>
													<a href="<?= base_url('deskapp/dashboard') ?>" class="btn btn-secondary btn-lg ml-2">
														<i class="fas fa-times"></i> Cancelar
													</a>
												</div>
											</div>
										</div>
									</section>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card-box mb-30">
				<div class="pd-20">
					<h5 class="text-blue h5">Ultimos tramites con bitacora</h5>
					<p class="mb-0">Selecciona uno para abrir su timeline</p>
				</div>
				<div class="pb-20 px-20">
					<div class="table-responsive">
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Tramite ID</th>
									<th>Folio</th>
									<th>Ultimo cambio</th>
									<th>Total movimientos</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php if (!empty($bitacora_list)): ?>
									<?php foreach ($bitacora_list as $item): ?>
										<tr>
											<td><?= esc($item['tramite_id'] ?? 'N/A') ?></td>
											<td><?= esc($item['folio_tramite'] ?? 'N/A') ?></td>
											<td>
												<?= esc(format_datetime_es($item['last_change'] ?? null, true, 'N/A')) ?>
											</td>
											<td><?= esc($item['total_changes'] ?? 0) ?></td>
											<td>
												<?php if (!empty($item['tramite_id'])): ?>
													<a class="btn btn-sm btn-outline-primary" href="<?= site_url('/bitacora/timeline') . '?tramite_id=' . urlencode($item['tramite_id']) ?>">
														Ver bitacora
													</a>
												<?php elseif (!empty($item['folio_tramite'])): ?>
													<a class="btn btn-sm btn-outline-primary" href="<?= site_url('/bitacora/timeline') . '?folio=' . urlencode($item['folio_tramite']) ?>">
														Ver bitacora
													</a>
												<?php else: ?>
													<span class="text-muted">N/A</span>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php else: ?>
									<tr>
										<td colspan="5" class="text-center">No hay registros</td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

		</div>
	</div>

	<script src="<?= base_url() ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/script.min.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/process.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/layout-settings.js"></script>

	<script>
	$(document).ready(function() {
		$('#folio').on('input', function() {
			this.value = this.value.toUpperCase();
		});

		$('#searchBitacoraForm').on('submit', function(e) {
			e.preventDefault();
			const tramiteId = $('#tramite_id').val();
			const folio = $('#folio').val().trim();

			if (!tramiteId && !folio) {
				showBitacoraError('Por favor ingresa el ID del tramite o el folio');
				return;
			}

			if (tramiteId) {
				window.location.href = '<?= site_url('/bitacora/timeline') ?>?tramite_id=' + encodeURIComponent(tramiteId);
				return;
			}

			window.location.href = '<?= site_url('/bitacora/timeline') ?>?folio=' + encodeURIComponent(folio);
		});

	function showBitacoraError(message) {
		const box = document.getElementById('bitacoraFlash');
		if (!box) {
			return;
		}
		box.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + message;
		box.style.display = 'flex';
		box.scrollIntoView({ behavior: 'smooth', block: 'start' });
	}
	});
	</script>
</body>
</html>
