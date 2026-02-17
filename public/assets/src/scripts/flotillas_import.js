/* global FLOTILLA_IMPORT */

(function ($) {
    'use strict';

    function buildSummary(total, errorCount) {
        var $summary = $('#flotillaSummary');
        $summary.empty();
        $summary.append('<span class="flotilla-badge">Filas validas: ' + total + '</span>');
        if (errorCount > 0) {
            $summary.append('<span class="flotilla-badge is-danger">Errores: ' + errorCount + '</span>');
        } else {
            $summary.append('<span class="flotilla-badge is-success">Sin errores</span>');
        }
        $summary.show();
    }

    function renderErrors(errors) {
        var $container = $('#flotillaErrors');
        var $table = $('#errorTable');
        $table.empty();

        if (!errors || !errors.length) {
            $container.hide();
            return;
        }

        var header = '<tr>' +
            '<th>Linea</th>' +
            '<th>Contrato</th>' +
            '<th>Errores</th>' +
            '<th>Tramite existente</th>' +
            '</tr>';
        $table.append(header);

        errors.forEach(function (err) {
            var line = err.linea || '-';
            var contrato = err.contrato || '';
            var mensajes = (err.errores || []).join(', ');
            var existingId = err.existing_tramite_id || '';
            var existingTipo = err.existing_tipo_label || '';
            var existingHtml = '-';

            if (existingId) {
                var url = (FLOTILLA_IMPORT.tramiteBaseUrl || '') + existingId;
                existingHtml = 'ID ' + existingId;
                if (existingTipo) {
                    existingHtml += ' - Tipo: ' + existingTipo;
                }
                existingHtml += ' <a href="' + url + '" target="_blank">Abrir</a>';
            }

            var row = '<tr>' +
                '<td>' + line + '</td>' +
                '<td>' + contrato + '</td>' +
                '<td>' + mensajes + '</td>' +
                '<td>' + existingHtml + '</td>' +
                '</tr>';
            $table.append(row);
        });

        $container.show();
    }

    function renderPreview(rows) {
        var $preview = $('#flotillaPreview');
        var $table = $('#previewTable');
        $table.empty();

        if (!rows || !rows.length) {
            $preview.hide();
            return;
        }

        var header = '<tr>' +
            '<th>Contrato</th>' +
            '<th>Placa</th>' +
            '<th>Serie</th>' +
            '<th>Entidad</th>' +
            '<th>Tipo</th>' +
            '</tr>';
        $table.append(header);

        rows.forEach(function (row) {
            var line = '<tr>' +
                '<td>' + (row.contrato || '') + '</td>' +
                '<td>' + (row.placas || '') + '</td>' +
                '<td>' + (row.serie || '') + '</td>' +
                '<td>' + (row.entidad_label || row.entidad_id || '') + '</td>' +
                '<td>' + (row.tipo_label || row.tra_tipos_id || '') + '</td>' +
                '</tr>';
            $table.append(line);
        });

        $preview.show();
    }

    function buildFormData() {
        var form = document.getElementById('flotillaForm');
        return new FormData(form);
    }

    function runPreview() {
        var formData = buildFormData();
        $.ajax({
            url: FLOTILLA_IMPORT.previewUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (!response || !response.success) {
                    alert(response && response.message ? response.message : 'Error al previsualizar.');
                    return;
                }
                buildSummary(response.total || 0, (response.errors || []).length);
                renderErrors(response.errors || []);
                renderPreview(response.rows || []);
                $('#btnImport').prop('disabled', (response.errors || []).length > 0);
            },
            error: function () {
                alert('Error al previsualizar.');
            }
        });
    }

    function runImport() {
        var formData = buildFormData();
        $.ajax({
            url: FLOTILLA_IMPORT.importUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (!response || !response.success) {
                    alert(response && response.message ? response.message : 'Error al importar.');
                    return;
                }
                alert(response.message || 'Importado.');
                location.reload();
            },
            error: function () {
                alert('Error al importar.');
            }
        });
    }

    $(document).ready(function () {
        $('#btnPreview').on('click', runPreview);
        $('#btnImport').on('click', runImport);
    });
})(jQuery);
