/* global jQuery */
window.__SGL_TRAMITESN_LOADED = true;
console.log('tramitesn_update_v2 loaded');
(function () {
	'use strict';

	function getConfig() {
		return window.SGL_TRAMITESN_UPDATE_V2 || null;
	}

	function init() {
		var cfg = getConfig();
		console.log('tramitesn_update_v2 init', !!cfg);
		if (!cfg) return;

		var maxStep = parseInt(cfg.maxStep || 3, 10);
		var csrfName = cfg.csrfName;
		var csrfHash = cfg.csrfHash;

		var TRAMITE_ID = parseInt(cfg.tramiteId || 0, 10);
		var TIPOS_OPTIONS = cfg.tiposOptions || {};

		var PRINCIPAL_TIPO_ID = parseInt(cfg.principalTipoId || 0, 10);
		var TIPOS_EXISTENTES = new Set(Array.isArray(cfg.tiposExistentes) ? cfg.tiposExistentes : []);
		if (PRINCIPAL_TIPO_ID) {
			TIPOS_EXISTENTES.add(PRINCIPAL_TIPO_ID);
		}

		// El gusanito es fijo al estatus (no se mueve al navegar en el wizard)
		var TRA_STATUS_ID = parseInt(cfg.statusId || 0, 10);
		function statusToStep(statusId) {
			var map = {
				11: 1,
				22: 2,
				25: 3,
				26: 3,
				27: 3,
				23: 3,
				28: 3,
				20: 3,
				21: 3,
				29: 1
			};
			return (typeof map[statusId] !== 'undefined') ? map[statusId] : 1;
		}
		var statusStep = statusToStep(TRA_STATUS_ID);

		function updateCsrf(newHash) {
			if (!newHash) return;
			csrfHash = newHash;
			var input = document.querySelector('input[name="' + csrfName + '"]');
			if (input) input.value = newHash;
		}

		function renderStepperFixed() {
			var stepper = document.getElementById('sglStepper');
			if (!stepper) return;
			var steps = stepper.querySelectorAll('.sgl-wizard-step');
			steps.forEach(function (node) {
				var s = parseInt(node.getAttribute('data-step'), 10);
				node.classList.toggle('is-active', s === statusStep);
				node.classList.toggle('is-completed', s < statusStep);
			});
			var fill = document.getElementById('sglStepperFill');
			if (fill) {
				fill.style.width = ((statusStep - 1) / (maxStep - 1)) * 100 + '%';
			}
		}

		function setTiposMsg(text, isError) {
			var el = document.getElementById('tiposMsg');
			if (!el) return;
			el.textContent = '';
			if (!text) return;
			var span = document.createElement('span');
			span.className = isError ? 'text-danger' : 'text-success';
			span.textContent = (isError ? '✕ ' : '✓ ') + text;
			el.appendChild(span);
		}

		function refreshBadgesBar() {
			var badges = document.getElementById('tiposLigadosBadges');
			if (!badges) return;
			badges.innerHTML = '';
			var ids = Array.from(TIPOS_EXISTENTES);
			if (ids.length === 0) {
				var none = document.createElement('span');
				none.className = 'badge badge-secondary badge-pill sgl-pill';
				none.textContent = 'Sin tipos ligados';
				badges.appendChild(none);
				return;
			}
			ids.forEach(function (id) {
				var label = (TIPOS_OPTIONS && TIPOS_OPTIONS[id]) ? TIPOS_OPTIONS[id] : ('Tipo #' + id);
				var span = document.createElement('span');
				span.className = 'badge badge-success badge-pill sgl-pill';
				span.textContent = '✓ ' + label;
				badges.appendChild(span);
			});
		}

		function refreshHeaderBadges() {
			var header = document.getElementById('headerTiposLigados');
			if (!header) return;
			header.innerHTML = '';
			var ids = Array.from(TIPOS_EXISTENTES);
			if (ids.length === 0) {
				var na = document.createElement('span');
				na.className = 'badge badge-secondary badge-pill sgl-pill';
				na.textContent = 'N/A';
				header.appendChild(na);
				return;
			}
			ids.slice(0, 3).forEach(function (id) {
				var b = document.createElement('span');
				b.className = 'badge badge-secondary badge-pill sgl-pill';
				b.textContent = (TIPOS_OPTIONS && TIPOS_OPTIONS[id]) ? TIPOS_OPTIONS[id] : ('Tipo #' + id);
				header.appendChild(b);
			});
			if (ids.length > 3) {
				var more = document.createElement('span');
				more.className = 'badge badge-secondary badge-pill sgl-pill';
				more.textContent = '+' + (ids.length - 3);
				header.appendChild(more);
			}
		}

		function createPendienteRow(tipoId) {
			var label = (TIPOS_OPTIONS && TIPOS_OPTIONS[tipoId]) ? TIPOS_OPTIONS[tipoId] : ('Tipo #' + tipoId);
			var row = document.createElement('div');
			row.className = 'card';
			row.setAttribute('data-tipo-id', String(tipoId));
			row.innerHTML = '' +
				'<div class="card-body py-2 d-flex align-items-center justify-content-between" style="gap:12px;">' +
					'<div>' +
						'<strong>' + label + '</strong>' +
						'<small class="text-muted d-block">Pendiente de guardar</small>' +
					'</div>' +
					'<div class="d-flex align-items-center" style="gap:8px;">' +
						'<button type="button" class="btn btn-sm btn-primary btnGuardarTipo">Guardar</button>' +
						'<span class="badge badge-secondary estado">…</span>' +
					'</div>' +
				'</div>';
			return row;
		}

		function createAsociadoCard(asociadoId, tipoId, label) {
			var card = document.createElement('div');
			card.className = 'card mb-2';
			card.setAttribute('data-asociado-id', String(asociadoId));
			card.setAttribute('data-tipo-id', String(tipoId));
			var canEdit = !!cfg.canEditAsociado;
			var canDelete = !!cfg.canDeleteAsociado;
			card.innerHTML = '' +
				'<div class="card-body py-2 sgl-associated-row">' +
					'<div>' +
						'<strong class="tipo-label">' + label + '</strong>' +
						'<small class="text-muted d-block">Asociado</small>' +
					'</div>' +
					'<div class="actions">' +
						(canEdit ? '<button type="button" class="btn btn-sm btn-outline-primary btnCambiarAsociado" data-toggle="modal" data-target="#modalEditAsociadoTipo" title="Cambiar"><i class="fas fa-pen"></i></button>' : '') +
						(canDelete ? '<button type="button" class="btn btn-sm btn-outline-danger btnEliminarAsociado" data-toggle="modal" data-target="#modalDeleteAsociado" title="Eliminar"><i class="fas fa-trash"></i></button>' : '') +
						'<span class="badge badge-success badge-pill sgl-pill" title="Ligado">✓</span>' +
					'</div>' +
				'</div>';
			return card;
		}

		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.btnGuardarTipo');
			if (!btn) return;
			var row = btn.closest('[data-tipo-id]');
			if (!row) return;
			var tipoId = parseInt(row.getAttribute('data-tipo-id') || '0', 10);
			if (!tipoId) return;

			if (PRINCIPAL_TIPO_ID && tipoId === PRINCIPAL_TIPO_ID) {
				var estadoBlocked = row.querySelector('.estado');
				if (estadoBlocked) {
					estadoBlocked.className = 'badge badge-danger estado';
					estadoBlocked.textContent = 'No permitido';
				}
				setTiposMsg('No puedes ligar el tipo principal como asociado.', true);
				return;
			}

			btn.disabled = true;
			var estado = row.querySelector('.estado');
			if (estado) {
				estado.className = 'badge badge-warning estado';
				estado.textContent = 'Guardando…';
			}

			var fd = new FormData();
			fd.append('tramite_id', String(TRAMITE_ID));
			fd.append('tra_tipos_id', String(tipoId));
			fd.append(csrfName, csrfHash);

			fetch(cfg.urls.servicesAdd, {
				method: 'POST',
				body: fd,
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			})
				.then(function (r) { return r.json(); })
				.then(function (json) {
					if (json && json.csrfHash) { updateCsrf(json.csrfHash); }
					if (json && (json.status === 'success' || json.status === 'exists')) {
						TIPOS_EXISTENTES.add(tipoId);
						if (estado) {
							estado.className = 'badge badge-success estado';
							estado.textContent = '✓ Guardado';
						}
						btn.remove();
						var list = document.getElementById('tiposAsociadosList');
						if (list && json.status === 'success') {
							var label = (json.label || (TIPOS_OPTIONS && TIPOS_OPTIONS[tipoId]) || ('Tipo #' + tipoId));
							var card = createAsociadoCard(json.asociado_id || 0, tipoId, label);
							if (!list.querySelector('[data-asociado-id]')) {
								list.innerHTML = '';
							}
							list.prepend(card);
							row.remove();
						}
						refreshBadgesBar();
						refreshHeaderBadges();
						setTiposMsg(json.message || 'Guardado correctamente', false);
						return;
					}
					if (estado) {
						estado.className = 'badge badge-danger estado';
						estado.textContent = 'Error';
					}
					btn.disabled = false;
					setTiposMsg((json && json.message) ? json.message : 'No se pudo guardar', true);
				})
				.catch(function () {
					if (estado) {
						estado.className = 'badge badge-danger estado';
						estado.textContent = 'Error';
					}
					btn.disabled = false;
					setTiposMsg('Error de red al guardar', true);
				});
		});

		function handleAgregarTipo() {
			var sel = document.getElementById('tra_tipos_add');
			var cont = document.getElementById('tiposPendientes');
			if (!sel || !cont) return;
			var tipoId = parseInt(sel.value || '0', 10);
			if (!tipoId) {
				setTiposMsg('Selecciona un tipo.', true);
				return;
			}
			if (PRINCIPAL_TIPO_ID && tipoId === PRINCIPAL_TIPO_ID) {
				setTiposMsg('No puedes ligar el tipo principal como asociado.', true);
				return;
			}
			if (TIPOS_EXISTENTES.has(tipoId) || cont.querySelector('[data-tipo-id="' + tipoId + '"]')) {
				setTiposMsg('Ese tipo ya está ligado o pendiente.', true);
				return;
			}
			cont.appendChild(createPendienteRow(tipoId));
			setTiposMsg('Tipo agregado a pendientes. Presiona Guardar.', false);
		}

		document.addEventListener('click', function (e) {
			var btn = e.target.closest('#btnAgregarTipo');
			if (!btn) return;
			e.preventDefault();
			handleAgregarTipo();
		});

		// Abrir modal de asociado: precargar ids
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.btnCambiarAsociado');
			if (!btn) return;
			var card = btn.closest('[data-asociado-id]');
			if (!card) return;
			var asociadoId = parseInt(card.getAttribute('data-asociado-id') || '0', 10);
			var tipoId = parseInt(card.getAttribute('data-tipo-id') || '0', 10);
			var hid = document.getElementById('asociadoIdInput');
			var sel = document.getElementById('asociadoTipoSelect');
			if (hid) hid.value = String(asociadoId);
			if (sel) sel.value = String(tipoId || '');
		});

		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.btnEliminarAsociado');
			if (!btn) return;
			var card = btn.closest('[data-asociado-id]');
			if (!card) return;
			var asociadoId = parseInt(card.getAttribute('data-asociado-id') || '0', 10);
			var hid = document.getElementById('deleteAsociadoIdInput');
			if (hid) hid.value = String(asociadoId);
		});

		var btnGuardarPrincipalTipo = document.getElementById('btnGuardarPrincipalTipo');
		if (btnGuardarPrincipalTipo) {
			btnGuardarPrincipalTipo.addEventListener('click', function () {
				var sel = document.getElementById('principalTipoSelect');
				var tipoId = parseInt((sel && sel.value) ? sel.value : '0', 10);
				if (!tipoId) {
					setTiposMsg('Selecciona un tipo para cambiar el principal.', true);
					return;
				}
				var fd = new FormData();
				fd.append('tramite_id', String(TRAMITE_ID));
				fd.append('tra_tipos_id', String(tipoId));
				fd.append(csrfName, csrfHash);
				fetch(cfg.urls.principalUpdateTipo, {
					method: 'POST',
					body: fd,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (r) { return r.json(); })
					.then(function (json) {
						if (json && json.csrfHash) { updateCsrf(json.csrfHash); }
						if (json && json.status === 'success') {
							PRINCIPAL_TIPO_ID = tipoId;
							var principalText = document.getElementById('principalTipoText');
							if (principalText) {
								principalText.textContent = json.label || (TIPOS_OPTIONS && TIPOS_OPTIONS[tipoId]) || principalText.textContent;
							}
							var mainSelect = document.querySelector('[name="tra_tipos_id"]');
							if (mainSelect) { mainSelect.value = String(tipoId); }
							TIPOS_EXISTENTES.add(tipoId);
							refreshBadgesBar();
							refreshHeaderBadges();
							setTiposMsg(json.message || 'Actualizado', false);
							try { window.jQuery && jQuery('#modalEditPrincipalTipo').modal('hide'); } catch (e) { /* noop */ }
							return;
						}
						setTiposMsg((json && json.message) ? json.message : 'No se pudo actualizar el tipo principal', true);
					})
					.catch(function () { setTiposMsg('Error de red al actualizar el tipo principal', true); });
			});
		}

		var btnGuardarAsociadoTipo = document.getElementById('btnGuardarAsociadoTipo');
		if (btnGuardarAsociadoTipo) {
			btnGuardarAsociadoTipo.addEventListener('click', function () {
				var asociadoId = parseInt((document.getElementById('asociadoIdInput') || {}).value || '0', 10);
				var sel = document.getElementById('asociadoTipoSelect');
				var tipoId = parseInt((sel && sel.value) ? sel.value : '0', 10);
				if (!asociadoId || !tipoId) {
					alert('Selecciona un tipo.');
					return;
				}
				var cardPre = document.querySelector('[data-asociado-id="' + asociadoId + '"]');
				var oldTipoId = cardPre ? parseInt(cardPre.getAttribute('data-tipo-id') || '0', 10) : 0;
				var fd = new FormData();
				fd.append('tramite_id', String(TRAMITE_ID));
				fd.append('asociado_id', String(asociadoId));
				fd.append('tra_tipos_id', String(tipoId));
				fd.append(csrfName, csrfHash);
				fetch(cfg.urls.servicesUpdate, {
					method: 'POST',
					body: fd,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (r) { return r.json(); })
					.then(function (json) {
						if (json && json.csrfHash) { updateCsrf(json.csrfHash); }
						if (json && json.status === 'success') {
							if (oldTipoId) { TIPOS_EXISTENTES.delete(oldTipoId); }
							TIPOS_EXISTENTES.add(tipoId);
							var card = document.querySelector('[data-asociado-id="' + asociadoId + '"]');
							if (card) {
								card.setAttribute('data-tipo-id', String(tipoId));
								var lbl = card.querySelector('.tipo-label');
								if (lbl) { lbl.textContent = json.label || (TIPOS_OPTIONS && TIPOS_OPTIONS[tipoId]) || lbl.textContent; }
							}
							refreshBadgesBar();
							refreshHeaderBadges();
							setTiposMsg(json.message || 'Actualizado', false);
							try { window.jQuery && jQuery('#modalEditAsociadoTipo').modal('hide'); } catch (e) { /* noop */ }
							return;
						}
						if (json && json.status === 'exists') {
							alert(json.message || 'Duplicado');
							return;
						}
						alert((json && json.message) ? json.message : 'No se pudo actualizar');
					})
					.catch(function () { alert('Error de red'); });
			});
		}

		var btnConfirmDeleteAsociado = document.getElementById('btnConfirmDeleteAsociado');
		if (btnConfirmDeleteAsociado) {
			btnConfirmDeleteAsociado.addEventListener('click', function () {
				var asociadoId = parseInt((document.getElementById('deleteAsociadoIdInput') || {}).value || '0', 10);
				if (!asociadoId) return;
				var fd = new FormData();
				fd.append('tramite_id', String(TRAMITE_ID));
				fd.append('asociado_id', String(asociadoId));
				fd.append(csrfName, csrfHash);
				fetch(cfg.urls.servicesDelete, {
					method: 'POST',
					body: fd,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (r) { return r.json(); })
					.then(function (json) {
						if (json && json.csrfHash) { updateCsrf(json.csrfHash); }
						if (json && json.status === 'success') {
							var card = document.querySelector('[data-asociado-id="' + asociadoId + '"]');
							if (card) {
								var tipoId = parseInt(card.getAttribute('data-tipo-id') || '0', 10);
								card.remove();
								if (tipoId) { TIPOS_EXISTENTES.delete(tipoId); }
							}
							var list = document.getElementById('tiposAsociadosList');
							if (list && !list.querySelector('[data-asociado-id]')) {
								list.innerHTML = '<div class="text-muted">No hay tipos asociados. Agrega uno arriba.</div>';
							}
							refreshBadgesBar();
							refreshHeaderBadges();
							setTiposMsg(json.message || 'Eliminado', false);
							try { window.jQuery && jQuery('#modalDeleteAsociado').modal('hide'); } catch (e) { /* noop */ }
							return;
						}
						alert((json && json.message) ? json.message : 'No se pudo eliminar');
					})
					.catch(function () { alert('Error de red'); });
			});
		}

		function validateStep(step) {
			var section = document.querySelector('.wizard-section[data-step="' + step + '"]');
			if (!section) return true;
			var valid = true;
			section.querySelectorAll('input, select, textarea').forEach(function (el) {
				if (el.hasAttribute('required')) {
					if (!el.value) {
						el.classList.add('is-invalid');
						valid = false;
					} else {
						el.classList.remove('is-invalid');
					}
				}
			});
			var ribbon = document.querySelector('.sgl-step-form-ribbon[data-ribbon-step="' + step + '"]');
			if (ribbon) {
				ribbon.classList.toggle('is-complete', valid);
				ribbon.classList.toggle('is-incomplete', !valid);
				ribbon.querySelector('.sgl-icon i').className = valid ? 'fas fa-check' : 'fas fa-exclamation';
				ribbon.querySelector('.sgl-text').textContent = valid
					? 'Datos completos en este paso'
					: 'Revisa los campos obligatorios de este paso';
			}
			return valid;
		}

		var isSaving = false;
		async function submitFormAjax() {
			if (isSaving) return false;
			isSaving = true;
			var form = document.getElementById('tramiteNuevoForm');
			if (!form) return false;
			var formData = new FormData(form);
			formData.set(csrfName, csrfHash);
			try {
				var resp = await fetch(form.action, {
					method: 'POST',
					body: formData,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				});
				var data = await resp.json();
				if (data && data.csrfHash) updateCsrf(data.csrfHash);
				if (data && data.success) {
					return true;
				}
				return false;
			} catch (err) {
				return false;
			} finally {
				isSaving = false;
			}
		}

		function initWizard() {
			if (!(window.jQuery && jQuery.fn && jQuery.fn.steps)) return;
			var $wizard = jQuery('#wizard');
			if (!$wizard.length) return;
			var targetIndex = Math.max(statusStep - 1, 0);

			function ensureStepTitles() {
				$wizard.find('> .steps a').each(function () {
					var $a = jQuery(this);
					if ($a.find('.step-title').length) return;
					var text = '';
					$a.contents().each(function () {
						if (this.nodeType === 3) text += this.nodeValue;
					});
					text = jQuery.trim(text);
					if (!text) return;
					$a.contents().filter(function () {
						return this.nodeType === 3;
					}).remove();
					var $num = $a.find('.number');
					if ($num.length) {
						$num.after(' <span class="step-title"></span>');
						$a.find('.step-title').text(text);
					} else {
						$a.append('<span class="step-title"></span>');
						$a.find('.step-title').text(text);
					}
				});
			}

			function syncWizardUi() {
				var $steps = $wizard.find('> .steps li');
				$steps.removeClass('done current');
				$steps.each(function (idx) {
					if (idx < targetIndex) {
						jQuery(this).addClass('done');
					} else if (idx === targetIndex) {
						jQuery(this).addClass('current');
					}
				});
			}

			$wizard.steps({
				headerTag: 'h3',
				bodyTag: 'section',
				transitionEffect: 'fade',
				autoFocus: true,
				startIndex: targetIndex,
				labels: {
					next: 'Siguiente',
					previous: 'Anterior',
					finish: 'Guardar'
				},
				onStepChanging: function (event, currentIndex, newIndex) {
					if (newIndex < currentIndex) return true;
					return validateStep(currentIndex + 1);
				},
				onStepChanged: function () {
					renderStepperFixed();
					syncGuardarButtons($wizard.steps('getCurrentIndex'));
				},
				onFinishing: function () {
					return validateStep(maxStep);
				},
				onFinished: function () {
					submitFormAjax();
				}
			});

			ensureStepTitles();

			// Mover acciones arriba (despues de los steps), como en el update original
			var stepsEl = $wizard.find('> .steps').get(0);
			var actionsEl = $wizard.find('> .actions').get(0);
			var contentEl = $wizard.find('> .content').get(0);
			if (stepsEl && actionsEl && contentEl) {
				$wizard.append(stepsEl);
				$wizard.append(actionsEl);
				$wizard.append(contentEl);
			}

			var $actionsList = $wizard.find('> .actions ul');
			if ($actionsList.length && !$actionsList.find('.sgl-action-guardar').length) {
				$actionsList.append(
					'<li class="sgl-action-guardar"><a href="#guardar" role="button"><i class="fas fa-save"></i> Guardar</a></li>'
				);
				$actionsList.on('click', '.sgl-action-guardar a', function (e) {
					e.preventDefault();
					submitFormAjax();
				});
			}

			function syncGuardarButtons(currentIndex) {
				var isLast = currentIndex === (maxStep - 1);
				var $finishLink = $wizard.find('> .actions a[href="#finish"]');
				var $finishLi = $finishLink.parent();
				var $customLi = $actionsList.find('.sgl-action-guardar');
				if (isLast) {
					$customLi.hide();
					$finishLi.show().addClass('sgl-action-guardar');
				} else {
					$customLi.show();
					$finishLi.hide().removeClass('sgl-action-guardar');
				}
			}

			// Forzar el gusanito fijo al estatus, incluso si algún plugin toca clases
			setTimeout(renderStepperFixed, 0);
			setTimeout(renderStepperFixed, 150);
			setTimeout(syncWizardUi, 0);
			setTimeout(syncWizardUi, 150);
			setTimeout(ensureStepTitles, 0);
			setTimeout(ensureStepTitles, 150);
			syncGuardarButtons(targetIndex);
		}

		function renderDerechosPreview(fileUrl, fileName) {
			var container = document.getElementById('documentos-container');
			if (!container) return;
			var ext = (fileName || '').split('.').pop().toLowerCase();
			var isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1;
			var wrapper = document.createElement('div');
			wrapper.className = 'file-preview';
			wrapper.setAttribute('data-file', fileName);
			wrapper.style.border = '1px solid #e5e7eb';
			wrapper.style.borderRadius = '6px';
			wrapper.style.padding = '6px';
			wrapper.style.background = '#f9fafb';
			wrapper.style.display = 'inline-block';
			wrapper.style.margin = '4px';
			wrapper.style.textAlign = 'center';
			wrapper.innerHTML =
				'<a href="' + fileUrl + '" target="_blank">' +
					(isImage
						? '<img src="' + fileUrl + '" alt="' + fileName + '" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">'
						: '<i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>') +
				'</a>' +
				'<p style="font-size:10px;margin-top:4px;max-width:80px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + fileName + '</p>';
			container.appendChild(wrapper);
		}

		function renderGestorPreview(fileUrl, fileName, docType) {
			var container = document.getElementById('gestor-container');
			if (!container) return;
			var ext = (fileName || '').split('.').pop().toLowerCase();
			var isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1;
			var docTypeLabel = docType;
			if (docType === 'tramite_recibido') {
				docTypeLabel = 'Tramite Entregado por Gestor';
			} else if (docType === 'acuse_recibo_cliente') {
				docTypeLabel = 'Acuse de Recibo del Cliente';
			} else if (docType === 'otro') {
				docTypeLabel = 'Otro';
			}
			var docBadge = docTypeLabel ? '<span class="badge badge-info" style="display:inline-block;margin-top:4px;">' + docTypeLabel + '</span>' : '';
			var wrapper = document.createElement('div');
			wrapper.className = 'file-preview';
			wrapper.setAttribute('data-file', fileName);
			if (docType) {
				wrapper.setAttribute('data-doc-type', docType);
			}
			wrapper.style.border = '1px solid #e5e7eb';
			wrapper.style.borderRadius = '6px';
			wrapper.style.padding = '6px';
			wrapper.style.background = '#f9fafb';
			wrapper.style.display = 'inline-block';
			wrapper.style.margin = '4px';
			wrapper.style.textAlign = 'center';
			wrapper.innerHTML =
				'<a href="' + fileUrl + '" target="_blank">' +
					(isImage
						? '<img src="' + fileUrl + '" alt="' + fileName + '" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">'
						: '<i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>') +
				'</a>' +
				'<p style="font-size:10px;margin-top:4px;max-width:80px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + fileName + '</p>' +
				docBadge;
			container.appendChild(wrapper);
		}

		function updatePagoGestorChips() {
			var ribbon = document.querySelector('.sgl-step-form-ribbon[data-ribbon-step="4"]');
			if (!ribbon) return;
			var hasTramite = ribbon.getAttribute('data-has-tramite-recibido') === '1';
			var hasAcuse = ribbon.getAttribute('data-has-acuse-recibo') === '1';
			var canCobrar = hasTramite && hasAcuse;

			var chipTramite = document.getElementById('chipTramiteRecibido');
			var chipAcuse = document.getElementById('chipAcuseRecibo');
			var chipCobrar = document.getElementById('chipPuedeCobrar');

			function setChipState(chip, isOk) {
				if (!chip) return;
				chip.classList.toggle('is-success', isOk);
				chip.classList.toggle('is-muted', !isOk);
			}

			setChipState(chipTramite, hasTramite);
			setChipState(chipAcuse, hasAcuse);
			if (canCobrar) {
				if (!chipCobrar) {
					chipCobrar = document.createElement('span');
					chipCobrar.id = 'chipPuedeCobrar';
					chipCobrar.className = 'sgl-status-chip is-success';
					chipCobrar.textContent = 'Ya se puede cobrar';
					var row = ribbon.querySelector('.sgl-status-row');
					if (row) row.appendChild(chipCobrar);
				}
				setChipState(chipCobrar, true);
			} else if (chipCobrar) {
				chipCobrar.remove();
			}
		}


		function initDropzoneDerechos() {
			if (typeof Dropzone === 'undefined') return;
			if (!cfg.urls || !cfg.urls.uploadComprobante || !cfg.urls.deleteComprobante) return;
			var el = document.querySelector('.dropzone-documentos, #miDropzone');
			if (!el) return;
			Dropzone.autoDiscover = false;
			var renamedFiles = {};
			var dz = el.dropzone || null;
			if (!dz) {
				dz = new Dropzone(el, {
					url: cfg.urls.uploadComprobante,
					autoProcessQueue: false,
					maxFilesize: 10,
					acceptedFiles: '.xml,.jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx',
					addRemoveLinks: true,
					dictRemoveFile: 'Quitar',
					params: (function(){
						var p = {};
						p[csrfName] = csrfHash;
						return p;
					})(),
					renameFile: function (file) {
						var randomHex = '-' + Array.from(crypto.getRandomValues(new Uint8Array(3)))
							.map(function (byte) { return byte.toString(16).padStart(2, '0'); })
							.join('');
						var originalName = file.name.split('.').slice(0, -1).join('.');
						var extension = file.name.split('.').pop();
						var newname = originalName + randomHex + '.' + extension;
						if (file.upload) {
							renamedFiles[file.upload.uuid] = newname;
						}
						return newname;
					}
				});
			}

			if (!dz.__sglDerechosBound) {
				dz.__sglDerechosBound = true;
				dz.on('sending', function (file, xhr, formData) {
					formData.append(csrfName, csrfHash);
				});

				dz.on('removedfile', function (file) {
					var renamedName = file.upload ? file.upload.filename : null;
					if (!renamedName) return;
					var fd = new FormData();
					fd.append('tramite_id', String(TRAMITE_ID));
					fd.append('file', renamedName);
					fd.append(csrfName, csrfHash);
					fetch(cfg.urls.deleteComprobante, {
						method: 'POST',
						body: fd,
						headers: { 'X-Requested-With': 'XMLHttpRequest' }
					}).then(function (r) { return r.json(); }).then(function (json) {
						if (json && json.csrfHash) { updateCsrf(json.csrfHash); }
						if (json && json.success) {
							var preview = document.querySelector('#documentos-container .file-preview[data-file="' + renamedName + '"]');
							if (preview) {
								preview.remove();
							}
						}
					});
					if (file.upload) delete renamedFiles[file.upload.uuid];
				});

				dz.on('success', function (file, response) {
					if (!(response && response.success && response.filePath)) return;
					var filePath = response.filePath || file.name;
					var ext = filePath.split('.').pop().toLowerCase();
					var isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1;
					var fileName = file.upload ? file.upload.filename : file.name;
					var iconPath = response.icon || '/public/assets/src/images/upload.svg';
					var filePreview =
						'<div class="file-preview" data-file="' + fileName + '" style="border:1px solid #ddd;border-radius:5px;padding:5px;background-color:#f9f9f9;display:inline-block;margin:4px;text-align:center;">' +
							(isImage
								? '<a href="' + response.filePath + '" target="_blank">' +
									'<img src="' + response.filePath + '" alt="' + (response.filePath || file.name) + '" data-file="' + fileName + '" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">' +
								'</a>'
								: '<a href="' + response.filePath + '" target="_blank">' +
									'<img src="' + iconPath + '" alt="File Icon" data-file="' + fileName + '" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">' +
								'</a>') +
							'<p style="font-size:10px;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + fileName + '</p>' +
						'</div>';
					var container = document.getElementById('documentos-container');
					if (container) container.insertAdjacentHTML('beforeend', filePreview);
				});
			}

			var btn = document.getElementById('btnSubirDocumentos');
			if (btn && !btn.__sglDerechosBtnBound) {
				btn.__sglDerechosBtnBound = true;
				btn.addEventListener('click', function (e) {
					e.preventDefault();
					if (dz.files && dz.files.length > 0) {
						dz.processQueue();
					}
				});
			}
		}

		function initDropzoneGestor() {
			if (typeof Dropzone === 'undefined') return;
			if (!cfg.urls || !cfg.urls.uploadPagoGestor || !cfg.urls.deletePagoGestor) return;
			var el = document.querySelector('.dropzone-gestor, #miDropzoneGestor');
			if (!el) return;
			var typeSelect = document.getElementById('pagoGestorComprobanteFinal');
			Dropzone.autoDiscover = false;
			var renamedFilesGestor = {};
			var dz = el.dropzone || null;
			if (dz && typeof dz.destroy === 'function') {
				dz.destroy();
				dz = null;
			}
			dz = new Dropzone(el, {
				url: cfg.urls.uploadPagoGestor,
				autoProcessQueue: false,
				maxFilesize: 10,
				acceptedFiles: '.xml,.jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx',
				addRemoveLinks: false,
				renameFile: function (file) {
					var randomHex = '-' + Array.from(crypto.getRandomValues(new Uint8Array(3)))
						.map(function (byte) { return byte.toString(16).padStart(2, '0'); })
						.join('');
					var originalName = file.name.split('.').slice(0, -1).join('.');
					var extension = file.name.split('.').pop();
					var newname = originalName + randomHex + '.' + extension;
					if (file.upload) {
						renamedFilesGestor[file.upload.uuid] = newname;
					}
					return newname;
				}
			});
			dz.on('sending', function (file, xhr, formData) {
				formData.append(csrfName, csrfHash);
				if (typeSelect && typeSelect.value) {
					formData.append('comprobante_final', typeSelect.value);
				}
			});

			dz.on('removedfile', function (file) {
				var uploadedName = file._sglUploadedName || (file.upload ? file.upload.filename : null);
				if (!uploadedName) return;
				var fd = new FormData();
				fd.append('tramite_id', String(TRAMITE_ID));
				fd.append('file', uploadedName);
				fd.append(csrfName, csrfHash);
				fetch(cfg.urls.deletePagoGestor, {
					method: 'POST',
					body: fd,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				}).then(function (r) { return r.json(); }).then(function (json) {
					if (json && json.csrfHash) { updateCsrf(json.csrfHash); }
					if (json && json.success) {
						var preview = document.querySelector('#gestor-container .file-preview[data-file="' + uploadedName + '"]');
						if (preview) {
							preview.remove();
						}
					}
				});
				if (file.upload) delete renamedFilesGestor[file.upload.uuid];
			});

			dz.on('success', function (file, response) {
				if (!(response && response.success && response.filePath)) return;
				var fileName = response.fileName || (file.upload ? file.upload.filename : file.name);
				if (fileName) {
					file._sglUploadedName = fileName;
				}
				var docType = response.comprobanteFinal || (typeSelect ? typeSelect.value : '');
				if (response.filePath && fileName) {
					renderGestorPreview(response.filePath, fileName, docType);
				}
				var ribbon = document.querySelector('.sgl-step-form-ribbon[data-ribbon-step="4"]');
				if (ribbon && docType) {
					if (docType === 'tramite_recibido') {
						ribbon.setAttribute('data-has-tramite-recibido', '1');
					} else if (docType === 'acuse_recibo_cliente') {
						ribbon.setAttribute('data-has-acuse-recibo', '1');
					}
					updatePagoGestorChips();
				}
				var modalText = document.getElementById('comprobanteFinalText');
				if (modalText && docType) {
					var modalLabel = docType;
					if (docType === 'tramite_recibido') {
						modalLabel = 'Tramite Entregado por Gestor';
					} else if (docType === 'acuse_recibo_cliente') {
						modalLabel = 'Acuse de Recibo del Cliente';
					} else if (docType === 'otro') {
						modalLabel = 'Otro';
					}
					modalText.textContent = modalLabel;
				}
				if (docType && window.jQuery) {
					jQuery('#modalComprobanteFinal').modal('show');
				}
			});


			var btn = document.getElementById('btnSubirGestor');
			if (btn && !btn.__sglGestorBtnBound) {
				btn.__sglGestorBtnBound = true;
				btn.addEventListener('click', function (e) {
					e.preventDefault();
					if (dz.files && dz.files.length > 0) {
						dz.processQueue();
					}
				});
			}
		}

		function renderFinalDocPreview(containerId, fileUrl, fileName, docId) {
			var container = document.getElementById(containerId);
			if (!container) return;
			container.innerHTML = '';
			var ext = (fileName || '').split('.').pop().toLowerCase();
			var isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1;
			var wrapper = document.createElement('div');
			wrapper.className = 'file-preview';
			wrapper.setAttribute('data-file', fileName);
			wrapper.style.border = '1px solid #ddd';
			wrapper.style.borderRadius = '5px';
			wrapper.style.padding = '5px';
			wrapper.style.backgroundColor = '#f9f9f9';
			wrapper.style.display = 'inline-block';
			wrapper.style.margin = '4px';
			wrapper.style.textAlign = 'center';
			wrapper.innerHTML =
				'<a href="' + fileUrl + '" target="_blank">' +
					(isImage
						? '<img src="' + fileUrl + '" alt="' + fileName + '" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">'
						: '<i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>') +
				'</a>' +
				'<p style="font-size:10px;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + fileName + '</p>' +
				'<button type="button" class="btn btn-sm btn-danger btn-delete-final-doc mt-1" data-doc-id="' + docId + '" data-file="' + fileName + '">X</button>';
			container.appendChild(wrapper);
		}

		function initFinalDocs() {
			if (!cfg.urls || !cfg.urls.uploadFinalDocBase || !cfg.urls.deleteFinalDoc) return;

			function setFinalButtonLabel(docId, hasFile) {
				var button = document.getElementById(docId === 16 ? 'btnSubirFinalDoc16' : 'btnSubirFinalDoc17');
				if (!button) return;
				button.setAttribute('data-has-file', hasFile ? '1' : '0');
				button.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Subir archivo';
			}

			function bindFinalNative(buttonId, inputId, containerId, docId) {
				var btn = document.getElementById(buttonId);
				var input = document.getElementById(inputId);
				if (!btn || !input) return;

				btn.addEventListener('click', function (e) {
					e.preventDefault();
					input.click();
				});

				input.addEventListener('change', function () {
					var file = input.files && input.files[0] ? input.files[0] : null;
					if (!file) return;
					var fd = new FormData();
					fd.append('file', file);
					fd.append(csrfName, csrfHash);
					fetch(cfg.urls.uploadFinalDocBase + TRAMITE_ID + '/' + docId, {
						method: 'POST',
						body: fd,
						headers: { 'X-Requested-With': 'XMLHttpRequest' }
					})
						.then(function (r) {
							return r.text().then(function (text) {
								var parsed = null;
								try {
									parsed = text ? JSON.parse(text) : null;
								} catch (e) {
									parsed = null;
								}
								return {
									httpOk: r.ok,
									httpStatus: r.status,
									json: parsed
								};
							});
						})
						.then(function (res) {
							if (res.json && res.json.csrfHash) updateCsrf(res.json.csrfHash);
							if (!res.httpOk || !(res.json && res.json.success)) {
								alert((res.json && res.json.message) ? res.json.message : ('No se pudo subir el archivo. HTTP ' + res.httpStatus));
								return;
							}
							var uploadedName = res.json.fileName || file.name;
							if (res.json.filePath && uploadedName) {
								renderFinalDocPreview(containerId, res.json.filePath, uploadedName, docId);
								setFinalButtonLabel(docId, true);
							}
						})
						.catch(function () {
							alert('Error de red al subir el archivo.');
						})
						.finally(function () {
							input.value = '';
						});
				});

				setFinalButtonLabel(docId, btn.getAttribute('data-has-file') === '1');
			}

			document.addEventListener('click', function (e) {
				var btnDelete = e.target.closest('.btn-delete-final-doc');
				if (!btnDelete) return;
				var docId = parseInt(btnDelete.getAttribute('data-doc-id') || '0', 10);
				var fileName = btnDelete.getAttribute('data-file') || '';
				if (!docId || !fileName) return;
				var fd = new FormData();
				fd.append('tramite_id', String(TRAMITE_ID));
				fd.append('documento_id', String(docId));
				fd.append('file', fileName);
				fd.append(csrfName, csrfHash);
				fetch(cfg.urls.deleteFinalDoc, {
					method: 'POST',
					body: fd,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				}).then(function (r) { return r.json(); }).then(function (json) {
					if (json && json.csrfHash) updateCsrf(json.csrfHash);
					if (json && json.success) {
						var containerId = docId === 16 ? 'final-doc-16-container' : 'final-doc-17-container';
						var container = document.getElementById(containerId);
						if (container) container.innerHTML = '<div class="text-muted">Sin documento.</div>';
						setFinalButtonLabel(docId, false);
					}
				});
			});

			bindFinalNative('btnSubirFinalDoc16', 'nativeFinalDoc16', 'final-doc-16-container', 16);
			bindFinalNative('btnSubirFinalDoc17', 'nativeFinalDoc17', 'final-doc-17-container', 17);
		}

		function initPagoGestorForm() {
			var form = document.getElementById('pagoGestorFormCustom');
			if (!form) return;
			var msg = document.getElementById('pagoGestorMessage');
			var submitBtn = form.querySelector('[data-submit="pago-gestor"]');
			var inFlight = false;

			form.addEventListener('submit', function (e) {
				e.preventDefault();
				if (inFlight) return;
				inFlight = true;
				if (submitBtn) submitBtn.disabled = true;
				if (msg) {
					msg.style.display = 'none';
					msg.className = 'alert';
					msg.textContent = '';
				}
				var formData = new FormData(form);
				fetch(cfg.urls.updatePagoGestor, {
					method: 'POST',
					body: formData,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (resp) { return resp.json(); })
					.then(function (data) {
						if (data && data.csrfHash) updateCsrf(data.csrfHash);
						if (data && data.success) {
							if (msg) {
								msg.className = 'alert alert-success';
								msg.textContent = data.message || 'Guardado correctamente.';
								msg.style.display = 'block';
							}
							if (data.redirect) {
								var url = data.redirect;
								if (url.indexOf('/deskapp/tramites/update/') !== -1) {
									url = url.replace('/deskapp/tramites/update/', '/deskapp/tramitesn/update/');
								}
								window.location.href = url;
							}
							return;
						}
						if (msg) {
							msg.className = 'alert alert-danger';
							msg.textContent = (data && data.message) ? data.message : 'No se pudo guardar.';
							msg.style.display = 'block';
						}
					})
					.catch(function () {
						if (msg) {
							msg.className = 'alert alert-danger';
							msg.textContent = 'Error de red al guardar.';
							msg.style.display = 'block';
						}
					})
					.finally(function () {
						inFlight = false;
						if (submitBtn) submitBtn.disabled = false;
					});
			});
		}

		function initServiceCosts() {
			var list = document.getElementById('gestor_costos_tipo_servicio');
			var totalEl = document.getElementById('costo_tramite_total');
			var totalInput = document.getElementById('costo_tramite');
			var statusEl = document.getElementById('costosSaveStatus');
			console.log('initServiceCosts elements', {
				list: !!list,
				totalEl: !!totalEl,
				totalInput: !!totalInput
			});
			if (!list || !totalEl) return;

			function formatNumber(n) {
				var num = parseFloat(n);
				if (isNaN(num)) num = 0;
				return num.toFixed(2);
			}

			function updateTotal() {
				var total = 0;
				list.querySelectorAll('input[data-cost-id]').forEach(function (input) {
					var v = parseFloat(input.value) || 0;
					total += v;
				});
				var totalStr = formatNumber(total);
				totalEl.textContent = '$' + totalStr;
				if (totalInput) totalInput.value = totalStr;
				// Solo sumar los campos agrupados para Gasto Total
				var impuesto = parseFloat(document.getElementById('impuesto_gestoria') ? document.getElementById('impuesto_gestoria').value : 0) || 0;
				var comision = parseFloat(document.getElementById('gestoria_comision') ? document.getElementById('gestoria_comision').value : 0) || 0;
				var paqueteria = parseFloat(document.getElementById('costo_paqueteria') ? document.getElementById('costo_paqueteria').value : 0) || 0;
				var depositoGestor = parseFloat(document.getElementById('deposito_gestor') ? document.getElementById('deposito_gestor').value : 0) || 0;
				var saldoInput = document.getElementById('col_a_favor');
				var saldoInfo = document.getElementById('saldoPendienteInfo');
				var reembolsoSelect = document.getElementById('reembolso_status_id');
				var totalPago = document.getElementById('gestor_total_pago');
				var totalPagoText = document.getElementById('gasto_total_text');
				var breakdownText = document.getElementById('gasto_total_breakdown');
				var totalGastos = total + impuesto + comision + paqueteria;
				var saldo = total - depositoGestor;
				var saldoAbs = Math.abs(saldo);
				if (totalPago) {
					totalPago.value = formatNumber(totalGastos);
				}
				if (totalPagoText) {
					totalPagoText.textContent = '$' + formatNumber(totalGastos);
				}
				if (breakdownText) {
					breakdownText.textContent =
						'Costos: $' + formatNumber(total) +
						' + Honorarios: $' + formatNumber(impuesto) +
						' + Gratificacion: $' + formatNumber(comision) +
						' + Paqueteria: $' + formatNumber(paqueteria);
				}
				if (saldoInput) {
					saldoInput.value = formatNumber(saldo);
					saldoInput.setAttribute('readonly', 'readonly');
				}
				if (reembolsoSelect) {
					var targetStatus = Math.abs(saldo) > 0.0001 ? '22' : '24';
					if (String(reembolsoSelect.value) !== targetStatus) {
						reembolsoSelect.value = targetStatus;
						reembolsoSelect.dispatchEvent(new Event('change', { bubbles: true }));
					}
				}
				if (saldoInfo) {
					saldoInfo.classList.remove('is-sgl', 'is-gestor', 'is-even');
					if (saldo > 0.0001) {
						saldoInfo.classList.add('is-gestor');
						saldoInfo.textContent = 'Saldo pendiente a favor del Gestor: $' + formatNumber(saldoAbs);
					} else if (saldo < -0.0001) {
						saldoInfo.classList.add('is-sgl');
						saldoInfo.textContent = 'Saldo pendiente a favor SGL: $' + formatNumber(saldoAbs);
					} else {
						saldoInfo.classList.add('is-even');
						saldoInfo.textContent = 'Sin saldo pendiente';
					}
				}
			}

			function ensureRowStatus(row) {
				if (!row) return null;
				var rowStatus = row.querySelector('.sgl-cost-row-status');
				if (!rowStatus) {
					rowStatus = document.createElement('span');
					rowStatus.className = 'sgl-cost-row-status';
					row.appendChild(rowStatus);
				}
				return rowStatus;
			}

			function ensureRowIcon(row) {
				if (!row) return null;
				var rowIcon = row.querySelector('.sgl-cost-icon');
				if (!rowIcon) {
					rowIcon = document.createElement('span');
					rowIcon.className = 'sgl-cost-icon';
					rowIcon.setAttribute('aria-hidden', 'true');
					row.appendChild(rowIcon);
				}
				return rowIcon;
			}

			function showSaved(row) {
				if (statusEl) {
					statusEl.style.display = 'inline-flex';
					statusEl.textContent = 'Guardado';
					setTimeout(function () {
						statusEl.style.display = 'none';
					}, 1500);
				}
				if (row) {
					var rowStatus = ensureRowStatus(row);
					var rowIcon = ensureRowIcon(row);
					if (rowStatus) {
						rowStatus.textContent = 'Guardado';
					}
					if (rowIcon) {
						rowIcon.innerHTML = '<i class="fas fa-check"></i>';
					}
					row.classList.remove('is-error');
					row.classList.add('is-saved');
					setTimeout(function () {
						row.classList.remove('is-saved');
						if (rowStatus) {
							rowStatus.textContent = '';
						}
						if (rowIcon) {
							rowIcon.textContent = '';
						}
					}, 1400);
				}
			}

			function showError(row) {
				if (!row) return;
				var rowStatus = ensureRowStatus(row);
				var rowIcon = ensureRowIcon(row);
				if (rowStatus) {
					rowStatus.textContent = 'Error al guardar';
				}
				if (rowIcon) {
					rowIcon.innerHTML = '<i class="fas fa-times"></i>';
				}
				row.classList.remove('is-saved');
				row.classList.add('is-error');
				setTimeout(function () {
					row.classList.remove('is-error');
					if (rowStatus) {
						rowStatus.textContent = '';
					}
					if (rowIcon) {
						rowIcon.textContent = '';
					}
				}, 2000);
			}

			function bindInputs() {
				list.querySelectorAll('input[data-cost-id]').forEach(function (input) {
					input.addEventListener('input', updateTotal);
					input.addEventListener('keyup', function (e) {
						console.log('keyup costo_tramite', e.target && e.target.value);
						updateTotal();
					});
				});
			}

			function bindGastoFields() {
				['impuesto_gestoria', 'gestoria_comision', 'costo_paqueteria', 'deposito_gestor'].forEach(function (id) {
					var el = document.getElementById(id);
					if (!el) {
						console.log('missing gasto field', id);
						return;
					}
					console.log('bind gasto field', id);
					el.addEventListener('input', function(e) {
						console.log('input gasto', id, e.target && e.target.value);
						updateTotal();
					});
					el.addEventListener('keyup', function (e) {
						console.log('keyup gasto', id, e.target && e.target.value);
						updateTotal();
					});
				});
				var form = document.getElementById('pagoGestorFormCustom');
				if (form) {
					form.addEventListener('keyup', function (e) {
						var t = e.target;
						if (!t || !t.id) return;
						if (t.id === 'impuesto_gestoria' || t.id === 'gestoria_comision' || t.id === 'costo_paqueteria') {
							console.log('keyup gasto delegated', t.id, t.value);
							updateTotal();
						}
					});
				}
			}

			function loadCosts() {
				fetch(cfg.urls.getServiceCosts, {
					method: 'GET',
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (resp) { return resp.json(); })
					.then(function (data) {
						var hasRendered = !!list.querySelector('.sgl-cost-item');
						list.innerHTML = '';
						if (!Array.isArray(data) || data.length === 0) {
							if (hasRendered) {
								bindInputs();
								updateTotal();
								return;
							}
							list.innerHTML = '<div class="text-muted">No hay tramites asociados.</div>';
							updateTotal();
							return;
						}
						data.forEach(function (row) {
							var id = row.id;
							var name = row.tipo_tramite || ('Servicio #' + id);
							var val = (row.costo_tramite !== null && row.costo_tramite !== undefined) ? row.costo_tramite : 0;
							var canEdit = !!cfg.canEditPagoGestor;
							var item = document.createElement('div');
							item.className = 'sgl-cost-item';
							item.innerHTML =
								'<div class="sgl-cost-name">' + name + '</div>' +
								'<input type="number" class="form-control form-control-sm text-end sgl-cost-input" data-cost-id="' + id + '" value="' + val + '" ' + (canEdit ? '' : 'disabled') + ' />' +
								(canEdit
									? '<button type="button" class="btn btn-success btn-sm sgl-btn-pill sgl-cost-save" data-save-id="' + id + '" title="Guardar"><i class="fas fa-save"></i></button>'
									: '') +
								'<span class="sgl-cost-icon" aria-hidden="true"></span>' +
								'<span class="sgl-cost-row-status">Guardado</span>';
							list.appendChild(item);
						});
						bindInputs();
						updateTotal();
					})
					.catch(function () {
						if (list.querySelector('.sgl-cost-item')) {
							bindInputs();
							updateTotal();
							return;
						}
						list.innerHTML = '<div class="text-danger">Error al cargar costos.</div>';
					});
			}

			if (!window.__SGL_TRAMITESN_COSTS_BOUND) {
				window.__SGL_TRAMITESN_COSTS_BOUND = true;
				list.addEventListener('click', function (e) {
				var btn = e.target.closest('[data-save-id]');
				if (!btn) return;
				var id = btn.getAttribute('data-save-id');
				var input = list.querySelector('input[data-cost-id="' + id + '"]');
				if (!input) return;
				var row = btn.closest('.sgl-cost-item');
				var fd = new FormData();
				fd.append('id', id);
				fd.append('costo_tramite', input.value || '0');
				fd.append(csrfName, csrfHash);
				fetch(cfg.urls.updateServiceCost, {
					method: 'POST',
					body: fd,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (resp) { return resp.json(); })
					.then(function (data) {
						if (data && data.csrfHash) updateCsrf(data.csrfHash);
						if (data && data.status === 'success') {
							showSaved(row);
							return;
						}
						showError(row);
					})
					.catch(function () {
						showError(row);
					});
				});
			}

			bindGastoFields();

			bindInputs();
			updateTotal();

			loadCosts();
		}

		function initChangeStatus() {
			window.changeStatusTramite = function (tramiteId, statusId) {
				var id = parseInt(tramiteId || 0, 10);
				var st = parseInt(statusId || 0, 10);
				if (!id || !st) return;
				var fd = new FormData();
				fd.append('tramite_id', String(id));
				fd.append('status_id', String(st));
				fd.append(csrfName, csrfHash);
				fetch('/deskapp/tramites/change_status', {
					method: 'POST',
					body: fd,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (resp) { return resp.json(); })
					.then(function (data) {
						if (data && data.csrfHash) updateCsrf(data.csrfHash);
						if (data && data.success) {
							window.location.reload();
						}
					})
					.catch(function () {
						// Silencio: no mostrar alertas
					});
			};
		}

		function initCancelTramite() {
			var btn = document.getElementById('saveCancelBtn');
			var motivoEl = document.getElementById('motivo');
			var err = document.getElementById('cancelError');
			if (!btn || !motivoEl) return;
			btn.addEventListener('click', function () {
				var motivo = (motivoEl.value || '').trim();
				if (!motivo) {
					if (err) {
						err.textContent = 'Ingresa el motivo de la cancelacion.';
						err.style.display = 'block';
					}
					return;
				}
				if (err) {
					err.textContent = '';
					err.style.display = 'none';
				}
				var fd = new FormData();
				fd.append('tramite_id', String(TRAMITE_ID));
				fd.append('status_id', '21');
				fd.append('motivo', motivo);
				fd.append(csrfName, csrfHash);
				fetch('/deskapp/tramites/cancelar_tramite', {
					method: 'POST',
					body: fd,
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (resp) { return resp.json(); })
					.then(function (data) {
						if (data && data.csrfHash) updateCsrf(data.csrfHash);
						if (data && data.success) {
							window.location.reload();
							return;
						}
						if (err) {
							err.textContent = (data && data.message) ? data.message : 'No se pudo cancelar.';
							err.style.display = 'block';
						}
					})
					.catch(function () {
						if (err) {
							err.textContent = 'Error de red al cancelar.';
							err.style.display = 'block';
						}
					});
			});
		}

		function initGestorDependents() {
			var empresaSelect = document.getElementById('empresa_gestora_id');
			var gestorSelect = document.getElementById('gestor_id');
			if (!empresaSelect || !gestorSelect) return;

			function setGestorOptions(options, selectedId) {
				gestorSelect.innerHTML = '';
				var placeholder = document.createElement('option');
				placeholder.value = '';
				placeholder.textContent = 'Seleccione un Gestor';
				gestorSelect.appendChild(placeholder);
				Object.keys(options || {}).forEach(function (key) {
					var opt = document.createElement('option');
					opt.value = key;
					opt.textContent = options[key];
					if (selectedId && String(selectedId) === String(key)) {
						opt.selected = true;
					}
					gestorSelect.appendChild(opt);
				});
			}

			function loadGestores(empresaId, selectedId) {
				if (!empresaId) {
					setGestorOptions({}, null);
					return;
				}
				fetch(cfg.urls.getGestoresByEmpresaId + '/' + empresaId, {
					method: 'GET',
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (resp) { return resp.json(); })
					.then(function (data) {
						setGestorOptions(data || {}, selectedId || null);
					})
					.catch(function () {
						setGestorOptions({}, null);
					});
			}

			empresaSelect.addEventListener('change', function () {
				loadGestores(empresaSelect.value || '', null);
			});

			if (empresaSelect.value) {
				loadGestores(empresaSelect.value, gestorSelect.value || null);
			}
		}

		var form = document.getElementById('tramiteNuevoForm');
		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				submitFormAjax();
			});
		}

		// Guardar ahora vive en la barra de acciones del wizard

		function safeInit(name, fn) {
			try {
				fn();
			} catch (error) {
				console.error('[tramitesn_update_v2] error en ' + name, error);
			}
		}

		safeInit('initWizard', initWizard);
		safeInit('initDropzoneDerechos', initDropzoneDerechos);
		safeInit('initDropzoneGestor', initDropzoneGestor);
		safeInit('updatePagoGestorChips', updatePagoGestorChips);
		safeInit('initFinalDocs', initFinalDocs);
		safeInit('initPagoGestorForm', initPagoGestorForm);
		safeInit('initChangeStatus', initChangeStatus);
		safeInit('initServiceCosts', initServiceCosts);
		safeInit('initCancelTramite', initCancelTramite);
		safeInit('initGestorDependents', initGestorDependents);
		renderStepperFixed();
		refreshBadgesBar();
		refreshHeaderBadges();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
