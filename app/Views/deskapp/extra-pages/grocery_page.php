<!DOCTYPE html>
<html>
<head>
	<!-- Basic Page Info -->
	<meta charset="utf-8">
	<title>SGT - Tramites</title>

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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/style.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/src/styles/forms_styles.css">

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-119386393-1"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

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
				<?php if(isset($insert_button_url)){ ?>
					<div class="gc-datagrid-tools-95">
						<div><a class="gc-simple-button-101 btn btn-outline-dark gc-bottom-space-102" href="<?php echo $insert_button_url?>" title="Añadir tramite" rel="noreferrer"><i class="fas fa-plus"></i>&nbsp;Añadir tramite</a>
						</div>
					</div>
				<?php } ?>
				<br>
				<!-- <div class="pd-20 bg-white border-radius-4 box-shadow mb-30"> -->
					<!-- Beginning of main content -->
					<!-- <div style='height:20px;'></div>  -->
								<?php
									if (!empty($output)) {
											echo $output;
									}
									?>
							<!-- End of main content -->
				<!-- </div> -->
			</div>
			<!-- footer -->
			<?php echo view('deskapp/includes/_footer'); ?>
		</div>
	</div>
	<!-- js -->

<?php
if (!empty($js_files)) {
    foreach($js_files as $file) { ?>
        <script src="<?php echo $file; ?>"></script>
    <?php }
}
?>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/script.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/process.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/layout-settings.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/scripts/my_scripts.js"></script>
</body>
</html>