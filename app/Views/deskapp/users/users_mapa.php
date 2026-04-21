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
						<a href="<?= base_url('deskapp/users/users') ?>">Usuarios</a>
					<span class="separator">/</span>
					<span class="current">Mapa</span>
				</div>

				<div class="grocery-crud-wrapper">
					<div class="grocery-crud-header">
						<h2>
							<i class="fas fa-map"></i>
							<?= esc($title ?? 'Mapa de permisos') ?>
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
									<?= esc(($target_user['firstname'] ?? '') . ' ' . ($target_user['midname'] ?? '') . ' ' . ($target_user['lastname'] ?? '')) ?>
									<small class="text-muted">@<?= esc($target_user['username'] ?? '') ?></small>
								</div>
								<div class="text-muted">
									ID: <?= (int) ($target_user['id'] ?? 0) ?> · Email: <?= esc($target_user['email'] ?? '') ?>
								</div>
							</div>
							<div class="d-flex flex-wrap">
								<a class="btn btn-secondary" href="<?= base_url('deskapp/users/users') ?>">
									<i class="fas fa-arrow-left"></i> Volver
								</a>
							</div>
						</div>

						<div class="mb-3">
							<div class="mb-2"><strong>Roles:</strong></div>
							<?php if (!empty($target_roles_db ?? [])): ?>
								<div class="table-responsive">
									<table class="table table-sm table-bordered mb-0">
										<thead class="thead-light">
											<tr>
												<th style="width: 90px;">ID</th>
												<th style="width: 240px;">Rol</th>
												<th>Descripción</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach (($target_roles_db ?? []) as $r): ?>
												<tr>
													<td><?= (int) ($r['role_id'] ?? 0) ?></td>
													<td>
														<span class="badge badge-primary"><?= esc((string)($r['role_name'] ?? '')) ?></span>
													</td>
													<td class="text-muted" style="font-size: 12px; white-space: normal;">
														<?php $rd = (string)($r['description'] ?? ''); ?>
														<?= $rd !== '' ? esc($rd) : '<span class="text-muted">—</span>' ?>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php else: ?>
								<div class="d-flex flex-wrap">
									<?php foreach (($target_roles ?? []) as $role): ?>
										<span class="badge badge-primary mr-2 mb-2"><?= esc($role) ?></span>
									<?php endforeach; ?>
									<?php if (empty($target_roles)): ?>
										<span class="text-muted">Sin roles</span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>

						<div class="mb-4">
							<div class="mb-2"><strong>Permisos por rol (DB):</strong> <span class="text-muted" style="font-size: 12px;">(us_user_roles → us_role_permissions → us_permissions)</span></div>
							<?php if (!empty($target_role_permissions_db ?? [])): ?>
								<?php foreach (($target_role_permissions_db ?? []) as $roleInfo): ?>
									<?php
										$roleName = (string)($roleInfo['role_name'] ?? '');
										$rolePerms = $roleInfo['permissions'] ?? [];
										$roleCount = is_array($rolePerms) ? count($rolePerms) : 0;
									?>
									<div class="card mb-3">
										<div class="card-body">
											<div class="d-flex justify-content-between align-items-start">
												<div>
													<span class="badge badge-primary mr-2"><?= esc($roleName !== '' ? $roleName : 'Rol') ?></span>
													<span class="text-muted" style="font-size: 12px;">Permisos: <?= (int)$roleCount ?></span>
												</div>
											</div>

											<?php if (empty($rolePerms)): ?>
												<div class="text-muted mt-2">Sin permisos asociados a este rol.</div>
											<?php else: ?>
												<div class="table-responsive mt-3">
													<table class="table table-sm table-bordered mb-0">
														<thead class="thead-light">
															<tr>
																<th style="width: 140px;">Origen</th>
																<th style="width: 260px;">Permiso</th>
																<th>Descripción</th>
															</tr>
														</thead>
														<tbody>
															<?php foreach ($rolePerms as $rp): ?>
																<?php
																	$permName = (string)($rp['permission_name'] ?? '');
																	$label = function_exists('permission_ui_label') ? permission_ui_label($permName) : $permName;
																	$desc = (string)($rp['description'] ?? '');
																	$area = (string)(($permission_ui_area[$permName] ?? '') ?: 'Acción');
																?>
																<tr>
																	<td><span class="badge badge-info"><?= esc($area) ?></span></td>
																	<td>
																		<div><?= esc($label) ?></div>
																		<div class="text-muted" style="font-size: 11px;"><?= esc($permName) ?></div>
																	</td>
																	<td class="text-muted" style="font-size: 12px; white-space: normal;">
																		<?= $desc !== '' ? esc($desc) : '<span class="text-muted">—</span>' ?>
																	</td>
																</tr>
															<?php endforeach; ?>
														</tbody>
													</table>
												</div>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
							<?php else: ?>
								<span class="text-muted">No hay roles/permisos para mostrar.</span>
							<?php endif; ?>
						</div>

						<div class="mb-4">
							<div class="d-flex flex-wrap align-items-center">
								<span class="badge badge-success mr-2 mb-2">Permiso asignado</span>
								<span class="badge badge-secondary mr-2 mb-2">Permiso no asignado</span>
								<?php if (!empty($can_authorize_target)): ?>
									<span class="badge badge-info mr-2 mb-2">Puede autorizar (important_pasar_a_pagos)</span>
								<?php endif; ?>
							</div>
						</div>

						<div class="row">
							<?php foreach (($steps ?? []) as $stepNum => $cfg): ?>
								<?php
									$canMove = !empty($can_move_step[$stepNum]);
								?>
								<div class="col-12 col-lg-6 mb-3">
									<div class="card">
										<div class="card-body">
											<div class="d-flex justify-content-between align-items-start">
												<div>
													<h5 class="card-title mb-1"><?= esc($cfg['name'] ?? ('Paso ' . (int)$stepNum)) ?></h5>
													<div class="text-muted" style="font-size: 13px;">
														Puede mover este paso:
														<?php if ($canMove): ?>
															<span class="badge badge-success">Sí</span>
														<?php else: ?>
															<span class="badge badge-secondary">No</span>
														<?php endif; ?>
													</div>
												</div>
											</div>

											<div class="mt-3">
												<div class="mb-2"><strong>Roles que pueden mover este paso:</strong></div>
													<div class="d-flex flex-wrap">
													<?php foreach (($cfg['roles_can_move'] ?? []) as $roleName): ?>
													<?php
														$targetRoles = $target_roles ?? [];
														$targetRolesLower = array_map('strtolower', $targetRoles);
														$userHasRole = in_array(strtolower((string)$roleName), $targetRolesLower, true);
													?>
															<span class="badge <?= $userHasRole ? 'badge-success' : 'badge-secondary' ?> mr-2 mb-2"><?= esc($roleName) ?></span>
													<?php endforeach; ?>
												</div>
											</div>

											<div class="mt-3">
												<div class="mb-2"><strong>Permisos de esta zona:</strong></div>
												<?php if (empty($cfg['permissions'] ?? [])): ?>
													<span class="text-muted">Sin permisos configurados para esta zona.</span>
												<?php else: ?>
													<div class="table-responsive">
														<table class="table table-sm table-bordered mb-0">
															<thead class="thead-light">
																<tr>
																	<th style="width: 140px;">Origen</th>
																	<th style="width: 220px;">Permiso</th>
																	<th>Descripción</th>
																	<th style="width: 120px;">Fuente</th>
																	<th style="width: 120px;">Asignado</th>
																</tr>
															</thead>
															<tbody>
																<?php foreach (($cfg['permissions'] ?? []) as $permName): ?>
																	<?php
																		$granted = has_permission($permName, $target_permissions ?? [], $target_roles ?? []);
																		$baseGranted = !empty(($target_role_permission_set ?? [])[$permName]);
																		$overrideExists = array_key_exists($permName, ($target_user_permission_overrides ?? []));
																		$overrideGranted = $overrideExists ? (int)($target_user_permission_overrides[$permName]) : null;
																		$sourceLabel = '—';
																		$sourceClass = 'badge-secondary';
																		$label = function_exists('permission_ui_label') ? permission_ui_label($permName) : $permName;
																		if ($overrideExists) {
																			if ($overrideGranted === 1) {
																				$sourceLabel = 'Usuario';
																				$sourceClass = 'badge-info';
																			} else {
																				$sourceLabel = 'Usuario (denegado)';
																				$sourceClass = 'badge-warning';
																			}
																		} elseif ($baseGranted) {
																			$sourceLabel = 'Rol';
																			$sourceClass = 'badge-primary';
																		}
																		$desc = (string) (($permission_descriptions[$permName] ?? '') ?: '');
																		$area = (string) (($permission_ui_area[$permName] ?? '') ?: 'Acción');
																	?>
																	<tr>
																		<td>
																			<span class="badge badge-info"><?= esc($area) ?></span>
																		</td>
																		<td>
																			<div<?= $desc !== '' ? ' title="' . esc($desc, 'attr') . '"' : '' ?>><?= esc($label) ?></div>
																			<div class="text-muted" style="font-size: 11px;"><?= esc($permName) ?></div>
																		</td>
																		<td class="text-muted" style="font-size: 12px; white-space: normal;">
																			<?= $desc !== '' ? esc($desc) : '<span class="text-muted">—</span>' ?>
																		</td>
																		<td>
																			<span class="badge <?= esc($sourceClass) ?> js-user-perm-source" data-perm="<?= esc($permName, 'attr') ?>"><?= esc($sourceLabel) ?></span>
																		</td>
																		<td>
																			<a
																				href="#"
																				class="js-toggle-user-perm badge <?= $granted ? 'badge-success' : 'badge-secondary' ?>"
																				data-user-id="<?= (int)($target_user['id'] ?? 0) ?>"
																				data-perm="<?= esc($permName, 'attr') ?>"
																				data-perm-label="<?= esc($label, 'attr') ?>"
																				data-granted="<?= $granted ? '1' : '0' ?>"
																				title="Click para cambiar"
																			>
																				<?= $granted ? 'Sí' : 'No' ?>
																			</a>
																		</td>
																	</tr>
																<?php endforeach; ?>
															</tbody>
														</table>
													</div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>

						<!-- Confirm modal: toggle permiso por usuario -->
						<div class="modal fade" id="confirmToggleUserPermModal" tabindex="-1" role="dialog" aria-labelledby="confirmToggleUserPermModalLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="confirmToggleUserPermModalLabel">Confirmar cambio</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<div class="text-muted" style="font-size: 13px;">
											Vas a <strong id="confirmUserToggleAction">cambiar</strong> el permiso para este usuario:
										</div>
										<div class="mt-2">
											<span class="badge badge-info" id="confirmUserTogglePerm">permiso</span>
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
										<button type="button" class="btn btn-primary" id="confirmUserToggleContinue">Continuar</button>
									</div>
								</div>
							</div>
						</div>

						<script>
							(() => {
								const toggleUrl = <?= json_encode((string)($toggle_user_permission_url ?? base_url('deskapp/users/toggle_user_permission'))) ?>;

								let pendingToggle = null;

								function isModalAvailable() {
									return !!(window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.modal === 'function');
								}

								function openConfirmModal({ el, userId, permName, permLabel, desiredGranted }) {
									pendingToggle = { el, userId, permName, permLabel, desiredGranted };
									const actionEl = document.getElementById('confirmUserToggleAction');
									const permEl = document.getElementById('confirmUserTogglePerm');
									if (actionEl) {
										actionEl.textContent = desiredGranted ? 'ASIGNAR' : 'DENEGAR';
									}
									if (permEl) {
										permEl.textContent = permLabel || permName;
									}

									if (isModalAvailable()) {
										window.jQuery('#confirmToggleUserPermModal').modal('show');
										return;
									}

									// Fallback
									const displayName = permLabel || permName;
									const msg = desiredGranted
										? '¿Confirmas asignar el permiso "' + displayName + '" a este usuario?'
										: '¿Confirmas denegar el permiso "' + displayName + '" a este usuario?';
									if (window.confirm(msg)) {
										doToggle(pendingToggle);
									} else {
										pendingToggle = null;
									}
								}

								async function postToggle({ userId, permName, desiredGranted }) {
									const body = new URLSearchParams();
									body.set('user_id', String(userId));
									body.set('permission_name', permName);
									body.set('granted', String(desiredGranted));

									const res = await fetch(toggleUrl, {
										method: 'POST',
										headers: {
											'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
											'X-Requested-With': 'XMLHttpRequest'
										},
										body
									});
									let data = null;
									try { data = await res.json(); } catch (e) {}
									if (!res.ok || !data || !data.ok) {
										const msg = (data && data.message) ? data.message : 'Error al actualizar permiso.';
										throw new Error(msg);
									}
									return data;
								}

								function sourceMeta(source) {
									if (source === 'role') return { text: 'Rol', cls: 'badge-primary' };
									if (source === 'user_allow') return { text: 'Usuario', cls: 'badge-info' };
									if (source === 'user_deny') return { text: 'Usuario (denegado)', cls: 'badge-warning' };
									return { text: '—', cls: 'badge-secondary' };
								}

								async function doToggle(p) {
									if (!p || !p.el) return;
									const a = p.el;
									const userId = p.userId;
									const permName = p.permName;
									const desiredGranted = p.desiredGranted;

									a.classList.add('disabled');
									try {
										const data = await postToggle({ userId, permName, desiredGranted });
										const finalGranted = String(data.granted) === '1';
										a.setAttribute('data-granted', finalGranted ? '1' : '0');
										a.textContent = finalGranted ? 'Sí' : 'No';
										a.classList.toggle('badge-success', finalGranted);
										a.classList.toggle('badge-secondary', !finalGranted);
										a.title = 'Click para cambiar';

										const src = sourceMeta(String(data.source || 'none'));
										document.querySelectorAll('.js-user-perm-source').forEach((srcEl) => {
											if ((srcEl.getAttribute('data-perm') || '') !== permName) return;
											srcEl.textContent = src.text;
											srcEl.classList.remove('badge-primary', 'badge-info', 'badge-warning', 'badge-secondary');
											srcEl.classList.add(src.cls);
										});
									} catch (err) {
										window.alert(err && err.message ? err.message : 'Error.');
									} finally {
										a.classList.remove('disabled');
									}
								}

								const continueBtn = document.getElementById('confirmUserToggleContinue');
								if (continueBtn) {
									continueBtn.addEventListener('click', async () => {
										if (!pendingToggle) return;
										const p = pendingToggle;
										pendingToggle = null;
										if (isModalAvailable()) {
											window.jQuery('#confirmToggleUserPermModal').modal('hide');
										}
										await doToggle(p);
									});
								}

								document.addEventListener('click', (e) => {
									const a = e.target && e.target.closest ? e.target.closest('.js-toggle-user-perm') : null;
									if (!a) return;
									e.preventDefault();
								if (a.classList.contains('disabled')) return;

									const userId = parseInt(a.getAttribute('data-user-id') || '0', 10);
									const permName = a.getAttribute('data-perm') || '';
									const permLabel = a.getAttribute('data-perm-label') || permName;
									const currentGranted = (a.getAttribute('data-granted') || '0') === '1';
									const desiredGranted = currentGranted ? 0 : 1;

									if (!userId || !permName) return;
									openConfirmModal({ el: a, userId, permName, permLabel, desiredGranted });
								});
							})();
						</script>

						<div class="mt-4">
							<h5 class="mb-2">Admin permisos</h5>
							<div class="text-muted" style="font-size: 12px;">
								Permisos administrativos (Gestores, Clientes, ACL, Documentos, Configuración, Monitoreo, etc.)
								que no pertenecen al flujo de Pasos 1–5.
							</div>

							<?php if (empty($admin_permissions ?? [])): ?>
								<div class="text-muted mt-2">(Ninguno)</div>
							<?php else: ?>
								<div class="table-responsive mt-3">
									<table class="table table-sm table-bordered mb-0">
										<thead class="thead-light">
											<tr>
												<th style="width: 140px;">Origen</th>
												<th style="width: 220px;">Permiso</th>
												<th>Descripción</th>
													<th style="width: 120px;">Fuente</th>
												<th style="width: 120px;">Asignado</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach (($admin_permissions ?? []) as $permName): ?>
												<?php
													$granted = has_permission($permName, $target_permissions ?? [], $target_roles ?? []);
															$baseGranted = !empty(($target_role_permission_set ?? [])[$permName]);
															$overrideExists = array_key_exists($permName, ($target_user_permission_overrides ?? []));
															$overrideGranted = $overrideExists ? (int)($target_user_permission_overrides[$permName]) : null;
															$sourceLabel = '—';
															$sourceClass = 'badge-secondary';
															$label = function_exists('permission_ui_label') ? permission_ui_label($permName) : $permName;
															if ($overrideExists) {
																	if ($overrideGranted === 1) {
																			$sourceLabel = 'Usuario';
																			$sourceClass = 'badge-info';
																		} else {
																			$sourceLabel = 'Usuario (denegado)';
																			$sourceClass = 'badge-warning';
																		}
															} elseif ($baseGranted) {
																	$sourceLabel = 'Rol';
																	$sourceClass = 'badge-primary';
															}
													$desc = (string) (($permission_descriptions[$permName] ?? '') ?: '');
													$area = (string) (($permission_ui_area[$permName] ?? '') ?: 'Acción');
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
															<span class="badge <?= esc($sourceClass) ?> js-user-perm-source" data-perm="<?= esc($permName, 'attr') ?>"><?= esc($sourceLabel) ?></span>
														</td>
													<td>
															<a
																href="#"
																class="js-toggle-user-perm badge <?= $granted ? 'badge-success' : 'badge-secondary' ?>"
																data-user-id="<?= (int)($target_user['id'] ?? 0) ?>"
																data-perm="<?= esc($permName, 'attr') ?>"
																	data-perm-label="<?= esc($label, 'attr') ?>"
																data-granted="<?= $granted ? '1' : '0' ?>"
																title="Click para cambiar"
															>
																<?= $granted ? 'Sí' : 'No' ?>
															</a>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php endif; ?>
						</div>

						<div class="mt-2 text-muted" style="font-size: 12px;">
							Nota: este mapa muestra permisos/roles asignados y una regla de “quién mueve qué paso” por rol.
							No evalúa estatus de un trámite en específico.
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?= $this->endSection() ?>
