(() => {
	const DEBUG = (() => {
		try {
			return window.localStorage && window.localStorage.getItem('debug_user_status') === '1';
		} catch (e) {
			return false;
		}
	})();

	function log(...args) {
		if (!DEBUG) return;
		// eslint-disable-next-line no-console
		console.log('[users_status_grid_ajax]', ...args);
	}

	function getToggleUrl() {
		const hasDeskapp = window.location.pathname.includes('/deskapp/');
		return (hasDeskapp ? '/deskapp' : '') + '/users/toggle_status';
	}

	function isModalAvailable() {
		return !!(window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.modal === 'function');
	}

	function ensureModal() {
		if (!isModalAvailable()) return null;
		if (document.getElementById('confirmToggleUserStatusModal')) {
			return document.getElementById('confirmToggleUserStatusModal');
		}

		const wrap = document.createElement('div');
		wrap.innerHTML = `
			<div class="modal fade" id="confirmToggleUserStatusModal" tabindex="-1" role="dialog" aria-labelledby="confirmToggleUserStatusModalLabel" aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="confirmToggleUserStatusModalLabel">Confirmar cambio</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div class="text-muted" style="font-size: 13px;">Vas a <strong id="confirmUserStatusAction">cambiar</strong> el status del usuario:</div>
							<div class="mt-2"><span class="badge badge-info" id="confirmUserStatusUser">usuario</span></div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
							<button type="button" class="btn btn-primary" id="confirmUserStatusContinue">Continuar</button>
						</div>
					</div>
				</div>
			</div>
		`;
		document.body.appendChild(wrap.firstElementChild);
		return document.getElementById('confirmToggleUserStatusModal');
	}

	let pending = null;

	function openConfirm({ el, userId, username, newStatus }) {
		pending = { el, userId, username, newStatus };

		if (isModalAvailable()) {
			ensureModal();
			const actionEl = document.getElementById('confirmUserStatusAction');
			const userEl = document.getElementById('confirmUserStatusUser');
			if (actionEl) actionEl.textContent = newStatus === 1 ? 'ACTIVAR' : 'DESACTIVAR';
			if (userEl) userEl.textContent = username ? `${username} (#${userId})` : `#${userId}`;
			window.jQuery('#confirmToggleUserStatusModal').modal('show');
			return;
		}

		const msg = newStatus === 1
			? `¿Deseas ACTIVAR al usuario ${username ? '"' + username + '" ' : ''}(#${userId})?`
			: `¿Deseas DESACTIVAR al usuario ${username ? '"' + username + '" ' : ''}(#${userId})?`;
		if (window.confirm(msg)) {
			doToggle(pending);
		} else {
			pending = null;
		}
	}

	function setBadge(el, status) {
		el.dataset.status = status ? '1' : '0';
		el.textContent = status ? 'Activo' : 'Inactivo';
		el.classList.remove('badge-success', 'badge-secondary');
		el.classList.add(status ? 'badge-success' : 'badge-secondary');
		el.title = status ? 'Click para desactivar' : 'Click para activar';
	}

	async function doToggle(p) {
		if (!p || !p.el) return;
		const { el, userId, newStatus } = p;

		const oldText = el.textContent;
		el.style.pointerEvents = 'none';
		el.textContent = '...';

		try {
			const url = getToggleUrl();
			log('POST', url, { userId, newStatus });
			const body = new URLSearchParams();
			body.set('user_id', String(userId));
			body.set('status', String(newStatus));

			const res = await fetch(url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
					'X-Requested-With': 'XMLHttpRequest',
				},
				body,
				credentials: 'same-origin',
			});

			const data = await res.json().catch(() => null);
			log('response', res.status, data);
			if (!res.ok || !data || data.ok !== true) {
				el.textContent = oldText;
				alert((data && data.message) ? data.message : 'No se pudo actualizar el status.');
				return;
			}

			setBadge(el, data.status === 1);
		} catch (e) {
			el.textContent = oldText;
			alert('Error de red al actualizar el status.');
		} finally {
			el.style.pointerEvents = '';
		}
	}

	function bindModalContinue() {
		if (!isModalAvailable()) return;
		ensureModal();
		const btn = document.getElementById('confirmUserStatusContinue');
		if (!btn || btn.dataset.bound === '1') return;
		btn.dataset.bound = '1';
		btn.addEventListener('click', async () => {
			if (!pending) return;
			const p = pending;
			pending = null;
			window.jQuery('#confirmToggleUserStatusModal').modal('hide');
			await doToggle(p);
		});
	}

	function handleClick(e) {
		const target = (e.target instanceof Element)
			? e.target
			: (e.target && e.target.parentElement ? e.target.parentElement : null);
		if (!target || typeof target.closest !== 'function') return;
		const el = target.closest('.js-toggle-user-status');
		if (!el) return;
		e.preventDefault();
		e.stopPropagation();

		const userId = parseInt(el.dataset.userId || '0', 10) || 0;
		if (!userId) return;

		const current = el.dataset.status === '1';
		const newStatus = current ? 0 : 1;
		const username = el.dataset.username || '';

		log('click', { userId, username, current, newStatus });
		openConfirm({ el, userId, username, newStatus });
	}

	function init() {
		window.__usersStatusGridAjaxLoaded = true;
		log('loaded');
		bindModalContinue();
		// Capture=true para sobrevivir a stopPropagation dentro del grid
		document.addEventListener('click', handleClick, true);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
