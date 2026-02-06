<!DOCTYPE html>
<html>
<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8">
    <title>Detalle Trámites por Cliente - Admin SGL</title>

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
        .background-verde { background-color: #28a745; color: white; }
        .background-amarillo { background-color: #ffc107; color: black; }
        .background-rojo { background-color: #dc3545; color: white; }
        .background-violeta { background-color: #6f42c1; color: white; }
        .background-gris { background-color: #6c757d; color: white; }
        .background-azul-claro { background-color: #17a2b8; color: white; }
        .background-azul { background-color: #007bff; color: white; }
        
        .badge-dias {
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        
        .tabla-tramites {
            font-size: 14px;
        }
        
        .tabla-tramites th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center;
        }
        
        .tabla-tramites td {
            vertical-align: middle;
            text-align: center;
        }
        
        .info-box {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .info-box .label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-box .value {
            font-size: 24px;
            font-weight: bold;
        }
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
                        <div class="col-md-8 col-sm-12">
                            <div class="title">
                                <h4>Detalle de Trámites - <?= esc($nombre_cliente) ?></h4>
                            </div>
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="/deskapp/dashboardadmin">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="/deskapp/dashboardadmin/por_cliente?anio=<?= $anio_seleccionado ?>">Por Cliente</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"><?= esc($nombre_cliente) ?></li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-md-4 col-sm-12 text-right">
                            <form method="GET" class="form-inline justify-content-end">
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

                <!-- Resumen del Cliente -->
                <div class="row mb-30">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box" style="background-color: #e3f2fd;">
                            <div class="label">Total Trámites</div>
                            <div class="value text-primary"><?= count($tramites) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box" style="background-color: #e8f5e9;">
                            <div class="label">Concluidos</div>
                            <div class="value text-success">
                                <?= count(array_filter($tramites, function($t) { return $t['tra_status_id'] == 20; })) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box" style="background-color: #fff3e0;">
                            <div class="label">En Proceso</div>
                            <div class="value text-warning">
                                <?= count(array_filter($tramites, function($t) { return !in_array($t['tra_status_id'], [20, 21]); })) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box" style="background-color: #f3e5f5;">
                            <div class="label">Cancelados</div>
                            <div class="value text-secondary">
                                <?= count(array_filter($tramites, function($t) { return $t['tra_status_id'] == 21; })) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leyenda de Colores -->
                <div class="row mb-20">
                    <div class="col-12">
                        <div class="card-box">
                            <h5 class="mb-15">Leyenda de Colores</h5>
                            <div class="row">
                                <div class="col-md-2 col-sm-4 col-6 mb-2">
                                    <span class="badge background-verde">Verde</span>
                                    <small class="d-block">Dentro de tiempo</small>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 mb-2">
                                    <span class="badge background-amarillo">Amarillo</span>
                                    <small class="d-block">Por vencer</small>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 mb-2">
                                    <span class="badge background-rojo">Rojo</span>
                                    <small class="d-block">Retrasado</small>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 mb-2">
                                    <span class="badge background-violeta">Violeta</span>
                                    <small class="d-block">Muy retrasado</small>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 mb-2">
                                    <span class="badge background-azul">Azul</span>
                                    <small class="d-block">Concluido</small>
                                </div>
                                <div class="col-md-2 col-sm-4 col-6 mb-2">
                                    <span class="badge background-gris">Gris</span>
                                    <small class="d-block">Cancelado</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Trámites -->
                <div class="row">
                    <div class="col-12">
                        <div class="card-box mb-30">
                            <h5 class="mb-20">Listado de Trámites</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover tabla-tramites">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Folio</th>
                                            <th>Contrato</th>
                                            <th>Unidad</th>
                                            <th>Serie</th>
                                            <th>Placas</th>
                                            <th>Tipo</th>
                                            <th>Cliente Directo</th>
                                            <th>Ejecutivo</th>
                                            <th>Status</th>
                                            <th>Cobro</th>
                                            <th>Días</th>
                                            <th>Creado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($tramites)): ?>
                                        <tr>
                                            <td colspan="14" class="text-center">
                                                <i class="icon-copy fa fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                                                <p class="text-muted mt-2">No hay trámites para mostrar</p>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($tramites as $tramite): ?>
                                            <tr>
                                                <td><?= $tramite['id'] ?></td>
                                                <td><?= esc($tramite['folio']) ?></td>
                                                <td><?= esc($tramite['contrato']) ?></td>
                                                <td><?= esc($tramite['unidad']) ?></td>
                                                <td><?= esc($tramite['serie']) ?></td>
                                                <td><?= esc($tramite['placas']) ?></td>
                                                <td>
                                                    <small><?= esc($tramite['tipo_tramite']) ?></small>
                                                </td>
                                                <td>
                                                    <small><?= esc($tramite['cliente_directo']) ?></small>
                                                </td>
                                                <td>
                                                    <small><?= esc($tramite['ejecutivo']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary">
                                                        <?= esc($tramite['tra_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($tramite['cobro_status']): ?>
                                                        <span class="badge badge-info">
                                                            <?= esc($tramite['cobro_status']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($tramite['etiqueta_dias']): ?>
                                                        <span class="badge badge-dias <?= $tramite['clase_css'] ?>">
                                                            <?= $tramite['etiqueta_dias'] ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-dias <?= $tramite['clase_css'] ?>">
                                                            <?= esc($tramite['tra_status']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?= date('d/m/Y', strtotime($tramite['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <a href="/deskapp/tramites/update/<?= $tramite['id'] ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Ver detalle">
                                                        <i class="icon-copy fa fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón Regresar -->
                <div class="row">
                    <div class="col-12">
                        <a href="/deskapp/dashboardadmin/por_cliente?anio=<?= $anio_seleccionado ?>" class="btn btn-secondary">
                            <i class="icon-copy fa fa-arrow-left"></i> Regresar al Resumen
                        </a>
                    </div>
                </div>

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
