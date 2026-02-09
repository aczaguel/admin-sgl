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
                            <h4>Reportes y Estadísticas <?= isset($anio_seleccionado) ? $anio_seleccionado : date('Y') ?></h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url('/deskapp/dashboard') ?>">Home</a></li>
                                <li class="breadcrumb-item"><a href="<?= base_url('/deskapp/dashboardadmin') ?>">Dashboard Admin</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Reportes</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-sm-12 text-right">
                        <?php $cliente_qs = !empty($cliente_id_filtro) ? ('cliente_id=' . (int)$cliente_id_filtro) : ''; ?>

                        <div class="dropdown d-inline-block mr-2">
                            <a class="btn btn-primary dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                <i class="icon-copy dw dw-calendar-1"></i> Año: <?= isset($anio_seleccionado) ? $anio_seleccionado : date('Y') ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="<?= base_url('/deskapp/dashboardadmin/reportes') ?><?= $cliente_qs ? ('?' . $cliente_qs) : '' ?>">2026 (Actual)</a>
                                <a class="dropdown-item" href="<?= base_url('/deskapp/dashboardadmin/reportes?anio=2025') ?><?= $cliente_qs ? ('&' . $cliente_qs) : '' ?>">2025</a>
                                <a class="dropdown-item" href="<?= base_url('/deskapp/dashboardadmin/reportes?anio=2024') ?><?= $cliente_qs ? ('&' . $cliente_qs) : '' ?>">2024</a>
                                <a class="dropdown-item" href="<?= base_url('/deskapp/dashboardadmin/reportes?anio=2023') ?><?= $cliente_qs ? ('&' . $cliente_qs) : '' ?>">2023</a>
                                <a class="dropdown-item" href="<?= base_url('/deskapp/dashboardadmin/reportes?anio=2022') ?><?= $cliente_qs ? ('&' . $cliente_qs) : '' ?>">2022</a>
                            </div>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="<?= base_url('/deskapp/dashboardadmin') ?><?= $cliente_qs ? ('?' . $cliente_qs) : '' ?>" class="btn btn-primary">
                                <i class="icon-copy fa fa-arrow-left"></i> Volver
                            </a>
                            <button class="btn btn-success" onclick="exportarReportePDF()">
                                <i class="icon-copy fa fa-file-pdf"></i> Exportar PDF
                            </button>
                            <button class="btn btn-info" onclick="exportarReporteExcel()">
                                <i class="icon-copy fa fa-file-excel"></i> Exportar Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros de Período -->
            <div class="row mb-30">
                <div class="col-md-12">
                    <div class="card-box pd-20">
                        <h5 class="h5 text-blue mb-20">Filtros de Período</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" onclick="cambiarPeriodo('mes')">
                                Este Mes
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="cambiarPeriodo('trimestre')">
                                Trimestre
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="cambiarPeriodo('semestre')">
                                Semestre
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="cambiarPeriodo('anio')">
                                Este Año
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficas de Trámites por Mes -->
            <div class="row">
                <div class="col-xl-8 col-lg-8 col-md-12 mb-30">
                    <div class="card-box pd-20 height-100-p">
                        <h4 class="h4 text-blue mb-20">
                            <i class="icon-copy fa fa-chart-line"></i> Evolución de Trámites del Año
                        </h4>
                        <div id="tramitesMesChart" style="height: 400px;"></div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-12 mb-30">
                    <div class="card-box pd-20 height-100-p">
                        <h4 class="h4 text-blue mb-20">
                            <i class="icon-copy fa fa-chart-pie"></i> Trámites por Tipo
                        </h4>
                        <div id="tipoTramiteChart" style="height: 400px;"></div>
                    </div>
                </div>
            </div>

            <!-- Gráfica de Ingresos -->
            <div class="row">
                <div class="col-xl-12 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="h4 text-blue mb-20">
                            <i class="icon-copy fa fa-dollar-sign"></i> Ingresos Mensuales del Año
                        </h4>
                        <div id="ingresosMesChart" style="height: 400px;"></div>
                    </div>
                </div>
            </div>

            <!-- Rankings Detallados -->
            <div class="row">
                <div class="col-xl-6 col-lg-6 col-md-12 mb-30">
                    <div class="card-box pd-20 height-100-p">
                        <h4 class="h4 text-blue mb-20">
                            <i class="icon-copy fa fa-trophy"></i> Top 10 Ejecutivos del Mes
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ejecutivo</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Concluidos</th>
                                        <th class="text-center">Cobrados</th>
                                        <th class="text-right">Monto</th>
                                        <th class="text-center">Tiempo Prom.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($top_ejecutivos) && count($top_ejecutivos) > 0): ?>
                                        <?php 
                                        $posicion = 1;
                                        foreach ($top_ejecutivos as $ejecutivo): 
                                            $badgeColor = 'primary';
                                            if ($posicion == 1) $badgeColor = 'warning'; // Oro
                                            elseif ($posicion == 2) $badgeColor = 'secondary'; // Plata
                                            elseif ($posicion == 3) $badgeColor = 'danger'; // Bronce
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-<?= $badgeColor ?>" style="font-size: 14px;">
                                                    <?= $posicion ?>
                                                </span>
                                            </td>
                                            <td><strong><?= esc($ejecutivo['ejecutivo']) ?></strong></td>
                                            <td class="text-center"><?= $ejecutivo['total_tramites'] ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-success"><?= $ejecutivo['tramites_concluidos'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info"><?= $ejecutivo['tramites_cobrados'] ?></span>
                                            </td>
                                            <td class="text-right text-primary">
                                                <strong>$<?= number_format($ejecutivo['monto_cobrado'], 2) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <?= isset($ejecutivo['tiempo_promedio_dias']) && $ejecutivo['tiempo_promedio_dias'] ? round($ejecutivo['tiempo_promedio_dias'], 1) : 'N/A' ?> días
                                            </td>
                                        </tr>
                                        <?php 
                                        $posicion++;
                                        endforeach; 
                                        ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No hay datos disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-lg-6 col-md-12 mb-30">
                    <div class="card-box pd-20 height-100-p">
                        <h4 class="h4 text-blue mb-20">
                            <i class="icon-copy fa fa-user-tie"></i> Top 10 Gestores del Mes
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Gestor</th>
                                        <th>Empresa</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Concluidos</th>
                                        <th class="text-center">Tiempo Prom.</th>
                                        <th class="text-right">Pagado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($top_gestores) && count($top_gestores) > 0): ?>
                                        <?php 
                                        $posicion = 1;
                                        foreach ($top_gestores as $gestor): 
                                            $badgeColor = 'primary';
                                            if ($posicion == 1) $badgeColor = 'warning';
                                            elseif ($posicion == 2) $badgeColor = 'secondary';
                                            elseif ($posicion == 3) $badgeColor = 'danger';
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-<?= $badgeColor ?>" style="font-size: 14px;">
                                                    <?= $posicion ?>
                                                </span>
                                            </td>
                                            <td><strong><?= esc($gestor['gestor']) ?></strong></td>
                                            <td><small class="text-muted"><?= esc($gestor['empresa_gestora']) ?></small></td>
                                            <td class="text-center"><?= $gestor['total_tramites'] ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-success"><?= $gestor['tramites_concluidos'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?= isset($gestor['tiempo_promedio_dias']) && $gestor['tiempo_promedio_dias'] ? round($gestor['tiempo_promedio_dias'], 1) : 'N/A' ?> días
                                            </td>
                                            <td class="text-right text-primary">
                                                <strong>$<?= number_format($gestor['total_pagado_gestor'], 2) ?></strong>
                                            </td>
                                        </tr>
                                        <?php 
                                        $posicion++;
                                        endforeach; 
                                        ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No hay datos disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Análisis por Tipo de Trámite -->
            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="h4 text-blue mb-20">
                            <i class="icon-copy fa fa-list-alt"></i> Análisis por Tipo de Trámite (Este Mes)
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tipo de Trámite</th>
                                        <th class="text-center">Cantidad Total</th>
                                        <th class="text-center">Concluidos</th>
                                        <th class="text-center">% Éxito</th>
                                        <th class="text-center">Tiempo Promedio</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($tramites_por_tipo) && count($tramites_por_tipo) > 0): ?>
                                        <?php foreach ($tramites_por_tipo as $tipo): 
                                            $porcentaje = $tipo['cantidad'] > 0 ? round(($tipo['concluidos'] / $tipo['cantidad']) * 100, 1) : 0;
                                            $badgeEstado = 'success';
                                            if ($porcentaje < 50) $badgeEstado = 'danger';
                                            elseif ($porcentaje < 75) $badgeEstado = 'warning';
                                        ?>
                                        <tr>
                                            <td><strong><?= esc($tipo['tipo_tramite']) ?></strong></td>
                                            <td class="text-center"><?= $tipo['cantidad'] ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-success"><?= $tipo['concluidos'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $badgeEstado ?>"><?= $porcentaje ?>%</span>
                                            </td>
                                            <td class="text-center">
                                                <?= isset($tipo['tiempo_promedio']) && $tipo['tiempo_promedio'] ? round($tipo['tiempo_promedio'], 1) : 'N/A' ?> días
                                            </td>
                                            <td class="text-center">
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-<?= $badgeEstado ?>" role="progressbar" 
                                                         style="width: <?= $porcentaje ?>%;" 
                                                         aria-valuenow="<?= $porcentaje ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <?= $porcentaje ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No hay datos disponibles</td>
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
<script src="<?= $assets ?>/src/plugins/apexcharts/apexcharts.min.js"></script>

