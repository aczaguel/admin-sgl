<!DOCTYPE html>
<html>
<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8">
    <title>Trámites por Cliente - Admin SGL</title>

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

    <style>
        .cliente-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .cliente-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .metric-box {
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin: 5px 0;
        }
        .metric-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
        }
        .background-verde { background-color: #28a745; color: white; }
        .background-amarillo { background-color: #ffc107; color: black; }
        .background-rojo { background-color: #dc3545; color: white; }
        .background-violeta { background-color: #6f42c1; color: white; }
        .background-azul { background-color: #007bff; color: white; }
        .background-gris { background-color: #6c757d; color: white; }
    </style>
</head>
<body>
    <?php 
        echo view('deskapp/includes/_header');
        echo view('deskapp/includes/_sidebar');
    ?>

    <div class="main-container">
        <div class="pd-ltr-20 xs-pd-20-10">
            <div class="min-height-200px">
                
                <!-- Encabezado -->
                <div class="page-header">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="title">
                                <h4>Trámites por Cliente</h4>
                            </div>
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="/deskapp/dashboardadmin">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Por Cliente</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-md-6 col-sm-12 text-right">
                            <form method="GET" action="/deskapp/dashboardadmin/por_cliente" class="form-inline justify-content-end">
                                <label class="mr-2">Año:</label>
                                <select name="anio" class="form-control mr-2" onchange="this.form.submit()">
                                    <?php for ($i = 2024; $i <= date('Y'); $i++): ?>
                                        <option value="<?= $i ?>" <?= ($i == $anio_seleccionado) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Resumen General -->
                <div class="row mb-30">
                    <div class="col-12">
                        <div class="card-box">
                            <h5 class="mb-20">Resumen General <?= $anio_seleccionado ?></h5>
                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <div class="metric-box" style="background-color: #e3f2fd;">
                                        <div class="metric-label">Total Clientes</div>
                                        <div class="metric-value"><?= count($tramites_por_cliente) ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="metric-box" style="background-color: #e8f5e9;">
                                        <div class="metric-label">Total Trámites</div>
                                        <div class="metric-value"><?= array_sum(array_column($tramites_por_cliente, 'total_tramites')) ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="metric-box" style="background-color: #fff3e0;">
                                        <div class="metric-label">Monto Cobrado</div>
                                        <div class="metric-value">$<?= number_format(array_sum(array_column($tramites_por_cliente, 'monto_cobrado')), 2) ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="metric-box" style="background-color: #fce4ec;">
                                        <div class="metric-label">Monto Pendiente</div>
                                        <div class="metric-value">$<?= number_format(array_sum(array_column($tramites_por_cliente, 'monto_pendiente')), 2) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards por Cliente -->
                <div class="row">
                    <?php foreach ($tramites_por_cliente as $cliente): ?>
                    <div class="col-lg-6 col-md-12 col-sm-12 mb-30">
                        <div class="card-box cliente-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="icon-copy dw dw-building"></i>
                                    <?= esc($cliente['cliente']) ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Total Trámites -->
                                    <div class="col-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-primary text-white mr-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="icon-copy fa fa-file-text" style="font-size: 24px;"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Total</div>
                                                <div class="h4 mb-0"><?= $cliente['total_tramites'] ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- En Proceso -->
                                    <div class="col-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-warning text-white mr-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="icon-copy fa fa-clock-o" style="font-size: 24px;"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">En Proceso</div>
                                                <div class="h4 mb-0"><?= $cliente['en_proceso'] ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Concluidos -->
                                    <div class="col-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-success text-white mr-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="icon-copy fa fa-check-circle" style="font-size: 24px;"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Concluidos</div>
                                                <div class="h4 mb-0"><?= $cliente['concluidos'] ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cancelados -->
                                    <div class="col-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-secondary text-white mr-3" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="icon-copy fa fa-times-circle" style="font-size: 24px;"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Cancelados</div>
                                                <div class="h4 mb-0"><?= $cliente['cancelados'] ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Información Financiera -->
                                <div class="row">
                                    <div class="col-12 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Cobrados:</span>
                                            <span class="badge badge-success"><?= $cliente['cobrados'] ?> trámites</span>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Monto Cobrado:</span>
                                            <span class="font-weight-bold text-success">$<?= number_format($cliente['monto_cobrado'], 2) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Monto Pendiente:</span>
                                            <span class="font-weight-bold text-warning">$<?= number_format($cliente['monto_pendiente'], 2) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Tiempo Promedio:</span>
                                            <span class="badge badge-info"><?= number_format($cliente['tiempo_promedio_dias'], 1) ?> días</span>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Botón Ver Detalle -->
                                <div class="text-center">
                                    <a href="/deskapp/dashboardadmin/detalle_cliente/<?= $cliente['cliente_id'] ?>?anio=<?= $anio_seleccionado ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="icon-copy fa fa-eye"></i> Ver Detalle Completo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($tramites_por_cliente)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card-box text-center">
                            <i class="icon-copy fa fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                            <h5 class="mt-3">No hay datos para mostrar</h5>
                            <p class="text-muted">No se encontraron trámites para el año <?= $anio_seleccionado ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            <?php echo view('deskapp/includes/_footer'); ?>
        </div>
    </div>

    <!-- js -->
    <script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/core.js"></script>
    <script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/script.min.js"></script>
    <script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/process.js"></script>
    <script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/layout-settings.js"></script>
</body>
</html>
