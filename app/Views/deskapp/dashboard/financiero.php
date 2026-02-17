<?= $this->extend('layout/main') ?>

<?php $assets = base_url('/public/assets'); ?>
<?php
    $qs = $_GET ?? [];
    $baseFinancieroUrl = base_url('/deskapp/dashboardadmin/financiero');
    $perPage = (int) ($per_page ?? 50);
    $pageAging = (int) ($page_aging ?? 1);
    $totalAging = (int) ($total_aging_report ?? 0);

    $renderPagination = function ($pageKey, $currentPage, $total, $perPage) use ($baseFinancieroUrl, $qs) {
        $totalPages = (int) ceil($total / max(1, $perPage));
        if ($totalPages <= 1) {
            return '';
        }

        $currentPage = max(1, min((int) $currentPage, $totalPages));
        $prev = max(1, $currentPage - 1);
        $next = min($totalPages, $currentPage + 1);

        $qsPrev = $qs;
        $qsPrev[$pageKey] = $prev;
        $qsNext = $qs;
        $qsNext[$pageKey] = $next;

        $prevUrl = $baseFinancieroUrl . '?' . http_build_query($qsPrev);
        $nextUrl = $baseFinancieroUrl . '?' . http_build_query($qsNext);

        $disabledPrev = $currentPage <= 1 ? ' disabled' : '';
        $disabledNext = $currentPage >= $totalPages ? ' disabled' : '';

        return '<nav class="mt-3 d-flex align-items-center justify-content-between">'
            . '<span class="text-muted">Pagina ' . $currentPage . ' de ' . $totalPages . '</span>'
            . '<ul class="pagination pagination-sm mb-0">'
            . '<li class="page-item' . $disabledPrev . '">'
                . '<a class="page-link" href="' . esc($disabledPrev ? '#' : $prevUrl) . '">Anterior</a>'
            . '</li>'
            . '<li class="page-item' . $disabledNext . '">' 
                . '<a class="page-link" href="' . esc($disabledNext ? '#' : $nextUrl) . '">Siguiente</a>'
            . '</li>'
            . '</ul>'
            . '</nav>';
    };
