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

	<div class="main-container">
		<div class="pd-ltr-20">
			<div class="card-box pd-20 height-100-p mb-30">
				<div class="row align-items-center">
					<div class="col-md-4">
						<img src="<?= base_url('public/assets/vendors/images/banner-img.png') ?>" alt="">
					</div>
					<div class="col-md-8">
						<h4 class="font-20 weight-500 mb-10 text-capitalize">
							<i class="fas fa-search text-primary"></i> Buscar Trámite
						</h4>
						<p class="font-18 max-width-600">
							Ingresa el ID o el folio para abrir el trámite en el flujo nuevo.
						</p>
					</div>
				</div>
			</div>

			<?php $flashError = session()->getFlashdata('error'); ?>
			<?php if ($flashError): ?>
				<div class="sgl-liston" id="tramiteSearchFlash"><i class="fas fa-exclamation-triangle"></i> <?= esc($flashError) ?></div>
			<?php else: ?>
				<div class="sgl-liston" id="tramiteSearchFlash" style="display:none;"></div>
			<?php endif; ?>

			<div class="row">
				<div class="col-xl-6 col-lg-8 col-md-10 mx-auto">
					<div class="card-box mb-30">
						<div class="pb-20 pt-20 pl-30 pr-30">
							<div class="wizard-content">
								<h5 class="text-center mb-30">Buscar Trámite</h5>

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
												<h6 class="mb-0 mt-20 mb-20">O</h6>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Buscar por Folio:</label>
													<input type="text" name="folio" id="folio" class="form-control text-uppercase" placeholder="Ej: ALD820807" style="text-transform: uppercase;">
													<small class="form-text text-muted">El folio se convertirá a mayúsculas automáticamente.</small>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12">
												<div class="form-group text-center mt-30">
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

	<script src="<?= base_url() ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/script.min.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/process.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/layout-settings.js"></script>
	<script>
		$(document).ready(function() {
			$('#folio').on('input', function() {
				this.value = this.value.toUpperCase();
			});
		});
	</script>
</body>
</html>
