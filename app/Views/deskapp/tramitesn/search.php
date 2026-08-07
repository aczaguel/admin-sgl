<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Buscar Trámite</title>
	<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url() ?>/public/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url() ?>/public/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= base_url() ?>/public/assets/vendors/images/favicon-16x16.png">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/style.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/src/styles/sgl_blue_template.css?v=20260610-1">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="sidebar-shrink sgl-theme-2026">
	<?= view('deskapp/includes/_header') ?>
	<?= view('deskapp/includes/_sidebar') ?>

	<div class="main-container">
		<div class="pd-ltr-20">
			<div class="card-box pd-20 height-100-p mb-30 sgl-search-hero">
				<div class="row align-items-center">
					<div class="col-md-4">
						<img src="<?= base_url('public/assets/vendors/images/banner-img.png') ?>" alt="">
					</div>
					<div class="col-md-8">
						<h4 class="font-20 weight-500 mb-10 text-capitalize">
							<i class="fas fa-search text-primary"></i> Buscar Trámite
						</h4>
						<p class="font-18 max-width-600">
							Ingresa el ID, el folio o el contrato para abrir el trámite en el flujo nuevo.
						</p>
					</div>
				</div>
			</div>

			<?php $flashError = session()->getFlashdata('error'); ?>
			<?php if ($flashError): ?>
				<div class="sgl-liston" id="tramiteSearchFlash"><i class="fas fa-exclamation-triangle"></i> <?= esc($flashError) ?></div>
			<?php else: ?>
				<div class="sgl-liston is-hidden" id="tramiteSearchFlash"></div>
			<?php endif; ?>

			<div class="row">
				<div class="col-xl-6 col-lg-8 col-md-10 mx-auto">
					<div class="card-box mb-30 sgl-search-panel">
						<div class="pb-20 pt-20 pl-30 pr-30">
							<div class="wizard-content">
								<h5 class="text-center mb-30 sgl-search-title">Buscar Trámite</h5>

								<form method="post" action="<?= site_url('/deskapp/tramitesn/search') ?>">
									<?= csrf_field() ?>
									<section>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Buscar por ID del Trámite:</label>
													<input type="number" name="tramite_id" id="tramite_id" class="form-control" placeholder="Ej: 7669" min="1">
													<small class="form-text text-muted">Si tienes el ID exacto, es la forma más rápida.</small>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12 text-center">
												<h6 class="mb-0 mt-20 mb-20 sgl-search-separator">O</h6>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Buscar por Folio:</label>
													<input type="text" name="folio" id="folio" class="form-control text-uppercase sgl-input-uppercase" placeholder="Ej: ALD820807">
													<small class="form-text text-muted">El folio se convertirá a mayúsculas automáticamente.</small>
												</div>
											</div>
										</div>

									<div class="row">
										<div class="col-md-12 text-center">
											<h6 class="mb-0 mt-20 mb-20 sgl-search-separator">O</h6>
										</div>
									</div>

									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label>Buscar por Contrato:</label>
												<input type="text" name="contrato" id="contrato" class="form-control text-uppercase sgl-input-uppercase" placeholder="Ej: ABC12345">
												<small class="form-text text-muted">El contrato se convertirá a mayúsculas automáticamente.</small>
											</div>
										</div>
									</div>

										<div class="row">
											<div class="col-md-12">
												<div class="form-group text-center mt-30 sgl-search-actions">
													<button type="submit" class="btn btn-primary btn-lg">
														<i class="fas fa-search"></i> Buscar
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
		</div>
	</div>

	<?php if (!empty($contrato_results ?? [])): ?>
	<div class="row mt-20">
		<div class="col-xl-10 col-lg-12 mx-auto">
			<div class="card-box mb-30">
				<div class="pb-10 pt-10 pl-15 pr-15">
					<h5 class="mb-20">
						<i class="fas fa-list text-primary"></i>
						<?= count($contrato_results) ?> trámites encontrados para el contrato <strong><?= esc($contrato_query ?? '') ?></strong>
					</h5>
					<div class="table-responsive">
						<table class="table table-striped table-hover" style="font-size: 11px;">
							<thead>
								<tr>
									<th>#</th>
									<th>Folio</th>
									<th>Contrato</th>
									<th>Tipo</th>
									<th>Ejecutivo</th>
									<th>Unidad</th>
									<th>Serie</th>
									<th>Placas</th>
									<th>Entidad</th>
									<th>Estatus</th>
									<th>Fecha</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($contrato_results as $tr): ?>
								<tr>
									<td><?= (int) $tr['id'] ?></td>
									<td><?= esc($tr['folio'] ?? '') ?></td>
									<td><?= esc($tr['contrato'] ?? '') ?></td>
									<td><?= esc($tr['tipo_tramite'] ?? '—') ?></td>
									<td><?= esc($tr['ejecutivo_nombre'] ?? '—') ?></td>
									<td><?= esc($tr['unidad'] ?? '') ?></td>
									<td><?= esc($tr['serie'] ?? '') ?></td>
									<td><?= esc($tr['placas'] ?? '') ?></td>
									<td><?= esc($tr['entidad_nombre'] ?? '—') ?></td>
									<td><?= esc($tr['status_nombre'] ?? 'Sin estatus') ?></td>
									<td><?= esc(substr((string) ($tr['created_at'] ?? ''), 0, 10)) ?></td>
									<td>
										<a href="<?= site_url('deskapp/tramitesn/unified-layout?tramite_id=' . (int) $tr['id'] . '&from=search') ?>"
										   class="btn btn-primary btn-sm">
											<i class="fas fa-eye"></i> Abrir
										</a>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<script src="<?= base_url() ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/script.min.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/process.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/layout-settings.js"></script>
	<script>
		$(document).ready(function() {
			$('#folio, #contrato').on('input', function() {
				this.value = this.value.toUpperCase();
			});
		});
	</script>
</body>
</html>
