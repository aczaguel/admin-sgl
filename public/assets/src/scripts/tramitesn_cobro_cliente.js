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

    function showConfirm(message, title, icon) {
        var safeTitle = title || 'Aviso';
        var safeIcon = icon || 'info';
        if (window.Swal && typeof Swal.fire === 'function') {
            return Swal.fire({
                title: safeTitle,
                text: message,
                icon: safeIcon,
                confirmButtonText: 'Aceptar'
            });
        }
        if (window.swal && typeof window.swal === 'function') {
            return window.swal({
                title: safeTitle,
                text: message,
                icon: safeIcon,
                buttons: {
                    confirm: 'Aceptar'
                }
            });
        }
        return Promise.resolve(confirm(message));
    }

    function showConfirmDecision(message, title) {
        var safeTitle = title || 'Confirmar';
        if (window.Swal && typeof Swal.fire === 'function') {
            return Swal.fire({
                title: safeTitle,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                customClass: {
                    popup: 'sgl-swal-confirm',
                    title: 'sgl-swal-title',
                    confirmButton: 'sgl-swal-confirm-btn',
                    cancelButton: 'sgl-swal-cancel-btn'
                }
            });
        }
        if (window.swal && typeof window.swal.fire === 'function') {
            return window.swal.fire({
                title: safeTitle,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                customClass: {
                    popup: 'sgl-swal-confirm',
                    title: 'sgl-swal-title',
                    confirmButton: 'sgl-swal-confirm-btn',
                    cancelButton: 'sgl-swal-cancel-btn'
                }
            });
        }
        return new Promise(function (resolve) {
            var overlay = document.createElement('div');
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.style.cssText = 'position:fixed;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(15,23,42,0.45);z-index:9999;padding:16px;';
            var card = document.createElement('div');
            card.style.cssText = 'max-width:520px;width:100%;background:#fff;border-radius:16px;box-shadow:0 20px 40px rgba(15,23,42,0.25);padding:20px 22px;font-family:inherit;';
            var titleEl = document.createElement('div');
            titleEl.style.cssText = 'font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:8px;';
            titleEl.textContent = safeTitle;
            var msgEl = document.createElement('div');
            msgEl.style.cssText = 'font-size:.95rem;color:#334155;line-height:1.45;';
            msgEl.textContent = message;
            var actions = document.createElement('div');
            actions.style.cssText = 'display:flex;justify-content:flex-end;gap:10px;margin-top:18px;';
            var cancelBtn = document.createElement('button');
            cancelBtn.type = 'button';
            cancelBtn.textContent = 'Cancelar';
            cancelBtn.style.cssText = 'border-radius:999px;border:1px solid #cbd5f5;background:#e0e7ff;color:#1e293b;font-weight:700;padding:8px 16px;';
            var okBtn = document.createElement('button');
            okBtn.type = 'button';
            okBtn.textContent = 'Continuar';
            okBtn.style.cssText = 'border-radius:999px;border:0;background:#10b981;color:#fff;font-weight:700;padding:8px 18px;box-shadow:0 6px 14px rgba(16,185,129,0.3);';
            cancelBtn.addEventListener('click', function () {
                overlay.remove();
                resolve({ isConfirmed: false });
            });
            okBtn.addEventListener('click', function () {
                overlay.remove();
                resolve({ isConfirmed: true });
            });
            actions.appendChild(cancelBtn);
            actions.appendChild(okBtn);
            card.appendChild(titleEl);
            card.appendChild(msgEl);
            card.appendChild(actions);
            overlay.appendChild(card);
            document.body.appendChild(overlay);
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
        var baseIva = honorarios + comision;
        var iva = baseIva * 0.16;
        var total = sumatoria + baseIva + iva;

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

        var $evidenciaTxt = $('#evidencia_cobro_txt');
        if ($evidenciaTxt.length) {
            $evidenciaTxt.attr('maxlength', '100');
            if (!$evidenciaTxt.next('.sgl-limit-note').length) {
                $evidenciaTxt.after('<small class="text-muted sgl-limit-note">Limite: 100 caracteres.</small>');
            }
            $evidenciaTxt.on('input', function () {
                var current = String($evidenciaTxt.val() || '');
                if (current.length > 100) {
                    $evidenciaTxt.val(current.slice(0, 100));
                }
            });
        }

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
                    ? 'Este tramite tiene un reembolso pendiente. ¿Estás seguro de cambiar el estatus?'
                    : '¿Estás seguro de cambiar el estatus de este tramite?';

                showConfirmDecision(mensajeConfirmacion, 'Confirmar cambio').then(function (result) {
                    if (!result || result.isConfirmed !== true) {
                        return;
                    }
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
                                showConfirm('Estatus del tramite actualizado correctamente.', 'Listo', 'success')
                                    .then(function (result) {
                                        if (result && result.isConfirmed === false) return;
                                        location.reload();
                                    });
                            } else {
                                showConfirm('Ocurrio un error al cambiar el estatus del tramite.', 'Aviso', 'error');
                            }
                        },
                        error: function () {
                            showConfirm('Ocurrio un error en la solicitud.', 'Aviso', 'error');
                        }
                    });
                });
            },
            error: function () {
                showConfirm('Ocurrio un error en la solicitud.', 'Aviso', 'error');
            }
        });
    };
})(jQuery);
