<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte anual <?= esc($anio ?? date('Y')) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
            margin: 24px;
            font-size: 12px;
        }

        h1, h2 {
            margin: 0 0 10px;
            color: #0f172a;
        }

        .meta {
            margin-bottom: 18px;
            line-height: 1.5;
        }

        .section {
            margin-top: 28px;
            page-break-inside: avoid;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #e5eef9;
            color: #0f172a;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        @media print {
            body {
                margin: 12px;
            }
        }
    </style>
</head>
<body>
    <?php
        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        $tramitesMap = [];
        foreach (($tramites_por_mes ?? []) as $item) {
            $tramitesMap[(int) ($item['mes'] ?? 0)] = $item;
        }
        $ingresosMap = [];
        foreach (($ingresos_por_mes ?? []) as $item) {
            $ingresosMap[(int) ($item['mes'] ?? 0)] = $item;
        }
    ?>

    <h1>Reporte anual de dashboard administrativo</h1>
    <div class="meta">
        <div><strong>Año:</strong> <?= esc((string) ($anio ?? date('Y'))) ?></div>
        <div><strong>Cliente:</strong> <?= esc((string) ($cliente_nombre ?? 'Todos los clientes')) ?></div>
        <div><strong>Generado:</strong> <?= esc((string) ($generated_at ?? date('Y-m-d H:i:s'))) ?></div>
        <div><strong>Nota:</strong> esta vista está optimizada para Imprimir o Guardar como PDF desde el navegador.</div>
    </div>

    <div class="section">
        <h2>Resumen mensual</h2>
        <table>
            <thead>
                <tr>
                    <th>Mes</th>
                    <th class="text-center">Ingresados</th>
                    <th class="text-center">Concluidos</th>
                    <th class="text-center">Cobrados</th>
                    <th class="text-right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthNames as $monthNumber => $monthName): ?>
                    <?php $tramites = $tramitesMap[$monthNumber] ?? []; ?>
                    <?php $ingresos = $ingresosMap[$monthNumber] ?? []; ?>
                    <tr>
                        <td><?= esc($monthName) ?></td>
                        <td class="text-center"><?= (int) ($tramites['total_ingresados'] ?? 0) ?></td>
                        <td class="text-center"><?= (int) ($tramites['total_concluidos'] ?? 0) ?></td>
                        <td class="text-center"><?= (int) ($tramites['total_cobrados'] ?? 0) ?></td>
                        <td class="text-right">$<?= number_format((float) ($ingresos['ingresos'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Análisis por tipo de trámite</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Concluidos</th>
                    <th class="text-center">% Éxito</th>
                    <th class="text-center">Tiempo promedio</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tramites_por_tipo)): ?>
                    <?php foreach ($tramites_por_tipo as $tipo): ?>
                        <?php
                            $cantidad = (int) ($tipo['cantidad'] ?? 0);
                            $concluidos = (int) ($tipo['concluidos'] ?? 0);
                            $porcentaje = $cantidad > 0 ? round(($concluidos / $cantidad) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?= esc((string) ($tipo['tipo_tramite'] ?? 'Sin tipo')) ?></td>
                            <td class="text-center"><?= $cantidad ?></td>
                            <td class="text-center"><?= $concluidos ?></td>
                            <td class="text-center"><?= $porcentaje ?>%</td>
                            <td class="text-center"><?= isset($tipo['tiempo_promedio']) && $tipo['tiempo_promedio'] !== null ? round((float) $tipo['tiempo_promedio'], 1) . ' días' : 'N/A' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay datos disponibles</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Top ejecutivos</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ejecutivo</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Concluidos</th>
                    <th class="text-center">Cobrados</th>
                    <th class="text-right">Monto cobrado</th>
                    <th class="text-center">Tiempo promedio</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($top_ejecutivos)): ?>
                    <?php foreach ($top_ejecutivos as $index => $ejecutivo): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= esc((string) ($ejecutivo['ejecutivo'] ?? 'Sin ejecutivo')) ?></td>
                            <td class="text-center"><?= (int) ($ejecutivo['total_tramites'] ?? 0) ?></td>
                            <td class="text-center"><?= (int) ($ejecutivo['tramites_concluidos'] ?? 0) ?></td>
                            <td class="text-center"><?= (int) ($ejecutivo['tramites_cobrados'] ?? 0) ?></td>
                            <td class="text-right">$<?= number_format((float) ($ejecutivo['monto_cobrado'] ?? 0), 2) ?></td>
                            <td class="text-center"><?= isset($ejecutivo['tiempo_promedio_dias']) && $ejecutivo['tiempo_promedio_dias'] !== null ? round((float) $ejecutivo['tiempo_promedio_dias'], 1) . ' días' : 'N/A' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay datos disponibles</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Top gestores</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Gestor</th>
                    <th>Empresa</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Concluidos</th>
                    <th class="text-center">Tiempo promedio</th>
                    <th class="text-right">Pagado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($top_gestores)): ?>
                    <?php foreach ($top_gestores as $index => $gestor): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= esc((string) ($gestor['gestor'] ?? 'Sin gestor')) ?></td>
                            <td><?= esc((string) ($gestor['empresa_gestora'] ?? 'Sin empresa')) ?></td>
                            <td class="text-center"><?= (int) ($gestor['total_tramites'] ?? 0) ?></td>
                            <td class="text-center"><?= (int) ($gestor['tramites_concluidos'] ?? 0) ?></td>
                            <td class="text-center"><?= isset($gestor['tiempo_promedio_dias']) && $gestor['tiempo_promedio_dias'] !== null ? round((float) $gestor['tiempo_promedio_dias'], 1) . ' días' : 'N/A' ?></td>
                            <td class="text-right">$<?= number_format((float) ($gestor['total_pagado_gestor'] ?? 0), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay datos disponibles</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>