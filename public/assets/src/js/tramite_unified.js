;(function(window, document) {
    'use strict';

    var TUL = {
        csrfName: null,
        csrfHash: null,

        /**
         * Initializes the module: reads CSRF tokens and binds all event handlers.
         * Called automatically on DOMContentLoaded.
         */
        init: function() {
            var nameMeta = document.querySelector('meta[name="csrf-token-name"]');
            var hashMeta = document.querySelector('meta[name="csrf-token-hash"]');
            this.csrfName = nameMeta ? nameMeta.getAttribute('content') : null;
            this.csrfHash = hashMeta ? hashMeta.getAttribute('content') : null;

            this.bindAccordions();
            this.bindSaveForms();
            this.bindDropzones();
            this.bindDeleteButtons();
            this.bindNoteForms();
            this.bindDependentGestor();
            this.enhanceSelects();
            this.bindServices();
            this.bindStep4Finance();
            this.bindStep4Costs();
            this.bindStep5Finance();
            this.bindTramiteActions();
        },

        // ------------------------------------------------------------------
        // Global tramite actions: Cancelar / Concluir
        // ------------------------------------------------------------------

        /**
         * Binds the Cancelar and Concluir buttons.
         *  - Cancelar → cancelar_tramite (tramite_id, motivo, status_id=21)
         *  - Concluir → autorizar (tramite_id, status_id=20)
         * Both reload on success so the gates and readonly states re-evaluate.
         */
        bindTramiteActions: function() {
            var self = this;

            var cancelBtn = document.querySelector('[data-tul-cancel-tramite]');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    var motivo = window.prompt('Motivo de cancelación del trámite:');
                    if (motivo === null) return; // canceló el prompt
                    motivo = String(motivo).trim();
                    if (motivo === '') {
                        self.notify('error', 'Debes indicar un motivo para cancelar el trámite.');
                        return;
                    }
                    var data = new FormData();
                    self.appendCsrf(data);
                    data.append('tramite_id', cancelBtn.getAttribute('data-tul-tramite-id'));
                    data.append('motivo', motivo);
                    data.append('status_id', '21');
                    self.runTramiteAction(cancelBtn, cancelBtn.getAttribute('data-tul-url'), data, 'Trámite cancelado.');
                });
            }

            var concludeBtn = document.querySelector('[data-tul-conclude-tramite]');
            if (concludeBtn) {
                concludeBtn.addEventListener('click', function() {
                    if (!window.confirm('¿Concluir este trámite? Esta acción cierra el expediente.')) return;
                    var data = new FormData();
                    self.appendCsrf(data);
                    data.append('tramite_id', concludeBtn.getAttribute('data-tul-tramite-id'));
                    data.append('status_id', '20');
                    self.runTramiteAction(concludeBtn, concludeBtn.getAttribute('data-tul-url'), data, 'Trámite concluido.');
                });
            }
        },

        /**
         * Executes a global tramite status action and reloads on success.
         */
        runTramiteAction: function(btn, url, data, okMessage) {
            var self = this;
            if (!url) return;
            btn.disabled = true;
            btn.classList.add('tul-loading');

            this.ajax('POST', url, data, {
                onSuccess: function(xhr) {
                    var json = null;
                    try { json = JSON.parse(xhr.responseText); } catch (e) { json = null; }
                    if (json && json.success === false) {
                        btn.disabled = false;
                        btn.classList.remove('tul-loading');
                        self.notify('error', json.message || 'No se pudo completar la acción.');
                        return;
                    }
                    self.notify('success', (json && json.message) ? json.message : okMessage);
                    setTimeout(function() { window.location.reload(); }, 800);
                },
                onError: function(xhr) {
                    self.handleError(xhr, { btn: btn });
                }
            });
        },

        // ------------------------------------------------------------------
        // Step 5 finance live calculation (Costo total)
        // ------------------------------------------------------------------

        /**
         * Live-computes the client billing total:
         *   Costo total = Sumatoria de derechos + Honorarios + Comisión + IVA
         */
        bindStep5Finance: function() {
            var form = document.querySelector('[data-tul-step5-finance]');
            if (!form) return;

            var getVal = function(name) {
                var el = form.querySelector('[name="' + name + '"]');
                return el ? (parseFloat(el.value) || 0) : 0;
            };

            var totalOut = form.querySelector('[name="costo_total"]');

            var recompute = function() {
                var derechos = getVal('costo_gestoria');
                var honorarios = getVal('costo_pago_cliente');
                var comision = getVal('comision_derechos');
                var iva = getVal('iva');
                var total = derechos + honorarios + comision + iva;
                if (totalOut) {
                    totalOut.value = total.toFixed(2);
                }
            };

            var drivers = ['costo_gestoria', 'costo_pago_cliente', 'comision_derechos', 'iva'];
            for (var i = 0; i < drivers.length; i++) {
                var el = form.querySelector('[name="' + drivers[i] + '"]');
                if (el) {
                    el.addEventListener('input', recompute);
                    el.addEventListener('change', recompute);
                }
            }

            recompute();
        },

        // ------------------------------------------------------------------
        // Step 4 — Costos por servicio (montos de trámites asociados)
        // ------------------------------------------------------------------

        /**
         * Loads the associated-service costs, renders an editable row per
         * service, saves each row via AJAX, and feeds the sum into the
         * costo_tramite field so the saldo recalculates.
         */
        bindStep4Costs: function() {
            var self = this;
            var panel = document.querySelector('[data-tul-costs]');
            if (!panel) return;

            var listEl = panel.querySelector('[data-tul-costs-list]');
            var totalEl = panel.querySelector('[data-tul-costs-total]');
            var costsUrl = panel.getAttribute('data-tul-costs-url');
            var updateUrl = panel.getAttribute('data-tul-cost-update-url');
            var financeForm = document.querySelector('[data-tul-step4-finance]');
            var readonly = financeForm ? financeForm.hasAttribute('data-tul-readonly') : true;

            var fmt = function(n) {
                return n.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };

            var recomputeTotal = function() {
                var total = 0;
                var inputs = listEl.querySelectorAll('[data-tul-cost-input]');
                for (var i = 0; i < inputs.length; i++) {
                    total += parseFloat(inputs[i].value) || 0;
                }
                if (totalEl) totalEl.textContent = '$' + fmt(total);

                // Feed the sum into costo_tramite (readonly) and recompute saldo
                if (financeForm && inputs.length > 0) {
                    var costoField = financeForm.querySelector('[name="costo_tramite"]');
                    if (costoField) {
                        costoField.value = total.toFixed(2);
                        costoField.setAttribute('readonly', 'readonly');
                        costoField.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
                return total;
            };

            var renderRows = function(rows) {
                listEl.innerHTML = '';
                if (!rows || rows.length === 0) {
                    var empty = document.createElement('p');
                    empty.className = 'tul-inline-note';
                    empty.textContent = 'No hay trámites asociados. Agrégalos en el Paso 1 (Composición del servicio) para capturar su costo aquí.';
                    listEl.appendChild(empty);
                    return;
                }

                rows.forEach(function(row) {
                    var wrap = document.createElement('div');
                    wrap.className = 'tul-cost-row';
                    wrap.setAttribute('data-tul-cost-row', '');
                    wrap.setAttribute('data-cost-id', row.id);

                    var nameWrap = document.createElement('div');
                    nameWrap.className = 'tul-cost-row__name';
                    var title = document.createElement('strong');
                    title.className = 'tul-cost-row__title';
                    title.textContent = row.tipo_tramite || ('Trámite #' + row.id);
                    var sub = document.createElement('span');
                    sub.className = 'tul-cost-row__sub';
                    sub.textContent = 'Monto editable por trámite';
                    nameWrap.appendChild(title);
                    nameWrap.appendChild(sub);
                    wrap.appendChild(nameWrap);

                    var input = document.createElement('input');
                    input.type = 'number';
                    input.step = '0.01';
                    input.min = '0';
                    input.className = 'tul-cost-row__input';
                    input.setAttribute('data-tul-cost-input', '');
                    input.value = (row.costo_tramite !== null && row.costo_tramite !== undefined) ? row.costo_tramite : '';
                    input.placeholder = '0.00';
                    if (readonly) input.disabled = true;
                    input.addEventListener('input', recomputeTotal);
                    wrap.appendChild(input);

                    // Persistent "saved" checkmark: visible when the row already
                    // has a stored cost, and after a successful save.
                    var check = document.createElement('span');
                    check.className = 'tul-cost-row__check';
                    check.setAttribute('data-tul-cost-check', '');
                    check.setAttribute('title', 'Costo guardado');
                    check.innerHTML = '<i class="icon-check"></i>';
                    var hasSavedValue = row.costo_tramite !== null
                        && row.costo_tramite !== undefined
                        && String(row.costo_tramite).trim() !== '';
                    if (!hasSavedValue) check.classList.add('is-hidden');
                    wrap.appendChild(check);

                    if (!readonly) {
                        // Hide the checkmark while the value is being edited
                        // (out of sync with the server until re-saved).
                        input.addEventListener('input', function() {
                            check.classList.add('is-hidden');
                        });

                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'tul-btn tul-btn--primary tul-btn--sm';
                        btn.textContent = 'Guardar';
                        btn.addEventListener('click', function() {
                            self.saveCostRow(updateUrl, row.id, input, btn, check);
                        });
                        wrap.appendChild(btn);
                    }

                    listEl.appendChild(wrap);
                });

                recomputeTotal();
            };

            // Load costs
            this.ajax('GET', costsUrl, null, {
                onSuccess: function(xhr) {
                    var rows = [];
                    try { rows = JSON.parse(xhr.responseText) || []; } catch (e) { rows = []; }
                    renderRows(rows);
                },
                onError: function() {
                    listEl.innerHTML = '<p class="tul-inline-note">No se pudieron cargar los costos.</p>';
                }
            });
        },

        /**
         * Persists a single service cost row.
         */
        saveCostRow: function(updateUrl, id, input, btn, check) {
            var self = this;
            btn.disabled = true;
            btn.classList.add('tul-loading');

            var data = new FormData();
            self.appendCsrf(data);
            data.append('id', id);
            data.append('costo_tramite', input.value);

            this.ajax('POST', updateUrl, data, {
                onSuccess: function(xhr) {
                    btn.disabled = false;
                    btn.classList.remove('tul-loading');
                    var json = null;
                    try { json = JSON.parse(xhr.responseText); } catch (e) { json = null; }
                    if (!json || json.status === 'error') {
                        self.notify('error', (json && json.message) ? json.message : 'No se pudo guardar el costo.');
                        return;
                    }
                    self.notify('success', json.message || 'Costo actualizado.');
                    // Reveal the persistent "saved" checkmark for this row.
                    if (check) check.classList.remove('is-hidden');
                },
                onError: function(xhr) {
                    self.handleError(xhr, { btn: btn });
                }
            });
        },

        // ------------------------------------------------------------------
        // Step 4 finance live calculation (Saldo a favor)
        // ------------------------------------------------------------------

        /**
         * Live-computes the pago-a-gestor totals:
         *   Pago total = costo_tramite + honorarios + gratificación + paquetería
         *   Saldo      = Pago total − depósito
         * and paints a colored banner indicating who owes whom.
         */
        bindStep4Finance: function() {
            var self = this;
            var form = document.querySelector('[data-tul-step4-finance]');
            if (!form) return;

            var getVal = function(name) {
                var el = form.querySelector('[name="' + name + '"]');
                return el ? (parseFloat(el.value) || 0) : 0;
            };

            var saldoOut = form.querySelector('[data-tul-step4-out="saldo"]');
            var totalOut = form.querySelector('[data-tul-step4-out="total"]');
            var banner = form.querySelector('[data-tul-step4-saldo]');
            var breakdown = form.querySelector('[data-tul-step4-breakdown]');
            var reembolso = form.querySelector('[name="reembolso_status_id"]');

            var fmt = function(n) {
                return n.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };

            var recompute = function() {
                var costo = getVal('costo_tramite');
                var deposito = getVal('deposito_gestor');
                var honorarios = getVal('impuesto_gestoria');
                var gratif = getVal('gestoria_comision');
                var paqueteria = getVal('costo_paqueteria');

                var totalPago = costo + honorarios + gratif + paqueteria;
                var saldo = totalPago - deposito;
                var saldoAbs = Math.abs(saldo);

                if (totalOut) totalOut.value = totalPago.toFixed(2);
                if (saldoOut) saldoOut.value = saldo.toFixed(2);

                if (breakdown) {
                    breakdown.textContent = 'Costo: $' + fmt(costo)
                        + '  +  Honorarios: $' + fmt(honorarios)
                        + '  +  Gratificación: $' + fmt(gratif)
                        + '  +  Paquetería: $' + fmt(paqueteria)
                        + '   |   Saldo = Pago total − Depósito';
                }

                if (banner) {
                    banner.classList.remove('is-sgl', 'is-gestor', 'is-even');
                    if (saldo > 0.0001) {
                        banner.classList.add('is-gestor');
                        banner.textContent = 'Saldo a favor del Gestor — SGL debe pagar: $' + fmt(saldoAbs);
                    } else if (saldo < -0.0001) {
                        banner.classList.add('is-sgl');
                        banner.textContent = 'Saldo a favor de la empresa — Gestor debe devolver: $' + fmt(saldoAbs);
                    } else {
                        banner.classList.add('is-even');
                        banner.textContent = 'Sin saldo pendiente';
                    }
                }

                // Reembolso: pendiente (22) si hay saldo, conciliado (24) si cuadra
                if (reembolso && !reembolso.disabled) {
                    var target = saldoAbs > 0.0001 ? '22' : '24';
                    if (String(reembolso.value || '') !== target && reembolso.querySelector('option[value="' + target + '"]')) {
                        reembolso.value = target;
                        self.refreshSelect2(reembolso);
                    }
                }
            };

            var drivers = ['costo_tramite', 'deposito_gestor', 'impuesto_gestoria', 'gestoria_comision', 'costo_paqueteria'];
            for (var i = 0; i < drivers.length; i++) {
                var el = form.querySelector('[name="' + drivers[i] + '"]');
                if (el) {
                    el.addEventListener('input', recompute);
                    el.addEventListener('change', recompute);
                }
            }

            // Initial paint
            recompute();
        },

        // ------------------------------------------------------------------
        // Searchable selects (Select2)
        // ------------------------------------------------------------------

        /**
         * Turns every select inside the unified layout into a searchable
         * Select2 widget, reusing the globally loaded Select2 plugin.
         * Doc-type selectors used purely for upload routing are skipped.
         */
        enhanceSelects: function() {
            if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2)) {
                return;
            }
            var $ = window.jQuery;
            $('.tul-container select').each(function() {
                var $s = $(this);
                // Skip if already enhanced or explicitly opted out
                if ($s.hasClass('select2-hidden-accessible') || $s.is('[data-sgl-no-search]')) {
                    return;
                }
                $s.select2({
                    width: '100%',
                    language: 'es',
                    minimumResultsForSearch: 0,
                    placeholder: $s.find('option[value=""]').first().text() || ''
                });
            });
        },

        // ------------------------------------------------------------------
        // Dependent dropdown: Empresa gestora -> Gestor
        // ------------------------------------------------------------------

        /**
         * When the empresa gestora changes, fetch the gestores for that empresa
         * and repopulate the gestor select. Refreshes Select2 if present.
         */
        bindDependentGestor: function() {
            var self = this;
            var empresaSelects = document.querySelectorAll('[name="empresa_gestora_id"]');

            for (var i = 0; i < empresaSelects.length; i++) {
                (function(empresaSelect) {
                    var form = empresaSelect.closest('form');
                    if (!form) return;

                    var gestorSelect = form.querySelector('[name="gestor_id"]');
                    var baseUrl = form.getAttribute('data-tul-gestores-url');
                    if (!gestorSelect || !baseUrl) return;

                    var handler = function() {
                        var empresaId = empresaSelect.value;
                        if (!empresaId) {
                            self.resetGestorSelect(gestorSelect);
                            return;
                        }
                        self.loadGestores(baseUrl, empresaId, gestorSelect);
                    };

                    // Select2 dispatches change via jQuery; bind with jQuery when
                    // available so the handler fires, plus native as a fallback.
                    if (window.jQuery) {
                        window.jQuery(empresaSelect).on('change', handler);
                    } else {
                        empresaSelect.addEventListener('change', handler);
                    }
                })(empresaSelects[i]);
            }
        },

        /**
         * Fetches gestores for an empresa and repopulates the gestor select.
         */
        loadGestores: function(baseUrl, empresaId, gestorSelect) {
            var self = this;
            var url = baseUrl.replace(/\/+$/, '') + '/' + encodeURIComponent(empresaId);

            gestorSelect.disabled = true;

            this.ajax('GET', url, null, {
                onSuccess: function(xhr) {
                    var options = {};
                    try {
                        options = JSON.parse(xhr.responseText) || {};
                    } catch (e) {
                        options = {};
                    }

                    // Rebuild the gestor options
                    gestorSelect.innerHTML = '';
                    var placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Seleccione un gestor';
                    gestorSelect.appendChild(placeholder);

                    Object.keys(options).forEach(function(key) {
                        var opt = document.createElement('option');
                        opt.value = key;
                        opt.textContent = options[key];
                        gestorSelect.appendChild(opt);
                    });

                    gestorSelect.disabled = false;
                    self.refreshSelect2(gestorSelect);
                },
                onError: function(xhr) {
                    gestorSelect.disabled = false;
                    self.handleError(xhr, {});
                }
            });
        },

        /**
         * Empties the gestor select back to just its placeholder.
         */
        resetGestorSelect: function(gestorSelect) {
            gestorSelect.innerHTML = '';
            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Seleccione un gestor';
            gestorSelect.appendChild(placeholder);
            this.refreshSelect2(gestorSelect);
        },

        /**
         * Refreshes the Select2 widget for a select after its options change.
         * Uses the global enhancer exposed by the layout shell.
         */
        refreshSelect2: function(selectEl) {
            try {
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                    var $ = window.jQuery;
                    var $sel = $(selectEl);
                    // Destroy the stale widget (options were replaced) and re-init
                    if ($sel.hasClass('select2-hidden-accessible')) {
                        $sel.select2('destroy');
                    }
                    $sel.select2({
                        width: '100%',
                        language: 'es',
                        minimumResultsForSearch: 0,
                        placeholder: $sel.find('option[value=""]').first().text() || ''
                    });
                }
            } catch (e) {
                // Select2 not available — native select still works
            }
        },

        // ------------------------------------------------------------------
        // CSRF Token Management
        // ------------------------------------------------------------------

        /**
         * Extracts a fresh CSRF token from the AJAX response and updates
         * the internal state plus all hidden inputs across the page.
         *
         * Token sources (in priority order):
         *  1. Response header X-CSRF-Hash
         *  2. JSON body field csrf_hash
         *  3. JSON body field matching this.csrfName
         *
         * @param {XMLHttpRequest} xhr - The completed request object
         */
        updateCsrf: function(xhr) {
            var newHash = xhr.getResponseHeader('X-CSRF-Hash');

            if (!newHash) {
                try {
                    var json = JSON.parse(xhr.responseText);
                    // Backend devuelve csrfHash (camelCase). Soportar variantes.
                    newHash = json.csrfHash || json.csrf_hash || (this.csrfName ? json[this.csrfName] : null);
                } catch (e) {
                    // Response is not JSON — no token to extract
                }
            }

            if (newHash) {
                this.csrfHash = newHash;
                // Update all hidden CSRF inputs in every form on the page
                var inputs = document.querySelectorAll('input[name="' + this.csrfName + '"]');
                for (var i = 0; i < inputs.length; i++) {
                    inputs[i].value = newHash;
                }
                // Also update the meta tag for consistency
                var hashMeta = document.querySelector('meta[name="csrf-token-hash"]');
                if (hashMeta) {
                    hashMeta.setAttribute('content', newHash);
                }
            }
        },

        // ------------------------------------------------------------------
        // AJAX Utility
        // ------------------------------------------------------------------

        /**
         * Generic AJAX helper using XMLHttpRequest.
         *
         * @param {string} method   - HTTP method (GET, POST, etc.)
         * @param {string} url      - Target URL
         * @param {FormData|string|null} data - Request body
         * @param {object} callbacks - { onSuccess: fn(xhr), onError: fn(xhr), onProgress: fn(event) }
         */
        ajax: function(method, url, data, callbacks) {
            var self = this;
            var xhr = new XMLHttpRequest();
            callbacks = callbacks || {};

            xhr.open(method, url, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            // For non-FormData payloads, set content-type
            if (data && !(data instanceof FormData)) {
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            }

            // Upload progress
            if (callbacks.onProgress && xhr.upload) {
                xhr.upload.addEventListener('progress', callbacks.onProgress);
            }

            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) return;

                // Always attempt to refresh CSRF from every response
                self.updateCsrf(xhr);

                if (xhr.status >= 200 && xhr.status < 300) {
                    if (callbacks.onSuccess) {
                        callbacks.onSuccess(xhr);
                    }
                } else {
                    if (callbacks.onError) {
                        callbacks.onError(xhr);
                    }
                }
            };

            xhr.send(data || null);
        },

        // ------------------------------------------------------------------
        // Notifications
        // ------------------------------------------------------------------

        /**
         * Shows a toast-style notification that auto-dismisses after ~4 seconds.
         *
         * @param {string} type    - 'success' or 'error'
         * @param {string} message - Text to display
         */
        notify: function(type, message) {
            // Create notification element
            var el = document.createElement('div');
            el.className = 'tul-notify tul-notify--' + type;
            el.textContent = message;
            document.body.appendChild(el);

            // Force reflow so the initial state registers before adding visible class
            el.offsetHeight; // eslint-disable-line no-unused-expressions

            // Make visible (allows CSS transition)
            el.classList.remove('is-hidden');

            // Auto-dismiss after 4 seconds
            setTimeout(function() {
                el.classList.add('is-hidden');
                // Remove from DOM after fade-out transition completes
                setTimeout(function() {
                    if (el.parentNode) {
                        el.parentNode.removeChild(el);
                    }
                }, 400);
            }, 4000);
        },

        // ------------------------------------------------------------------
        // Error Handling
        // ------------------------------------------------------------------

        /**
         * Centralized error handler for AJAX failures.
         *
         * Handles:
         *  - status 0:   network error / no connection
         *  - status 419: CSRF token expired (session timeout)
         *  - 4xx/5xx:    parse JSON message or show generic fallback
         *
         * @param {XMLHttpRequest} xhr     - The failed request
         * @param {object} context         - { btn: HTMLElement|null } for UI cleanup
         */
        handleError: function(xhr, context) {
            var msg = 'Error del servidor';
            context = context || {};

            if (xhr.status === 0) {
                msg = 'Sin conexión al servidor. Verifica tu red.';
            } else if (xhr.status === 419) {
                msg = 'Sesión expirada. Recarga la página para continuar.';
            } else {
                try {
                    var json = JSON.parse(xhr.responseText);
                    msg = json.message || msg;
                } catch (e) {
                    // Not JSON — keep generic message
                }
            }

            this.notify('error', msg);

            // Re-enable the triggering button if provided
            if (context.btn) {
                context.btn.disabled = false;
                context.btn.classList.remove('tul-loading');
            }
        },

        // ------------------------------------------------------------------
        // Accordion (Task 4.2)
        // ------------------------------------------------------------------

        /**
         * Binds click handlers on all [data-accordion-trigger] elements to toggle
         * their parent accordion section expand/collapse state.
         */
        bindAccordions: function() {
            var self = this;
            var triggers = document.querySelectorAll('[data-accordion-trigger]');

            for (var i = 0; i < triggers.length; i++) {
                (function(trigger) {
                    trigger.addEventListener('click', function() {
                        var section = trigger.closest('[data-accordion]');
                        if (section) {
                            self.toggleAccordion(section);
                        }
                    });
                })(triggers[i]);
            }
        },

        /**
         * Toggles expand/collapse state of an accordion section.
         *
         * Expanding:
         *  - Sets max-height to scrollHeight so CSS transition animates open
         *  - Adds `is-expanded` class, sets aria-hidden="false"
         *  - On transitionend, removes inline max-height to allow content resizing
         *
         * Collapsing:
         *  - Snapshots current scrollHeight as explicit max-height
         *  - Forces reflow, then sets max-height to 0
         *  - Removes `is-expanded` class, sets aria-hidden="true"
         *
         * @param {HTMLElement} section - The [data-accordion] container element
         */
        toggleAccordion: function(section) {
            var isExpanded = section.classList.contains('is-expanded');
            var body = section.querySelector('[data-accordion-body]');

            if (!body) return;

            if (isExpanded) {
                // Collapsing: set explicit height first, then animate to 0
                body.style.maxHeight = body.scrollHeight + 'px';
                // Force reflow so the browser registers the starting value
                body.offsetHeight; // eslint-disable-line no-unused-expressions
                body.style.maxHeight = '0';
                section.classList.remove('is-expanded');
                body.setAttribute('aria-hidden', 'true');
            } else {
                // Expanding: animate from 0 to scrollHeight
                body.style.maxHeight = body.scrollHeight + 'px';
                section.classList.add('is-expanded');
                body.setAttribute('aria-hidden', 'false');

                // After transition completes, remove inline max-height so the
                // content can grow if its height changes dynamically.
                body.addEventListener('transitionend', function handler(e) {
                    if (e.propertyName === 'max-height') {
                        body.style.maxHeight = '';
                        body.removeEventListener('transitionend', handler);
                    }
                });
            }
        },

        /** Binds AJAX save handlers for all step forms */
        bindSaveForms: function() {
            var self = this;
            var forms = document.querySelectorAll('[data-tul-save]');

            for (var i = 0; i < forms.length; i++) {
                var form = forms[i];

                // Skip forms marked as readonly
                if (form.hasAttribute('data-tul-readonly')) {
                    continue;
                }

                (function(f) {
                    f.addEventListener('submit', function(e) {
                        e.preventDefault();
                        var btn = f.querySelector('[data-tul-save-btn]');
                        self.handleSave(f, btn);
                    });
                })(form);
            }
        },

        /**
         * Handles the AJAX save operation for a form.
         *
         * @param {HTMLFormElement} form - The form element to submit
         * @param {HTMLElement|null} btn - The submit button (for loading state)
         */
        handleSave: function(form, btn) {
            var self = this;
            var url = form.getAttribute('data-tul-url');

            if (!url) {
                return;
            }

            // Disable button and show loading state
            if (btn) {
                btn.disabled = true;
                btn.classList.add('tul-loading');
            }

            // Serialize form data (handles all field types including hidden CSRF)
            var data = new FormData(form);

            self.ajax('POST', url, data, {
                onSuccess: function(xhr) {
                    // Re-enable button
                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('tul-loading');
                    }

                    var json = null;
                    try {
                        json = JSON.parse(xhr.responseText);
                    } catch (e) {
                        json = null;
                    }

                    // El backend puede devolver success:false con HTTP 200
                    if (json && json.success === false) {
                        var errMsg = json.message;
                        if (!errMsg && json.errors) {
                            errMsg = Object.keys(json.errors).map(function(k) {
                                return json.errors[k];
                            }).join(' ');
                        }
                        self.notify('error', errMsg || 'No se pudo guardar.');
                        return;
                    }

                    var message = (json && json.message) ? json.message : 'Datos guardados';
                    self.notify('success', message);

                    // Si el guardado cambia el estado/gate del trámite, recargar
                    // para reflejar el desbloqueo progresivo de las siguientes fases.
                    if (form.hasAttribute('data-tul-reload')) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 700);
                    }
                },
                onError: function(xhr) {
                    // handleError re-enables button and shows error notification
                    self.handleError(xhr, { btn: btn });
                }
            });
        },

        /** Binds file upload dropzone handlers */
        bindDropzones: function() {
            var self = this;
            var dropzones = document.querySelectorAll('[data-tul-dropzone]');

            for (var i = 0; i < dropzones.length; i++) {
                (function(dropzone) {
                    // Bind file input change event
                    var fileInput = dropzone.querySelector('[data-tul-file-input]');
                    if (fileInput) {
                        fileInput.addEventListener('change', function() {
                            if (fileInput.files && fileInput.files.length > 0) {
                                self._uploadQueue(dropzone, fileInput.files);
                            }
                        });
                    }

                    // Bind upload button click (alternative to auto-upload on change)
                    var uploadBtn = dropzone.querySelector('[data-tul-upload-btn]');
                    if (uploadBtn) {
                        uploadBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                                self._uploadQueue(dropzone, fileInput.files);
                            }
                        });
                    }
                })(dropzones[i]);
            }
        },

        /**
         * Uploads multiple files sequentially, one at a time.
         * Reloads the page after all files have been processed.
         *
         * @param {HTMLElement} dropzone - The dropzone container
         * @param {FileList} files       - The selected files
         */
        _uploadQueue: function(dropzone, files) {
            var self = this;
            var fileArray = Array.prototype.slice.call(files);
            var total = fileArray.length;
            var index = 0;
            var errors = 0;

            // Show count if multiple files selected
            var feedback = dropzone.querySelector('[data-tul-upload-feedback]');
            if (total > 1 && feedback) {
                feedback.textContent = 'Subiendo ' + total + ' archivos...';
                feedback.removeAttribute('hidden');
            }

            var uploadNext = function() {
                if (index >= total) {
                    // All done — reload to show updated gallery
                    if (errors === 0) {
                        self.notify('success', total === 1 ? 'Archivo subido correctamente.' : total + ' archivos subidos correctamente.');
                    } else {
                        self.notify('error', errors + ' de ' + total + ' archivos no pudieron subirse.');
                    }
                    setTimeout(function() { window.location.reload(); }, 800);
                    return;
                }

                var file = fileArray[index];
                index++;

                // Override handleUpload's reload behavior: we reload only after the queue finishes.
                self._uploadSingle(dropzone, file, function(ok) {
                    if (!ok) errors++;
                    uploadNext();
                });
            };

            uploadNext();
        },

        /**
         * Uploads a single file without reloading, calling back with success boolean.
         * Used internally by _uploadQueue.
         *
         * @param {HTMLElement} dropzone - The dropzone container
         * @param {File} file            - The file to upload
         * @param {function} callback    - Called with true on success, false on error
         */
        _uploadSingle: function(dropzone, file, callback) {
            var self = this;
            var url = dropzone.getAttribute('data-tul-upload-url');
            var progressEl = dropzone.querySelector('[data-tul-upload-progress]');
            var fileInput = dropzone.querySelector('[data-tul-file-input]');

            var docType = dropzone.querySelector('[data-tul-doc-type]');
            if (docType && (docType.value === '' || docType.value === null)) {
                self.notify('error', 'Selecciona el tipo de documento antes de subir el archivo.');
                if (fileInput) fileInput.value = '';
                callback(false);
                return;
            }

            if (progressEl) {
                progressEl.removeAttribute('hidden');
                progressEl.textContent = '0%';
            }

            var formData = new FormData();
            if (this.csrfName && this.csrfHash) {
                formData.append(this.csrfName, this.csrfHash);
            }
            formData.append('file', file);

            var namedFields = dropzone.querySelectorAll('input[name], select[name], textarea[name]');
            for (var n = 0; n < namedFields.length; n++) {
                var fld = namedFields[n];
                if (fld.type === 'file') continue;
                if (fld.name === this.csrfName) continue;
                formData.append(fld.name, fld.value);
            }

            var docTypeSelect = dropzone.querySelector('[data-tul-doc-type-select]');
            if (docTypeSelect && !docTypeSelect.name) {
                formData.set('tipo', docTypeSelect.value);
            }

            this.ajax('POST', url, formData, {
                onProgress: function(event) {
                    if (progressEl && event.lengthComputable) {
                        var percent = Math.round((event.loaded / event.total) * 100);
                        progressEl.textContent = percent + '%';
                    }
                },
                onSuccess: function(xhr) {
                    if (progressEl) {
                        progressEl.setAttribute('hidden', '');
                        progressEl.textContent = '';
                    }
                    var response;
                    try { response = JSON.parse(xhr.responseText); } catch (e) { response = {}; }
                    if (response.success === false) {
                        self.notify('error', response.message || 'No se pudo subir: ' + file.name);
                        callback(false);
                        return;
                    }
                    callback(true);
                },
                onError: function(xhr) {
                    if (progressEl) {
                        progressEl.setAttribute('hidden', '');
                        progressEl.textContent = '';
                    }
                    self.handleError(xhr, {});
                    callback(false);
                }
            });
        },

        /**
         * Handles the AJAX file upload for a dropzone.
         *
         * @param {HTMLElement} dropzone - The dropzone container element
         * @param {File} file            - The selected file to upload
         */
        handleUpload: function(dropzone, file) {
            var self = this;
            var url = dropzone.getAttribute('data-tul-upload-url');
            var step = dropzone.getAttribute('data-tul-step');
            var progressEl = dropzone.querySelector('[data-tul-upload-progress]');
            var fileInput = dropzone.querySelector('[data-tul-file-input]');

            // Guard: if there's a document-type selector and it's empty, warn
            // the user instead of sending an incomplete request to the backend.
            var docType = dropzone.querySelector('[data-tul-doc-type]');
            if (docType && (docType.value === '' || docType.value === null)) {
                self.notify('error', 'Selecciona el tipo de documento antes de subir el archivo.');
                if (fileInput) {
                    fileInput.value = '';
                }
                return;
            }

            // Show progress indicator
            if (progressEl) {
                progressEl.removeAttribute('hidden');
                progressEl.textContent = '0%';
            }

            // Build FormData
            var formData = new FormData();

            // Add CSRF token
            if (this.csrfName && this.csrfHash) {
                formData.append(this.csrfName, this.csrfHash);
            }

            // Add the actual file (backend reads $_FILES['file'])
            formData.append('file', file);

            // Append every named input/select/textarea inside the dropzone
            // (documento_id, tipo, cobro_correcto, etc.) so each step sends
            // the fields its endpoint expects.
            var namedFields = dropzone.querySelectorAll('input[name], select[name], textarea[name]');
            for (var n = 0; n < namedFields.length; n++) {
                var fld = namedFields[n];
                // Skip the file input and the CSRF (already appended)
                if (fld.type === 'file') continue;
                if (fld.name === this.csrfName) continue;
                formData.append(fld.name, fld.value);
            }

            // Step 4 special case: doc-type select has no name attribute; it
            // drives the "tipo" field via data-tul-doc-type-select.
            var docTypeSelect = dropzone.querySelector('[data-tul-doc-type-select]');
            if (docTypeSelect && !docTypeSelect.name) {
                formData.set('tipo', docTypeSelect.value);
            }

            // POST upload
            this.ajax('POST', url, formData, {
                onProgress: function(event) {
                    if (progressEl && event.lengthComputable) {
                        var percent = Math.round((event.loaded / event.total) * 100);
                        progressEl.textContent = percent + '%';
                    }
                },
                onSuccess: function(xhr) {
                    // Hide progress
                    if (progressEl) {
                        progressEl.setAttribute('hidden', '');
                        progressEl.textContent = '';
                    }

                    // Parse response
                    var response;
                    try {
                        response = JSON.parse(xhr.responseText);
                    } catch (e) {
                        response = {};
                    }

                    // Clear file input so the same file can be re-selected
                    if (fileInput) {
                        fileInput.value = '';
                    }

                    // El backend puede devolver success:false con HTTP 200
                    if (response.success === false) {
                        self.notify('error', response.message || 'No se pudo subir el archivo.');
                        return;
                    }

                    // Recargar para mostrar el documento con su preview correcto.
                    // La galería se renderiza server-side con la info completa del archivo.
                    self.notify('success', response.message || 'Archivo subido correctamente');
                    setTimeout(function() {
                        window.location.reload();
                    }, 800);
                },
                onError: function(xhr) {
                    // Hide progress
                    if (progressEl) {
                        progressEl.setAttribute('hidden', '');
                        progressEl.textContent = '';
                    }

                    // Clear file input
                    if (fileInput) {
                        fileInput.value = '';
                    }

                    // Show error notification
                    self.handleError(xhr, {});
                }
            });
        },

        // ------------------------------------------------------------------
        // AJAX Delete (Task 4.5)
        // ------------------------------------------------------------------

        /**
         * Binds click handlers on all [data-tul-delete-btn] elements.
         * On click, shows a confirmation dialog and proceeds with deletion.
         */
        bindDeleteButtons: function() {
            var self = this;
            var buttons = document.querySelectorAll('[data-tul-delete-btn]');

            for (var i = 0; i < buttons.length; i++) {
                (function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        self.handleDelete(btn);
                    });
                })(buttons[i]);
            }
        },

        /**
         * Handles the AJAX delete operation for a document.
         *
         * Flow:
         *  1. Confirm with user
         *  2. Disable button, add loading state
         *  3. Build FormData with CSRF + doc identifier
         *  4. POST to delete URL
         *  5. On success: remove [data-tul-doc] parent from DOM, notify
         *  6. On error: re-enable button, show error
         *
         * @param {HTMLElement} btn - The delete button element
         */
        handleDelete: function(btn) {
            var self = this;

            // Step 1: Confirmation
            if (!window.confirm('¿Eliminar este documento?')) {
                return;
            }

            var url = btn.getAttribute('data-tul-delete-url');
            if (!url) {
                return;
            }

            // Step 2: Disable button and show loading
            btn.disabled = true;
            btn.classList.add('tul-loading');

            // Step 3: Build FormData with CSRF token and doc identifier
            var data = new FormData();
            if (self.csrfName && self.csrfHash) {
                data.append(self.csrfName, self.csrfHash);
            }

            // Support both data-tul-doc-id and data-tul-doc-file identifiers
            var docId = btn.getAttribute('data-tul-doc-id');
            var docFile = btn.getAttribute('data-tul-doc-file');
            if (docId) {
                data.append('id', docId);
            }
            if (docFile) {
                data.append('file', docFile);
            }

            // Some delete endpoints require the tramite id in the body
            var tramiteId = btn.getAttribute('data-tul-tramite-id');
            if (tramiteId) {
                data.append('tramite_id', tramiteId);
            }

            var shouldReload = btn.hasAttribute('data-tul-reload');

            // Step 4: POST to delete URL
            self.ajax('POST', url, data, {
                onSuccess: function(xhr) {
                    var json = null;
                    try {
                        json = JSON.parse(xhr.responseText);
                    } catch (e) {
                        json = null;
                    }

                    if (json && json.success === false) {
                        self.handleError(xhr, { btn: btn });
                        self.notify('error', json.message || 'No se pudo eliminar.');
                        return;
                    }

                    // Remove the [data-tul-doc] parent from DOM
                    var docContainer = btn.closest('[data-tul-doc]');
                    if (docContainer && docContainer.parentNode) {
                        docContainer.parentNode.removeChild(docContainer);
                    }

                    self.notify('success', (json && json.message) ? json.message : 'Documento eliminado');

                    // Removing a doc can change the gate state (e.g. evidencias) —
                    // reload so the progressive disclosure re-evaluates.
                    if (shouldReload) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 700);
                    }
                },
                onError: function(xhr) {
                    // Re-enable button and show error
                    self.handleError(xhr, { btn: btn });
                }
            });
        },

        // ------------------------------------------------------------------
        // Notes / Bitácora (Task 4.5)
        // ------------------------------------------------------------------

        /**
         * Binds submit handlers on all note forms.
         * Supports two DOM structures:
         *  - Direct form: <form data-tul-notes ...>
         *  - Wrapped form: <div data-tul-notes ...><form data-tul-note-form>...</form></div>
         */
        bindNoteForms: function() {
            var self = this;
            var containers = document.querySelectorAll('[data-tul-notes]');

            for (var i = 0; i < containers.length; i++) {
                (function(container) {
                    // Determine the actual form element
                    var form;
                    if (container.tagName === 'FORM') {
                        // Direct form structure (steps 1, 2, 3)
                        form = container;
                    } else {
                        // Wrapper div structure (steps 4, 5)
                        form = container.querySelector('[data-tul-note-form]');
                    }

                    if (!form) return;

                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        self.handleNoteSubmit(container, form);
                    });
                })(containers[i]);
            }
        },

        /**
         * Handles the AJAX note submission.
         *
         * Flow:
         *  1. Get textarea value — if empty, show error and return
         *  2. Disable button, add loading state
         *  3. Build FormData with CSRF + comentario
         *  4. POST to URL from data-tul-url
         *  5. On success: prepend note to list, clear textarea, re-enable, notify
         *  6. On error: re-enable button, show error
         *
         * @param {HTMLElement} container - The [data-tul-notes] container
         * @param {HTMLFormElement} form - The actual form element
         */
        handleNoteSubmit: function(container, form) {
            var self = this;

            // Step 1: Get textarea value
            var textarea = container.querySelector('[data-tul-note-input]');
            if (!textarea) return;

            var text = textarea.value.trim();
            if (!text) {
                self.notify('error', 'Escribe un comentario antes de enviar.');
                return;
            }

            // Step 2: Disable button and show loading
            var btn = container.querySelector('[data-tul-note-btn]');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('tul-loading');
            }

            // Step 3: Build FormData
            var url = container.getAttribute('data-tul-url') || form.getAttribute('data-tul-url');
            if (!url) {
                if (btn) { btn.disabled = false; btn.classList.remove('tul-loading'); }
                return;
            }

            var data = new FormData();
            if (self.csrfName && self.csrfHash) {
                data.append(self.csrfName, self.csrfHash);
            }
            data.append('comentario', text);

            // Step 4: POST to notes URL
            self.ajax('POST', url, data, {
                onSuccess: function(xhr) {
                    var json = null;
                    try {
                        json = JSON.parse(xhr.responseText);
                    } catch (e) {
                        json = null;
                    }

                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('tul-loading');
                    }

                    if (!json || json.success === false) {
                        self.notify('error', (json && json.message) ? json.message : 'No se pudo guardar la nota.');
                        return;
                    }

                    // Step 5: Render the new note using the item from the backend
                    var item = json.item || {
                        comment: text,
                        author: 'Tú',
                        createdAtLabel: 'Ahora'
                    };
                    self.prependNote(container, item);

                    // Clear textarea
                    textarea.value = '';

                    self.notify('success', json.message || 'Nota agregada');
                },
                onError: function(xhr) {
                    self.handleError(xhr, { btn: btn });
                }
            });
        },

        /**
         * Inserts a new note at the top of the notes list, matching the markup
         * variant used by the step partial (steps 1/3 vs steps 4/5).
         *
         * @param {HTMLElement} container - The [data-tul-notes] container
         * @param {object} item - Note data: { comment, author, createdAtLabel }
         */
        prependNote: function(container, item) {
            var meta = (item.createdAtLabel || 'Ahora') + ' · ' + (item.author || 'Sistema');
            var body = item.comment || '';

            // Determine the scope (form vs wrapper div).
            var scope = container;
            if (container.tagName === 'FORM') {
                scope = container.closest('.tul-rail--notes') || container.parentNode || container;
            }

            // Shared bitácora: a note posted to the general log must appear in
            // every list of the same group (e.g. steps 1 and 3 share it).
            var group = container.getAttribute('data-tul-notes-group') ||
                        (container.tagName === 'FORM' ? null : null);
            if (!group) {
                var scopedForm = scope.querySelector('[data-tul-notes-group]');
                if (scopedForm) {
                    group = scopedForm.getAttribute('data-tul-notes-group');
                }
            }

            var targets = [];
            if (group) {
                // All lists belonging to this shared group across the page
                var groupLists = document.querySelectorAll('[data-tul-notes-list][data-tul-notes-group="' + group + '"]');
                for (var g = 0; g < groupLists.length; g++) {
                    targets.push(groupLists[g]);
                }
            }
            if (targets.length === 0) {
                // Fallback: the single list within this rail
                var localList = scope.querySelector('[data-tul-notes-list]') ||
                                scope.querySelector('[data-tul-note-list]');
                if (localList) {
                    targets.push(localList);
                }
            }

            for (var t = 0; t < targets.length; t++) {
                var list = targets[t];
                var isCompact = list.hasAttribute('data-tul-note-list'); // steps 4/5
                var noteEl = document.createElement('div');

                if (isCompact) {
                    noteEl.className = 'tul-note-item';
                    var m1 = document.createElement('span');
                    m1.className = 'tul-note-item__meta';
                    m1.textContent = meta;
                    var b1 = document.createElement('p');
                    b1.className = 'tul-note-item__body';
                    b1.textContent = body;
                    noteEl.appendChild(m1);
                    noteEl.appendChild(b1);
                } else {
                    noteEl.className = 'tul-notes__item';
                    var m2 = document.createElement('span');
                    m2.className = 'tul-notes__item-meta';
                    m2.textContent = meta;
                    var b2 = document.createElement('span');
                    b2.className = 'tul-notes__item-body';
                    b2.textContent = body;
                    noteEl.appendChild(m2);
                    noteEl.appendChild(b2);
                }

                list.removeAttribute('hidden');
                list.classList.remove('is-hidden');
                if (list.firstChild) {
                    list.insertBefore(noteEl, list.firstChild);
                } else {
                    list.appendChild(noteEl);
                }
            }

            // Hide empty-state messages (group-wide if grouped, else local)
            var emptyStates;
            if (group) {
                emptyStates = document.querySelectorAll('[data-tul-notes-group="' + group + '"][data-tul-notes-empty], [data-tul-notes-group="' + group + '"] [data-tul-notes-empty]');
            } else {
                emptyStates = scope.querySelectorAll('[data-tul-note-empty], [data-tul-notes-empty]');
            }
            for (var e = 0; e < emptyStates.length; e++) {
                emptyStates[e].setAttribute('hidden', '');
            }
        },

        /**
         * Escapes HTML special characters to prevent XSS when inserting user text.
         *
         * @param {string} str - Raw text to escape
         * @returns {string} - Escaped HTML-safe string
         */
        escapeHtml: function(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        },

        // ------------------------------------------------------------------
        // Service composition (principal + associated tipos) — Step 1
        // ------------------------------------------------------------------

        /**
         * Binds the service composition controls: change principal tipo,
         * add / change / delete associated tipos. All via AJAX.
         */
        bindServices: function() {
            var self = this;
            var panel = document.querySelector('[data-tul-services]');
            if (!panel) return;

            var tramiteId = panel.getAttribute('data-tul-tramite-id');
            var principalUrl = panel.getAttribute('data-tul-svc-principal-url');
            var addUrl = panel.getAttribute('data-tul-svc-add-url');
            var updateUrl = panel.getAttribute('data-tul-svc-update-url');
            var deleteUrl = panel.getAttribute('data-tul-svc-delete-url');

            // Change principal tipo
            var principalBtn = panel.querySelector('[data-tul-svc-principal-btn]');
            if (principalBtn) {
                principalBtn.addEventListener('click', function() {
                    var sel = panel.querySelector('[data-tul-svc-principal-select]');
                    if (!sel || !sel.value) return;
                    var data = new FormData();
                    self.appendCsrf(data);
                    data.append('tramite_id', tramiteId);
                    data.append('tra_tipos_id', sel.value);
                    self.svcRequest(principalUrl, data, principalBtn, function() {
                        panel.setAttribute('data-tul-principal-id', sel.value);
                        self.notify('success', 'Tipo principal actualizado.');
                    });
                });
            }

            // Add associated tipo
            var addBtn = panel.querySelector('[data-tul-svc-add-btn]');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    var sel = panel.querySelector('[data-tul-svc-add-select]');
                    if (!sel || !sel.value) {
                        self.notify('error', 'Selecciona un tipo para agregar.');
                        return;
                    }
                    var label = sel.options[sel.selectedIndex].textContent;
                    var tipoId = sel.value;
                    var data = new FormData();
                    self.appendCsrf(data);
                    data.append('tramite_id', tramiteId);
                    data.append('tra_tipos_id', tipoId);
                    self.svcRequest(addUrl, data, addBtn, function(json) {
                        self.addServiceItem(panel, json.asociado_id, tipoId, label);
                        sel.value = '';
                        self.refreshSelect2(sel);
                        self.notify('success', 'Tipo asociado agregado.');
                    });
                });
            }

            // Delegate change / delete on associated items
            var list = panel.querySelector('[data-tul-svc-list]');
            if (list) {
                list.addEventListener('change', function(e) {
                    var sel = e.target.closest('[data-tul-svc-item-select]');
                    if (!sel) return;
                    var item = sel.closest('[data-tul-svc-item]');
                    var asociadoId = item.getAttribute('data-asociado-id');
                    var data = new FormData();
                    self.appendCsrf(data);
                    data.append('tramite_id', tramiteId);
                    data.append('asociado_id', asociadoId);
                    data.append('tra_tipos_id', sel.value);
                    self.svcRequest(updateUrl, data, null, function() {
                        self.notify('success', 'Tipo asociado actualizado.');
                    });
                });

                list.addEventListener('click', function(e) {
                    var btn = e.target.closest('[data-tul-svc-delete-btn]');
                    if (!btn) return;
                    if (!window.confirm('¿Eliminar este tipo asociado?')) return;
                    var item = btn.closest('[data-tul-svc-item]');
                    var asociadoId = item.getAttribute('data-asociado-id');
                    var data = new FormData();
                    self.appendCsrf(data);
                    data.append('tramite_id', tramiteId);
                    data.append('asociado_id', asociadoId);
                    self.svcRequest(deleteUrl, data, btn, function() {
                        if (item.parentNode) {
                            item.parentNode.removeChild(item);
                        }
                        self.notify('success', 'Tipo asociado eliminado.');
                    });
                });
            }
        },

        /**
         * Appends the current CSRF token to a FormData payload.
         */
        appendCsrf: function(data) {
            if (this.csrfName && this.csrfHash) {
                data.append(this.csrfName, this.csrfHash);
            }
        },

        /**
         * Generic service AJAX request. Backend uses {status:'success'|'error'}.
         */
        svcRequest: function(url, data, btn, onOk) {
            var self = this;
            if (btn) { btn.disabled = true; btn.classList.add('tul-loading'); }
            this.ajax('POST', url, data, {
                onSuccess: function(xhr) {
                    if (btn) { btn.disabled = false; btn.classList.remove('tul-loading'); }
                    var json = null;
                    try { json = JSON.parse(xhr.responseText); } catch (e) { json = null; }
                    if (!json || json.status === 'error') {
                        self.notify('error', (json && json.message) ? json.message : 'No se pudo completar la operación.');
                        return;
                    }
                    if (json.status === 'exists') {
                        self.notify('error', json.message || 'Ese tipo ya está ligado.');
                        return;
                    }
                    onOk(json || {});
                },
                onError: function(xhr) {
                    self.handleError(xhr, { btn: btn });
                }
            });
        },

        /**
         * Inserts a new associated-service item into the list.
         */
        addServiceItem: function(panel, asociadoId, tipoId, label) {
            var list = panel.querySelector('[data-tul-svc-list]');
            if (!list) return;

            var empty = list.querySelector('[data-tul-svc-empty]');
            if (empty) { empty.parentNode.removeChild(empty); }

            var item = document.createElement('div');
            item.className = 'tul-services__item';
            item.setAttribute('data-tul-svc-item', '');
            item.setAttribute('data-asociado-id', asociadoId || '');

            var nameSpan = document.createElement('span');
            nameSpan.className = 'tul-services__item-name';
            nameSpan.textContent = label;
            item.appendChild(nameSpan);

            var delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'tul-btn tul-btn--danger tul-btn--sm';
            delBtn.setAttribute('data-tul-svc-delete-btn', '');
            delBtn.title = 'Eliminar tipo asociado';
            delBtn.innerHTML = '&times;';
            item.appendChild(delBtn);

            list.appendChild(item);
        }
    };

    // ------------------------------------------------------------------
    // Bootstrap
    // ------------------------------------------------------------------

    document.addEventListener('DOMContentLoaded', function() {
        TUL.init();
    });

    // Expose for debugging and external access
    window.TUL = TUL;

})(window, document);
