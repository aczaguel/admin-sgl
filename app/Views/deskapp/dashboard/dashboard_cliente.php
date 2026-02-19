<?= $this->extend('layout/main') ?>

<?php $assets = base_url('/public/assets'); ?>

<?= $this->section('additional_css') ?>
<style>
    :root {
        --cd-ink: #1f2933;
        --cd-muted: #6b7280;
        --cd-surface: #ffffff;
        --cd-panel: #f3efe7;
        --cd-accent: #0f766e;
        --cd-accent-2: #f97316;
        --cd-accent-3: #2563eb;
        --cd-border: #e7e1d6;
        --cd-shadow: 0 14px 32px rgba(15, 23, 42, 0.12);
    }

    .client-dashboard {
        background: radial-gradient(circle at top right, #f7efe1 0%, #f9fafb 55%, #ffffff 100%);
        min-height: 100vh;
    }

    .client-dashboard .page-header {
        border-radius: 18px;
        background: linear-gradient(120deg, #ffffff 0%, #fdf6ea 100%);
        border: 1px solid var(--cd-border);
        box-shadow: var(--cd-shadow);
        padding: 18px 20px;
    }

    .client-dashboard .title h4 {
        font-family: "Poppins", "Montserrat", sans-serif;
        color: var(--cd-ink);
        letter-spacing: 0.3px;
    }

    .client-dashboard .subtitle {
        color: var(--cd-muted);
        font-size: 13px;
        margin-top: 6px;
    }

    .client-dashboard .card-box,
    .client-dashboard .widget-style3 {
        border: 1px solid var(--cd-border);
        border-radius: 16px;
        background: var(--cd-surface);
        box-shadow: var(--cd-shadow);
    }

    .client-dashboard .filters-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid var(--cd-border);
        padding: 14px 18px;
    }

    .client-dashboard label {
        font-weight: 600;
        color: var(--cd-ink);
        font-size: 12px;
    }

    .client-dashboard .form-control {
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }

    .client-dashboard .badge-pill {
        border-radius: 999px;
        padding: 6px 12px;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .client-dashboard .summary-tile {
        background: linear-gradient(135deg, #ffffff 0%, #f8f5ef 100%);
        border-radius: 14px;
        border: 1px solid var(--cd-border);
        padding: 16px;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .client-dashboard .summary-tile::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--cd-accent), var(--cd-accent-3));
        opacity: 0.9;
    }

    .client-dashboard .summary-tile.is-blue::before { background: linear-gradient(90deg, var(--cd-accent-3), var(--cd-accent)); }
    .client-dashboard .summary-tile.is-green::before { background: linear-gradient(90deg, var(--cd-accent), var(--cd-accent-3)); }
    .client-dashboard .summary-tile.is-orange::before { background: linear-gradient(90deg, var(--cd-accent-2), var(--cd-accent)); }

    .client-dashboard .summary-tile .tile-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .client-dashboard .summary-tile .tile-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        background: rgba(15, 118, 110, 0.10);
        color: var(--cd-accent);
    }

    .client-dashboard .summary-tile.is-blue .tile-icon {
        background: rgba(37, 99, 235, 0.10);
        color: var(--cd-accent-3);
    }

    .client-dashboard .summary-tile.is-orange .tile-icon {
        background: rgba(249, 115, 22, 0.10);
        color: var(--cd-accent-2);
    }

    .client-dashboard .summary-tile h3 {
        margin: 0;
        font-size: 22px;
        color: var(--cd-ink);
        font-weight: 700;
    }

    .client-dashboard .summary-tile span {
        font-size: 12px;
        color: var(--cd-muted);
    }

    .client-dashboard .tile-meta {
        margin-top: 10px;
        font-size: 12px;
        color: var(--cd-muted);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .client-dashboard .tile-meta strong {
        color: var(--cd-ink);
        font-weight: 800;
    }

    .client-dashboard .tile-kpi {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--cd-border);
        border-radius: 999px;
        padding: 2px 10px;
        background: rgba(255, 255, 255, 0.7);
    }

    .client-dashboard .filter-chips {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
    }

    .client-dashboard .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid var(--cd-border);
        background: rgba(255, 255, 255, 0.75);
        color: var(--cd-ink);
    }

    .client-dashboard .filter-chip.is-muted {
        color: var(--cd-muted);
        font-weight: 600;
    }

    .client-dashboard .live-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.12);
        color: var(--cd-accent);
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
    }

    .client-dashboard .live-chip.is-loading {
        background: rgba(37, 99, 235, 0.10);
        color: var(--cd-accent-3);
    }

    .client-dashboard .live-chip.is-error {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }

    .client-dashboard .table thead th {
        background: var(--cd-panel);
        color: var(--cd-ink);
        border-bottom: 1px solid var(--cd-border);
    }

    .client-dashboard .table td:last-child,
    .client-dashboard .table th:last-child {
        text-align: right;
        white-space: nowrap;
        font-weight: 700;
        color: var(--cd-ink);
    }

    .client-dashboard .table td:first-child,
    .client-dashboard .table th:first-child {
        width: 1%;
        white-space: nowrap;
        color: var(--cd-muted);
        font-weight: 700;
    }

    .client-dashboard .table tbody tr:hover {
        background: rgba(15, 118, 110, 0.04);
    }

    .client-dashboard .semaforo-badge {
        border-radius: 999px;
        padding: 6px 10px;
        font-weight: 700;
        font-size: 12px;
        color: #fff;
    }

    .client-dashboard .semaforo-verde { background: #22c55e; }
    .client-dashboard .semaforo-amarillo { background: #f59e0b; }
    .client-dashboard .semaforo-rojo { background: #ef4444; }
    .client-dashboard .semaforo-violeta { background: #7c3aed; }

    .client-dashboard .refresh-note {
        font-size: 11px;
        color: var(--cd-muted);
    }

    .client-dashboard .date-range {
        display: flex;
        gap: 8px;
    }

    .client-dashboard .filters-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .client-dashboard .section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
    }

    .client-dashboard .section-title h4,
    .client-dashboard .section-title h5 {
        margin: 0;
    }

    .client-dashboard .section-subtitle {
        font-size: 12px;
        color: var(--cd-muted);
        margin-top: 6px;
    }

    .client-dashboard .period-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .client-dashboard .period-tile {
        border: 1px solid var(--cd-border);
        border-radius: 14px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f5ef 100%);
        padding: 14px;
        position: relative;
        overflow: hidden;
    }

    .client-dashboard .period-tile::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--cd-accent), var(--cd-accent-2));
        opacity: 0.9;
    }

    .client-dashboard .period-kicker {
        font-size: 12px;
        color: var(--cd-muted);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .client-dashboard .period-value {
        font-size: 26px;
        font-weight: 900;
        color: var(--cd-ink);
        margin-top: 8px;
        letter-spacing: 0.2px;
    }

    .client-dashboard .chart-wrap {
        border: 1px solid var(--cd-border);
        border-radius: 14px;
        background: #ffffff;
        padding: 12px;
    }

    .client-dashboard .chart-box {
        height: 320px;
    }

    @media (max-width: 992px) {
        .client-dashboard .period-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 576px) {
        .client-dashboard .period-grid { grid-template-columns: 1fr; }
    }

    .client-dashboard .empty-state {
        padding: 18px;
        border: 1px dashed var(--cd-border);
        border-radius: 12px;
        color: var(--cd-muted);
        text-align: center;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="main-container client-dashboard">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header mb-20">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="title">
                            <h4>Dashboard Cliente</h4>
                        </div>
                        <div class="subtitle">Seguimiento en tiempo real de tus trámites y facturas pendientes.</div>
                    </div>
                    <div class="col-md-5 text-right">
                        <span class="live-chip is-loading" id="lastUpdate"><i class="fas fa-sync"></i> Actualizando...</span>
                    </div>
                </div>
            </div>

            <div class="filters-card mb-20">
                <form id="clienteDashboardFilters" class="row">
                    <div class="col-md-3 mb-2">
                        <label>Cliente Directo</label>
                        <select class="form-control" name="cli_directo_id">
                            <option value="">Todos</option>
                            <?php foreach ($cli_directo_list as $cliente): ?>
                                <?php $selected = (!empty($filters['cli_directo_id']) && (int) $filters['cli_directo_id'] === (int) $cliente['id']) ? 'selected' : ''; ?>
                                <option value="<?= (int) $cliente['id'] ?>" <?= $selected ?>>
                                    <?= esc($cliente['razon_social'] ?? 'Cliente Directo') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Tipo de tramite</label>
                        <select class="form-control" name="tra_tipos_id">
                            <option value="">Todos</option>
                            <?php foreach ($tipos_list as $tipo): ?>
                                <?php $selected = (!empty($filters['tra_tipos_id']) && (int) $filters['tra_tipos_id'] === (int) $tipo['id']) ? 'selected' : ''; ?>
                                <option value="<?= (int) $tipo['id'] ?>" <?= $selected ?>>
                                    <?= esc($tipo['tipo_tramite']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Estatus</label>
                        <select class="form-control" name="tra_status_id">
                            <option value="">Todos</option>
                            <?php foreach ($status_list as $status): ?>
                                <?php $selected = (!empty($filters['tra_status_id']) && (int) $filters['tra_status_id'] === (int) $status['id']) ? 'selected' : ''; ?>
                                <option value="<?= (int) $status['id'] ?>" <?= $selected ?>>
                                    <?= esc($status['tra_status']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Pendiente de pago</label>
                        <select class="form-control" name="pendiente_pago">
                            <option value="">Todos</option>
                            <option value="1" <?= ($filters['pendiente_pago'] ?? '') === '1' ? 'selected' : '' ?>>Solo pendientes</option>
                            <option value="0" <?= ($filters['pendiente_pago'] ?? '') === '0' ? 'selected' : '' ?>>Sin pendientes</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Rango de fechas</label>
                        <div class="date-range">
                            <input type="date" name="fecha_inicio" class="form-control" value="<?= esc($filters['fecha_inicio'] ?? '') ?>">
                            <input type="date" name="fecha_fin" class="form-control" value="<?= esc($filters['fecha_fin'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-12 mt-2">
                        <div class="filter-chips" id="activeFilters">
                            <span class="filter-chip is-muted"><i class="fas fa-sliders-h"></i> Sin filtros</span>
                        </div>
                    </div>
                    <div class="col-md-12 text-right mt-2">
                        <div class="filters-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Aplicar filtros</button>
                            <button type="button" class="btn btn-outline-secondary" id="resetFilters"><i class="fas fa-undo"></i> Limpiar</button>
                            <span class="refresh-note">Auto actualiza cada 5 minutos</span>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row">
                <div class="col-md-3 mb-20">
                    <div class="summary-tile is-blue">
                        <div class="tile-row">
                            <div>
                                <span>Total trámites</span>
                                <h3 id="resumenTotal">0</h3>
                                <div class="tile-meta">
                                    <span class="tile-kpi"><i class="fas fa-ban"></i> Cancelados: <strong id="resumenCancelados">0</strong></span>
                                    <span class="tile-kpi"><i class="fas fa-clock"></i> En proceso: <strong id="resumenProcesoMini">0</strong></span>
                                </div>
                            </div>
                            <div class="tile-icon"><i class="fas fa-layer-group"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-20">
                    <div class="summary-tile is-green">
                        <div class="tile-row">
                            <div>
                                <span>En proceso</span>
                                <h3 id="resumenProceso">0</h3>
                                <div class="tile-meta">
                                    <span class="tile-kpi"><i class="fas fa-percentage"></i> <strong id="resumenProcesoPct">0%</strong> del total</span>
                                </div>
                            </div>
                            <div class="tile-icon"><i class="fas fa-spinner"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-20">
                    <div class="summary-tile is-green">
                        <div class="tile-row">
                            <div>
                                <span>Concluidos</span>
                                <h3 id="resumenConcluidos">0</h3>
                                <div class="tile-meta">
                                    <span class="tile-kpi"><i class="fas fa-percentage"></i> <strong id="resumenConcluidosPct">0%</strong> del total</span>
                                </div>
                            </div>
                            <div class="tile-icon"><i class="fas fa-check"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-20">
                    <div class="summary-tile is-orange">
                        <div class="tile-row">
                            <div>
                                <span>Facturas pendientes</span>
                                <h3 id="facturasPendientes">0</h3>
                                <span id="facturasMonto">$0</span>
                                <div class="tile-meta">
                                    <span class="tile-kpi"><i class="fas fa-receipt"></i> Monto total</span>
                                </div>
                            </div>
                            <div class="tile-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <div class="section-title">
                            <h4 class="h4 text-blue"><i class="fas fa-calendar-check"></i> Trámites concluidos</h4>
                            <span class="refresh-note">Se calcula por fecha de cierre</span>
                        </div>
                        <div class="period-grid">
                            <div class="period-tile">
                                <div class="period-kicker"><i class="fas fa-sun"></i> Hoy</div>
                                <div class="period-value" id="concluidosHoy">0</div>
                            </div>
                            <div class="period-tile">
                                <div class="period-kicker"><i class="fas fa-calendar-week"></i> Esta semana</div>
                                <div class="period-value" id="concluidosSemana">0</div>
                            </div>
                            <div class="period-tile">
                                <div class="period-kicker"><i class="fas fa-calendar-alt"></i> Este mes</div>
                                <div class="period-value" id="concluidosMes">0</div>
                            </div>
                            <div class="period-tile">
                                <div class="period-kicker"><i class="fas fa-calendar"></i> Este año</div>
                                <div class="period-value" id="concluidosAnio">0</div>
                            </div>
                        </div>
                        <div class="section-subtitle">Esta vista ayuda a medir cuántos trámites ya se cerraron en cada periodo.</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <div class="section-title">
                            <h4 class="h4 text-blue"><i class="fas fa-chart-bar"></i> Trámites por tipo</h4>
                            <span class="refresh-note">En proceso vs concluidos</span>
                        </div>
                        <div class="chart-wrap">
                            <div id="tipoTramiteClienteChart" class="chart-box"></div>
                            <div class="empty-state" id="emptyTipoTramiteChart" style="display:none;">Sin datos para los filtros actuales.</div>
                        </div>
                        <div class="section-subtitle">Comparativa por tipo de trámite (Top 10 por volumen).</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <div class="section-title">
                            <h4 class="h4 text-blue"><i class="icon-copy fa fa-traffic-light"></i> Semáforo de atención</h4>
                            <span class="refresh-note">Local vs foráneo</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-20">
                                <h6 class="text-muted mb-10">Locales (CDMX + EdoMex)</h6>
                                <div class="d-flex flex-wrap" style="gap: 10px;">
                                    <span class="semaforo-badge semaforo-verde">Verde: <span id="localVerde">0</span></span>
                                    <span class="semaforo-badge semaforo-amarillo">Amarillo: <span id="localAmarillo">0</span></span>
                                    <span class="semaforo-badge semaforo-rojo">Rojo: <span id="localRojo">0</span></span>
                                    <span class="semaforo-badge semaforo-violeta">Violeta: <span id="localVioleta">0</span></span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-20">
                                <h6 class="text-muted mb-10">Foraneos</h6>
                                <div class="d-flex flex-wrap" style="gap: 10px;">
                                    <span class="semaforo-badge semaforo-verde">Verde: <span id="foraneoVerde">0</span></span>
                                    <span class="semaforo-badge semaforo-amarillo">Amarillo: <span id="foraneoAmarillo">0</span></span>
                                    <span class="semaforo-badge semaforo-rojo">Rojo: <span id="foraneoRojo">0</span></span>
                                    <span class="semaforo-badge semaforo-violeta">Violeta: <span id="foraneoVioleta">0</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <div class="section-title">
                            <h5 class="text-blue h5">Sin movimiento &gt; 7 días</h5>
                            <span class="refresh-note">Por tipo</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm" id="tablaTipos">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tipo</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="empty-state" id="emptyTipos" style="display:none;">Sin datos para los filtros actuales.</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <div class="section-title">
                            <h5 class="text-blue h5">Sin movimiento &gt; 7 días</h5>
                            <span class="refresh-note">Por estado</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm" id="tablaEstados">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Estado</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="empty-state" id="emptyEstados" style="display:none;">Sin datos para los filtros actuales.</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-12 mb-30">
                    <div class="card-box pd-20">
                        <div class="section-title">
                            <h5 class="text-blue h5">Sin movimiento &gt; 7 días</h5>
                            <span class="refresh-note">Por cliente</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm" id="tablaClientes">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="empty-state" id="emptyClientes" style="display:none;">Sin datos para los filtros actuales.</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
<script src="<?= $assets ?>/src/plugins/apexcharts/apexcharts.min.js"></script>
<script>
    const dataUrl = '<?= site_url('/deskapp/clientes/dashboard_data') ?>';
    const filtersForm = document.getElementById('clienteDashboardFilters');
    const lastUpdate = document.getElementById('lastUpdate');
    const activeFilters = document.getElementById('activeFilters');
    const tipoTramiteChartEl = document.getElementById('tipoTramiteClienteChart');
    const emptyTipoTramiteChart = document.getElementById('emptyTipoTramiteChart');

    let tipoTramiteChart = null;

    function setChipState(state) {
        lastUpdate.classList.remove('is-loading', 'is-error');
        if (state === 'loading') {
            lastUpdate.classList.add('is-loading');
        }
        if (state === 'error') {
            lastUpdate.classList.add('is-error');
        }
    }

    function formatMoney(value) {
        const number = Number(value || 0);
        return number.toLocaleString('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 });
    }

    function safeInt(value) {
        const number = Number(value || 0);
        return Number.isFinite(number) ? Math.trunc(number) : 0;
    }

    function pct(part, total) {
        const p = safeInt(part);
        const t = safeInt(total);
        if (t <= 0) return 0;
        return Math.max(0, Math.min(100, Math.round((p / t) * 100)));
    }

    function getSelectedText(selectName) {
        const select = filtersForm.querySelector(`[name="${selectName}"]`);
        if (!select) return null;
        const option = select.options[select.selectedIndex];
        const value = select.value;
        if (!value) return null;
        return option ? option.textContent.trim() : null;
    }

    function getFieldValue(fieldName) {
        const el = filtersForm.querySelector(`[name="${fieldName}"]`);
        if (!el) return null;
        const value = (el.value || '').trim();
        return value !== '' ? value : null;
    }

    function formatIsoDate(value) {
        if (!value) return null;
        const raw = String(value).trim();
        if (!raw) return null;
        // Espera YYYY-MM-DD (inputs type=date)
        const date = new Date(`${raw}T00:00:00`);
        if (Number.isNaN(date.getTime())) return raw;
        return date.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatNow() {
        const now = new Date();
        return now.toLocaleString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function renderFilterChips() {
        if (!activeFilters) return;
        const chips = [];

        const cliente = getSelectedText('cli_directo_id');
        if (cliente) chips.push({ icon: 'fa-building', label: `Cliente: ${cliente}` });

        const tipo = getSelectedText('tra_tipos_id');
        if (tipo) chips.push({ icon: 'fa-tags', label: `Tipo: ${tipo}` });

        const estatus = getSelectedText('tra_status_id');
        if (estatus) chips.push({ icon: 'fa-clipboard-check', label: `Estatus: ${estatus}` });

        const pendientePago = getFieldValue('pendiente_pago');
        if (pendientePago === '1') chips.push({ icon: 'fa-money-bill-wave', label: 'Pendiente de pago: Sí' });
        if (pendientePago === '0') chips.push({ icon: 'fa-money-bill-wave', label: 'Pendiente de pago: No' });

        const fi = getFieldValue('fecha_inicio');
        const ff = getFieldValue('fecha_fin');
        if (fi || ff) {
            const rango = `${formatIsoDate(fi) || '...'} → ${formatIsoDate(ff) || '...'}`;
            chips.push({ icon: 'fa-calendar-alt', label: `Fechas: ${rango}` });
        }

        activeFilters.innerHTML = '';
        if (chips.length === 0) {
            activeFilters.innerHTML = '<span class="filter-chip is-muted"><i class="fas fa-sliders-h"></i> Sin filtros</span>';
            return;
        }

        chips.forEach((chip) => {
            const span = document.createElement('span');
            span.className = 'filter-chip';
            span.innerHTML = `<i class="fas ${chip.icon}"></i> ${chip.label}`;
            activeFilters.appendChild(span);
        });
    }

    function updateTable(targetId, emptyId, rows, keyLabel) {
        const tbody = document.querySelector(`#${targetId} tbody`);
        const empty = document.getElementById(emptyId);
        tbody.innerHTML = '';

        if (!rows || rows.length === 0) {
            empty.style.display = 'block';
            return;
        }

        empty.style.display = 'none';
        rows.forEach((row, index) => {
            const tr = document.createElement('tr');
            const label = row[keyLabel] || 'N/A';
            const total = row.total || 0;
            tr.innerHTML = `<td>${index + 1}</td><td>${label}</td><td>${total}</td>`;
            tbody.appendChild(tr);
        });
    }

    function ensureTipoTramiteChart() {
        if (!tipoTramiteChartEl) return null;
        if (typeof ApexCharts === 'undefined') return null;
        if (tipoTramiteChart) return tipoTramiteChart;

        const css = getComputedStyle(document.documentElement);
        const colorProceso = css.getPropertyValue('--cd-accent-3').trim() || '#2563eb';
        const colorConcluido = css.getPropertyValue('--cd-accent').trim() || '#0f766e';
        const ink = css.getPropertyValue('--cd-ink').trim() || '#1f2933';
        const muted = css.getPropertyValue('--cd-muted').trim() || '#6b7280';

        const options = {
            chart: {
                type: 'bar',
                height: 320,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif',
            },
            series: [
                { name: 'En proceso', data: [] },
                { name: 'Concluidos', data: [] },
            ],
            colors: [colorProceso, colorConcluido],
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    horizontal: true,
                    barHeight: '70%',
                },
            },
            dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(0,0,0,0.06)', strokeDashArray: 4 },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                labels: { colors: ink },
            },
            xaxis: {
                categories: [],
                labels: { style: { colors: muted } },
            },
            yaxis: {
                labels: { style: { colors: muted } },
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (val) {
                        return `${safeInt(val)} trámites`;
                    },
                },
            },
        };

        tipoTramiteChart = new ApexCharts(tipoTramiteChartEl, options);
        tipoTramiteChart.render();
        return tipoTramiteChart;
    }

    function renderTramitesPorTipoChart(rows) {
        if (!tipoTramiteChartEl) return;
        const chart = ensureTipoTramiteChart();

        const hasRows = Array.isArray(rows) && rows.length > 0;
        if (emptyTipoTramiteChart) {
            emptyTipoTramiteChart.style.display = hasRows ? 'none' : 'block';
        }
        tipoTramiteChartEl.style.display = hasRows ? 'block' : 'none';

        if (!hasRows || !chart) return;

        const categories = rows.map((r) => (r.tipo || 'Sin tipo'));
        const enProceso = rows.map((r) => safeInt(r.en_proceso || 0));
        const concluidos = rows.map((r) => safeInt(r.concluidos || 0));

        chart.updateOptions({ xaxis: { categories } }, false, true);
        chart.updateSeries([
            { name: 'En proceso', data: enProceso },
            { name: 'Concluidos', data: concluidos },
        ], true);
    }

    function updateDashboard(data) {
        const semaforo = data.semaforo || {};
        document.getElementById('localVerde').textContent = semaforo.local_verde || 0;
        document.getElementById('localAmarillo').textContent = semaforo.local_amarillo || 0;
        document.getElementById('localRojo').textContent = semaforo.local_rojo || 0;
        document.getElementById('localVioleta').textContent = semaforo.local_violeta || 0;
        document.getElementById('foraneoVerde').textContent = semaforo.foraneo_verde || 0;
        document.getElementById('foraneoAmarillo').textContent = semaforo.foraneo_amarillo || 0;
        document.getElementById('foraneoRojo').textContent = semaforo.foraneo_rojo || 0;
        document.getElementById('foraneoVioleta').textContent = semaforo.foraneo_violeta || 0;

        const facturas = data.facturas_pendientes || {};
        document.getElementById('facturasPendientes').textContent = facturas.total || 0;
        document.getElementById('facturasMonto').textContent = formatMoney(facturas.monto_total || 0);

        const resumen = data.resumen || {};
        const totalTramites = safeInt(resumen.total_tramites || 0);
        const enProceso = safeInt(resumen.en_proceso || 0);
        const concluidos = safeInt(resumen.concluidos || 0);
        const cancelados = safeInt(resumen.cancelados || 0);

        document.getElementById('resumenTotal').textContent = totalTramites;
        document.getElementById('resumenProceso').textContent = enProceso;
        document.getElementById('resumenConcluidos').textContent = concluidos;
        document.getElementById('resumenCancelados').textContent = cancelados;
        document.getElementById('resumenProcesoMini').textContent = enProceso;

        document.getElementById('resumenProcesoPct').textContent = `${pct(enProceso, totalTramites)}%`;
        document.getElementById('resumenConcluidosPct').textContent = `${pct(concluidos, totalTramites)}%`;

        const concluidosPeriodos = data.concluidos_periodos || {};
        document.getElementById('concluidosHoy').textContent = safeInt(concluidosPeriodos.hoy || 0);
        document.getElementById('concluidosSemana').textContent = safeInt(concluidosPeriodos.semana || 0);
        document.getElementById('concluidosMes').textContent = safeInt(concluidosPeriodos.mes || 0);
        document.getElementById('concluidosAnio').textContent = safeInt(concluidosPeriodos.anio || 0);

        renderTramitesPorTipoChart(data.tramites_por_tipo || []);

        updateTable('tablaTipos', 'emptyTipos', data.atorados_por_tipo || [], 'tipo');
        updateTable('tablaEstados', 'emptyEstados', data.atorados_por_estado || [], 'estado');
        updateTable('tablaClientes', 'emptyClientes', data.atorados_por_cliente || [], 'cliente');

        setChipState('ok');
        lastUpdate.innerHTML = `<i class="fas fa-sync"></i> Actualizado ${formatNow()}`;
    }

    function fetchDashboard() {
        renderFilterChips();
        setChipState('loading');
        lastUpdate.innerHTML = '<i class="fas fa-sync"></i> Actualizando...';

        const formData = new FormData(filtersForm);
        const params = new URLSearchParams(formData);
        fetch(`${dataUrl}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
            },
        })
            .then(async (response) => {
                const contentType = (response.headers.get('content-type') || '').toLowerCase();
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP ${response.status} ${response.statusText}: ${text.slice(0, 300)}`);
                }
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error(`Respuesta no JSON (${contentType || 'sin content-type'}): ${text.slice(0, 300)}`);
                }
                return response.json();
            })
            .then((data) => {
                try {
                    updateDashboard(data);
                } catch (err) {
                    console.error('Error pintando dashboard:', err);
                    throw err;
                }
            })
            .catch((err) => {
                console.error('Error actualizando dashboard:', err);
                setChipState('error');
                lastUpdate.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error al actualizar';
            });
    }

    filtersForm.addEventListener('submit', function (event) {
        event.preventDefault();
        fetchDashboard();
    });

    document.getElementById('resetFilters').addEventListener('click', function () {
        filtersForm.reset();
        fetchDashboard();
    });

    fetchDashboard();
    setInterval(fetchDashboard, 300000);
</script>
<?= $this->endSection() ?>