<script>
// Gráfica de Trámites por Mes
<?php if (isset($tramites_por_mes) && count($tramites_por_mes) > 0): ?>
var tramitesMesOptions = {
    series: [{
        name: 'Ingresados',
        data: [<?= implode(',', array_column($tramites_por_mes, 'total_ingresados')) ?>]
    }, {
        name: 'Concluidos',
        data: [<?= implode(',', array_column($tramites_por_mes, 'total_concluidos')) ?>]
    }, {
        name: 'Cobrados',
        data: [<?= implode(',', array_column($tramites_por_mes, 'total_cobrados')) ?>]
    }],
    chart: {
        type: 'line',
        height: 400,
        toolbar: {
            show: true
        }
    },
    colors: ['#1b00ff', '#00e091', '#ffc107'],
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'smooth',
        width: 3
    },
    xaxis: {
        categories: [<?= "'" . implode("','", array_map(function($m) { 
            return date('M', mktime(0, 0, 0, $m['mes'], 1)); 
        }, $tramites_por_mes)) . "'" ?>],
        title: {
            text: 'Mes'
        }
    },
    yaxis: {
        title: {
            text: 'Cantidad de Trámites'
        }
    },
    legend: {
        position: 'top'
    },
    grid: {
        borderColor: '#e7e7e7'
    }
};
var tramitesMesChart = new ApexCharts(document.querySelector("#tramitesMesChart"), tramitesMesOptions);
tramitesMesChart.render();
<?php endif; ?>

