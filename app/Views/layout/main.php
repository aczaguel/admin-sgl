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
        <link rel="stylesheet" href="<?= $assets ?>/src/plugins/select2/dist/css/select2.min.css">
        <!-- <link rel="stylesheet" href="<?= $assets ?>/src/plugins/datatables/css/dataTables.4.min.css"> -->
        <link rel="stylesheet" href="<?= $assets ?>/vendors/styles/style.css">
        <style>
            .sgl-preloader{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(15,23,42,.92) 0%,rgba(30,41,59,.92) 100%);z-index:9999;color:#fff;opacity:1;visibility:visible;transition:opacity .3s ease,visibility .3s ease}
            .sgl-preloader.is-hidden{opacity:0;visibility:hidden}
            .sgl-preloader-card{display:flex;flex-direction:column;align-items:center;gap:10px;padding:18px 20px;border-radius:14px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);box-shadow:0 12px 30px rgba(0,0,0,.25)}
            .sgl-preloader-spinner{width:44px;height:44px;border-radius:999px;border:4px solid rgba(255,255,255,.25);border-top-color:#34d399;animation:sglSpin .9s linear infinite}
            .sgl-preloader-text{font-size:.85rem;font-weight:700;letter-spacing:.03em;text-transform:uppercase}
            @keyframes sglSpin{to{transform:rotate(360deg)}}

            .sgl-perm-audit{color:#c00;font-size:10px;line-height:1.1;font-weight:700;margin-left:6px;white-space:nowrap}

            .select2-container{
                width:100% !important;
            }
            .select2-container .select2-selection--single,
            .select2-container .select2-selection--multiple,
            .select2-container .select2-selection--single .select2-selection__rendered,
            .select2-container .select2-search__field,
            .select2-dropdown{
                font-family:'Inter', sans-serif;
                color:#131e22;
                font-weight:400;
                font-size:12px;
                letter-spacing:.035em;
            }
            select.form-control-sm + .select2-container .select2-selection--single{
                height:38px;
            }
            select.form-control-sm + .select2-container .select2-selection--single .select2-selection__rendered{
                line-height:36px;
                font-size:12px;
            }
            select.form-control-sm + .select2-container .select2-selection--single .select2-selection__arrow{
                height:36px;
            }
            select.form-control-sm + .select2-container .select2-selection--multiple{
                min-height:38px;
            }
            .select2-container--default .select2-selection--single .select2-selection__placeholder,
            .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder,
            .select2-search--dropdown .select2-search__field::placeholder{
                color:#131e22;
                opacity:.65;
            }
            .select2-dropdown{
                border-color:#ced4da;
            }
            .select2-search--dropdown .select2-search__field{
                border-color:#ced4da;
                border-radius:.25rem;
                min-height:34px;
            }
        </style>

        
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
        <div class="sgl-preloader" id="sglPreloader" aria-live="polite" aria-busy="true">
            <div class="sgl-preloader-card">
                <div class="sgl-preloader-spinner" aria-hidden="true"></div>
                <div class="sgl-preloader-text">Cargando...</div>
            </div>
        </div>
        <script>
            (function () {
                function hideGlobalPreloader() {
                    var loader = document.getElementById('sglPreloader');
                    if (!loader) return;
                    loader.classList.add('is-hidden');
                    setTimeout(function () {
                        if (loader && loader.parentNode) {
                            loader.parentNode.removeChild(loader);
                        }
                    }, 350);
                }
                window.hideGlobalPreloader = hideGlobalPreloader;
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', hideGlobalPreloader);
                } else {
                    hideGlobalPreloader();
                }
            })();
        </script>
        	<!-- Scripts -->
            <script src="https://unpkg.com/jquery@3.6.4/dist/jquery.min.js"></script>
            <script src="<?= $assets ?>/vendors/scripts/core.js"></script>
            <script src="<?= $assets ?>/vendors/scripts/script.min.js"></script>
            <script src="<?= $assets ?>/vendors/scripts/process.js"></script>
            <script src="<?= $assets ?>/vendors/scripts/layout-settings.js"></script>
            <script src="<?= $assets ?>/src/plugins/select2/dist/js/select2.full.min.js"></script>
            <script src="<?= $assets ?>/src/plugins/select2/dist/js/i18n/es.js"></script>
            
            <script src="<?= $assets ?>/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
            <script src="<?= $assets ?>/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
            <script src="<?= $assets ?>/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
            <script src="<?= $assets ?>/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
            <script>
                (function (window, document, $) {
                    if (!$ || !$.fn || !$.fn.select2) {
                        return;
                    }

                    function shouldEnhanceSelect(element) {
                        var $element = $(element);

                        if ($element.data('sglSearchReady')) {
                            return false;
                        }

                        if (
                            $element.hasClass('select2-hidden-accessible') ||
                            $element.hasClass('select2') ||
                            $element.hasClass('custom-select2') ||
                            $element.hasClass('selectpicker') ||
                            $element.is('[data-role="tagsinput"]') ||
                            $element.is('[data-sgl-no-search]')
                        ) {
                            return false;
                        }

                        if (
                            $element.closest('.dataTables_wrapper').length ||
                            $element.closest('.bootstrap-select').length
                        ) {
                            return false;
                        }

                        return $element.is('select.form-control, select.custom-select, select.form-select');
                    }

                    function buildOptions($element) {
                        var options = {
                            width: '100%',
                            language: 'es',
                            minimumResultsForSearch: 0
                        };

                        var placeholder = $element.attr('placeholder') || $element.data('placeholder') || '';
                        if (placeholder) {
                            options.placeholder = placeholder;
                            options.allowClear = !$element.prop('multiple') && !$element.prop('required');
                        }

                        var $modalParent = $element.closest('.modal, .swal2-container, .swal2-popup');
                        if ($modalParent.length) {
                            options.dropdownParent = $modalParent.first();
                        }

                        return options;
                    }

                    function enhanceSelects(context) {
                        var $scope = context ? $(context) : $(document);
                        var $selects = $scope.is('select') ? $scope : $scope.find('select');

                        $selects.each(function () {
                            var $select = $(this);
                            if (!shouldEnhanceSelect(this)) {
                                return;
                            }

                            $select.select2(buildOptions($select));
                            $select.data('sglSearchReady', true);
                        });
                    }

                    function refreshSelect(element) {
                        var $select = $(element);
                        if (!$select.length) {
                            return;
                        }

                        if (shouldEnhanceSelect($select[0])) {
                            enhanceSelects($select[0]);
                            return;
                        }

                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.trigger('change.select2');
                        }
                    }

                    window.SglSelectEnhancer = {
                        init: enhanceSelects,
                        refresh: refreshSelect
                    };

                    $(function () {
                        enhanceSelects(document);

                        var observer = new MutationObserver(function (mutations) {
                            mutations.forEach(function (mutation) {
                                mutation.addedNodes.forEach(function (node) {
                                    if (node.nodeType === 1) {
                                        enhanceSelects(node);
                                    }
                                });
                            });
                        });

                        observer.observe(document.body, {
                            childList: true,
                            subtree: true
                        });
                    });
                })(window, document, window.jQuery);
            </script>


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
