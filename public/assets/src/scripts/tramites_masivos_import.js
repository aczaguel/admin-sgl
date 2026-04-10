/* global TRAMITES_MASIVOS_IMPORT */

(function ($) {
    'use strict';

    var previewRows = [];
    var tipoOptions = TRAMITES_MASIVOS_IMPORT.tipos || {};
    var clienteOptions = TRAMITES_MASIVOS_IMPORT.clientes || {};
    var entidadOptions = TRAMITES_MASIVOS_IMPORT.entidades || {};

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function buildFormData() {
        return new FormData(document.getElementById('tramitesMasivosForm'));
    }

    function buildSummary(total, savable) {
        var $summary = $('#tmassSummary');
        $summary.empty();
        $summary.append('<span class="tmass-badge"><i class="fas fa-list"></i> Filas leídas: ' + total + '</span>');
        $summary.append('<span class="tmass-badge is-success"><i class="fas fa-check"></i> Listas para guardar: ' + savable + '</span>');
        $summary.append('<span class="tmass-badge is-muted"><i class="fas fa-exclamation-triangle"></i> Con observaciones: ' + Math.max(total - savable, 0) + '</span>');
        $summary.show();
    }

    function getRowState(row) {
        if (row && row.saved) {
            return 'saved';
        }
        if (row && row.existing_tramite_url) {
            return 'existing';
        }
        if (row && row.errors && row.errors.length) {
            return 'error';
        }
        return 'ready';
    }

    function getStatusHtml(row) {
        var state = getRowState(row);
        var html = '';

        if (state === 'existing') {
            html = '<div class="tmass-status is-existing"><strong>Ya existe:</strong> este trámite ya está cargado en el sistema.';
            html += ' <a href="' + escapeHtml(row.existing_tramite_url) + '" target="_blank" rel="noopener noreferrer">Abrir trámite existente</a>';
            if (row.existing_tramite_folio) {
                html += ' <span>(Folio: ' + escapeHtml(row.existing_tramite_folio) + ')</span>';
            }
            html += '</div>';
            return html;
        }

        if (state === 'saved') {
            html = '<div class="tmass-status is-saved"><strong>Guardado:</strong> la fila ya fue registrada correctamente.</div>';
            return html;
        }

        if (state === 'error') {
            html = '<div class="tmass-status is-error"><strong>Error:</strong> ' + escapeHtml((row.errors || []).join(' ')) + '</div>';
            return html;
        }

        return '<div class="tmass-status is-ready"><strong>Lista:</strong> la fila está lista para guardarse. Aún no existe un trámite cargado con este contrato.</div>';
    }

    function getActionHtml(row, index) {
        if (row && row.saved) {
            return '<button type="button" class="btn btn-sm tmass-save-btn is-saved" disabled><i class="fas fa-check"></i> Guardado</button>';
        }

        if (row && row.existing_tramite_url) {
            return '<a href="' + escapeHtml(row.existing_tramite_url) + '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary tmass-open-btn"><i class="fas fa-external-link-alt"></i> Abrir existente</a>';
        }

        return '<button type="button" class="btn btn-sm btn-primary tmass-save-btn" data-index="' + index + '"><i class="fas fa-save"></i> Guardar</button>';
    }

    function applyRowState(index) {
        var row = previewRows[index];
        var state = getRowState(row);
        var $row = $('#tmass-row-' + index);
        var $statusRow = $('#warning-row-' + index);

        $row.removeClass('tmass-row-ready tmass-row-existing tmass-row-error tmass-row-saved');
        $row.addClass('tmass-row-' + state);
        $('#action-cell-' + index).html(getActionHtml(row, index));
        $statusRow.find('td').html(getStatusHtml(row));
        $statusRow.show();
    }

    function renderOptions(options, selectedValue, placeholder) {
        var html = '<option value="">' + escapeHtml(placeholder || 'Seleccione...') + '</option>';
        Object.keys(options || {}).forEach(function (key) {
            var selected = String(selectedValue || '') === String(key) ? ' selected' : '';
            html += '<option value="' + escapeHtml(key) + '"' + selected + '>' + escapeHtml(options[key]) + '</option>';
        });
        return html;
    }

    function buildClienteSelect(row, index) {
        return '<select class="form-control form-control-sm tmass-cliente-select" data-index="' + index + '">' +
            renderOptions(clienteOptions, row.cli_directo_id, 'Selecciona cliente') +
            '</select>';
    }

    function buildTipoSelect(row, index) {
        return '<select class="form-control form-control-sm tmass-tipo-select" data-index="' + index + '">' +
            renderOptions(tipoOptions, row.tra_tipos_id, 'Selecciona tipo') +
            '</select>';
    }

    function buildEjecutivoSelect(row, index) {
        return '<select class="form-control form-control-sm tmass-ejecutivo-select" data-index="' + index + '"' +
            (row.cli_directo_id ? '' : ' disabled') + '>' +
            renderOptions(row.ejecutivo_options || {}, row.cli_directo_ejecutivo_id, 'Selecciona ejecutivo') +
            '</select>';
    }

    function buildEntidadSelect(row, index) {
        return '<select class="form-control form-control-sm tmass-entidad-select" data-index="' + index + '">' +
            renderOptions(entidadOptions, row.entidad_id, 'Selecciona entidad') +
            '</select>';
    }

    function initTableSelects() {
        if (window.SglSelectEnhancer && typeof window.SglSelectEnhancer.init === 'function') {
            window.SglSelectEnhancer.init($('#tmassTableWrap'));
        }
    }

    function hideWarning(index) {
        applyRowState(index);
    }

    function updateRowLabels(index) {
        var row = previewRows[index];
        var ejecutivoOptions;

        if (!row) {
            return;
        }

        if (row.cli_directo_id && clienteOptions[row.cli_directo_id]) {
            row.cliente = clienteOptions[row.cli_directo_id];
            row.cliente_label = clienteOptions[row.cli_directo_id];
        }

        if (row.tra_tipos_id && tipoOptions[row.tra_tipos_id]) {
            row.tipo_tramite = tipoOptions[row.tra_tipos_id];
            row.tipo_tramite_label = tipoOptions[row.tra_tipos_id];
        }

        if (row.entidad_id && entidadOptions[row.entidad_id]) {
            row.entidad = entidadOptions[row.entidad_id];
            row.entidad_label = entidadOptions[row.entidad_id];
        }

        ejecutivoOptions = row.ejecutivo_options || {};
        if (row.cli_directo_ejecutivo_id && ejecutivoOptions[row.cli_directo_ejecutivo_id]) {
            row.ejecutivo_cliente = ejecutivoOptions[row.cli_directo_ejecutivo_id];
            row.ejecutivo_cliente_label = ejecutivoOptions[row.cli_directo_ejecutivo_id];
        }
    }

    function populateEjecutivoSelect(index, options, selectedValue) {
        var row = previewRows[index];
        var $select = $('.tmass-ejecutivo-select[data-index="' + index + '"]');
        row.ejecutivo_options = options || {};
        $select.html(renderOptions(row.ejecutivo_options, selectedValue, 'Selecciona ejecutivo'));
        $select.prop('disabled', !row.cli_directo_id);
        row.cli_directo_ejecutivo_id = selectedValue || '';
        row.ejecutivo_cliente = '';
        row.ejecutivo_cliente_label = '';
        updateRowLabels(index);
        if (window.SglSelectEnhancer && typeof window.SglSelectEnhancer.refresh === 'function') {
            window.SglSelectEnhancer.refresh($select);
        }
    }

    function fetchEjecutivos(index, clienteId, selectedValue) {
        var $select = $('.tmass-ejecutivo-select[data-index="' + index + '"]');

        if (!clienteId) {
            populateEjecutivoSelect(index, {}, '');
            return;
        }

        $select.html('<option value="">Cargando...</option>').prop('disabled', true);
        if (window.SglSelectEnhancer && typeof window.SglSelectEnhancer.refresh === 'function') {
            window.SglSelectEnhancer.refresh($select);
        }

        $.ajax({
            url: TRAMITES_MASIVOS_IMPORT.getEjecutivosUrlBase + '/' + clienteId,
            method: 'GET',
            success: function (response) {
                var normalizedOptions = {};

                if (Array.isArray(response)) {
                    response.forEach(function (item) {
                        if (item && typeof item.id !== 'undefined') {
                            normalizedOptions[item.id] = item.nombre || item.label || ('Ejecutivo #' + item.id);
                        }
                    });
                } else {
                    normalizedOptions = response || {};
                }

                populateEjecutivoSelect(index, normalizedOptions, selectedValue || '');
            },
            error: function () {
                populateEjecutivoSelect(index, {}, '');
                showWarning(index, ['No se pudieron cargar los ejecutivos para el cliente seleccionado.']);
            }
        });
    }

    function renderRows(rows) {
        var $table = $('#tmassTable');
        var $wrap = $('#tmassTableWrap');
        var $empty = $('#tmassEmpty');

        $table.empty();
        previewRows = rows || [];

        if (!previewRows.length) {
            $wrap.hide();
            $empty.show();
            return;
        }

        var header = '<thead><tr>' +
            '<th>Línea</th>' +
            '<th>Contrato</th>' +
            '<th>Unidad</th>' +
            '<th>Serie</th>' +
            '<th>Placas</th>' +
            '<th>Tipo de Trámite</th>' +
            '<th>Cliente</th>' +
            '<th>Ejecutivo de Cliente</th>' +
            '<th>Entidad</th>' +
            '<th class="tmass-col-observaciones">Observaciones</th>' +
            '<th>Acción</th>' +
            '</tr></thead><tbody></tbody>';

        $table.append(header);

        previewRows.forEach(function (row, index) {
            var warningRow = '<tr class="tmass-status-row" id="warning-row-' + index + '">' +
                '<td colspan="11">' + getStatusHtml(row) + '</td>' +
                '</tr>';

            var rowHtml = '<tr id="tmass-row-' + index + '">' +
                '<td>' + escapeHtml(row.linea) + '</td>' +
                '<td>' + escapeHtml(row.contrato) + '</td>' +
                '<td>' + escapeHtml(row.unidad) + '</td>' +
                '<td>' + escapeHtml(row.serie) + '</td>' +
                '<td>' + escapeHtml(row.placas) + '</td>' +
                '<td>' + buildTipoSelect(row, index) + '</td>' +
                '<td>' + buildClienteSelect(row, index) + '</td>' +
                '<td>' + buildEjecutivoSelect(row, index) + '</td>' +
                '<td>' + buildEntidadSelect(row, index) + '</td>' +
                '<td class="tmass-col-observaciones">' + escapeHtml(row.observaciones) + '</td>' +
                '<td class="tmass-actions" id="action-cell-' + index + '">' + getActionHtml(row, index) + '</td>' +
                '</tr>';

            $table.find('tbody').append(rowHtml + warningRow);
        });

        $empty.hide();
        $wrap.show();
        initTableSelects();
    }

    function markSaved(index, response) {
        previewRows[index].saved = true;
        previewRows[index].errors = [];
        if (response && response.tramite_url) {
            previewRows[index].saved_tramite_url = response.tramite_url;
        }
        applyRowState(index);

        if (response && response.tramite_url) {
            $('#action-cell-' + index).append(' <a href="' + escapeHtml(response.tramite_url) + '" target="_blank" rel="noopener noreferrer" class="btn btn-link btn-sm">Abrir</a>');
        }
    }

    function showWarning(index, messages, rowData) {
        var row = rowData || previewRows[index] || null;
        if (row) {
            row.errors = messages || ['No se pudo guardar la fila.'];
        }
        applyRowState(index);
    }

    function setButtonLoading(index, loading) {
        var $button = $('.tmass-save-btn[data-index="' + index + '"]');
        if (!$button.length) {
            return;
        }

        if (loading) {
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando');
            return;
        }

        $button.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
    }

    function runPreview() {
        $.ajax({
            url: TRAMITES_MASIVOS_IMPORT.previewUrl,
            method: 'POST',
            data: buildFormData(),
            processData: false,
            contentType: false,
            success: function (response) {
                if (!response || !response.success) {
                    alert(response && response.message ? response.message : 'No se pudo leer el archivo.');
                    return;
                }

                buildSummary(response.total || 0, response.savable || 0);
                renderRows(response.rows || []);
            },
            error: function () {
                alert('No se pudo leer el archivo.');
            }
        });
    }

    function saveRow(index) {
        var row = previewRows[index];
        if (!row || row.saved) {
            return;
        }

        updateRowLabels(index);
        hideWarning(index);

        setButtonLoading(index, true);

        $.ajax({
            url: TRAMITES_MASIVOS_IMPORT.saveRowUrl,
            method: 'POST',
            data: JSON.stringify({ row: row }),
            contentType: 'application/json; charset=utf-8',
            success: function (response) {
                if (response && response.success) {
                    markSaved(index, response);
                    return;
                }

                if (response && response.existing_tramite_url) {
                    row.existing_tramite_id = response.existing_tramite_id || '';
                    row.existing_tramite_folio = response.existing_tramite_folio || '';
                    row.existing_tramite_url = response.existing_tramite_url || '';
                }

                setButtonLoading(index, false);
                showWarning(index, (response && response.errors && response.errors.length)
                    ? response.errors
                    : [response && response.message ? response.message : 'No se pudo guardar la fila.'], row);
            },
            error: function () {
                setButtonLoading(index, false);
                showWarning(index, ['Error de comunicación con el servidor.'], row);
            }
        });
    }

    $(document).ready(function () {
        $('#btnPreviewMassive').on('click', runPreview);
        $(document).on('click', '.tmass-save-btn', function () {
            saveRow(Number($(this).data('index')));
        });
        $(document).on('change', '.tmass-cliente-select', function () {
            var index = Number($(this).data('index'));
            var row = previewRows[index];
            var clienteId = $(this).val();

            row.cli_directo_id = clienteId || '';
            row.cli_directo_ejecutivo_id = '';
            row.ejecutivo_options = {};
            row.ejecutivo_cliente = '';
            row.ejecutivo_cliente_label = '';
            row.errors = [];
            hideWarning(index);
            updateRowLabels(index);
            fetchEjecutivos(index, clienteId, '');
        });
        $(document).on('change', '.tmass-tipo-select', function () {
            var index = Number($(this).data('index'));
            var row = previewRows[index];

            row.tra_tipos_id = $(this).val() || '';
            row.errors = [];
            hideWarning(index);
            updateRowLabels(index);
        });
        $(document).on('change', '.tmass-ejecutivo-select', function () {
            var index = Number($(this).data('index'));
            var row = previewRows[index];

            row.cli_directo_ejecutivo_id = $(this).val() || '';
            row.errors = [];
            hideWarning(index);
            updateRowLabels(index);
        });
        $(document).on('change', '.tmass-entidad-select', function () {
            var index = Number($(this).data('index'));
            var row = previewRows[index];

            row.entidad_id = $(this).val() || '';
            row.errors = [];
            hideWarning(index);
            updateRowLabels(index);
        });
    });
})(jQuery);