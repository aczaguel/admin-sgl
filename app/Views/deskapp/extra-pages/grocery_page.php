<?= $this->extend('layout/main') ?>
<?= $this->section('additional_css') ?>
    <!-- CSS adicionales -->
	<?php $assets = base_url('/public/assets'); ?>
    <!-- <link rel="stylesheet" href="<?= $assets ?>/src/styles/forms_styles.css') ?>"> -->
    <!-- <link rel="stylesheet" href="<?= $assets ?>/src/styles/my_grocery.css') ?>"> -->
<?= $this->endSection() ?>
<?= $this->section('content') ?>
	<div class="main-container">
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">
				

					
								<?php
									if (!empty($output)) {
											echo $output;
									}
									?>
					
			</div>
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

<?= $this->endSection() ?>

	



