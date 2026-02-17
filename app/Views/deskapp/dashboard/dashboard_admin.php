<?= $this->extend('layout/main') ?>

<?php $assets = base_url('/public/assets'); ?>

<!-- CSS adicionales -->
<link rel="stylesheet" href="<?= $assets ?>/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= $assets ?>/vendors/styles/style.css">

<style>
    /* Dashboard Admin visual refresh (no functional changes) */
    :root {
        --da-ink: #1f2933;
        --da-muted: #637381;
        --da-surface: #ffffff;
        --da-panel: #f7f5f0;
        --da-accent: #0f766e;
        --da-accent-2: #f59e0b;
        --da-accent-3: #2563eb;
        --da-border: #e6e2d9;
        --da-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
    }

    .main-container {
        background: radial-gradient(circle at top left, #f5efe6 0%, #f9fafb 55%, #ffffff 100%);
    }

    .page-header .title h4 {
        font-family: "Poppins", "Montserrat", sans-serif;
        color: var(--da-ink);
        letter-spacing: 0.2px;
    }

    .breadcrumb a,
    .breadcrumb-item {
        color: var(--da-muted);
    }

    .card-box,
    .widget-style3 {
        border: 1px solid var(--da-border);
        border-radius: 14px;
        box-shadow: var(--da-shadow);
        background: var(--da-surface);
    }

    .card-box.pd-20 {
        background: linear-gradient(180deg, #ffffff 0%, #fbfaf7 100%);
    }

    .card-box .h4,
    .card-box .h5 {
        font-family: "Poppins", "Montserrat", sans-serif;
        color: var(--da-ink);
    }

    .text-blue {
        color: var(--da-accent) !important;
    }

    .widget-style3 {
        background: linear-gradient(135deg, #ffffff 0%, #f7f7f2 100%);
    }

    .widget-style3 .widget-data .weight-700 {
        color: var(--da-ink);
    }

    .widget-style3 .widget-icon .icon-copy {
        filter: drop-shadow(0 6px 12px rgba(15, 23, 42, 0.12));
    }

    .alert {
        border-radius: 12px;
        box-shadow: var(--da-shadow);
    }

    .alert .alert-link {
        color: inherit;
        text-decoration: underline;
    }

    .table thead th {
        background: var(--da-panel);
        color: var(--da-ink);
        border-bottom: 1px solid var(--da-border);
    }

    .table {
        color: var(--da-ink);
    }

    .table td,
    .table th {
        border-top: 1px solid var(--da-border);
    }

    .table tbody tr:hover {
        background: rgba(15, 118, 110, 0.05);
    }

    .badge {
        border-radius: 999px;
        padding: 6px 10px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .badge.badge-dark {
        background: #4b5563;
    }

    .badge.badge-info {
        background: #38bdf8;
    }

    .badge.badge-danger {
        background: #ef4444;
    }

    .badge.badge-success {
        background: #22c55e;
    }

    .btn.btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
        border: none;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
    }

    .btn.btn-primary:hover {
        filter: brightness(0.95);
    }

    .dropdown-menu {
        border-radius: 12px;
        box-shadow: var(--da-shadow);
    }

    .page-header {
        background: #ffffff;
        border: 1px solid var(--da-border);
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: var(--da-shadow);
    }

    .page-header .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0;
    }
</style>

<?= $this->section('content') ?>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            
            <!-- Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4>Dashboard Administrativo <?= isset($anio_seleccionado) ? $anio_seleccionado : date('Y') ?></h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url('/deskapp/dashboard') ?>">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Dashboard Admin</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="col-md-6 col-sm-12 text-right">
                        <?php $cliente_qs = !empty($cliente_id_filtro) ? ('cliente_id=' . (int)$cliente_id_filtro) : ''; ?>

                        <div class="d-flex justify-content-end align-items-start flex-wrap" style="gap: 10px;">
                        <div class="dropdown">
                            <a class="btn btn-primary dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                <i class="icon-copy dw dw-calendar-1"></i> Año: <?= isset($anio_seleccionado) ? $anio_seleccionado : date('Y') ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="<?= base_url('/deskapp/dashboardadmin') ?><?= $cliente_qs ? ('?' . $cliente_qs) : '' ?>">2026 (Actual)</a>
                                <a class="dropdown-item" href="<?= base_url('/deskapp/dashboardadmin?anio=2025') ?><?= $cliente_qs ? ('&' . $cliente_qs) : '' ?>">2025</a>
                                <a class="dropdown-item" href="<?= base_url('/deskapp/dashboardadmin?anio=2024') ?><?= $cliente_qs ? ('&' . $cliente_qs) : '' ?>">2024</a>
                                <a class="dropdown-item" href="<?= base_url('/deskapp/dashboardadmin?anio=2023') ?><?= $cliente_qs ? ('&' . $cliente_qs) : '' ?>">2023</a>
                                <a class="dropdown-item" href="<?= base_url('/deskapp/dashboardadmin?anio=2022') ?><?= $cliente_qs ? ('&' . $cliente_qs) : '' ?>">2022</a>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas Críticas (solo para año actual) -->
            <?php if (isset($es_anio_actual) && $es_anio_actual): ?>
                <?php if (isset($count_retrasados) && $count_retrasados > 0): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>⚠️ Alerta:</strong> Tienes <?= $count_retrasados ?> trámites retrasados. 
                <a href="<?= base_url('/deskapp/dashboardadmin/alertas') ?><?= $cliente_qs ? ('?' . $cliente_qs) : '' ?>" class="alert-link">Ver detalles</a>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <?php if (isset($count_pendientes_cobro) && $count_pendientes_cobro > 0): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>💰 Importante:</strong> Hay <?= $count_pendientes_cobro ?> trámites pendientes de cobro. 
                <a href="<?= base_url('/deskapp/dashboardadmin/financiero') ?><?= $cliente_qs ? ('?' . $cliente_qs) : '' ?>" class="alert-link">Ver detalles</a>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            <?php endif; // Fin de es_anio_actual ?>

            <?php if (isset($es_anio_actual) && $es_anio_actual): ?>
                <?php
                    $semaforo = $semaforo_atencion ?? [];
                    $atoradosTipos = $atorados_por_tipo ?? [];
                    $atoradosEstados = $atorados_por_estado ?? [];
                    $atoradosClientes = $atorados_por_cliente ?? [];
                ?>

                <!-- Semaforo de atencion -->
                <div class="row">
                    <div class="col-md-12 mb-30">
                        <div class="card-box pd-20">
                            <h4 class="h4 text-blue mb-20">
                                <i class="icon-copy fa fa-traffic-light"></i> Semaforo de atencion (local vs foraneo)
                            </h4>
                            <div class="row">
                                <div class="col-md-6 mb-20">
                                    <h6 class="text-muted mb-10">Locales (CDMX + EdoMex)</h6>
                                    <div class="d-flex flex-wrap" style="gap: 10px;">
                                        <span class="badge badge-success">Verde: <?= (int)($semaforo['local_verde'] ?? 0) ?></span>
                                        <span class="badge badge-info">Amarillo: <?= (int)($semaforo['local_amarillo'] ?? 0) ?></span>
                                        <span class="badge badge-danger">Rojo: <?= (int)($semaforo['local_rojo'] ?? 0) ?></span>
                                        <span class="badge badge-dark">Violeta: <?= (int)($semaforo['local_violeta'] ?? 0) ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-20">
                                    <h6 class="text-muted mb-10">Foraneos</h6>
                                    <div class="d-flex flex-wrap" style="gap: 10px;">
                                        <span class="badge badge-success">Verde: <?= (int)($semaforo['foraneo_verde'] ?? 0) ?></span>
                                        <span class="badge badge-info">Amarillo: <?= (int)($semaforo['foraneo_amarillo'] ?? 0) ?></span>
                                        <span class="badge badge-danger">Rojo: <?= (int)($semaforo['foraneo_rojo'] ?? 0) ?></span>
                                        <span class="badge badge-dark">Violeta: <?= (int)($semaforo['foraneo_violeta'] ?? 0) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sin movimiento mas de 7 dias por criterio -->
                <div class="row">
                    <div class="col-xl-4 col-lg-4 col-md-12 mb-30">
                        <div class="card-box pd-20">
                            <h5 class="h5 text-blue mb-15">Sin movimiento mas de 7 dias por Tipo de Servicio</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($atoradosTipos)): ?>
                                            <?php foreach ($atoradosTipos as $row): ?>
                                                <tr>
                                                    <td><?= esc($row['tipo']) ?></td>
                                                    <td class="text-right"><strong><?= (int)$row['total'] ?></strong></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="2" class="text-center">Sin datos</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-12 mb-30">
                        <div class="card-box pd-20">
                            <h5 class="h5 text-blue mb-15">Sin movimiento mas de 7 dias por Estado</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Estado</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($atoradosEstados)): ?>
                                            <?php foreach ($atoradosEstados as $row): ?>
                                                <tr>
                                                    <td><?= esc($row['estado']) ?></td>
                                                    <td class="text-right"><strong><?= (int)$row['total'] ?></strong></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="2" class="text-center">Sin datos</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-12 mb-30">
                        <div class="card-box pd-20">
                            <h5 class="h5 text-blue mb-15">Sin movimiento mas de 7 dias por Cliente (Top 10)</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($atoradosClientes)): ?>
                                            <?php foreach ($atoradosClientes as $row): ?>
                                                <tr>
                                                    <td><?= esc($row['cliente']) ?></td>
                                                    <td class="text-right"><strong><?= (int)$row['total'] ?></strong></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="2" class="text-center">Sin datos</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- KPIs Principales -->
            <div class="row">
                <div class="col-xl-12 mb-30">
                    <h5 class="text-blue h5">Indicadores Principales (Este Mes)</h5>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?= isset($kpis['tramites_activos']) ? number_format($kpis['tramites_activos']) : 0 ?></div>
                                <div class="font-14 text-secondary weight-500">Trámites Activos</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon-copy fa fa-tasks" style="font-size: 48px; color: #1b00ff;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?= isset($kpis['tasa_conversion_mes']) ? $kpis['tasa_conversion_mes'] : 0 ?>%</div>
                                <div class="font-14 text-secondary weight-500">Tasa de Conversión</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon-copy fa fa-chart-line" style="font-size: 48px; color: #00e091;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?= isset($kpis['tiempo_promedio_gestion']) ? round($kpis['tiempo_promedio_gestion'], 1) : 0 ?></div>
                                <div class="font-14 text-secondary weight-500">Días Promedio de Gestión</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon-copy fa fa-clock" style="font-size: 48px; color: #ffc107;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">$<?= isset($kpis['monto_pendiente_cobro']) ? number_format($kpis['monto_pendiente_cobro'], 2) : 0 ?></div>
                                <div class="font-14 text-secondary weight-500">Pendiente de Cobro</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon-copy fa fa-dollar-sign" style="font-size: 48px; color: #dc3545;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Métricas por Período -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h5 class="h5 text-blue">Hoy</h5>
                        <hr>
                        <div class="font-14">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ingresados:</span>
                                <strong><?= isset($metricas_hoy['total_ingresados']) ? $metricas_hoy['total_ingresados'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Concluidos:</span>
                                <strong class="text-success"><?= isset($metricas_hoy['total_concluidos']) ? $metricas_hoy['total_concluidos'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Cobrados:</span>
                                <strong class="text-info"><?= isset($metricas_hoy['total_cobrados']) ? $metricas_hoy['total_cobrados'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Monto Cobrado:</span>
                                <strong class="text-primary">$<?= isset($metricas_hoy['monto_cobrado_hoy']) ? number_format($metricas_hoy['monto_cobrado_hoy'], 2) : '0.00' ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h5 class="h5 text-blue">Esta Semana</h5>
                        <hr>
                        <div class="font-14">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ingresados:</span>
                                <strong><?= isset($metricas_semana['total_ingresados']) ? $metricas_semana['total_ingresados'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Concluidos:</span>
                                <strong class="text-success"><?= isset($metricas_semana['total_concluidos']) ? $metricas_semana['total_concluidos'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Cobrados:</span>
                                <strong class="text-info"><?= isset($metricas_semana['total_cobrados']) ? $metricas_semana['total_cobrados'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tiempo Prom.:</span>
                                <strong class="text-warning"><?= isset($metricas_semana['tiempo_promedio_dias']) ? round($metricas_semana['tiempo_promedio_dias'], 1) : 0 ?> días</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h5 class="h5 text-blue">Este Mes</h5>
                        <hr>
                        <div class="font-14">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ingresados:</span>
                                <strong><?= isset($metricas_mes['total_ingresados']) ? $metricas_mes['total_ingresados'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Concluidos:</span>
                                <strong class="text-success"><?= isset($metricas_mes['total_concluidos']) ? $metricas_mes['total_concluidos'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Cobrados:</span>
                                <strong class="text-info"><?= isset($metricas_mes['total_cobrados']) ? $metricas_mes['total_cobrados'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Monto Cobrado:</span>
                                <strong class="text-primary">$<?= isset($metricas_mes['monto_cobrado']) ? number_format($metricas_mes['monto_cobrado'], 2) : '0.00' ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h5 class="h5 text-blue">Enero a la Fecha</h5>
                        <hr>
                        <div class="font-14">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ingresados:</span>
                                <strong><?= isset($metricas_enero['total_ingresados']) ? $metricas_enero['total_ingresados'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Concluidos:</span>
                                <strong class="text-success"><?= isset($metricas_enero['total_concluidos']) ? $metricas_enero['total_concluidos'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Pendiente Cobro:</span>
                                <strong class="text-danger"><?= isset($metricas_enero['pendientes_cobro']) ? $metricas_enero['pendientes_cobro'] : 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>$ Por Cobrar:</span>
                                <strong class="text-danger">$<?= isset($metricas_enero['monto_por_cobrar']) ? number_format($metricas_enero['monto_por_cobrar'], 2) : '0.00' ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Embudo de Conversión -->
            <div class="row">
                <div class="col-xl-8 col-lg-8 col-md-12 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h4 class="h4 text-blue mb-20">Embudo de Conversión (Este Mes)</h4>
                        <div id="embudoChart" style="height: 400px;"></div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-12 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h4 class="h4 text-blue mb-20">Distribución por Estado</h4>
                        <div id="estadosChart" style="height: 400px;"></div>
                    </div>
                </div>
            </div>

            <!-- Top Ejecutivos y Gestores -->
            <div class="row">
                <div class="col-xl-6 col-lg-6 col-md-12 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h4 class="h4 text-blue mb-20">Top 5 Ejecutivos (Este Mes)</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Ejecutivo</th>
                                        <th class="text-center">Concluidos</th>
                                        <th class="text-center">Cobrados</th>
                                        <th class="text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($top_ejecutivos) && count($top_ejecutivos) > 0): ?>
                                        <?php foreach ($top_ejecutivos as $ejecutivo): ?>
                                        <tr>
                                            <td><?= esc($ejecutivo['ejecutivo']) ?></td>
                                            <td class="text-center"><span class="badge badge-success"><?= $ejecutivo['tramites_concluidos'] ?></span></td>
                                            <td class="text-center"><span class="badge badge-info"><?= $ejecutivo['tramites_cobrados'] ?></span></td>
                                            <td class="text-right">$<?= number_format($ejecutivo['monto_cobrado'], 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No hay datos disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-lg-6 col-md-12 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h4 class="h4 text-blue mb-20">Top 5 Gestores (Este Mes)</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Gestor</th>
                                        <th class="text-center">Concluidos</th>
                                        <th class="text-center">Tiempo Prom.</th>
                                        <th class="text-right">Total Pagado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($top_gestores) && count($top_gestores) > 0): ?>
                                        <?php foreach ($top_gestores as $gestor): ?>
                                        <tr>
                                            <td>
                                                <?= esc($gestor['gestor']) ?><br>
                                                <small class="text-muted"><?= esc($gestor['empresa_gestora']) ?></small>
                                            </td>
                                            <td class="text-center"><span class="badge badge-success"><?= $gestor['tramites_concluidos'] ?></span></td>
                                            <td class="text-center"><?= round($gestor['tiempo_promedio_dias'], 1) ?> días</td>
                                            <td class="text-right">$<?= number_format($gestor['total_pagado_gestor'], 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No hay datos disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas Recientes -->
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 mb-30">
                    <div class="card-box pd-20 height-100-p">
                        <div class="d-flex justify-content-between mb-20">
                            <h4 class="h4 text-blue">Alertas Críticas</h4>
                            <a href="<?= base_url('/deskapp/dashboardadmin/alertas') ?><?= $cliente_qs ? ('?' . $cliente_qs) : '' ?>" class="btn btn-sm btn-primary">Ver Todas</a>
                        </div>
                        
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#retrasados" role="tab">
                                    Retrasados <span class="badge badge-danger"><?= $count_retrasados ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#pendientes" role="tab">
                                    Pendientes Cobro <span class="badge badge-warning"><?= $count_pendientes_cobro ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#estancados" role="tab">
                                    Estancados <span class="badge badge-secondary"><?= $count_estancados ?></span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content pt-20">
                            <div class="tab-pane fade show active" id="retrasados" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Unidad</th>
                                                <th>Tipo</th>
                                                <th>Cliente</th>
                                                <th>Ejecutivo</th>
                                                <th>Días</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (isset($tramites_retrasados) && count($tramites_retrasados) > 0): ?>
                                                <?php foreach ($tramites_retrasados as $tramite): ?>
                                                <tr>
                                                    <td><?= esc($tramite['folio']) ?></td>
                                                    <td><?= esc($tramite['unidad']) ?></td>
                                                    <td><?= esc($tramite['tipo_tramite']) ?></td>
                                                    <td><?= esc($tramite['cliente']) ?></td>
                                                    <td><?= esc($tramite['ejecutivo']) ?></td>
                                                    <td><span class="badge badge-danger"><?= $tramite['dias_transcurridos'] ?> días</span></td>
                                                    <td>
                                                        <a href="<?= base_url('/deskapp/tramites/update/' . $tramite['id']) ?>" class="btn btn-sm btn-primary">Ver</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No hay trámites retrasados</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="pendientes" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Factura</th>
                                                <th>Cliente</th>
                                                <th>Ejecutivo</th>
                                                <th>Monto</th>
                                                <th>Días Sin Cobrar</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (isset($pendientes_cobro) && count($pendientes_cobro) > 0): ?>
                                                <?php foreach ($pendientes_cobro as $tramite): ?>
                                                <tr>
                                                    <td><?= esc($tramite['folio']) ?></td>
                                                    <td><?= esc($tramite['numero_factura'] ?? $tramite['numero_refactura']) ?></td>
                                                    <td><?= esc($tramite['cliente']) ?></td>
                                                    <td><?= esc($tramite['ejecutivo']) ?></td>
                                                    <td>$<?= number_format($tramite['costo_total'], 2) ?></td>
                                                    <td><span class="badge badge-warning"><?= $tramite['dias_sin_cobrar'] ?> días</span></td>
                                                    <td>
                                                        <a href="<?= base_url('/deskapp/tramites/update/' . $tramite['id']) ?>" class="btn btn-sm btn-primary">Ver</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No hay trámites pendientes de cobro</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="estancados" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Unidad</th>
                                                <th>Tipo</th>
                                                <th>Cliente</th>
                                                <th>Ejecutivo</th>
                                                <th>Días Sin Movimiento</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (isset($tramites_estancados) && count($tramites_estancados) > 0): ?>
                                                <?php foreach ($tramites_estancados as $tramite): ?>
                                                <tr>
                                                    <td><?= esc($tramite['folio']) ?></td>
                                                    <td><?= esc($tramite['unidad']) ?></td>
                                                    <td><?= esc($tramite['tipo_tramite']) ?></td>
                                                    <td><?= esc($tramite['cliente']) ?></td>
                                                    <td><?= esc($tramite['ejecutivo']) ?></td>
                                                    <td><span class="badge badge-secondary"><?= $tramite['dias_sin_movimiento'] ?> días</span></td>
                                                    <td>
                                                        <a href="<?= base_url('/deskapp/tramites/update/' . $tramite['id']) ?>" class="btn btn-sm btn-primary">Ver</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No hay trámites estancados</td>
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
    </div>
</div>

<!-- Scripts -->
<script src="<?= $assets ?>/src/plugins/apexcharts/apexcharts.min.js"></script>
<script>
// Embudo de conversión
<?php if (isset($embudo)): ?>
var embudoOptions = {
    series: [{
        name: 'Trámites',
        data: [
            <?= $embudo['total_ingresados'] ?? 0 ?>,
            <?= $embudo['en_proceso'] ?? 0 ?>,
            <?= $embudo['concluidos'] ?? 0 ?>,
            <?= $embudo['facturados'] ?? 0 ?>,
            <?= $embudo['cobrados'] ?? 0 ?>
        ]
    }],
    chart: {
        type: 'bar',
        height: 400
    },
    plotOptions: {
        bar: {
            horizontal: true,
            distributed: true
        }
    },
    colors: ['#1b00ff', '#00e091', '#ffc107', '#17a2b8', '#28a745'],
    dataLabels: {
        enabled: true,
        formatter: function(val, opts) {
            var pct = <?= json_encode([
                100,
                $embudo['pct_en_proceso'] ?? 0,
                $embudo['pct_concluidos'] ?? 0,
                $embudo['pct_facturados'] ?? 0,
                $embudo['pct_cobrados'] ?? 0
            ]) ?>[opts.dataPointIndex];
            return val + " (" + pct + "%)";
        }
    },
    xaxis: {
        categories: ['Ingresados', 'En Proceso', 'Concluidos', 'Facturados', 'Cobrados']
    },
    legend: {
        show: false
    }
};
var embudoChart = new ApexCharts(document.querySelector("#embudoChart"), embudoOptions);
embudoChart.render();
<?php endif; ?>

// Distribución por estado
<?php if (isset($distribucion_estados)): ?>
var estadosOptions = {
    series: [<?= implode(',', array_column($distribucion_estados, 'cantidad')) ?>],
    chart: {
        type: 'donut',
        height: 400
    },
    labels: [<?= "'" . implode("','", array_column($distribucion_estados, 'tra_status')) . "'" ?>],
    colors: ['#1b00ff', '#00e091', '#ffc107', '#17a2b8', '#dc3545'],
    legend: {
        position: 'bottom'
    },
    dataLabels: {
        enabled: true,
        formatter: function(val) {
            return val.toFixed(1) + "%";
        }
    }
};
var estadosChart = new ApexCharts(document.querySelector("#estadosChart"), estadosOptions);
estadosChart.render();
<?php endif; ?>

// Función para cargar métricas dinámicamente
function cargarMetricas(periodo) {
    // Aquí se puede implementar AJAX para actualizar las métricas sin recargar
    window.location.href = '<?= base_url('/deskapp/dashboardadmin') ?>?periodo=' + periodo +
        '<?= isset($anio_seleccionado) ? ('&anio=' . (int)$anio_seleccionado) : '' ?>' +
        '<?= !empty($cliente_id_filtro) ? ('&cliente_id=' . (int)$cliente_id_filtro) : '' ?>';
}

// Auto-refresh cada 5 minutos
setInterval(function() {
    location.reload();
}, 300000);
</script>

<?= $this->endSection() ?>
