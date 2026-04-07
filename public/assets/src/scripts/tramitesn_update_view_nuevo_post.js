(() => {
	try {
		console.log('tramitesn_update_v2 loaded flag', window.__SGL_TRAMITESN_LOADED);
	} catch (e) {}

	function finalDocsPresenceCheck() {
		try {
			console.log('[HTML FinalDocs] presence check', {
				path: window.location.pathname,
				hasBtn16: !!document.getElementById('btnSubirFinalDoc16'),
				hasBtn17: !!document.getElementById('btnSubirFinalDoc17'),
				hasInput16: !!document.getElementById('nativeFinalDoc16'),
				hasInput17: !!document.getElementById('nativeFinalDoc17'),
				hasCont16: !!document.getElementById('final-doc-16-container'),
				hasCont17: !!document.getElementById('final-doc-17-container')
			});
		} catch (e) {}
	}

	function initCostFallback() {
		if (window.__SGL_TRAMITESN_COSTS_BOUND) return;
		const cfg = window.SGL_TRAMITESN_UPDATE_V2 || {};
		const urls = cfg.urls || {};
		const list = document.getElementById('gestor_costos_tipo_servicio');
		const totalEl = document.getElementById('costo_tramite_total');
		const totalInput = document.getElementById('costo_tramite');
		if (!list || !totalEl) return;

		function formatNumber(n) {
			let num = parseFloat(n);
			if (Number.isNaN(num)) num = 0;
			return num.toFixed(2);
		}

		function updateTotal() {
			let total = 0;
			list.querySelectorAll('input[data-cost-id]').forEach((input) => {
				const v = parseFloat(input.value) || 0;
				total += v;
			});
			const totalStr = formatNumber(total);
			totalEl.textContent = '$' + totalStr;
			if (totalInput) totalInput.value = totalStr;

			const impuestoEl = document.getElementById('impuesto_gestoria');
			const comisionEl = document.getElementById('gestoria_comision');
			const paqueteriaEl = document.getElementById('costo_paqueteria');
			const depositoEl = document.getElementById('deposito_gestor');
			const saldoInput = document.getElementById('col_a_favor');
			const saldoInfo = document.getElementById('saldoPendienteInfo');
			const reembolsoSelect = document.getElementById('reembolso_status_id');
			const totalPago = document.getElementById('gestor_total_pago');
			const totalPagoText = document.getElementById('gasto_total_text');
			const breakdownText = document.getElementById('gasto_total_breakdown');

			const impuesto = parseFloat(impuestoEl ? impuestoEl.value : 0) || 0;
			const comision = parseFloat(comisionEl ? comisionEl.value : 0) || 0;
			const paqueteria = parseFloat(paqueteriaEl ? paqueteriaEl.value : 0) || 0;
			const deposito = parseFloat(depositoEl ? depositoEl.value : 0) || 0;

			const gastoTotal = total + impuesto + comision + paqueteria;
			const saldo = total - deposito;
			const saldoAbs = Math.abs(saldo);

			if (totalPago) totalPago.value = formatNumber(gastoTotal);
			if (totalPagoText) totalPagoText.textContent = '$' + formatNumber(gastoTotal);
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
				const targetStatus = Math.abs(saldo) > 0.0001 ? '22' : '24';
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

		function bindInputs() {
			list.querySelectorAll('input[data-cost-id]').forEach((input) => {
				input.addEventListener('input', updateTotal);
				input.addEventListener('keyup', (e) => {
					console.log('fallback keyup costo_tramite', e.target && e.target.value);
					updateTotal();
				});
			});
		}

		function renderItems(data) {
			list.innerHTML = '';
			if (!Array.isArray(data) || data.length === 0) {
				list.innerHTML = '<div class="text-muted">No hay tramites asociados.</div>';
				updateTotal();
				return;
			}
			const canEdit = !!cfg.canEditPagoGestor;
			data.forEach((row) => {
				const id = row.id;
				const name = row.tipo_tramite || ('Servicio #' + id);
				const val = (row.costo_tramite !== null && row.costo_tramite !== undefined) ? row.costo_tramite : 0;
				const item = document.createElement('div');
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
		}

		list.addEventListener('click', (e) => {
			const btn = e.target && e.target.closest ? e.target.closest('[data-save-id]') : null;
			if (!btn || !urls.updateServiceCost) return;
			const id = btn.getAttribute('data-save-id');
			const input = list.querySelector('input[data-cost-id="' + id + '"]');
			if (!input) return;

			const fd = new FormData();
			fd.append('id', id);
			fd.append('costo_tramite', input.value || '0');
			if (cfg.csrfName && cfg.csrfHash) {
				fd.append(cfg.csrfName, cfg.csrfHash);
			}

			fetch(urls.updateServiceCost, {
				method: 'POST',
				body: fd,
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			})
				.then((resp) => resp.json())
				.then((data) => {
					const inputEl = list.querySelector('input[data-cost-id="' + id + '"]');
					const row = inputEl ? inputEl.closest('.sgl-cost-item') : null;
					const rowStatus = row ? row.querySelector('.sgl-cost-row-status') : null;
					const rowIcon = row ? row.querySelector('.sgl-cost-icon') : null;

					if (data && data.status === 'success') {
						if (rowStatus) rowStatus.textContent = 'Guardado';
						if (rowIcon) rowIcon.innerHTML = '<i class="fas fa-check"></i>';
						if (row) row.classList.add('is-saved');
						setTimeout(() => {
							if (rowStatus) rowStatus.textContent = '';
							if (rowIcon) rowIcon.textContent = '';
							if (row) row.classList.remove('is-saved');
						}, 1400);
						return;
					}

					if (rowStatus) rowStatus.textContent = 'Error al guardar';
					if (rowIcon) rowIcon.innerHTML = '<i class="fas fa-times"></i>';
					if (row) row.classList.add('is-error');
					setTimeout(() => {
						if (rowStatus) rowStatus.textContent = '';
						if (rowIcon) rowIcon.textContent = '';
						if (row) row.classList.remove('is-error');
					}, 2000);
				})
				.catch(() => {
					const inputEl = list.querySelector('input[data-cost-id="' + id + '"]');
					const row = inputEl ? inputEl.closest('.sgl-cost-item') : null;
					const rowStatus = row ? row.querySelector('.sgl-cost-row-status') : null;
					const rowIcon = row ? row.querySelector('.sgl-cost-icon') : null;
					if (rowStatus) rowStatus.textContent = 'Error al guardar';
					if (rowIcon) rowIcon.innerHTML = '<i class="fas fa-times"></i>';
					if (row) row.classList.add('is-error');
					setTimeout(() => {
						if (rowStatus) rowStatus.textContent = '';
						if (rowIcon) rowIcon.textContent = '';
						if (row) row.classList.remove('is-error');
					}, 2000);
				});
		});

		['impuesto_gestoria', 'gestoria_comision', 'costo_paqueteria', 'deposito_gestor'].forEach((id) => {
			const el = document.getElementById(id);
			if (!el) return;
			el.addEventListener('input', updateTotal);
			el.addEventListener('keyup', (e) => {
				console.log('fallback keyup gasto', id, e.target && e.target.value);
				updateTotal();
			});
		});

		if (list.querySelector('.sgl-cost-item')) {
			bindInputs();
			updateTotal();
			return;
		}

		if (!urls.getServiceCosts) {
			bindInputs();
			updateTotal();
			return;
		}

		fetch(urls.getServiceCosts, {
			method: 'GET',
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		})
			.then((resp) => resp.json())
			.then(renderItems)
			.catch(() => {
				list.innerHTML = '<div class="text-danger">Error al cargar costos.</div>';
			});
	}

	function init() {
		finalDocsPresenceCheck();
		initCostFallback();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
