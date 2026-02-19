<?= $this->extend('layout/main') ?>

<?php $assets = base_url('/public/assets'); ?>

<link rel="stylesheet" href="<?= $assets ?>/vendors/styles/style.css">

<style>
    :root {
        --cl-ink: #1f2933;
        --cl-muted: #6b7280;
        --cl-surface: #ffffff;
        --cl-panel: #f3efe7;
        --cl-accent: #0f766e;
        --cl-border: #e7e1d6;
        --cl-shadow: 0 14px 32px rgba(15, 23, 42, 0.12);
    }

    .client-tramites {
        background: radial-gradient(circle at top right, #f7efe1 0%, #f9fafb 55%, #ffffff 100%);
        min-height: 100vh;
    }

    .client-tramites .page-header {
        border-radius: 18px;
        background: linear-gradient(120deg, #ffffff 0%, #fdf6ea 100%);
        border: 1px solid var(--cl-border);
        box-shadow: var(--cl-shadow);
        padding: 18px 20px;
    }

    .client-tramites .filters-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid var(--cl-border);
        padding: 14px 18px;
    }

    .client-tramites .card-box {
        border: 1px solid var(--cl-border);
        border-radius: 16px;
        background: var(--cl-surface);
        box-shadow: var(--cl-shadow);
    }

    .client-tramites label {
        font-weight: 600;
        color: var(--cl-ink);
        font-size: 12px;
    }

    .client-tramites .form-control {
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }

    .client-tramites .table thead th {
        background: var(--cl-panel);
        color: var(--cl-ink);
        border-bottom: 1px solid var(--cl-border);
    }

    .client-tramites .table tbody tr:hover {
        background: rgba(15, 118, 110, 0.04);
    }

    .client-tramites .status-pill {
        border-radius: 999px;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 11px;
        color: #fff;
        background: #64748b;
    }

    .client-tramites .status-en-proceso { background: #0ea5e9; }
    .client-tramites .status-concluido { background: #22c55e; }
    .client-tramites .status-cancelado { background: #ef4444; }

    .client-tramites .pagination-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
        font-size: 12px;
        color: var(--cl-muted);
    }

    .client-tramites .empty-state {
        padding: 18px;
        border: 1px dashed var(--cl-border);
        border-radius: 12px;
        color: var(--cl-muted);
        text-align: center;
    }
</style>

<?= $this->section('content') ?>

<div class="main-container client-tramites">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header mb-20">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="title">
                            <h4>Tramites del Cliente</h4>
                        </div>
                        <div class="subtitle">Listado filtrado de tus tramites con acceso rapido a detalle.</div>
                    </div>
                    <div class="col-md-5 text-right">
                        <span class="refresh-note" id="tramitesCount">Cargando...</span>
                    </div>
                </div>
            </div>

            <div class="filters-card mb-20">
                <form id="clienteTramitesFilters" class="row">
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
                        <div class="d-flex" style="gap: 8px;">
                            <input type="date" name="fecha_inicio" class="form-control" value="<?= esc($filters['fecha_inicio'] ?? '') ?>">
                            <input type="date" name="fecha_fin" class="form-control" value="<?= esc($filters['fecha_fin'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Buscar</label>
                        <input type="text" name="q" class="form-control" placeholder="Folio, contrato, unidad..." value="<?= esc($filters['q'] ?? '') ?>">
                    </div>
                    <div class="col-md-8 text-right mt-2">
                        <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                        <button type="button" class="btn btn-outline-secondary" id="resetFilters">Limpiar</button>
                    </div>
                </form>
            </div>

            <div class="card-box pd-20">
                <div class="table-responsive">
                    <table class="table table-striped" id="tramitesTable">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cliente Directo</th>
                                <th>Tipo</th>
                                <th>Estatus</th>
                                <th>Creacion</th>
                                <th>Facturas</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div class="empty-state" id="tramitesEmpty" style="display:none;">No hay tramites con los filtros actuales.</div>
                </div>
                <div class="pagination-wrap">
                    <button class="btn btn-sm btn-outline-secondary" id="prevPage">Anterior</button>
                    <span id="pageInfo">Pagina 1</span>
                    <button class="btn btn-sm btn-outline-secondary" id="nextPage">Siguiente</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const dataUrl = '<?= site_url('/deskapp/clientes/tramites/data') ?>';
    const filtersForm = document.getElementById('clienteTramitesFilters');
    const tableBody = document.querySelector('#tramitesTable tbody');
    const emptyState = document.getElementById('tramitesEmpty');
    const pageInfo = document.getElementById('pageInfo');
    const totalInfo = document.getElementById('tramitesCount');
    let currentPage = 1;

    function formatDate(value) {
        if (!value) {
            return 'N/A';
        }
        const date = new Date(value.replace(' ', 'T'));
        return date.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function statusClass(statusId) {
        if (Number(statusId) === 20) {
            return 'status-concluido';
        }
        if (Number(statusId) === 21) {
            return 'status-cancelado';
        }
        return 'status-en-proceso';
    }

    function renderRows(rows) {
        tableBody.innerHTML = '';
        if (!rows || rows.length === 0) {
            emptyState.style.display = 'block';
            return;
        }
        emptyState.style.display = 'none';
        rows.forEach((row) => {
            const facturas = row.numero_factura || row.numero_refactura ? 'Si' : 'No';
            const statusLabel = row.tra_status || 'En proceso';
            const statusId = row.tra_status_id || 0;
            const link = `<?= site_url('/customers/tramite') ?>/${row.id}`;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.folio || 'N/A'}</td>
                <td>${row.cliente_directo || 'N/A'}</td>
                <td>${row.tipo_tramite || 'N/A'}</td>
                <td><span class="status-pill ${statusClass(statusId)}">${statusLabel}</span></td>
                <td>${formatDate(row.created_at)}</td>
                <td>${facturas}</td>
                <td><a class="btn btn-sm btn-primary" href="${link}">Ver</a></td>
            `;
            tableBody.appendChild(tr);
        });
    }

    function fetchData() {
        const formData = new FormData(filtersForm);
        const params = new URLSearchParams(formData);
        params.set('page', String(currentPage));

        fetch(`${dataUrl}?${params.toString()}`)
            .then((response) => response.json())
            .then((payload) => {
                renderRows(payload.rows || []);
                const total = payload.total || 0;
                const perPage = payload.per_page || 25;
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                pageInfo.textContent = `Pagina ${payload.page} de ${totalPages}`;
                totalInfo.textContent = `${total} tramites encontrados`;
                document.getElementById('prevPage').disabled = payload.page <= 1;
                document.getElementById('nextPage').disabled = payload.page >= totalPages;
            })
            .catch(() => {
                emptyState.style.display = 'block';
                emptyState.textContent = 'Error al cargar tramites.';
            });
    }

    filtersForm.addEventListener('submit', function (event) {
        event.preventDefault();
        currentPage = 1;
        fetchData();
    });

    document.getElementById('resetFilters').addEventListener('click', function () {
        filtersForm.reset();
        currentPage = 1;
        fetchData();
    });

    document.getElementById('prevPage').addEventListener('click', function () {
        if (currentPage > 1) {
            currentPage -= 1;
            fetchData();
        }
    });

    document.getElementById('nextPage').addEventListener('click', function () {
        currentPage += 1;
        fetchData();
    });

    fetchData();
</script>

<?= $this->endSection() ?>