?>

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
                            <h4>Análisis Financiero <?= date('Y') ?></h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url('/deskapp/dashboard') ?>">Home</a></li>
                                <?php $cliente_qs = !empty($cliente_id_filtro) ? ('cliente_id=' . (int)$cliente_id_filtro) : ''; ?>
                                <li class="breadcrumb-item"><a href="<?= base_url('/deskapp/dashboardadmin') ?><?= $cliente_qs ? ('?' . $cliente_qs) : '' ?>">Dashboard Admin</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Financiero</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-sm-12 text-right">
                        <span class="badge badge-success mr-2" style="font-size: 12px; padding: 8px 12px;">
                            <i class="icon-copy fa fa-money"></i> Pendientes de cobro (Todos los años)
                        </span>
                        <a href="<?= base_url('/deskapp/dashboardadmin') ?><?= $cliente_qs ? ('?' . $cliente_qs) : '' ?>" class="btn btn-primary">
                            <i class="icon-copy fa fa-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Resumen Financiero -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 mb-20">
                    <div class="card-box height-100-p pd-20">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="font-20 weight-500 mb-10 text-capitalize">
                                    <div class="weight-600 font-24 text-primary">
                                        $<?= isset($proyeccion['pendiente_cobro']) ? number_format($proyeccion['pendiente_cobro'], 2) : '0.00' ?>
                                    </div>
                                </h4>
                                <p class="font-14 max-width-600">Total Pendiente de Cobro</p>
                            </div>
                            <div class="icon-copy fa fa-money-bill-wave" style="font-size: 40px; color: #1b00ff;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-20">
                    <div class="card-box height-100-p pd-20">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="font-20 weight-500 mb-10 text-capitalize">
                                    <div class="weight-600 font-24 text-success">
                                        $<?= isset($metricas_mes['monto_cobrado']) ? number_format($metricas_mes['monto_cobrado'], 2) : '0.00' ?>
                                    </div>
                                </h4>
                                <p class="font-14 max-width-600">Cobrado Este Mes</p>
                            </div>
                            <div class="icon-copy fa fa-check-circle" style="font-size: 40px; color: #00e091;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-20">
                    <div class="card-box height-100-p pd-20">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="font-20 weight-500 mb-10 text-capitalize">
                                    <div class="weight-600 font-24 text-info">
                                        $<?= isset($proyeccion['en_proceso_estimado']) ? number_format($proyeccion['en_proceso_estimado'], 2) : '0.00' ?>
                                    </div>
                                </h4>
                                <p class="font-14 max-width-600">En Proceso (Estimado)</p>
                            </div>
                            <div class="icon-copy fa fa-hourglass-half" style="font-size: 40px; color: #17a2b8;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-20">
                    <div class="card-box height-100-p pd-20">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="font-20 weight-500 mb-10 text-capitalize">
                                    <div class="weight-600 font-24 text-warning">
                                        $<?= isset($metricas_anio['monto_cobrado_anio']) ? number_format($metricas_anio['monto_cobrado_anio'], 2) : '0.00' ?>
                                    </div>
                                </h4>
                                <p class="font-14 max-width-600">Cobrado Este Año</p>
                            </div>
                            <div class="icon-copy fa fa-chart-line" style="font-size: 40px; color: #ffc107;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen por Rangos de Días -->
            <div class="row">
                <div class="col-xl-12 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="h4 text-blue mb-20">
                            <i class="icon-copy fa fa-chart-bar"></i> Cuentas por Cobrar - Resumen por Antigüedad
                        </h4>
                        <div class="row">
                            <?php if (isset($resumen_rangos) && count($resumen_rangos) > 0): ?>
                                <?php 
                                $colores = [
                                    '0-15 días' => 'success',
                                    '16-30 días' => 'info',
                                    '31-60 días' => 'warning',
                                    '61-90 días' => 'orange',
                                    'Más de 90 días' => 'danger'
                                ];
                                foreach ($resumen_rangos as $rango): 
                                ?>
                                <div class="col-xl col-lg-4 col-md-6 col-sm-12 mb-30">
                                    <div class="card-box pd-30 height-100-p">
                                        <div class="progress-box text-center">
                                            <p class="text-muted"><strong><?= esc($rango['rango']) ?></strong></p>
                                            <h3 class="text-<?= $colores[$rango['rango']] ?? 'primary' ?>">
                                                <?= $rango['cantidad_tramites'] ?>
                                            </h3>
                                            <p class="font-14 text-muted">trámites</p>
                                            <h4 class="text-<?= $colores[$rango['rango']] ?? 'primary' ?>">
                                                $<?= number_format($rango['monto_total'], 2) ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <p class="text-center">No hay cuentas por cobrar</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfica de Cuentas por Cobrar -->
            <div class="row">
                <div class="col-xl-12 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="h4 text-blue mb-20">Distribución de Cuentas por Cobrar</h4>
                        <div id="agingChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Aging Report Detallado -->
            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <div class="d-flex justify-content-between mb-20">
                            <h4 class="h4 text-blue">
                                <i class="icon-copy fa fa-file-invoice-dollar"></i> Aging Report - Detalle Completo
                            </h4>
                            <?php
                                $roles = $session->get('user_roles') ?? [];
                                if (!is_array($roles)) { $roles = [$roles]; }
                                $perms = $session->get('user_permissions') ?? [];
                                if (!is_array($perms)) { $perms = [$perms]; }
                                $canExport = is_super_admin($roles) || is_admin($roles) || has_permission('menu_dashboard_admin', $perms, $roles);
                            ?>
                            <?php if ($canExport): ?>
                                <button class="btn btn-sm btn-success" onclick="exportarExcel()">
                                    <i class="icon-copy fa fa-file-excel"></i> Exportar a Excel
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped data-table-export nowrap" id="aging-table">
                                <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Contrato</th>
                                        <th>Factura</th>
                                        <th>Cliente</th>
                                        <th>Ejecutivo</th>
                                        <th>Fecha Conclusión</th>
                                        <th>Monto</th>
                                        <th>Días Vencidos</th>
                                        <th>Rango</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($aging_report) && count($aging_report) > 0): ?>
                                        <?php 
                                        $total_por_cobrar = 0;
                                        foreach ($aging_report as $item): 
                                            $total_por_cobrar += $item['costo_total'];
                                            
                                            // Asignar color según el rango
                                            $badge_color = 'success';
                                            if ($item['dias_vencidos'] > 90) $badge_color = 'danger';
                                            elseif ($item['dias_vencidos'] > 60) $badge_color = 'orange';
                                            elseif ($item['dias_vencidos'] > 30) $badge_color = 'warning';
                                            elseif ($item['dias_vencidos'] > 15) $badge_color = 'info';
                                        ?>
                                        <tr>
                                            <td><strong><?= esc($item['folio']) ?></strong></td>
                                            <td><?= esc($item['contrato']) ?></td>
                                            <td><?= esc($item['numero_factura'] ?? $item['numero_refactura']) ?></td>
                                            <td><?= esc($item['cliente']) ?></td>
                                            <td><?= esc($item['ejecutivo']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($item['finished_at'])) ?></td>
                                            <td><strong class="text-primary">$<?= number_format($item['costo_total'], 2) ?></strong></td>
                                            <td>
                                                <span class="badge badge-pill badge-<?= $badge_color ?>" style="font-size: 13px;">
                                                    <?= $item['dias_vencidos'] ?> días
                                                </span>
                                            </td>
                                            <td><span class="badge badge-<?= $badge_color ?>"><?= esc($item['rango_dias']) ?></span></td>
                                            <td>
                                                <a href="<?= base_url('/deskapp/tramites/update/' . $item['id']) ?>" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="icon-copy fa fa-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <!-- Total -->
                                        <tr class="bg-light-gray">
                                            <td colspan="6" class="text-right"><strong>TOTAL POR COBRAR:</strong></td>
                                            <td colspan="4"><strong class="text-danger font-18">$<?= number_format($total_por_cobrar, 2) ?></strong></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center">No hay cuentas por cobrar</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?= $renderPagination('page_aging', $pageAging, $totalAging, $perPage) ?>
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
<script src="<?= $assets ?>/src/plugins/apexcharts/apexcharts.min.js"></script>

