<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Buscar Auditoría de Trámite</title>
	<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url() ?>/public/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url() ?>/public/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= base_url() ?>/public/assets/vendors/images/favicon-16x16.png">
	
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/style.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/src/styles/sgl_blue_template.css?v=20260610-1">
</head>
<body class="sidebar-shrink sgl-theme-2026">
    <?= view('deskapp/includes/_header') ?>
    <?= view('deskapp/includes/_sidebar') ?>
    
    <div class="main-container">
        <div class="pd-ltr-20">
            <div class="card-box pd-20 height-100-p mb-30 sgl-search-hero">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <img src="<?= base_url('public/assets/vendors/images/banner-img.png') ?>" alt="">
                    </div>
                    <div class="col-md-8">
                        <h4 class="font-20 weight-500 mb-10 text-capitalize">
                            <i class="fas fa-history text-primary"></i> Buscar Auditoría de Trámite
                        </h4>
                        <p class="font-18 max-width-600">
                            Ingresa el ID o Folio del trámite para ver su historial completo de cambios
                        </p>
                    </div>
                </div>
            </div>

            <?php $flashError = session()->getFlashdata('error'); ?>
            <?php if ($flashError): ?>
                <div class="sgl-liston" id="auditFlash"><i class="fas fa-exclamation-triangle"></i> <?= esc($flashError) ?></div>
            <?php else: ?>
                <div class="sgl-liston is-hidden" id="auditFlash"></div>
            <?php endif; ?>

            <!-- Formulario de búsqueda -->
            <div class="row">
                <div class="col-xl-6 col-lg-8 col-md-10 mx-auto">
                    <div class="card-box mb-30 sgl-search-panel">
                        <div class="pb-20 pt-20 pl-30 pr-30">
                            <div class="wizard-content">
                                <h5 class="text-center mb-30 sgl-search-title">Buscar Trámite</h5>
                                
                                <form id="searchAuditForm">
                                    <section>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Buscar por ID del Trámite:</label>
                                                    <input type="number" 
                                                           name="tramite_id" 
                                                           id="tramite_id" 
                                                           class="form-control" 
                                                           placeholder="Ej: 7669"
                                                           min="1">
                                                    <small class="form-text text-muted">
                                                        Puedes encontrar el ID en la URL al editar un trámite
                                                    </small>
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
                                                    <input type="text" 
                                                           name="folio" 
                                                           id="folio" 
                                                           class="form-control text-uppercase sgl-input-uppercase" 
                                                           placeholder="Ej: ALD820807">
                                                    <small class="form-text text-muted">
                                                        El folio se mostrará en mayúsculas automáticamente
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group text-center mt-30 sgl-search-actions">
                                                    <button type="submit" class="btn btn-primary btn-lg">
                                                        <i class="fas fa-search"></i> Buscar Auditoría
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

    <!-- js -->
    <script src="<?= base_url() ?>/public/assets/vendors/scripts/core.js"></script>
    <script src="<?= base_url() ?>/public/assets/vendors/scripts/script.min.js"></script>
    <script src="<?= base_url() ?>/public/assets/vendors/scripts/process.js"></script>
    <script src="<?= base_url() ?>/public/assets/vendors/scripts/layout-settings.js"></script>
    
    <script>
    $(document).ready(function() {
        // Convertir folio a mayúsculas automáticamente
        $('#folio').on('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Manejar el envío del formulario
        $('#searchAuditForm').on('submit', function(e) {
            e.preventDefault();
            
            const tramiteId = $('#tramite_id').val();
            const folio = $('#folio').val().trim();
            
            // Validar que al menos uno esté lleno
            if (!tramiteId && !folio) {
                showAuditError('Por favor ingresa el ID del trámite o el folio');
                return;
            }
            
            // Si tiene ID, ir directo al timeline
            if (tramiteId) {
                window.location.href = '<?= site_url('/deskapp/tramites/audit_timeline') ?>/' + tramiteId;
                return;
            }
            
            // Si tiene folio, buscar primero
            if (folio) {
                // Mostrar loading
                const btn = $(this).find('button[type="submit"]');
                const originalText = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i> Buscando...').prop('disabled', true);
                
                $.ajax({
                    url: '<?= site_url('/deskapp/tramites/buscar_por_folio') ?>',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ folio: folio }),
                    success: function(response) {
                        if (response.success && response.tramite_id) {
                            window.location.href = '<?= site_url('/deskapp/tramites/audit_timeline') ?>/' + response.tramite_id;
                        } else {
                            showAuditError(response.message || 'No se encontró el trámite');
                            btn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        showAuditError(response?.message || 'Error al buscar el trámite');
                        btn.html(originalText).prop('disabled', false);
                    }
                });
            }
        });

        function showAuditError(message) {
            const box = document.getElementById('auditFlash');
            if (!box) {
                return;
            }
            box.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + message;
            box.classList.remove('is-hidden');
            box.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
    </script>
</body>
</html>
