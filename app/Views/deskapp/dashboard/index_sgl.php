<?= $this->extend('layout/main') ?>
<!-- DataTables CSS -->
<?php $assets = base_url('/public/assets'); ?>
<link rel="stylesheet" href="<?= $assets ?>/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= $assets ?>/src/plugins/datatables/css/responsive.bootstrap4.min.css">

<?= $this->section('content') ?>
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
							Bienvenido al sistema de administración SGL, donde podrás dar de alta, modificar y dar seguimiento a trámites. ¡Nos alegra tenerte de vuelta!
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
	
	<script>
		var graphData = <?= json_encode($graph ?? []); ?>;
		var perMonth = <?= json_encode($perMonth ?? []); ?>;
	</script>
	<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="<?= $assets ?>/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
	<script src="<?= $assets ?>/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script> -->
	
<?= $this->endSection() ?>

<script src="<?= $assets ?>/src/plugins/apexcharts/apexcharts.min.js"></script>
<script src="<?= $assets ?>/vendors/scripts/dashboard.js"></script>