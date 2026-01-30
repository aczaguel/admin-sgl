<?= $this->extend('layout/main') ?>

<?php $assets = base_url('/public/assets'); ?>

<link rel="stylesheet" href="<?= $assets ?>/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= $assets ?>/src/plugins/datatables/css/responsive.bootstrap4.min.css">

<?= $this->section('content') ?>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            
            <!-- Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4>Alertas Críticas <?= date('Y') ?></h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url('/deskapp/dashboard') ?>">Home</a></li>
                                <li class="breadcrumb-item"><a href="<?= base_url('/deskapp/dashboardadmin') ?>">Dashboard Admin</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Alertas</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-sm-12 text-right">
                        <span class="badge badge-info mr-2" style="font-size: 12px; padding: 8px 12px;">
                            <i class="icon-copy dw dw-notification"></i> Solo año actual (<?= date('Y') ?>)
                        </span>
                        <a href="<?= base_url('/deskapp/dashboardadmin') ?>" class="btn btn-primary">
                            <i class="icon-copy fa fa-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Resumen de Alertas -->
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-6 mb-20">
                    <div class="card-box height-100-p pd-20">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="font-20 weight-500 mb-10 text-capitalize">
                                    <div class="weight-600 font-30 text-danger"><?= count($tramites_retrasados) ?></div>
                                </h4>
                                <p class="font-18 max-width-600">Trámites Retrasados</p>
                            </div>
                            <div class="icon-copy fa fa-exclamation-triangle" style="font-size: 48px; color: #dc3545;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 mb-20">
                    <div class="card-box height-100-p pd-20">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="font-20 weight-500 mb-10 text-capitalize">
                                    <div class="weight-600 font-30 text-warning"><?= count($pendientes_cobro) ?></div>
                                </h4>
                                <p class="font-18 max-width-600">Pendientes de Cobro</p>
                            </div>
                            <div class="icon-copy fa fa-dollar-sign" style="font-size: 48px; color: #ffc107;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 mb-20">
                    <div class="card-box height-100-p pd-20">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="font-20 weight-500 mb-10 text-capitalize">
                                    <div class="weight-600 font-30 text-secondary"><?= count($tramites_estancados) ?></div>
                                </h4>
                                <p class="font-18 max-width-600">Trámites Estancados</p>
                            </div>
                            <div class="icon-copy fa fa-pause-circle" style="font-size: 48px; color: #6c757d;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trámites Retrasados -->
            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="h4 text-danger mb-20">
                            <i class="icon-copy fa fa-exclamation-triangle"></i> Trámites Retrasados (Más de 30 días)
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-striped data-table-export nowrap">
                                <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Contrato</th>
                                        <th>Unidad</th>
                                        <th>Tipo</th>
                                        <th>Cliente</th>
                                        <th>Ejecutivo</th>
                                        <th>Estado</th>
                                        <th>Fecha Inicio</th>
                                        <th>Días Transcurridos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($tramites_retrasados) > 0): ?>
                                        <?php foreach ($tramites_retrasados as $tramite): ?>
                                        <tr>
                                            <td><strong><?= esc($tramite['folio']) ?></strong></td>
                                            <td><?= esc($tramite['contrato']) ?></td>
                                            <td><?= esc($tramite['unidad']) ?></td>
                                            <td><?= esc($tramite['tipo_tramite']) ?></td>
                                            <td><?= esc($tramite['cliente']) ?></td>
                                            <td><?= esc($tramite['ejecutivo']) ?></td>
                                            <td><span class="badge badge-warning"><?= esc($tramite['tra_status']) ?></span></td>
                                            <td><?= date('d/m/Y', strtotime($tramite['started_at'] ?? $tramite['created_at'])) ?></td>
                                            <td>
                                                <span class="badge badge-pill badge-danger" style="font-size: 14px;">
                                                    <?= $tramite['dias_transcurridos'] ?> días
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('/deskapp/tramites/update/' . $tramite['id']) ?>" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="icon-copy fa fa-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center">No hay trámites retrasados</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pendientes de Cobro -->
            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="h4 text-warning mb-20">
                            <i class="icon-copy fa fa-dollar-sign"></i> Pendientes de Cobro (Más de 15 días)
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-striped data-table-export nowrap">
                                <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Contrato</th>
                                        <th>Unidad</th>
                                        <th>Factura</th>
                                        <th>Cliente</th>
                                        <th>Ejecutivo</th>
                                        <th>Estado Cobro</th>
                                        <th>Fecha Conclusión</th>
                                        <th>Monto</th>
                                        <th>Días Sin Cobrar</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($pendientes_cobro) > 0): ?>
                                        <?php foreach ($pendientes_cobro as $tramite): ?>
                                        <tr>
                                            <td><strong><?= esc($tramite['folio']) ?></strong></td>
                                            <td><?= esc($tramite['contrato']) ?></td>
                                            <td><?= esc($tramite['unidad']) ?></td>
                                            <td><?= esc($tramite['numero_factura'] ?? $tramite['numero_refactura']) ?></td>
                                            <td><?= esc($tramite['cliente']) ?></td>
                                            <td><?= esc($tramite['ejecutivo']) ?></td>
                                            <td><span class="badge badge-secondary"><?= esc($tramite['cobro_status']) ?></span></td>
                                            <td><?= date('d/m/Y', strtotime($tramite['finished_at'])) ?></td>
                                            <td><strong class="text-primary">$<?= number_format($tramite['costo_total'], 2) ?></strong></td>
                                            <td>
                                                <span class="badge badge-pill badge-warning" style="font-size: 14px;">
                                                    <?= $tramite['dias_sin_cobrar'] ?> días
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('/deskapp/tramites/update/' . $tramite['id']) ?>" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="icon-copy fa fa-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="11" class="text-center">No hay trámites pendientes de cobro</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trámites Estancados -->
            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="h4 text-secondary mb-20">
                            <i class="icon-copy fa fa-pause-circle"></i> Trámites Estancados (Sin movimiento por más de 7 días)
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-striped data-table-export nowrap">
                                <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Contrato</th>
                                        <th>Unidad</th>
                                        <th>Tipo</th>
                                        <th>Cliente</th>
                                        <th>Ejecutivo</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th>Días Sin Movimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($tramites_estancados) > 0): ?>
                                        <?php foreach ($tramites_estancados as $tramite): ?>
                                        <tr>
                                            <td><strong><?= esc($tramite['folio']) ?></strong></td>
                                            <td><?= esc($tramite['contrato']) ?></td>
                                            <td><?= esc($tramite['unidad']) ?></td>
                                            <td><?= esc($tramite['tipo_tramite']) ?></td>
                                            <td><?= esc($tramite['cliente']) ?></td>
                                            <td><?= esc($tramite['ejecutivo']) ?></td>
                                            <td><span class="badge badge-info"><?= esc($tramite['tra_status']) ?></span></td>
                                            <td><?= date('d/m/Y', strtotime($tramite['created_at'])) ?></td>
                                            <td>
                                                <span class="badge badge-pill badge-secondary" style="font-size: 14px;">
                                                    <?= $tramite['dias_sin_movimiento'] ?> días
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('/deskapp/tramites/update/' . $tramite['id']) ?>" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="icon-copy fa fa-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center">No hay trámites estancados</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Scripts -->
<script src="<?= $assets ?>/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="<?= $assets ?>/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= $assets ?>/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="<?= $assets ?>/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
<script src="<?= $assets ?>/src/plugins/datatables/js/dataTables.buttons.min.js"></script>
<script src="<?= $assets ?>/src/plugins/datatables/js/buttons.bootstrap4.min.js"></script>
<script src="<?= $assets ?>/src/plugins/datatables/js/buttons.print.min.js"></script>
<script src="<?= $assets ?>/src/plugins/datatables/js/buttons.html5.min.js"></script>
<script src="<?= $assets ?>/src/plugins/datatables/js/buttons.flash.min.js"></script>
<script src="<?= $assets ?>/src/plugins/datatables/js/pdfmake.min.js"></script>
<script src="<?= $assets ?>/src/plugins/datatables/js/vfs_fonts.js"></script>

<script>
$(document).ready(function() {
    $('.data-table-export').DataTable({
        scrollCollapse: true,
        autoWidth: false,
        responsive: true,
        columnDefs: [{
            targets: "datatable-nosort",
            orderable: false,
        }],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: 'Copiar',
                className: 'btn btn-primary'
            },
            {
                extend: 'csv',
                text: 'CSV',
                className: 'btn btn-primary'
            },
            {
                extend: 'excel',
                text: 'Excel',
                className: 'btn btn-primary'
            },
            {
                extend: 'pdf',
                text: 'PDF',
                className: 'btn btn-primary'
            },
            {
                extend: 'print',
                text: 'Imprimir',
                className: 'btn btn-primary',
                customize: function (win){
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                }
            }
        ]
    });
});
</script>

<?= $this->endSection() ?>
