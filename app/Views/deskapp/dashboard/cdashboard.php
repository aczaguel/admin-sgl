<?= $this->extend('layout/main') ?>

<?php $assets = base_url('/public/assets'); ?>

<?= $this->section('additional_css') ?>
<style>
:root {
    --cdb-ink:       #1f2933;
    --cdb-muted:     #6b7280;
    --cdb-surface:   #ffffff;
    --cdb-panel:     #f3efe7;
    --cdb-accent:    #0f766e;
    --cdb-accent2:   #f97316;
    --cdb-accent3:   #2563eb;
    --cdb-border:    #e7e1d6;
    --cdb-shadow:    0 14px 32px rgba(15,23,42,0.10);
    --cdb-radius:    18px;
}

.cdb {
    background: radial-gradient(circle at top right, #f7efe1 0%, #f9fafb 55%, #fff 100%);
    min-height: 100vh;
}

/* ── Hero / KPI strip ──────────────────────────────────── */
.cdb-hero {
    background: linear-gradient(120deg, #0f766e 0%, #0e7490 60%, #2563eb 100%);
    border-radius: var(--cdb-radius);
    padding: 32px 28px 28px;
    color: #fff;
    box-shadow: var(--cdb-shadow);
    position: relative;
    overflow: hidden;
    margin-bottom: 28px;
}
.cdb-hero::after {
    content: "";
    position: absolute;
    inset: -60px -60px auto auto;
    width: 260px; height: 260px;
    background: radial-gradient(circle, rgba(255,255,255,0.10) 0%, transparent 70%);
    pointer-events: none;
}
.cdb-hero-title   { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.cdb-hero-sub     { font-size: 13px; opacity: 0.78; margin-bottom: 28px; }

.cdb-kpi-strip    { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
.cdb-kpi          {
    background: rgba(255,255,255,0.14);
    border: 1px solid rgba(255,255,255,0.22);
    border-radius: 14px;
    padding: 18px 16px;
    backdrop-filter: blur(6px);
}
.cdb-kpi-label    { font-size: 11px; opacity: 0.75; text-transform: uppercase; letter-spacing: 0.06em; }
.cdb-kpi-value    { font-size: 36px; font-weight: 900; line-height: 1; margin: 6px 0 4px; }
.cdb-kpi-sub      { font-size: 12px; opacity: 0.70; }
.cdb-kpi-icon     {
    width: 36px; height: 36px;
    background: rgba(255,255,255,0.18);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .cdb-kpi-strip { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .cdb-kpi-strip { grid-template-columns: 1fr; }
}

/* ── Cards ─────────────────────────────────────────────── */
.cdb-card {
    background: var(--cdb-surface);
    border: 1px solid var(--cdb-border);
    border-radius: var(--cdb-radius);
    box-shadow: var(--cdb-shadow);
    padding: 20px;
    height: 100%;
}
.cdb-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--cdb-ink);
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.cdb-card-sub {
    font-size: 12px;
    color: var(--cdb-muted);
    margin-bottom: 16px;
}
.cdb-icon-accent  { color: var(--cdb-accent); }
.cdb-icon-accent2 { color: var(--cdb-accent2); }
.cdb-icon-accent3 { color: var(--cdb-accent3); }

/* ── Chart containers ──────────────────────────────────── */
.cdb-chart-lg  { height: 300px; }
.cdb-chart-md  { height: 260px; }
.cdb-chart-sm  { height: 220px; }

/* ── Periodo tiles ─────────────────────────────────────── */
.cdb-period-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
.cdb-period-tile {
    border: 1px solid var(--cdb-border);
    border-radius: 14px;
    background: linear-gradient(135deg, #fff 0%, #f8f5ef 100%);
    padding: 14px 16px;
    position: relative; overflow: hidden;
}
.cdb-period-tile::before {
    content: "";
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--cdb-accent), var(--cdb-accent3));
}
.cdb-period-label { font-size: 11px; color: var(--cdb-muted); font-weight: 700; display: flex; align-items: center; gap: 6px; }
.cdb-period-value { font-size: 28px; font-weight: 900; color: var(--cdb-ink); margin-top: 6px; }

/* ── Factura tile ──────────────────────────────────────── */
.cdb-factura-tile {
    background: linear-gradient(135deg, #fff7ed 0%, #fff 100%);
    border: 1px solid #fcd9aa;
    border-radius: 14px;
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 16px;
    text-decoration: none;
    color: inherit;
    transition: transform 0.18s, box-shadow 0.18s;
}
.cdb-factura-tile:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(249,115,22,0.14);
    color: inherit;
    text-decoration: none;
}
.cdb-factura-icon {
    width: 52px; height: 52px;
    background: rgba(249,115,22,0.12);
    color: var(--cdb-accent2);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    flex: 0 0 auto;
}
.cdb-factura-total { font-size: 28px; font-weight: 900; color: var(--cdb-ink); line-height: 1; }
.cdb-factura-monto { font-size: 13px; color: var(--cdb-accent2); font-weight: 700; margin-top: 2px; }
.cdb-factura-label { font-size: 12px; color: var(--cdb-muted); }

/* ── Recent table ──────────────────────────────────────── */
.cdb-table-wrap { overflow-x: auto; }
.cdb-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.cdb-table th {
    background: var(--cdb-panel);
    color: var(--cdb-ink);
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 10px 12px;
    border-bottom: 1px solid var(--cdb-border);
    white-space: nowrap;
}
.cdb-table td { padding: 10px 12px; border-bottom: 1px solid #f0ece4; color: var(--cdb-ink); vertical-align: middle; }
.cdb-table tr:hover td { background: rgba(15,118,110,0.04); }
.cdb-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}
.cdb-badge.is-process { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.cdb-badge.is-cancel  { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
.cdb-badge.is-default { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }

/* ── Filter bar ────────────────────────────────────────── */
.cdb-filters {
    background: #fff;
    border: 1px solid var(--cdb-border);
    border-radius: 14px;
    padding: 14px 18px;
    margin-bottom: 24px;
}

/* ── Live chip ─────────────────────────────────────────── */
.cdb-live { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 4px 12px; font-size: 11px; font-weight: 600; background: rgba(15,118,110,0.12); color: var(--cdb-accent); }
.cdb-live.loading { background: rgba(37,99,235,0.10); color: var(--cdb-accent3); }
.cdb-live.error   { background: rgba(239,68,68,0.12); color: #ef4444; }

.cdb-empty { padding: 24px; text-align: center; color: var(--cdb-muted); font-size: 13px; border: 1px dashed var(--cdb-border); border-radius: 12px; }

.cdb-cta { display: flex; justify-content: center; padding: 8px 0 24px; }
.cdb-cta a { display: inline-flex; align-items: center; gap: 8px; background: var(--cdb-accent); color: #fff; border-radius: 12px; padding: 12px 28px; font-weight: 700; font-size: 14px; text-decoration: none; transition: background 0.18s; }
.cdb-cta a:hover { background: #0d6561; color: #fff; text-decoration: none; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="main-container cdb">
<div class="pd-ltr-20 xs-pd-20-10">
<div class="min-height-200px">

    <!-- ── Hero ───────────────────────────────────────────── -->
    <div class="cdb-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:12px; margin-bottom: 20px;">
            <div>
                <div class="cdb-hero-title"><i class="fas fa-th-large"></i> Mis Trámites</div>
                <div class="cdb-hero-sub">Resumen de todos tus trámites activos y completados</div>
            </div>
            <span class="cdb-live loading" id="cdbLiveChip"><i class="fas fa-sync fa-spin"></i> Actualizando...</span>
        </div>
        <div class="cdb-kpi-strip">
            <div class="cdb-kpi">
                <div class="cdb-kpi-icon"><i class="fas fa-layer-group"></i></div>
                <div class="cdb-kpi-label">Total trámites</div>
                <div class="cdb-kpi-value" id="cdbTotal">—</div>
                <div class="cdb-kpi-sub">histórico</div>
            </div>
            <div class="cdb-kpi">
                <div class="cdb-kpi-icon"><i class="fas fa-spinner"></i></div>
                <div class="cdb-kpi-label">En proceso</div>
                <div class="cdb-kpi-value" id="cdbProceso">—</div>
                <div class="cdb-kpi-sub" id="cdbProcesoPct">—</div>
            </div>
            <div class="cdb-kpi">
                <div class="cdb-kpi-icon"><i class="fas fa-check-circle"></i></div>
                <div class="cdb-kpi-label">Completados</div>
                <div class="cdb-kpi-value" id="cdbConcluidos">—</div>
                <div class="cdb-kpi-sub" id="cdbConcluidosPct">—</div>
            </div>
            <div class="cdb-kpi">
                <div class="cdb-kpi-icon"><i class="fas fa-ban"></i></div>
                <div class="cdb-kpi-label">Cancelados</div>
                <div class="cdb-kpi-value" id="cdbCancelados">—</div>
                <div class="cdb-kpi-sub">total histórico</div>
            </div>
        </div>
    </div>

    <!-- ── Filter bar ─────────────────────────────────────── -->
    <div class="cdb-filters">
        <form id="cdbFilters" class="row align-items-end">
            <div class="col-md-3 mb-2">
                <label style="font-size:11px;font-weight:700;color:var(--cdb-ink);">Cliente directo</label>
                <select class="form-control form-control-sm" name="cli_directo_id">
                    <option value="">Todos</option>
                    <?php foreach ($cli_directo_list as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (!empty($filters['cli_directo_id']) && (int)$filters['cli_directo_id'] === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= esc($c['razon_social']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label style="font-size:11px;font-weight:700;color:var(--cdb-ink);">Tipo de trámite</label>
                <select class="form-control form-control-sm" name="tra_tipos_id">
                    <option value="">Todos</option>
                    <?php foreach ($tipos_list as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= (!empty($filters['tra_tipos_id']) && (int)$filters['tra_tipos_id'] === (int)$t['id']) ? 'selected' : '' ?>>
                            <?= esc($t['tipo_tramite']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label style="font-size:11px;font-weight:700;color:var(--cdb-ink);">Rango de fechas</label>
                <div class="d-flex" style="gap:6px;">
                    <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?= esc($filters['fecha_inicio'] ?? '') ?>">
                    <input type="date" name="fecha_fin"    class="form-control form-control-sm" value="<?= esc($filters['fecha_fin'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3 mb-2 d-flex" style="gap:8px; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
                <button type="button" id="cdbReset" class="btn btn-outline-secondary btn-sm"><i class="fas fa-undo"></i> Limpiar</button>
            </div>
        </form>
    </div>

    <!-- ── Row 1: Donut + Área ────────────────────────────── -->
    <div class="row mb-20">

        <!-- Donut — distribución por estatus -->
        <div class="col-md-4 mb-20">
            <div class="cdb-card">
                <div class="cdb-card-title"><i class="fas fa-chart-pie cdb-icon-accent"></i> Por estatus</div>
                <div class="cdb-card-sub">Distribución de todos tus trámites</div>
                <div id="cdbDonut" style="height:240px;"></div>
                <div class="cdb-empty" id="cdbDonutEmpty" style="display:none;">Sin datos disponibles.</div>
            </div>
        </div>

        <!-- Área — trámites por mes -->
        <div class="col-md-8 mb-20">
            <div class="cdb-card">
                <div class="cdb-card-title"><i class="fas fa-chart-area cdb-icon-accent3"></i> Trámites por mes</div>
                <div class="cdb-card-sub">Últimos 12 meses — ingresados vs completados</div>
                <div id="cdbArea" class="cdb-chart-lg"></div>
                <div class="cdb-empty" id="cdbAreaEmpty" style="display:none;">Sin datos disponibles.</div>
            </div>
        </div>

    </div>

    <!-- ── Row 2: Barra horizontal + Períodos + Facturas ──── -->
    <div class="row mb-20">

        <!-- Barra horizontal — por tipo de trámite -->
        <div class="col-md-6 mb-20">
            <div class="cdb-card">
                <div class="cdb-card-title"><i class="fas fa-chart-bar cdb-icon-accent3"></i> Por tipo de trámite</div>
                <div class="cdb-card-sub">En proceso vs completados (Top 10)</div>
                <div id="cdbBar" class="cdb-chart-md"></div>
                <div class="cdb-empty" id="cdbBarEmpty" style="display:none;">Sin datos disponibles.</div>
            </div>
        </div>

        <!-- Períodos + Factura tile -->
        <div class="col-md-6 mb-20">
            <div class="row h-100">
                <div class="col-12 mb-20">
                    <div class="cdb-card">
                        <div class="cdb-card-title"><i class="fas fa-calendar-check cdb-icon-accent"></i> Completados por período</div>
                        <div class="cdb-period-grid" style="margin-top:10px;">
                            <div class="cdb-period-tile">
                                <div class="cdb-period-label"><i class="fas fa-sun"></i> Hoy</div>
                                <div class="cdb-period-value" id="cdbHoy">—</div>
                            </div>
                            <div class="cdb-period-tile">
                                <div class="cdb-period-label"><i class="fas fa-calendar-week"></i> Esta semana</div>
                                <div class="cdb-period-value" id="cdbSemana">—</div>
                            </div>
                            <div class="cdb-period-tile">
                                <div class="cdb-period-label"><i class="fas fa-calendar-alt"></i> Este mes</div>
                                <div class="cdb-period-value" id="cdbMes">—</div>
                            </div>
                            <div class="cdb-period-tile">
                                <div class="cdb-period-label"><i class="fas fa-calendar"></i> Este año</div>
                                <div class="cdb-period-value" id="cdbAnio">—</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <a href="<?= site_url('/deskapp/tramitesn/tramite?pendiente_pago=1') ?>" class="cdb-factura-tile">
                        <div class="cdb-factura-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div>
                            <div class="cdb-factura-label">Facturas pendientes de pago</div>
                            <div class="cdb-factura-total" id="cdbFacturasTotal">—</div>
                            <div class="cdb-factura-monto" id="cdbFacturasMonto">—</div>
                        </div>
                        <div style="margin-left:auto; color: var(--cdb-accent2);"><i class="fas fa-chevron-right"></i></div>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Row 3: Trámites recientes ──────────────────────── -->
    <div class="row mb-20">
        <div class="col-12">
            <div class="cdb-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="cdb-card-title" style="margin-bottom:0;"><i class="fas fa-history cdb-icon-accent"></i> Trámites recientes</div>
                    <a href="<?= site_url('/deskapp/tramitesn/tramite') ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list"></i> Ver todos
                    </a>
                </div>
                <div class="cdb-card-sub">Últimos 8 trámites</div>
                <div class="cdb-table-wrap">
                    <table class="cdb-table">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Tipo</th>
                                <th>Cliente</th>
                                <th>Estatus</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cdbRecentBody"></tbody>
                    </table>
                    <div class="cdb-empty" id="cdbRecentEmpty" style="display:none;">Sin trámites recientes.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── CTA ────────────────────────────────────────────── -->
    <div class="cdb-cta">
        <a href="<?= site_url('/deskapp/tramitesn/tramite') ?>">
            <i class="fas fa-list-alt"></i> Ver todos mis trámites
        </a>
    </div>

</div>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
<script src="<?= $assets ?>/src/plugins/apexcharts/apexcharts.min.js"></script>
<script>
(function () {
    'use strict';

    const DATA_URL  = '<?= site_url('/deskapp/clientes/cdashboard_data') ?>';
    const form      = document.getElementById('cdbFilters');
    const liveChip  = document.getElementById('cdbLiveChip');
    let chartDonut  = null;
    let chartArea   = null;
    let chartBar    = null;

    /* ── Helpers ────────────────────────────────────────── */
    function safeInt(v) { const n = Number(v || 0); return Number.isFinite(n) ? Math.trunc(n) : 0; }
    function pct(p, t) { const tot = safeInt(t); return tot <= 0 ? 0 : Math.min(100, Math.round((safeInt(p) / tot) * 100)); }
    function money(v) { return Number(v || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 }); }
    function fmtDate(s) { if (!s) return '—'; const d = new Date(s + (s.length === 10 ? 'T00:00:00' : '')); return isNaN(d) ? s : d.toLocaleDateString('es-MX'); }
    function now() { return new Date().toLocaleString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }); }

    function setLive(state) {
        liveChip.className = 'cdb-live' + (state === 'loading' ? ' loading' : state === 'error' ? ' error' : '');
        if (state === 'loading') liveChip.innerHTML = '<i class="fas fa-sync fa-spin"></i> Actualizando...';
        else if (state === 'error') liveChip.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error al cargar';
        else liveChip.innerHTML = '<i class="fas fa-check-circle"></i> Actualizado ' + now();
    }

    function badgeClass(status) {
        if (!status) return 'cdb-badge is-default';
        const s = status.toLowerCase();
        if (s.includes('proceso') || s.includes('activo') || s.includes('pendiente')) return 'cdb-badge is-process';
        if (s.includes('cancel')) return 'cdb-badge is-cancel';
        return 'cdb-badge';
    }

    /* ── KPIs ───────────────────────────────────────────── */
    function renderKPIs(r, f) {
        const total      = safeInt(r.total_tramites);
        const proceso    = safeInt(r.en_proceso);
        const concluidos = safeInt(r.concluidos);
        const cancelados = safeInt(r.cancelados);

        document.getElementById('cdbTotal').textContent      = total;
        document.getElementById('cdbProceso').textContent    = proceso;
        document.getElementById('cdbConcluidos').textContent = concluidos;
        document.getElementById('cdbCancelados').textContent = cancelados;
        document.getElementById('cdbProcesoPct').textContent    = pct(proceso, total) + '% del total';
        document.getElementById('cdbConcluidosPct').textContent = pct(concluidos, total) + '% completados';

        // Facturas
        document.getElementById('cdbFacturasTotal').textContent = safeInt(f.total) + ' facturas';
        document.getElementById('cdbFacturasMonto').textContent  = money(f.monto_total);
    }

    /* ── Períodos ───────────────────────────────────────── */
    function renderPeriodos(p) {
        document.getElementById('cdbHoy').textContent    = safeInt(p.hoy);
        document.getElementById('cdbSemana').textContent = safeInt(p.semana);
        document.getElementById('cdbMes').textContent    = safeInt(p.mes);
        document.getElementById('cdbAnio').textContent   = safeInt(p.anio);
    }

    /* ── Donut ──────────────────────────────────────────── */
    function renderDonut(rows) {
        const el    = document.getElementById('cdbDonut');
        const empty = document.getElementById('cdbDonutEmpty');
        if (!rows || rows.length === 0) { el.style.display = 'none'; empty.style.display = 'block'; return; }
        el.style.display = 'block'; empty.style.display = 'none';

        const labels = rows.map(r => r.label || 'Sin estatus');
        const series = rows.map(r => safeInt(r.total));
        const colors = ['#0f766e','#2563eb','#f97316','#7c3aed','#ef4444','#10b981','#6b7280'];

        if (chartDonut) { chartDonut.destroy(); chartDonut = null; }
        chartDonut = new ApexCharts(el, {
            chart: { type: 'donut', height: 240, fontFamily: 'Inter, system-ui, sans-serif', toolbar: { show: false } },
            series, labels,
            colors: colors.slice(0, labels.length),
            plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label: 'Total', formatter: () => series.reduce((a, b) => a + b, 0) } } } } },
            legend: { position: 'bottom', fontSize: '11px' },
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: v => v + ' trámites' } },
        });
        chartDonut.render();
    }

    /* ── Área ───────────────────────────────────────────── */
    function renderArea(rows) {
        const el    = document.getElementById('cdbArea');
        const empty = document.getElementById('cdbAreaEmpty');
        if (!rows || rows.length === 0) { el.style.display = 'none'; empty.style.display = 'block'; return; }
        el.style.display = 'block'; empty.style.display = 'none';

        const cats       = rows.map(r => r.mes_label || r.mes);
        const ingresados = rows.map(r => safeInt(r.total));
        const concluidos = rows.map(r => safeInt(r.concluidos));

        if (chartArea) { chartArea.destroy(); chartArea = null; }
        chartArea = new ApexCharts(el, {
            chart: { type: 'area', height: 300, fontFamily: 'Inter, system-ui, sans-serif', toolbar: { show: false }, zoom: { enabled: false } },
            series: [
                { name: 'Ingresados',  data: ingresados },
                { name: 'Completados', data: concluidos },
            ],
            colors: ['#2563eb', '#0f766e'],
            xaxis: { categories: cats, labels: { style: { colors: '#6b7280', fontSize: '11px' }, rotate: -30 } },
            yaxis: { labels: { style: { colors: '#6b7280' } } },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
            stroke: { curve: 'smooth', width: 2.5 },
            dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(0,0,0,0.06)', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'left' },
            tooltip: { shared: true, intersect: false, y: { formatter: v => v + ' trámites' } },
        });
        chartArea.render();
    }

    /* ── Barra horizontal ───────────────────────────────── */
    function renderBar(rows) {
        const el    = document.getElementById('cdbBar');
        const empty = document.getElementById('cdbBarEmpty');
        if (!rows || rows.length === 0) { el.style.display = 'none'; empty.style.display = 'block'; return; }
        el.style.display = 'block'; empty.style.display = 'none';

        const cats       = rows.map(r => r.tipo || 'Sin tipo');
        const proceso    = rows.map(r => safeInt(r.en_proceso));
        const concluidos = rows.map(r => safeInt(r.concluidos));

        if (chartBar) { chartBar.destroy(); chartBar = null; }
        chartBar = new ApexCharts(el, {
            chart: { type: 'bar', height: 260, fontFamily: 'Inter, system-ui, sans-serif', stacked: true, toolbar: { show: false } },
            series: [
                { name: 'En proceso',  data: proceso },
                { name: 'Completados', data: concluidos },
            ],
            colors: ['#2563eb', '#0f766e'],
            plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '65%' } },
            dataLabels: { enabled: false },
            xaxis: { categories: cats, labels: { style: { colors: '#6b7280', fontSize: '10px' } } },
            yaxis: { labels: { style: { colors: '#6b7280', fontSize: '10px' }, maxWidth: 120 } },
            grid: { borderColor: 'rgba(0,0,0,0.06)', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'left' },
            tooltip: { shared: true, intersect: false, y: { formatter: v => v + ' trámites' } },
        });
        chartBar.render();
    }

    /* ── Recientes ──────────────────────────────────────── */
    function renderRecientes(rows) {
        const tbody = document.getElementById('cdbRecentBody');
        const empty = document.getElementById('cdbRecentEmpty');
        tbody.innerHTML = '';
        if (!rows || rows.length === 0) { empty.style.display = 'block'; return; }
        empty.style.display = 'none';
        rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td><strong>' + (r.folio || '—') + '</strong></td>' +
                '<td>' + (r.tipo_tramite || '—') + '</td>' +
                '<td>' + (r.razon_social || '—') + '</td>' +
                '<td><span class="' + badgeClass(r.tra_status) + '">' + (r.tra_status || '—') + '</span></td>' +
                '<td style="color:var(--cdb-muted);">' + fmtDate(r.created_at) + '</td>' +
                '<td><a href="<?= site_url('/deskapp/tramitesn/unified-client') ?>?tramite_id=' + r.id + '" class="btn btn-xs btn-primary" title="Ver trámite"><i class="fas fa-eye"></i></a></td>';
            tbody.appendChild(tr);
        });
    }

    /* ── Fetch & render ─────────────────────────────────── */
    function load() {
        setLive('loading');
        const params = new URLSearchParams(new FormData(form));
        fetch(DATA_URL + '?' + params, { headers: { Accept: 'application/json' } })
            .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function(d) {
                renderKPIs(d.resumen || {}, d.facturas_pendientes || {});
                renderPeriodos(d.concluidos_periodos || {});
                renderDonut(d.distribucion_estatus || []);
                renderArea(d.tramites_por_mes || []);
                renderBar(d.tramites_por_tipo || []);
                renderRecientes(d.recent_tramites || []);
                setLive('ok');
            })
            .catch(function(err) { console.error(err); setLive('error'); });
    }

    form.addEventListener('submit', function(e) { e.preventDefault(); load(); });
    document.getElementById('cdbReset').addEventListener('click', function() { form.reset(); load(); });

    load();
    setInterval(load, 300000); // auto-refresh cada 5 min
})();
</script>
<?= $this->endSection() ?>
