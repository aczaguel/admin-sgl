<!DOCTYPE html>
<html>
<head>
	<!-- Basic Page Info -->
	<meta charset="utf-8">
	<title>DeskApp - Bootstrap Admin Dashboard HTML Template</title>

	<!-- Site favicon -->
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url(); ?>/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url(); ?>/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url(); ?>/assets/vendors/images/favicon-16x16.png">

	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- Google Font -->
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/assets/vendors/styles/style.css">

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
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">
				<div class="page-header">
					<div class="row">
						<div class="col-md-12 col-sm-12">
							<div class="title">
								<h4>Bitacora</h4>
							</div>
							<nav aria-label="breadcrumb" role="navigation">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="index.html">Home</a></li>
									<li class="breadcrumb-item active" aria-current="page">Bitacora</li>
								</ol>
							</nav>
						</div>
					</div>
				</div>
				<div class="container pd-0">

					<?php
					foreach ($bitacoras as $item) {
						?>

					<div class="timeline mb-30">
						<ul>
							<li>
								<div class="timeline-date">
									<?php echo date('d M Y g:i:s A', strtotime($item['created_at']))?>
								</div>
								<div class="timeline-desc card-box">
									<div class="pd-20">
									<?php 
										if($item["origen"]=="tramite"){ ?>
											<div class="alert alert-success" role="alert">
											<?php echo ($item["tipo"]=="insert")? "NUEVO":"Actualización del" ?> folio: <?php echo $item["folio_tramite"] ?>  <?php echo  "por ". $item['firstname']. " " . $item['lastname'] ?>
											</div>
										<?php }else{ ?>
											<div class="alert alert-info" role="alert">
											<?php echo ($item["tipo"]=="insert")? "NUEVO":"Actualización del" ?> folio: <?php echo $item["folio_tramite"] ?> <?php echo  "por ". $item['firstname']. " " . $item['lastname'] ?>
											</div>
										<?php } ?>
									
										<?php if(isset($item['cambios']["documento_id"])){ ?>
											<div class="alert alert-info" role="alert"><bold>Documento: </bold>
															<?php echo $documentos[$item['cambios']["documento_id"]]; ?>
											</div>
													<?php } ?>
										

											<div class="container">
											<?php
													echo '<div class="card mb-3">';
													echo '<div class="list-group">';
													if(isset($item['cambios']['created_at'])){
														unset($item['cambios']['created_at']);
													}

													if(isset($item['cambios']['updated_at'])){
														unset($item['cambios']['updated_at']);
													}

													if(isset($item['cambios']['documento_id'])){
														unset($item['cambios']['documento_id']);
													}

													if(isset($item['cambios']['user_id'])){
														unset($item['cambios']['user_id']); 
													}

													if(isset($item['cambios']['folio_tramite'])){
														unset($item['cambios']['folio_tramite']);
													}

													if(isset($item['cambios']['tramite_id'])){
														unset($item['cambios']['tramite_id']);
													}

													if(isset($item['cambios'])){
														foreach ($item['cambios'] as $campo => $valores) {
															if(isset($valores['valor_nuevo'])){
																$valor_nuevo = $valores['valor_nuevo'];
															}else{
																$valor_nuevo = '';
															}

															if(isset($valores['valor_original'])){
																$valor_original = $valores['valor_original'];
															}else{
																$valor_original = '';
															}
															
															if($campo == "status"){
																if($valor_nuevo != ""){
																	$valor_original = ($valor_original==0)?'Inactivo':'Activo';
																	$valor_nuevo = ($valor_nuevo==0)?'Inactivo':'Activo';
																}
															}
															
															if($campo == "file"){
																$val_image = ($valor_nuevo!="")?$valor_nuevo:$valor_original;

																if(stristr($val_image, "pdf")){
																	$valor_nuevo = '<a href="/assets/uploads/'.$val_image.'" target="_blank" rel="noreferrer">'.$val_image.'</a>';
																}else{
																	if(file_exists('assets/uploads/'. $val_image)){
																		?>
																			<img class="card-img-top" src="<?php echo base_url() . '/assets/uploads/'. $val_image; ?>" alt="Card image cap">
																		<?php
																	}else{
																		echo "El archivo: ". 'assets/uploads/'. $val_image . " no existe";
																	}
																}
															}

															$tipo = $item['tipo'];
															$origen = $item['origen'];

															// Verificar el valor del campo tipo
															if ($tipo == 'insert') {
																	// Si el tipo es 'insert', determinar la acción basada en el valor de origen
																	if ($origen == 'tramite') {
																		echo '<p class="mb-1 font-14">';
																		echo str_replace('_', '', ucwords($campo, '_')). ' con el valor: ' . $valor_original;
																		echo '</p>';
																	} elseif ($origen == 'documentos') {
																			// Realizar acción específica para tipo 'insert' y origen 'documentos'
																			// ...
																	} elseif ($origen == 'final') {
																			echo '<div class="list-group-item list-group-item-action flex-column align-items-start">';
																			echo '<p class="mb-1 font-14">';
																			echo str_replace('_', '', ucwords($campo, '_')) . ' cambió de: ' . $valor_original;
																			echo ' a: ' . $valor_nuevo;
																			echo '</p>';
																			echo '</div>';
																	} elseif ($origen == 'evidencia') {
																		echo '<div class="list-group-item list-group-item-action flex-column align-items-start">';
																		echo '<p class="mb-1 font-14">';
																		echo str_replace('_', '', ucwords($campo, '_')) . ' con el valor: ' . $valor_original;
																		echo '</p>';
																		echo '</div>';
																			// Realizar acción específica para tipo 'insert' y origen 'evidencia'
																			// ...
																	} else {
																			// Acción por defecto o manejo de valores no esperados para el origen
																			// ...
																	}
															} elseif ($tipo == 'update') {
																	// Si el tipo es 'update', determinar la acción basada en el valor de origen
																	if ($origen == 'tramite') {
																		if($valor_nuevo != ""){
																			echo '<div class="list-group-item list-group-item-action flex-column align-items-start">';
																			echo '<p class="mb-1 font-14">';
																			echo str_replace('_', '', ucwords($campo, '_')) . ' cambió de: ' . $valor_original;
																			echo ' a: ' . $valor_nuevo;
																			echo '</p>';
																			echo '</div>';
																		}
																	} elseif ($origen == 'documentos') {
																			// Realizar acción específica para tipo 'update' y origen 'documentos'
																			// ...
																	} elseif ($origen == 'final') {
																			echo '<div class="list-group-item list-group-item-action flex-column align-items-start">';
																			echo '<p class="mb-1 font-14">';
																			echo str_replace('_', '', ucwords($campo, '_')) . ' cambió de: ' . $valor_original;
																			echo ' a: ' . $valor_nuevo;
																			echo '</p>';
																			echo '</div>';
																	} elseif ($origen == 'evidencia') {
																															// 			echo '<div class="list-group-item list-group-item-action flex-column align-items-start">';
																		
																		if($valor_nuevo != ""){
																			echo '<div class="list-group-item list-group-item-action flex-column align-items-start">';
																			echo '<p class="mb-1 font-14">';
																			echo str_replace('_', '', ucwords($campo, '_')) . ' cambió de: ' . $valor_original;
																			echo ' a: ' . $valor_nuevo;
																			echo '</p>';
																			echo '</div>';
																		}
																		
																	} else {
																			// Acción por defecto o manejo de valores no esperados para el origen
																			// ...
																	}
															} else {
																	// Acción por defecto o manejo de valores no esperados para el tipo
																	// ...
															}
														}
													}

												echo '</div>';
												echo '</div>';
											
											?>
										</div>
									</div>
								</div>
							</li>
						</ul>
					</div>
				
					<?php
					}
				?>							




				</div>
			</div>
			<!-- footer -->
			<?php echo view('deskapp/includes/_footer'); ?>
		</div>
	</div>
	<!-- js -->
	<script src="<?php echo base_url(); ?>/assets/vendors/scripts/core.js"></script>
	<script src="<?php echo base_url(); ?>/assets/vendors/scripts/script.min.js"></script>
	<script src="<?php echo base_url(); ?>/assets/vendors/scripts/process.js"></script>
	<script src="<?php echo base_url(); ?>/assets/vendors/scripts/layout-settings.js"></script>
</body>
</html>