// Gráfica de Ingresos por Mes
<?php if (isset($ingresos_por_mes) && count($ingresos_por_mes) > 0): ?>
var ingresosMesOptions = {
    series: [{
        name: 'Ingresos',
        data: [<?= implode(',', array_column($ingresos_por_mes, 'ingresos')) ?>]
    }],
    chart: {
        type: 'bar',
        height: 400
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded',
            dataLabels: {
                position: 'top'
            }
        }
    },
    colors: ['#00e091'],
    dataLabels: {
        enabled: true,
        formatter: function (val) {
            return "$" + val.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        offsetY: -20,
        style: {
            fontSize: '12px',
            colors: ["#304758"]
        }
    },
    xaxis: {
        categories: [<?= "'" . implode("','", array_map(function($m) { 
            return date('M', mktime(0, 0, 0, $m['mes'], 1)); 
        }, $ingresos_por_mes)) . "'" ?>],
        title: {
            text: 'Mes'
        }
    },
    yaxis: {
        title: {
            text: 'Ingresos ($)'
        },
        labels: {
            formatter: function (val) {
                return "$" + val.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
        }
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return "$" + val.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
        }
    }
};
var ingresosMesChart = new ApexCharts(document.querySelector("#ingresosMesChart"), ingresosMesOptions);
ingresosMesChart.render();
<?php endif; ?>

// Gráfica de Trámites por Tipo
<?php if (isset($tramites_por_tipo) && count($tramites_por_tipo) > 0): ?>
var tipoTramiteOptions = {
    series: [<?= implode(',', array_column($tramites_por_tipo, 'cantidad')) ?>],
    chart: {
        type: 'donut',
        height: 400
    },
    labels: [<?= "'" . implode("','", array_column($tramites_por_tipo, 'tipo_tramite')) . "'" ?>],
    colors: ['#1b00ff', '#00e091', '#ffc107', '#17a2b8', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'],
    legend: {
        position: 'bottom',
        fontSize: '12px'
    },
    dataLabels: {
        enabled: true,
        formatter: function(val) {
            return val.toFixed(1) + "%";
        }
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 300
            },
            legend: {
                position: 'bottom'
            }
        }
    }]
};
var tipoTramiteChart = new ApexCharts(document.querySelector("#tipoTramiteChart"), tipoTramiteOptions);
tipoTramiteChart.render();
<?php endif; ?>

// Funciones de exportación
function exportarReporteExcel() {
    window.location.href = '<?= base_url('/deskapp/dashboardadmin/exportar_excel') ?>?tipo=reportes<?= isset($anio_seleccionado) ? ('&anio=' . (int)$anio_seleccionado) : '' ?><?= !empty($cliente_id_filtro) ? ('&cliente_id=' . (int)$cliente_id_filtro) : '' ?>';
}

function exportarReportePDF() {
    window.location.href = '<?= base_url('/deskapp/dashboardadmin/exportar_pdf') ?>?tipo=reportes<?= isset($anio_seleccionado) ? ('&anio=' . (int)$anio_seleccionado) : '' ?><?= !empty($cliente_id_filtro) ? ('&cliente_id=' . (int)$cliente_id_filtro) : '' ?>';
}

function cambiarPeriodo(periodo) {
    // Cambiar estilo de botón activo
    $('.btn-group button').removeClass('active');
    event.target.classList.add('active');
    
    // Aquí puedes implementar la lógica para recargar los datos según el período
    console.log('Cambiando a período:', periodo);
    
    // Opcional: recargar la página con el parámetro
    // window.location.href = '<?= base_url('/deskapp/dashboardadmin/reportes') ?>?periodo=' + periodo;
}
</script>

<?= $this->endSection() ?>
