<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?= esc(is_scalar($title ?? null) ? (string) $title : 'Historial') ?> - Admin SGL</title>
	<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url() ?>/public/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url() ?>/public/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= base_url() ?>/public/assets/vendors/images/favicon-16x16.png">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/src/plugins/datatables/css/responsive.bootstrap4.min.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/style.css">
</head>
<body>
	<?php 
		helper('datetime_es');
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
								<h4><i class="fa fa-history"></i> Historial de Correcciones</h4>
							</div>
							<nav aria-label="breadcrumb" role="navigation">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="<?= base_url('deskapp/dashboard') ?>">Home</a></li>
									<li class="breadcrumb-item"><a href="<?= base_url('correccion-tramites') ?>">Corrección</a></li>
									<li class="breadcrumb-item active">Historial</li>
								</ol>
							</nav>
						</div>
						<div class="col-md-6 text-right">
							<a href="<?= base_url('correccion-tramites') ?>" class="btn btn-outline-primary">
								<i class="fa fa-arrow-left"></i> Volver a Corrección
							</a>
						</div>
					</div>
				</div>

				<!-- Tabla de historial -->
				<div class="card-box mb-30">
					<div class="pd-20">
						<h4 class="text-blue h4">Registro de Cambios</h4>
						<p class="mb-30">Últimos 500 cambios realizados en trámites</p>
					</div>
					<div class="pb-20">
						<div class="table-responsive">
							<table class="table table-striped table-hover data-table">
								<thead>
									<tr>
										<th>Fecha y Hora</th>
										<th>ID</th>
										<th>Folio</th>
										<th>Usuario</th>
										<th>Cambios Realizados</th>
									</tr>
								</thead>
								<tbody>
									<?php if (!empty($logs)): ?>
										<?php foreach ($logs as $log): ?>
											<tr>
												<td><?= format_datetime_es($log['created_at'] ?? null, true, 'N/A') ?></td>
												<td>
													<span class="badge badge-secondary">#<?= $log['tramite_id'] ?></span>
												</td>
												<td>
													<a href="<?= base_url('deskapp/tramites/update/' . $log['tramite_id']) ?>" target="_blank">
														<strong><?= esc($log['folio']) ?></strong>
													</a>
												</td>
												<td>
													<span class="badge badge-info">
														<?= esc($log['username']) ?>
													</span>
												</td>
												<td><?= esc($log['cambios']) ?></td>
											</tr>
										<?php endforeach; ?>
									<?php else: ?>
										<tr>
											<td colspan="5" class="text-center text-muted">No hay registros de cambios</td>
										</tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
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
	<script src="<?= base_url() ?>/public/assets/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
	<script src="<?= base_url() ?>/public/assets/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
	<script src="<?= base_url() ?>/public/assets/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
	<script src="<?= base_url() ?>/public/assets/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
	
	<script>
	$(document).ready(function() {
		$('.data-table').DataTable({
			scrollCollapse: true,
			autoWidth: false,
			responsive: true,
			pageLength: 25,
			order: [[0, 'desc']],
			language: {
				url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
			}
		});
	});
	</script>
</body>
</html>
