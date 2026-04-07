(() => {
	function getQueryParam(name) {
		try {
			return new URLSearchParams(window.location.search).get(name);
		} catch (e) {
			return null;
		}
	}

	function isEditState() {
		const state = (getQueryParam('gc_state') || '').toLowerCase();
		if (state === 'edit') return true;
		// Algunas integraciones usan variantes
		return state.includes('edit');
	}

	function getPkValue() {
		const pk = getQueryParam('pk_value');
		let n = parseInt(pk || '0', 10);
		if (Number.isFinite(n) && n > 0) return n;

		// Fallback: algunos forms traen el id como input hidden
		const idInput = document.querySelector('input[name="id"], input[name="users.id"], input[name="data[id]"]');
		if (idInput && idInput.value) {
			n = parseInt(String(idInput.value), 10);
			if (Number.isFinite(n) && n > 0) return n;
		}
		return 0;
	}

	function getToggleUrl() {
		// Soporta ambas variantes de rutas que existen en el proyecto.
		const hasDeskapp = window.location.pathname.includes('/deskapp/');
		return (hasDeskapp ? '/deskapp' : '') + '/users/toggle_status';
	}

	function getCsrf() {
		// Compatible con el setup previo del proyecto (si CSRF está activo).
		const meta = document.querySelector('meta[name="X-CSRF-TOKEN"], meta[name="csrf-token"]');
		const token = meta ? (meta.getAttribute('content') || '') : '';
		return token || null;
	}

	function findStatusInput() {
		// Evita el hidden; buscamos el control real.
		const checkbox = document.querySelector('input[name="status"][type="checkbox"]');
		if (checkbox) return checkbox;

		const select = document.querySelector('select[name="status"]');
		if (select) return select;

		const other = document.querySelector('input[name="status"]:not([type="hidden"])');
		return other || null;
	}

	function ensureHidden0BeforeCheckbox(checkbox) {
		if (!checkbox || checkbox.type !== 'checkbox' || checkbox.name !== 'status') return;

		// Patrón estándar: <input type=hidden name=status value=0> antes del checkbox.
		const prev = checkbox.previousElementSibling;
		if (prev && prev.tagName === 'INPUT' && prev.type === 'hidden' && prev.name === 'status') {
			return;
		}

		const hidden = document.createElement('input');
		hidden.type = 'hidden';
		hidden.name = 'status';
		hidden.value = '0';
		hidden.dataset.injected = '1';
		checkbox.parentNode.insertBefore(hidden, checkbox);

		// Cuando está checked debe mandar 1.
		checkbox.value = '1';
	}

	async function postStatus(userId, status) {
		const url = getToggleUrl();
		const body = new URLSearchParams();
		body.set('user_id', String(userId));
		body.set('status', String(status));

		const headers = {
			'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
			'X-Requested-With': 'XMLHttpRequest',
		};

		// En este proyecto se usó este header (ver mapa de roles)
		const csrf = getCsrf();
		if (csrf) {
			headers['X-CSRF-TOKEN'] = csrf;
		}

		const res = await fetch(url, {
			method: 'POST',
			headers,
			body,
			credentials: 'same-origin',
		});

		const data = await res.json().catch(() => null);
		return { res, data };
	}

	function bindOnce() {
		// Si no detecta estado edit, igual intentamos (por si la URL no se actualiza)
		// pero necesitamos el userId para el endpoint.

		const userId = getPkValue();
		if (!userId) return;
,,,
		const input = findStatusInput();
		if (!input) return;
		if (input.dataset.ajaxStatusBound === '1') return;
		input.dataset.ajaxStatusBound = '1';

		if (input.type === 'checkbox') {
			ensureHidden0BeforeCheckbox(input);
		}

		let isBusy = false;
		let lastKnownStatus = input.type === 'checkbox'
			? (input.checked ? 1 : 0)
			: (parseInt(String(input.value || '0'), 10) || 0);
		input.addEventListener('change', async () => {
			if (isBusy) return;
			isBusy = true;

			const oldValue = lastKnownStatus;
			const newValue = input.type === 'checkbox'
				? (input.checked ? 1 : 0)
				: (parseInt(String(input.value || '0'), 10) || 0);

			try {
				input.disabled = true;
				const { res, data } = await postStatus(userId, newValue);
				if (!res.ok || !data || data.ok !== true) {
					const msg = (data && data.message) ? data.message : 'No se pudo actualizar el status.';
					alert(msg);

					// Revertir UI
					if (input.type === 'checkbox') {
						input.checked = oldValue === 1;
					} else {
						input.value = String(oldValue);
					}
					return;
				}

				// Asegurar coherencia con respuesta
				const granted = (data.status === 1);
				if (input.type === 'checkbox') {
					input.checked = granted;
				} else {
					input.value = String(data.status);
				}
				lastKnownStatus = granted ? 1 : 0;
			} catch (e) {
				alert('Error de red al actualizar el status.');
				// Revertir UI
				if (input.type === 'checkbox') {
					input.checked = oldValue === 1;
				} else {
					input.value = String(oldValue);
				}
			} finally {
				input.disabled = false;
				isBusy = false;
			}
		});
	}

	function startObserver() {
		// GroceryCRUD es SPA; el input puede aparecer después.
		bindOnce();
		const obs = new MutationObserver(() => bindOnce());
		obs.observe(document.documentElement || document.body, { childList: true, subtree: true });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', startObserver);
	} else {
		startObserver();
	}
})();
