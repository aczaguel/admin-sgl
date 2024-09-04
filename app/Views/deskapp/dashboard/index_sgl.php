<!DOCTYPE html>
<html>
<head>
	<!-- Basic Page Info -->
	<meta charset="utf-8">
	<title>DeskApp - Bootstrap Admin Dashboard HTML Template</title>

	<!-- Site favicon -->
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url(); ?>/public/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url(); ?>/public/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url(); ?>/public/assets/vendors/images/favicon-16x16.png">

	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- Google Font -->
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/css/responsive.bootstrap4.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/style.css">

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-119386393-1"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'UA-119386393-1');
	</script>
</head>
<body>
	<!-- echo header,rightsidebar,leftsidebar and loader -->
	<?php 
		echo view('deskapp/includes/_header');
		echo view('deskapp/includes/_sidebar');
	?>
	
	<div class="main-container">
		<div class="pd-ltr-20">
			<div class="card-box pd-20 height-100-p mb-30">
				<div class="row align-items-center">
					<div class="col-md-4">
						<img src="<?php echo base_url(); ?>/public/assets/vendors/images/banner-img.png" alt="">
					</div>
					<div class="col-md-8">
						<h4 class="font-20 weight-500 mb-10 text-capitalize">
							¡Bienvenido de nuevo, <span class="weight-600 font-30 text-blue"><?= esc($session->get('firstname')); ?>!</span>
						</h4>
						<p class="font-18 max-width-600">
							Bienvenido al sistema de administración SGL, donde podrás dar de alta, modificar y dar seguimiento a trámites de SGL. ¡Nos alegra tenerte de vuelta!
						</p>
					</div>

				</div>
			</div>
			<div class="row">
				<div class="col-xl-12 mb-30">
					<h2>Locales</h2>
				</div>
				
				<div class="col-xl-3 mb-30">
					<div class="card-box height-100-p widget-style1">
						<div class="d-flex flex-wrap align-items-center">
							<div class="progress-data">
								<div id="chart"></div>
							</div>
							<div class="widget-data">
								<div class="h4 mb-0">Recientes</div>
								<div class="weight-600 font-14"><?php echo isset($graph['local']['verde']) ? $graph['local']['verde'] : 0; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 mb-30">
					<div class="card-box height-100-p widget-style1">
						<div class="d-flex flex-wrap align-items-center">
							<div class="progress-data">
								<div id="chart2"></div>
							</div>
							<div class="widget-data">
								<div class="h4 mb-0">> 5 Días</div>
								<div class="weight-600 font-14"><?php echo isset($graph['local']['amarillo']) ? $graph['local']['amarillo'] : 0; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 mb-30">
					<div class="card-box height-100-p widget-style1">
						<div class="d-flex flex-wrap align-items-center">
							<div class="progress-data">
								<div id="chart3"></div>
							</div>
							<div class="widget-data">
								<div class="h4 mb-0">> 8 Días</div>
								<div class="weight-600 font-14"><?php echo isset($graph['local']['rojo']) ? $graph['local']['rojo'] : 0; ?></div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 mb-30">
					<div class="card-box height-100-p widget-style1">
						<div class="d-flex flex-wrap align-items-center">
							<div class="progress-data">
								<div id="chart4"></div>
							</div>
							<div class="widget-data">
								<div class="h4 mb-0">> 12 Días</div>
								<div class="weight-600 font-14"><?php echo isset($graph['local']['violeta']) ? $graph['local']['violeta'] : 0; ?></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xl-12 mb-30">
					<h2>Foraneos</h2>
				</div>
				<div class="col-xl-3 mb-30">
					<div class="card-box height-100-p widget-style1">
						<div class="d-flex flex-wrap align-items-center">
							<div class="progress-data">
								<div id="chartf1"></div>
							</div>
							<div class="widget-data">
								<div class="h4 mb-0">Recientes</div>
								<div class="weight-600 font-14"><?php echo isset($graph['foraneo']['verde']) ? $graph['foraneo']['verde'] : 0; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 mb-30">
					<div class="card-box height-100-p widget-style1">
						<div class="d-flex flex-wrap align-items-center">
							<div class="progress-data">
								<div id="chartf2"></div>
							</div>
							<div class="widget-data">
								<div class="h4 mb-0">> 5 Días</div>
								<div class="weight-600 font-14"><?php echo isset($graph['foraneo']['amarillo']) ? $graph['foraneo']['amarillo'] : 0; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 mb-30">
					<div class="card-box height-100-p widget-style1">
						<div class="d-flex flex-wrap align-items-center">
							<div class="progress-data">
								<div id="chartf3"></div>
							</div>
							<div class="widget-data">
								<div class="h4 mb-0">> 8 Días</div>
								<div class="weight-600 font-14"><?php echo isset($graph['foraneo']['rojo']) ? $graph['foraneo']['rojo'] : 0; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-3 mb-30">
					<div class="card-box height-100-p widget-style1">
						<div class="d-flex flex-wrap align-items-center">
							<div class="progress-data">
								<div id="chartf4"></div>
							</div>
							<div class="widget-data">
								<div class="h4 mb-0">> 12 Días</div>
								<div class="weight-600 font-14"><?php echo isset($graph['foraneo']['violeta']) ? $graph['foraneo']['violeta'] : 0; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			
			<div class="row">
				<div class="col-xl-8 mb-30">
					<div class="card-box height-100-p pd-20">
						<h2 class="h4 mb-20">Actividad</h2>
						<div id="chart5"></div>
					</div>
				</div>
				<div class="col-xl-4 mb-30">
					<div class="card-box height-100-p pd-20">
						<h2 class="h4 mb-20">Objetivos</h2>
						<div id="chart6"></div>
					</div>
				</div>
			</div>
			
			<!-- footer -->
			<?php echo view('deskapp/includes/_footer'); ?>
		</div>
	</div>
	<!-- js -->
	<script>
		// Pasar los datos de PHP a JavaScript usando JSON
		var graphData = <?php echo json_encode($graph); ?>;
		var perMonth = <?php echo json_encode($perMonth); ?>;
		 // Verifica que los datos se pasen correctamente
	</script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/script.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/process.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/layout-settings.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/plugins/apexcharts/apexcharts.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/dashboard.js"></script>
</body>
</html>