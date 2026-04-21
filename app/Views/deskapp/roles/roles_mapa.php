<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
	<div class="main-container">
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">
				<div class="crud-breadcrumb">
					<a href="<?= base_url('/deskapp/dashboard') ?>">
						<i class="fas fa-home"></i> Inicio
					</a>
					<span class="separator">/</span>
					<a href="<?= base_url('deskapp/roles/roles') ?>">Roles</a>
					<span class="separator">/</span>
					<span class="current">Mapa</span>
				</div>

				<div class="grocery-crud-wrapper">
					<div class="grocery-crud-header">
						<h2>
							<i class="fas fa-map"></i>
							<?= esc($title ?? 'Mapa de permisos (Rol)') ?>
						</h2>
						<p><?= esc($description ?? '') ?></p>
					</div>

					<div class="grocery-crud-body">
						<?php if (session()->getFlashdata('error')): ?>
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<strong><?= esc(session()->getFlashdata('error')) ?></strong>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
							</div>
						<?php endif; ?>

						<div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
							<div>
								<div class="font-weight-bold" style="font-size: 18px;">
									<?= esc((string)($target_role['role_name'] ?? 'Rol')) ?>
									<small class="text-muted">#<?= (int)($target_role['id'] ?? 0) ?></small>
								</div>
								<div class="text-muted" style="font-size: 12px; white-space: normal;">
									<?php $rd = (string)($target_role['description'] ?? ''); ?>
									<?= $rd !== '' ? esc($rd) : '<span class="text-muted">—</span>' ?>
								</div>
							</div>
							<div class="d-flex flex-wrap">
								<a class="btn btn-secondary" href="<?= base_url('deskapp/roles/roles') ?>">
									<i class="fas fa-arrow-left"></i> Volver
								</a>
							</div>
						</div>

						<form method="get" action="<?= base_url('deskapp/roles/roles_mapa/' . (int)($target_role['id'] ?? 0)) ?>" class="card card-body mb-3">
							<div class="row align-items-end">
								<div class="col-12 col-md-7 col-lg-6 mb-2 mb-md-0">
									<label for="compare_role_id" class="mb-1 font-weight-bold">Comparar contra otro rol</label>
									<select name="compare_role_id" id="compare_role_id" class="form-control form-control-sm">
										<option value="">Sin comparativo</option>
										<?php foreach (($role_catalog ?? []) as $roleOption): ?>
											<?php
												$optionId = (int)($roleOption['id'] ?? 0);
												if ($optionId === (int)($target_role['id'] ?? 0)) {
													continue;
												}
												$selected = ($optionId === (int)($compare_role['id'] ?? 0));
											?>
											<option value="<?= $optionId ?>" <?= $selected ? 'selected' : '' ?>><?= esc((string)($roleOption['role_name'] ?? ('Rol #' . $optionId))) ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-12 col-md-auto d-flex flex-wrap" style="gap:8px;">
									<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-balance-scale"></i> Comparar</button>
									<a href="<?= base_url('deskapp/roles/roles_mapa/' . (int)($target_role['id'] ?? 0)) ?>" class="btn btn-light btn-sm">Limpiar</a>
								</div>
							</div>
						</form>

						<?php if (!empty($comparison['enabled'])): ?>
							<div class="card card-body mb-3" style="background:#f8fafc;border:1px solid #e5e7eb;">
								<div class="d-flex flex-wrap justify-content-between align-items-start mb-2" style="gap:10px;">
									<div>
										<h5 class="mb-1">Comparativo</h5>
										<div class="text-muted" style="font-size:13px;">
											<?= esc((string)($target_role['role_name'] ?? 'Rol base')) ?> vs <?= esc((string)($compare_role['role_name'] ?? 'Rol comparado')) ?>
										</div>
									</div>
								</div>
								<div class="d-flex flex-wrap" style="gap:8px;">
									<span class="badge badge-primary">Base: <?= (int)($comparison['counts']['target'] ?? 0) ?></span>
									<span class="badge badge-info">Comparado: <?= (int)($comparison['counts']['compare'] ?? 0) ?></span>
									<span class="badge badge-success">Coinciden: <?= (int)($comparison['counts']['shared'] ?? 0) ?></span>
									<span class="badge badge-warning">Solo base: <?= (int)($comparison['counts']['only_target'] ?? 0) ?></span>
									<span class="badge badge-secondary">Solo comparado: <?= (int)($comparison['counts']['only_compare'] ?? 0) ?></span>
								</div>
							</div>
						<?php endif; ?>

						<div class="mb-4">
							<div class="d-flex flex-wrap align-items-center">
								<span class="badge badge-success mr-2 mb-2">Asignado al rol</span>
								<?php if (!empty($comparison['enabled'])): ?>
									<span class="badge badge-info mr-2 mb-2">Asignado al rol comparado</span>
									<span class="badge badge-warning mr-2 mb-2">Solo rol base</span>
									<span class="badge badge-secondary mr-2 mb-2">Solo rol comparado</span>
									<span class="badge badge-light border mr-2 mb-2">Coinciden</span>
								<?php endif; ?>
								<span class="badge badge-secondary mr-2 mb-2">No asignado</span>
								<span class="text-muted mb-2" style="font-size: 12px;">(Click en Sí/No para asignar/quitar)</span>
							</div>
						</div>

						<div class="row">
							<?php foreach (($permission_zones ?? []) as $zoneKey => $cfg): ?>
								<?php
									$zoneComparison = $comparison['zone_counts'][$zoneKey] ?? ['shared' => 0, 'only_target' => 0, 'only_compare' => 0];
									$zonePermissions = $cfg['permissions'] ?? [];
									$assignedCount = 0;
									$compareAssignedCount = 0;
									foreach ($zonePermissions as $permName) {
										if (!empty(($target_role_permission_set ?? [])[$permName])) {
											$assignedCount++;
										}
										if (!empty(($compare_role_permission_set ?? [])[$permName])) {
											$compareAssignedCount++;
										}
									}
								?>
								<div class="col-12 col-lg-6 mb-3">
									<div class="card h-100">
										<div class="card-body">
											<div class="d-flex justify-content-between align-items-start" style="gap:10px;">
												<div>
													<h5 class="card-title mb-1"><?= esc((string)($cfg['title'] ?? 'Zona')) ?></h5>
													<div class="text-muted" style="font-size: 13px; white-space: normal;">
														<?= esc((string)($cfg['description'] ?? '')) ?>
													</div>
												</div>
											</div>

											<div class="mt-2" style="display:flex;gap:6px;flex-wrap:wrap;">
												<span class="badge badge-primary">Catálogo: <?= count($zonePermissions) ?></span>
												<span class="badge badge-success">Base: <?= $assignedCount ?></span>
												<?php if (!empty($comparison['enabled'])): ?>
													<span class="badge badge-info">Comparado: <?= $compareAssignedCount ?></span>
													<span class="badge badge-light border">Coinciden: <?= (int)($zoneComparison['shared'] ?? 0) ?></span>
													<span class="badge badge-warning">Solo base: <?= (int)($zoneComparison['only_target'] ?? 0) ?></span>
													<span class="badge badge-secondary">Solo comparado: <?= (int)($zoneComparison['only_compare'] ?? 0) ?></span>
												<?php endif; ?>
											</div>

											<div class="mt-3">
												<div class="mb-2"><strong>Permisos de esta zona:</strong></div>
												<div class="table-responsive">
													<table class="table table-sm table-bordered mb-0">
														<thead class="thead-light">
															<tr>
																<th style="width: 140px;">Origen</th>
																<th style="width: 220px;">Permiso</th>
																<th>Descripción</th>
																<th style="width: 120px;">Asignado</th>
																<?php if (!empty($comparison['enabled'])): ?>
																	<th style="width: 120px;">Comparado</th>
																	<th style="width: 130px;">Diferencia</th>
																<?php endif; ?>
															</tr>
														</thead>
														<tbody>
															<?php foreach ($zonePermissions as $permName): ?>
																<?php
																	$granted = !empty(($target_role_permission_set ?? [])[$permName]);
																	$compareGranted = !empty(($compare_role_permission_set ?? [])[$permName]);
																	$label = function_exists('permission_ui_label') ? permission_ui_label($permName) : $permName;
																	$desc = (string)(($permission_descriptions[$permName] ?? '') ?: '');
																	$area = (string)(($permission_ui_area[$permName] ?? '') ?: 'Acción');
																	$diffLabel = 'Coinciden';
																	$diffClass = 'badge-light border';
																	if (!empty($comparison['enabled'])) {
																		if ($granted && !$compareGranted) {
																			$diffLabel = 'Solo base';
																			$diffClass = 'badge-warning';
																		} elseif (!$granted && $compareGranted) {
																			$diffLabel = 'Solo comparado';
																			$diffClass = 'badge-secondary';
																		}
																	}
																?>
																<tr>
																	<td><span class="badge badge-info"><?= esc($area) ?></span></td>
																	<td>
																		<div<?= $desc !== '' ? ' title="' . esc($desc, 'attr') . '"' : '' ?>><?= esc($label) ?></div>
																		<div class="text-muted" style="font-size: 11px;"><?= esc($permName) ?></div>
																	</td>
																	<td class="text-muted" style="font-size: 12px; white-space: normal;">
																		<?= $desc !== '' ? esc($desc) : '<span class="text-muted">—</span>' ?>
																	</td>
																	<td>
																		<a
																			href="#"
																			class="js-toggle-role-perm badge <?= $granted ? 'badge-success' : 'badge-secondary' ?>"
																			data-perm="<?= esc($permName, 'attr') ?>"
																			data-perm-label="<?= esc($label, 'attr') ?>"
																			data-granted="<?= $granted ? '1' : '0' ?>"
																			title="Click para <?= $granted ? 'quitar' : 'agregar' ?>"
																		>
																			<?= $granted ? 'Sí' : 'No' ?>
																		</a>
																	</td>
																	<?php if (!empty($comparison['enabled'])): ?>
																		<td><span class="badge <?= $compareGranted ? 'badge-info' : 'badge-secondary' ?>"><?= $compareGranted ? 'Sí' : 'No' ?></span></td>
																		<td><span class="badge <?= $diffClass ?>"><?= $diffLabel ?></span></td>
																	<?php endif; ?>
																</tr>
															<?php endforeach; ?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>

						<div class="mt-2 text-muted" style="font-size: 12px;">
							Nota: este mapa muestra permisos asociados al rol, ordenados por zonas funcionales conforme a la nomenclatura ACL vigente.
							No evalúa por sí mismo el estatus runtime de un trámite específico.
						</div>

						<!-- Modal de confirmación (toggle rol-permiso) -->
						<div class="modal fade" id="confirmToggleRolePermModal" tabindex="-1" role="dialog" aria-labelledby="confirmToggleRolePermModalLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="confirmToggleRolePermModalLabel">Confirmar cambio</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<div class="text-muted" style="font-size: 13px;">
											Vas a <strong id="confirmToggleAction">cambiar</strong> la relación del rol con el permiso:
										</div>
										<div class="mt-2">
											<span class="badge badge-info" id="confirmTogglePerm">permiso</span>
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
										<button type="button" class="btn btn-primary" id="confirmToggleContinue">Continuar</button>
									</div>
								</div>
							</div>
						</div>

						<script>
							(() => {
								const roleId = <?= (int)($target_role['id'] ?? 0) ?>;
								const toggleUrl = <?= json_encode((string)base_url('deskapp/roles/toggle_permission')) ?>;
								const csrfHeaderName = 'X-CSRF-TOKEN';
								const csrfHash = <?php if (function_exists('csrf_hash')): ?><?= json_encode((string)csrf_hash()) ?><?php else: ?>null<?php endif; ?>;

								let pendingToggle = null;

								function isModalAvailable() {
									return !!(window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.modal === 'function');
								}

								function openConfirmModal({ el, permName, permLabel, action, currentlyGranted }) {
									pendingToggle = { el, permName, permLabel, action, currentlyGranted };
									const actionEl = document.getElementById('confirmToggleAction');
									const permEl = document.getElementById('confirmTogglePerm');
									if (actionEl) {
										actionEl.textContent = action === 'add' ? 'AGREGAR' : 'QUITAR';
									}
									if (permEl) {
										permEl.textContent = permLabel || permName;
									}

									if (isModalAvailable()) {
										window.jQuery('#confirmToggleRolePermModal').modal('show');
										return;
									}

									// Fallback
									const displayName = permLabel || permName;
									const msg = (action === 'add')
										? `¿Deseas AGREGAR el permiso "${displayName}" a este rol?`
										: `¿Deseas QUITAR el permiso "${displayName}" de este rol?`;
									if (window.confirm(msg)) {
										doToggle(pendingToggle);
									} else {
										pendingToggle = null;
									}
								}

								async function doToggle(p) {
									if (!p || !p.el) return;
									const el = p.el;
									const permName = p.permName;
									const action = p.action;

									el.style.pointerEvents = 'none';
									const oldText = el.textContent;
									el.textContent = '...';

									try {
										const body = new URLSearchParams();
										body.set('role_id', String(roleId));
										body.set('permission_name', permName);
										body.set('action', action);

										const headers = {
											'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
											'X-Requested-With': 'XMLHttpRequest',
										};
										if (csrfHash) {
											headers[csrfHeaderName] = csrfHash;
										}

										const res = await fetch(toggleUrl, {
											method: 'POST',
											headers,
											body,
											credentials: 'same-origin',
										});

										const data = await res.json().catch(() => null);
										if (!res.ok || !data || data.ok !== true) {
											el.textContent = oldText;
											const msg = (data && data.message) ? data.message : 'No se pudo actualizar.';
											alert(msg);
											return;
										}

										setBadgeState(el, !!data.granted);
									} catch (err) {
										el.textContent = oldText;
										alert('Error de red al actualizar.');
									} finally {
										el.style.pointerEvents = '';
									}
								}

								function setBadgeState(el, granted) {
									el.dataset.granted = granted ? '1' : '0';
									el.textContent = granted ? 'Sí' : 'No';
									el.classList.remove('badge-success', 'badge-secondary');
									el.classList.add(granted ? 'badge-success' : 'badge-secondary');
									el.title = granted ? 'Click para quitar' : 'Click para agregar';
								}

								const continueBtn = document.getElementById('confirmToggleContinue');
								if (continueBtn) {
									continueBtn.addEventListener('click', async () => {
										if (!pendingToggle) return;
										const p = pendingToggle;
										pendingToggle = null;
										if (isModalAvailable()) {
											window.jQuery('#confirmToggleRolePermModal').modal('hide');
										}
										await doToggle(p);
									});
								}

								document.addEventListener('click', (e) => {
									const el = e.target.closest('.js-toggle-role-perm');
									if (!el) return;
									e.preventDefault();

									if (!roleId) return;
									const permName = el.dataset.perm || '';
									const permLabel = el.dataset.permLabel || permName;
									if (!permName) return;

									const currentlyGranted = el.dataset.granted === '1';
									const action = currentlyGranted ? 'remove' : 'add';

									openConfirmModal({ el, permName, permLabel, action, currentlyGranted });
								});
							})();
						</script>
					</div>
				</div>
			</div>
		</div>
	</div>
<?= $this->endSection() ?>
