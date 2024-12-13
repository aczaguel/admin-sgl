<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title><?= $title ?? 'SGL - Dashboard' ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Favicon -->
        <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('/public/assets/vendors/images/apple-touch-icon.png') ?>">
        <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('/public/assets/vendors/images/favicon-32x32.png') ?>">
        <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('/public/assets/vendors/images/favicon-16x16.png') ?> ">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- CSS -->
        <?php $assets = base_url('/public/assets'); ?>
        <link rel="stylesheet" href="<?= $assets ?>/src/styles/forms_styles.css">
        <link rel="stylesheet" href="<?= $assets ?>/src/styles/my_grocery.css">
        <link rel="stylesheet" href="<?= $assets ?>/vendors/styles/core.css">
        <link rel="stylesheet" href="<?= $assets ?>/vendors/styles/icon-font.min.css">
        <!-- <link rel="stylesheet" href="<?= $assets ?>/src/plugins/datatables/css/dataTables.4.min.css"> -->
        <link rel="stylesheet" href="<?= $assets ?>/vendors/styles/style.css">

        
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-119386393-1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'UA-119386393-1');
        </script>

        <?= $this->include('deskapp/includes/_header') ?>
    </head>
    <body>
        	<!-- Scripts -->
            <script src="https://unpkg.com/jquery@3.6.4/dist/jquery.min.js"></script>
            <script src="<?= $assets ?>/vendors/scripts/core.js"></script>
            <script src="<?= $assets ?>/vendors/scripts/script.min.js"></script>
            <script src="<?= $assets ?>/vendors/scripts/process.js"></script>
            <script src="<?= $assets ?>/vendors/scripts/layout-settings.js"></script>
            
            <script src="<?= $assets ?>/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
            <script src="<?= $assets ?>/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
            <script src="<?= $assets ?>/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
            <script src="<?= $assets ?>/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>


        <?= $this->include('deskapp/includes/_sidebar') ?>
        <!-- Sección para estilos adicionales -->
        <?= $this->renderSection('additional_css') ?>
        <main id="main-content">
            <?= $this->renderSection('content') ?>
        </main>
        <!-- Sección para estilos adicionales -->
        <?= $this->renderSection('additional_js') ?>
        <?= $this->include('deskapp/includes/_footer') ?>


    </body>
</html>
