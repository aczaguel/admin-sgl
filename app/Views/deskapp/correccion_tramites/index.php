<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?= $title ?> - Admin SGL</title>
	<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url() ?>/public/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url() ?>/public/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= base_url() ?>/public/assets/vendors/images/favicon-16x16.png">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/style.css">
	
	<?php foreach ($css_files as $file): ?>
		<link rel="stylesheet" href="<?= $file ?>">
	<?php endforeach; ?>

	<style>
	.alert-warning-correccion {
		background: #fff3cd;
		border: 2px solid #ffc107;
		border-radius: 8px;
		padding: 15px 20px;
		margin-bottom: 20px;
	}
	.alert-warning-correccion i {
		font-size: 24px;
		color: #ffc107;
		margin-right: 10px;
		vertical-align: middle;
	}
	.alert-warning-correccion strong {
		color: #856404;
	}
	</style>
</head>
<body>
	<?php 
		echo view('deskapp/includes/_header');
		echo view('deskapp/includes/_sidebar');
	?>

	<div class="main-container">
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">
				<!-- Header -->
				<div class="page-header">
					<div class="row">
						<div class="col-md-6">
							<div class="title">
								<h4><i class="fa fa-edit"></i> Corrección de Trámites</h4>
							</div>
							<nav aria-label="breadcrumb" role="navigation">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="<?= base_url('deskapp/dashboard') ?>">Home</a></li>
									<li class="breadcrumb-item active">Corrección de Trámites</li>
								</ol>
							</nav>
						</div>
						<div class="col-md-6 text-right">
							<a href="<?= base_url('correccion-tramites/historial') ?>" class="btn btn-outline-info">
								<i class="fa fa-history"></i> Ver Historial de Cambios
							</a>
						</div>
					</div>
				</div>

				<!-- Alerta de advertencia -->
				<div class="alert-warning-correccion">
					<i class="icon-copy fa fa-exclamation-triangle" aria-hidden="true"></i>
					<strong>MÓDULO DE CORRECCIÓN:</strong> Este módulo permite modificar únicamente el <strong>Tipo de Trámite</strong> y el <strong>Estatus</strong>. 
					Todos los cambios quedan registrados en el historial con fecha, hora y usuario que realizó la modificación.
				</div>

				<!-- Contenido GroceryCRUD -->
				<div class="card-box mb-30">
					<div class="pd-20">
						<?= $output ?>
					</div>
				</div>
			</div>
			<?php echo view('deskapp/includes/_footer'); ?>
		</div>
	</div>

	<script src="<?= base_url() ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/script.min.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/process.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/layout-settings.js"></script>
	
	<?php foreach ($js_files as $file): ?>
		<script src="<?= $file ?>"></script>
	<?php endforeach; ?>
</body>
</html>