<script>
$(document).ready(function() {
    // DataTable
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
        order: [[7, 'desc']], // Ordenar por días vencidos
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: 'Copiar',
                className: 'btn btn-sm btn-primary'
            },
            {
                extend: 'csv',
                text: 'CSV',
                className: 'btn btn-sm btn-primary'
            },
            {
                extend: 'excel',
                text: 'Excel',
                className: 'btn btn-sm btn-primary',
                title: 'Aging Report - <?= date('Y-m-d') ?>'
            },
            {
                extend: 'pdf',
                text: 'PDF',
                className: 'btn btn-sm btn-primary',
                title: 'Aging Report - <?= date('Y-m-d') ?>',
                orientation: 'landscape',
                pageSize: 'LEGAL'
            },
            {
                extend: 'print',
                text: 'Imprimir',
                className: 'btn btn-sm btn-primary',
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

// Gráfica de Aging
<?php if (isset($resumen_rangos) && count($resumen_rangos) > 0): ?>
var agingOptions = {
    series: [{
        name: 'Cantidad',
        data: [<?= implode(',', array_column($resumen_rangos, 'cantidad_tramites')) ?>]
    }, {
        name: 'Monto',
        data: [<?= implode(',', array_column($resumen_rangos, 'monto_total')) ?>]
    }],
    chart: {
        type: 'bar',
        height: 350
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    xaxis: {
        categories: [<?= "'" . implode("','", array_column($resumen_rangos, 'rango')) . "'" ?>],
    },
    yaxis: [
        {
            title: {
                text: 'Cantidad de Trámites'
            }
        },
        {
            opposite: true,
            title: {
                text: 'Monto ($)'
            }
        }
    ],
    fill: {
        opacity: 1
    },
    colors: ['#00e091', '#1b00ff'],
    legend: {
        position: 'top'
    },
    tooltip: {
        y: {
            formatter: function (val, opts) {
                if (opts.seriesIndex === 1) {
                    return "$" + val.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }
                return val + " trámites";
            }
        }
    }
};

var agingChart = new ApexCharts(document.querySelector("#agingChart"), agingOptions);
agingChart.render();
<?php endif; ?>

function exportarExcel() {
    // Trigger del botón de Excel de DataTables
    $('.buttons-excel').click();
}
</script>

<style>
.badge-orange {
    background-color: #fd7e14;
    color: white;
}
</style>

<?= $this->endSection() ?>
