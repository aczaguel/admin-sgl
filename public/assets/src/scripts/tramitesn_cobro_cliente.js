/* global Dropzone, tramite_id */

(function ($) {
    'use strict';

    if (typeof Dropzone !== 'undefined') {
        Dropzone.autoDiscover = false;
    }

    var $container = $('#cliente-container');
    var previousResponse = null;
    var intervalId = null;

    function updateCobroStatus(files) {
        var $status = $('#cobro-status-chips');
        if (!$status.length) {
            return;
        }

        var hasCompleto = false;
        var hasParcial = false;

        files.forEach(function (file) {
            if (file.cobro_correcto === 'completo') {
                hasCompleto = true;
            } else if (file.cobro_correcto === 'parcial') {
                hasParcial = true;
            }
        });

        if (hasCompleto) {
            $status.html('<span class="sgl-status-chip">Existe evidencia de pago completo</span>');
            return;
        }

        if (hasParcial) {
            $status.html('<span class="sgl-status-chip">Ha habido un pago parcial</span>');
            return;
        }

        $status.empty();
    }

    function renderFiles(files) {
        if (!$container.length) {
            return;
        }

        updateCobroStatus(files);
        $container.empty();
        files.forEach(function (file) {
            var cobroLabel = 'Otro';
            var badgeClass = ' is-other';
            if (file.cobro_correcto === 'parcial') {
                cobroLabel = 'Parcial';
                badgeClass = ' is-partial';
            } else if (file.cobro_correcto === 'completo') {
                cobroLabel = 'Completo';
                badgeClass = ' is-complete';
            }
            var filePreview = '' +
                '<div class="file-preview">' +
                '  <a href="' + file.existing_path + '" target="_blank">' +
                '    <img src="' + file.icon + '" alt="' + file.name + '" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">' +
                '  </a>' +
                '  <span class="doc-badge' + badgeClass + '">' + cobroLabel + '</span>' +
                '  <p style="font-size:10px;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' +
                file.name +
                '  </p>' +
                '</div>';
            $container.append(filePreview);
        });
    }

    function fetchFiles() {
        if (typeof tramite_id === 'undefined' || !tramite_id) {
            return;
        }

        $.ajax({
            url: '/deskapp/tramites/getCobroClienteFiles/' + tramite_id,
            method: 'GET',
            success: function (response) {
                var currentResponse = JSON.stringify(response);
                if (currentResponse === previousResponse) {
                    return;
                }
                previousResponse = currentResponse;
                renderFiles(response);
            },
            error: function (xhr) {
                if (xhr && xhr.status === 403 && intervalId) {
                    clearInterval(intervalId);
                }
            }
        });
    }

    function initDropzone() {
        if (typeof Dropzone === 'undefined') {
            return;
        }

        if (!$('.dropzone-cliente').length || typeof tramite_id === 'undefined' || !tramite_id) {
            return;
        }

        var renamedFilesCliente = {};
        var dropzoneCliente = new Dropzone('.dropzone-cliente', {
            url: '/deskapp/tramites/upload_cobro_cliente/' + tramite_id,
            autoProcessQueue: false,
            maxFilesize: 10,
            acceptedFiles: '.xml,.jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx',
            addRemoveLinks: true,
            dictRemoveFile: 'Quitar',
            renameFile: function (file) {
                var randomHex = '-' + Array.from(crypto.getRandomValues(new Uint8Array(3)))
                    .map(function (byte) { return byte.toString(16).padStart(2, '0'); })
                    .join('');
                var originalName = file.name.split('.').slice(0, -1).join('.');
                var extension = file.name.split('.').pop();
                var newname = originalName + randomHex + '.' + extension;
                if (file.upload) {
                    renamedFilesCliente[file.upload.uuid] = newname;
                }
                return newname;
            }
        });

        dropzoneCliente.on('sending', function (file, xhr, formData) {
            var tipo = $('#cobro_correcto').val() || 'otro';
            formData.append('cobro_correcto', tipo);
        });

        dropzoneCliente.on('removedfile', function (file) {
            var renamedName = file.upload ? file.upload.filename : null;
            if (!renamedName) {
                return;
            }

            $.ajax({
                url: '/deskapp/tramites/delete_cobro_cliente',
                type: 'POST',
                data: { tramite_id: tramite_id, file: renamedName },
                success: function (response) {
                    if (response && response.success) {
                        fetchFiles();
                    }
                }
            });

            if (file.upload) {
                delete renamedFilesCliente[file.upload.uuid];
            }
        });

        dropzoneCliente.on('success', function () {
            fetchFiles();
        });

        $('#btnSubirCliente').on('click', function () {
            if (dropzoneCliente.files.length > 0) {
                dropzoneCliente.processQueue();
            }
        });
    }

    function parseMoney(value) {
        if (value === null || value === undefined) {
            return 0;
        }
        var cleaned = String(value).replace(/,/g, '').trim();
        var parsed = parseFloat(cleaned);
        return Number.isNaN(parsed) ? 0 : parsed;
    }

    function formatMoney(value) {
        return value.toFixed(2);
    }

    function updateCobroTotals() {
        var sumatoria = parseMoney($('#costo_gestoria').val());
        var honorarios = parseMoney($('#costo_pago_cliente').val());
        var comision = parseMoney($('#comision_derechos').val());
        var base = sumatoria + honorarios + comision;
        var iva = base * 0.16;
        var total = base + iva;

        if ($('#iva').length) {
            $('#iva').val(formatMoney(iva));
        }
        if ($('#costo_total').length) {
            $('#costo_total').val(formatMoney(total));
        }
    }

    function groupMoneyFields() {
        var $form = $('#finalForm');
        if (!$form.length) {
            return;
        }

        if ($form.find('.sgl-money-group').length) {
            return;
        }

        var $moneyRows = $form.find('.mb-3.row').filter(function () {
            return $(this).find('#costo_gestoria, #costo_pago_cliente, #comision_derechos, #iva, #costo_total').length > 0;
        });

        if (!$moneyRows.length) {
            return;
        }

        var $group = $('<div class="sgl-money-group"><div class="sgl-money-title"><i class="fas fa-coins"></i> Resumen de cobro</div></div>');
        $group.insertBefore($moneyRows.first());
        $moneyRows.appendTo($group);
    }

    $(document).ready(function () {
        if ($container.length) {
            fetchFiles();
            intervalId = setInterval(fetchFiles, 3000);
        }
        initDropzone();

        groupMoneyFields();
        updateCobroTotals();

        $('#costo_pago_cliente, #comision_derechos').on('input', function () {
            updateCobroTotals();
        });

        $('#finalForm').on('submit', function (e) {
            e.preventDefault();

            if ($('#costo_gestoria_hidden').length) {
                $('#costo_gestoria_hidden').val($('#costo_gestoria').val());
            }

            var formData = new FormData(this);

            $.ajax({
                url: '/deskapp/tramites/update_final_save/' + tramite_id,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response && response.success === true) {
                        $('#final_mensaje').html('<i class="fas fa-check-circle"></i> ' + response.message);
                        $('#final_respuesta').show();

                        $('#finalForm button[type="submit"]').addClass('btn-success-flash');
                        setTimeout(function () {
                            $('#finalForm button[type="submit"]').removeClass('btn-success-flash');
                        }, 2000);

                        setTimeout(function () {
                            $('#final_respuesta').fadeOut('slow');
                        }, 3000);
                    } else {
                        $('#final_mensaje_error').html('Favor de revisar los campos requeridos');
                        $('#final_respuesta_error').show();
                        setTimeout(function () {
                            $('#final_respuesta_error').fadeOut('slow');
                        }, 5000);
                    }
                },
                error: function (xhr) {
                    var message = xhr && xhr.responseText ? xhr.responseText : 'Error al guardar.';
                    $('#final_mensaje_error').html(message);
                    $('#final_respuesta_error').show();
                    setTimeout(function () {
                        $('#final_respuesta_error').fadeOut('slow');
                    }, 5000);
                }
            });
        });
    });

    window.concluirTramite = function (tramiteId, statusId) {
        $.ajax({
            url: '/deskapp/tramites/check_reembolso_status',
            type: 'POST',
            data: {
                tramite_id: tramiteId,
                csrf_token: $('meta[name="csrf_token"]').attr('content')
            },
            success: function (response) {
                var mensajeConfirmacion = response && response.reembolso_pendiente
                    ? 'Este tramite tiene un reembolso pendiente. Estas seguro de cambiar el estatus?'
                    : 'Estas seguro de cambiar el estatus de este tramite?';

                if (confirm(mensajeConfirmacion)) {
                    $.ajax({
                        url: '/deskapp/tramites/change_status',
                        type: 'POST',
                        data: {
                            tramite_id: tramiteId,
                            status_id: statusId,
                            csrf_token: $('meta[name="csrf_token"]').attr('content')
                        },
                        success: function (res) {
                            if (res && res.success) {
                                alert('Estatus del tramite actualizado correctamente.');
                                location.reload();
                            } else {
                                alert('Ocurrio un error al cambiar el estatus del tramite.');
                            }
                        },
                        error: function () {
                            alert('Ocurrio un error en la solicitud.');
                        }
                    });
                }
            },
            error: function () {
                alert('Ocurrio un error en la solicitud.');
            }
        });
    };
})(jQuery);
