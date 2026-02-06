<!DOCTYPE html>
<html>
<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8">
    <title>Listado de Trámites - Admin SGL</title>

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
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/style.css">

    <style>
        .filters-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filter-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: #333;
        }

        .btn-filter {
            padding: 10px 25px;
            border-radius: 5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 28px;
        }

        .stat-icon.blue {
            background: #e3f2fd;
            color: #007bff;
        }

        .stat-icon.green {
            background: #e8f5e9;
            color: #28a745;
        }

        .stat-icon.yellow {
            background: #fff3e0;
            color: #ffc107;
        }

        .stat-icon.red {
            background: #ffebee;
            color: #dc3545;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-proceso {
            background: #fff3cd;
            color: #856404;
        }

        .badge-concluido {
            background: #d4edda;
            color: #155724;
        }

        .badge-cancelado {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-dias {
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
        }

        .table-actions {
            display: flex;
            gap: 5px;
        }

        .btn-icon {
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-icon:hover {
            transform: translateY(-2px);
        }

        .btn-view {
            background: #007bff;
            color: white;
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <?php 
        echo view('deskapp/includes/_header');
        echo view('deskapp/includes/_sidebar');
    ?>

    <div class="main-container">
        <div class="pd-ltr-20">
            <div class="page-header mb-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-0">Listado de Trámites</h2>
                        <p class="text-muted">Gestión completa de trámites</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="<?= base_url() ?>/deskapp/tramitewizard" class="btn btn-primary">
                            <i class="icon-copy fa fa-plus"></i> Nuevo Trámite
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="icon-copy fa fa-list"></i>
                    </div>
                    <div class="stat-value"><?= count($tramites) ?></div>
                    <div class="stat-label">Total Trámites</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon yellow">
                        <i class="icon-copy fa fa-clock-o"></i>
                    </div>
                    <div class="stat-value">
                        <?= count(array_filter($tramites, function($t) { return $t['status'] == 'En Proceso'; })) ?>
                    </div>
                    <div class="stat-label">En Proceso</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="icon-copy fa fa-check"></i>
                    </div>
                    <div class="stat-value">
                        <?= count(array_filter($tramites, function($t) { return $t['status'] == 'Concluido'; })) ?>
                    </div>
                    <div class="stat-label">Concluidos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="icon-copy fa fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">
                        <?= count(array_filter($tramites, function($t) { return $t['dias_desde_creacion'] > 10; })) ?>
                    </div>
                    <div class="stat-label">Urgentes</div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters-card">
                <h4 class="mb-20"><i class="icon-copy fa fa-filter"></i> Filtros de Búsqueda</h4>
                <form id="filterForm" method="GET">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" 
                                   value="<?= $_GET['fecha_inicio'] ?? date('Y-01-01') ?>">
                        </div>
                        <div class="filter-group">
                            <label>Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" 
                                   value="<?= $_GET['fecha_fin'] ?? date('Y-m-d') ?>">
                        </div>
                        <div class="filter-group">
                            <label>Status</label>
                            <select class="form-control" name="status_id">
                                <option value="">Todos</option>
                                <option value="22">En Proceso</option>
                                <option value="20">Concluido</option>
                                <option value="21">Cancelado</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="btn-filter btn-primary">
                                <i class="icon-copy fa fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Botones de Acción -->
            <div class="action-buttons">
                <a href="<?= base_url() ?>/deskapp/tramitewizard/exportar_excel?<?= http_build_query($_GET) ?>" 
                   class="btn-filter btn-success">
                    <i class="icon-copy fa fa-file-excel-o"></i> Exportar a Excel
                </a>
                <button type="button" class="btn-filter btn-secondary" onclick="window.print()">
                    <i class="icon-copy fa fa-print"></i> Imprimir
                </button>
            </div>

            <!-- Tabla de Trámites -->
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h4 class="text-blue h4">Trámites Registrados</h4>
                </div>
                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap" id="tramitesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Folio</th>
                                <th>Contrato</th>
                                <th>Serie</th>
                                <th>Tipo</th>
                                <th>Cliente</th>
                                <th>Gestor</th>
                                <th>Status</th>
                                <th>Días</th>
                                <th>Responsable</th>
                                <th>Fecha</th>
                                <th class="datatable-nosort">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tramites as $tramite): ?>
                            <tr>
                                <td><?= $tramite['id'] ?></td>
                                <td><strong><?= $tramite['folio'] ?></strong></td>
                                <td><?= $tramite['contrato'] ?></td>
                                <td><?= $tramite['serie'] ?></td>
                                <td><?= $tramite['tipo_tramite'] ?></td>
                                <td><?= $tramite['cliente'] ?></td>
                                <td><?= $tramite['gestor'] ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-proceso';
                                    if ($tramite['status'] == 'Concluido') $badgeClass = 'badge-concluido';
                                    if ($tramite['status'] == 'Cancelado') $badgeClass = 'badge-cancelado';
                                    ?>
                                    <span class="badge-status <?= $badgeClass ?>">
                                        <?= $tramite['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $dias = $tramite['dias_desde_creacion'];
                                    $colorClass = 'background-verde';
                                    if ($dias >= 5 && $dias < 8) $colorClass = 'background-amarillo';
                                    if ($dias >= 8 && $dias < 12) $colorClass = 'background-rojo';
                                    if ($dias >= 12) $colorClass = 'background-violeta';
                                    ?>
                                    <span class="badge-dias <?= $colorClass ?>">
                                        <?= $dias ?> días
                                    </span>
                                </td>
                                <td><?= $tramite['firstname'] ?> <?= $tramite['lastname'] ?></td>
                                <td><?= date('d/m/Y', strtotime($tramite['created_at'])) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn-icon btn-view" 
                                                onclick="verTramite(<?= $tramite['id'] ?>)" 
                                                title="Ver detalle">
                                            <i class="icon-copy fa fa-eye"></i>
                                        </button>
                                        <button class="btn-icon btn-edit" 
                                                onclick="editarTramite(<?= $tramite['id'] ?>)" 
                                                title="Editar">
                                            <i class="icon-copy fa fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
    <script src="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
    <script src="<?php echo base_url(); ?>/public/assets/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.data-table').DataTable({
                scrollCollapse: true,
                autoWidth: false,
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                },
                columnDefs: [{
                    targets: "datatable-nosort",
                    orderable: false,
                }],
                order: [[0, 'desc']]
            });
        });

        function verTramite(id) {
            window.location.href = `<?= base_url() ?>/deskapp/tramites/update/${id}`;
        }

        function editarTramite(id) {
            window.location.href = `<?= base_url() ?>/deskapp/tramites/update/${id}`;
        }

        // Auto-submit en cambio de filtros
        document.querySelectorAll('#filterForm select').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });
    </script>
</body>
</html>
