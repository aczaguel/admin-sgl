<?php
	$principalTipoIdAudit = (int) ($principal_tipo_id ?? 0);
	$principalTipoLabelAudit = $tra_tipos_options[$principalTipoIdAudit] ?? '';
?>
<?php
	helper(['permissions']);
	$sessionDbg = session();
	$canDebugAudit = has_permission('debug_perm_audit_tags', $sessionDbg->get('user_permissions'), $sessionDbg->get('user_roles'));
?>
<?php if (!empty($canDebugAudit)): ?>
	<div id="audit-info" class="alert debug-info-container" style="display:none;background:#f8fafc;border:1px dashed #94a3b8;border-radius:10px;padding:10px 12px;margin-bottom:12px;">
		<div style="font-weight:700;color:#0f172a;margin-bottom:6px;">Audit</div>
		<div><strong>Tipo principal:</strong> <?= esc($principalTipoLabelAudit !== '' ? $principalTipoLabelAudit : 'N/A') ?> (ID <?= (int) $principalTipoIdAudit ?>)</div>
		<div style="margin-top:6px;"><strong>Tramites asociados:</strong></div>
		<?php if (!empty($servicios_asociados) && is_array($servicios_asociados)): ?>
			<ul style="margin:6px 0 0 18px;">
				<?php foreach ($servicios_asociados as $srv): ?>
					<li><?= esc($srv['label'] ?? 'N/A') ?> (ID <?= (int) ($srv['tra_tipos_id'] ?? 0) ?>)</li>
				<?php endforeach; ?>
			</ul>
		<?php else: ?>
			<div class="text-muted">Sin tramites asociados.</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
<form id="tramiteNuevoForm" method="post" action="<?= site_url('/deskapp/tramitesn/update_save/' . $id) ?>">
	<?= csrf_field() ?>
	<input type="hidden" id="current_step" name="current_step" value="1">
	<div id="tramiteNuevoMessage" class="alert mt-2" style="display:none;"></div>
	<?php if (has_permission('tramite_detalle_quick_actions_historial_actividad_ver', $user_permissions ?? [], $user_roles ?? [])): ?>
		<div class="d-flex justify-content-end mb-3">
			<a href="<?= site_url('/deskapp/tramites/audit_timeline/' . (int) ($id ?? 0)) ?>" class="btn btn-info btn-sm sgl-btn-pill">
				<i class="fas fa-stream"></i> Ver Historial de Actividad
			</a>
		</div>
	<?php endif; ?>
	<div id="wizard" class="wizard-modern">
		<h3>Datos del Trámite</h3>
		<section>
			<div class="wizard-section" data-step="1">
			<div class="sgl-step-form-ribbon <?= !empty($step1_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="1">
				<div class="sgl-icon"><i class="<?= !empty($step1_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
				<div class="sgl-text">
					<?= !empty($step1_complete) ? 'Datos completos' : 'Completa los datos generales del trámite' ?>
				</div>
			</div>

				<div class="row" style="margin-left:-6px;margin-right:-6px;">
					<div class="col-lg-4" style="padding-left:6px;padding-right:6px;">

			<!-- Tipos de trámite ligados (tra_tramite_asociado) -->
			<div class="card mb-3 sgl-card-tight">
				<div class="card-body">
					<div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:12px;">
						<div>
							<h5 class="mb-1">Tipos de trámite ligados</h5>
							<small class="text-muted">Incluye el tipo principal y los asociados.</small>
						</div>
						<div id="tiposLigadosBadges" class="d-flex flex-wrap" style="gap:8px;">
							<?php if (!empty($servicios_asociados) && is_array($servicios_asociados)): ?>
								<?php foreach ($servicios_asociados as $srv): ?>
									<span class="badge badge-success badge-pill sgl-pill">✓ <?= esc($srv['label'] ?? '') ?></span>
								<?php endforeach; ?>
							<?php else: ?>
								<span class="badge badge-secondary badge-pill sgl-pill">Sin tipos ligados</span>
							<?php endif; ?>
						</div>
					</div>

					<hr>

					<div class="sgl-highlight-box mb-2">
						<div class="row align-items-end">
							<div class="col-12 mb-1">
								<label for="tra_tipos_add" class="form-label mb-1">Agregar Tipo de Trámite</label>
								<div class="d-flex align-items-center sgl-add-row" style="gap:10px;">
									<div class="sgl-add-controls">
										<select id="tra_tipos_add" class="form-control form-control-sm" <?= $step1DisabledAttr ?>>
											<option value="">Seleccione...</option>
											<?php if (!empty($tra_tipos_options) && is_array($tra_tipos_options)): ?>
												<?php foreach ($tra_tipos_options as $tipoId => $tipoLabel): ?>
													<option value="<?= (int) $tipoId ?>"><?= esc($tipoLabel) ?></option>
												<?php endforeach; ?>
											<?php endif; ?>
										</select>
										<?php if (!$isReadOnlyStep1): ?>
											<button type="button" id="btnAgregarTipo" class="sgl-btn-mini">
												<i class="fas fa-plus"></i> Agregar
											</button>
										<?php endif; ?>
									</div>
									<span class="sgl-inline-status" id="tiposMsg"></span>
								</div>
							</div>
						</div>
					</div>

					<div class="mt-3" id="tiposAsociadosList">
						<?php if (!empty($servicios_asociados) && is_array($servicios_asociados)): ?>
							<?php foreach ($servicios_asociados as $srv): ?>
								<?php $isPrincipalTipo = (int) ($srv['tra_tipos_id'] ?? 0) === (int) ($principal_tipo_id ?? 0); ?>
								<div class="card mb-2" data-asociado-id="<?= (int) ($srv['asociado_id'] ?? 0) ?>" data-tipo-id="<?= (int) ($srv['tra_tipos_id'] ?? 0) ?>">
									<div class="card-body py-2 sgl-associated-row">
										<div>
											<strong class="tipo-label"><?= esc($srv['label'] ?? '') ?></strong>
											<small class="text-muted d-block">Asociado</small>
										</div>
										<div class="actions">
											<?php if (!empty($can_edit_asociado) && !$isPrincipalTipo && !$isReadOnlyStep1): ?>
												<button type="button" class="btn btn-sm btn-outline-primary btnCambiarAsociado" data-toggle="modal" data-target="#modalEditAsociadoTipo" title="Cambiar">
													<i class="fas fa-pen"></i>
												</button>
											<?php endif; ?>
											<?php if (!empty($can_delete_asociado) && !$isPrincipalTipo && !$isReadOnlyStep1): ?>
												<button type="button" class="btn btn-sm btn-outline-danger btnEliminarAsociado" data-toggle="modal" data-target="#modalDeleteAsociado" title="Eliminar">
													<i class="fas fa-trash"></i>
												</button>
											<?php endif; ?>
											<span class="badge badge-success badge-pill sgl-pill" title="Ligado">✓</span>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php else: ?>
							<div class="text-muted">No hay tipos asociados. Agrega uno arriba.</div>
						<?php endif; ?>
					</div>

					<div class="mt-2">
						<div id="tiposPendientes" class="d-grid" style="gap:10px;"></div>
					</div>
				</div>
			</div>
					</div>

					<div class="col-lg-8" style="padding-left:6px;padding-right:6px;">

			<div class="sgl-soft-panel mt-3">
				<p class="sgl-soft-panel-title">Datos del trámite</p>
				<div class="row">
					<?php if (!empty($fields) && is_array($fields)): ?>
						<?php
							$clienteCfg = $fields['cli_directo_id'] ?? null;
							$clienteEjCfg = $fields['cli_directo_ejecutivo_id'] ?? null;
							$renderClienteGroup = is_array($clienteCfg) || is_array($clienteEjCfg);
						?>

						<?php if ($renderClienteGroup): ?>
							<div class="col-12">
								<div class="sgl-linked-fields">
									<p class="sgl-linked-title mb-0">Cliente y Ejecutivo (conectados)</p>
									<div class="row">
										<?php
											$groupFields = [
												'cli_directo_id' => $clienteCfg,
												'cli_directo_ejecutivo_id' => $clienteEjCfg,
											];
										?>
										<?php foreach ($groupFields as $name => $cfg): ?>
											<?php if (empty($cfg) || !is_array($cfg)) continue; ?>
											<?php
												$type = $cfg['type'] ?? 'text';
												if ($type === 'hidden') {
													echo '<input type="hidden" name="'.$name.'" value="'.esc($cfg['value'] ?? '', 'attr').'">';
													continue;
												}
												$required = !empty($cfg['required']);
											?>
														<div class="col-md-6 mb-2">
												<div class="form-group">
													<label for="<?= esc($name) ?>"><?= esc($cfg['label'] ?? ucfirst($name)) ?><?= $required ? ' *' : '' ?></label>
													<?php if ($type === 'select'): ?>
															<select class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $required ? 'required' : '' ?> <?= $step1DisabledAttr ?>>
															<option value="">Seleccione...</option>
															<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
																<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
																	<option value="<?= esc($optValue, 'attr') ?>" <?= ($cfg['value'] ?? null) == $optValue ? 'selected' : '' ?>><?= esc($optLabel) ?></option>
																<?php endforeach; ?>
															<?php endif; ?>
														</select>
													<?php else: ?>
															<input type="<?= esc($type) ?>" class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($cfg['value'] ?? '', 'attr') ?>" <?= $required ? 'required' : '' ?> <?= $step1DisabledAttr ?>>
													<?php endif; ?>
													<div class="invalid-feedback">Campo obligatorio.</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<?php foreach ($fields as $name => $cfg): ?>
							<?php
								if ($name === 'cli_directo_id' || $name === 'cli_directo_ejecutivo_id') {
									continue;
								}
								$type = $cfg['type'] ?? 'text';
								if ($type === 'hidden') {
									echo '<input type="hidden" name="'.$name.'" value="'.esc($cfg['value'] ?? '', 'attr').'">';
									continue;
								}
								$required = !empty($cfg['required']);
							?>
							<div class="col-lg-3 col-md-4 col-sm-6 mb-2">
								<div class="form-group">
									<label for="<?= esc($name) ?>"><?= esc($cfg['label'] ?? ucfirst($name)) ?><?= $required ? ' *' : '' ?></label>
									<?php if ($type === 'textarea'): ?>
										<textarea class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" rows="2" <?= $required ? 'required' : '' ?> <?= $step1DisabledAttr ?>><?= esc($cfg['value'] ?? '') ?></textarea>
									<?php elseif ($type === 'select'): ?>
										<select class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $required ? 'required' : '' ?> <?= $step1DisabledAttr ?>>
											<option value="">Seleccione...</option>
											<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
												<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
													<option value="<?= esc($optValue, 'attr') ?>" <?= ($cfg['value'] ?? null) == $optValue ? 'selected' : '' ?>><?= esc($optLabel) ?></option>
												<?php endforeach; ?>
											<?php endif; ?>
										</select>
									<?php else: ?>
										<input type="<?= esc($type) ?>" class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($cfg['value'] ?? '', 'attr') ?>" <?= $required ? 'required' : '' ?> <?= $step1DisabledAttr ?>>
									<?php endif; ?>
									<div class="invalid-feedback">Campo obligatorio.</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

			</div>
					</div>
				</div>
			</div>
			</section>

			<h3>Gestor y Empresa</h3>
			<section>
				<div class="wizard-section" data-step="2">
					<div class="sgl-step-center">
						<div class="sgl-step-form-ribbon <?= !empty($step2_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="2">
							<div class="sgl-icon"><i class="<?= !empty($step2_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
							<div class="sgl-text">
								<?= !empty($step2_complete) ? 'Datos completos' : 'Asigna la empresa gestora y el gestor responsable' ?>
							</div>
						</div>
						<div class="sgl-soft-panel mt-3">
							<p class="sgl-soft-panel-title">Gestor y Empresa</p>
							<div class="row">
							<?php if (!empty($gestor_campos) && is_array($gestor_campos)): ?>
								<?php foreach ($gestor_campos as $name => $cfg): ?>
									<?php $required = !empty($cfg['required']); ?>
									<div class="col-lg-6 col-md-6 col-sm-12 mb-2">
										<div class="form-group">
											<label for="<?= esc($name) ?>"><?= esc($cfg['label'] ?? ucfirst($name)) ?><?= $required ? ' *' : '' ?></label>
											<select
												class="form-control form-control-sm"
												id="<?= esc($name) ?>"
												name="<?= esc($name) ?>"
												<?= $required ? 'required' : '' ?> <?= $step2DisabledAttr ?>>
												<option value="">Seleccione...</option>
												<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
													<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
														<option value="<?= esc($optValue, 'attr') ?>" <?= ($cfg['value'] ?? null) == $optValue ? 'selected' : '' ?>>
															<?= esc($optLabel) ?>
														</option>
													<?php endforeach; ?>
												<?php endif; ?>
											</select>
											<div class="invalid-feedback">Campo obligatorio.</div>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
					</div>
				</div>
			</section>

			<h3>Pagos de Derechos</h3>
			<section>
				<div class="wizard-section" data-step="3">
				<div class="sgl-step-center">
					<div id="tramiteStep3Message" class="alert mt-2" style="display:none;"></div>
					<div class="sgl-step-form-ribbon <?= !empty($step3_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="3">
						<div class="sgl-icon"><i class="<?= !empty($step3_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
						<div class="sgl-text">
							<?= !empty($step3_complete) ? 'Datos completos' : 'Configura los pagos de derechos y su evidencia' ?>
						</div>
					</div>
					<div class="form-dropzone-grid">
						<div class="form-column">
							<div class="row">
							<?php if (!empty($derechos_campos) && is_array($derechos_campos)): ?>
								<?php foreach ($derechos_campos as $name => $cfg): ?>
									<?php
										$type = $cfg['type'] ?? 'text';
										$required = !empty($cfg['required']);
									?>
									<div class="col-lg-3 col-md-4 col-sm-6 mb-2">
										<div class="form-group">
											<label for="<?= esc($name) ?>"><?= esc($cfg['label'] ?? ucfirst($name)) ?><?= $required ? ' *' : '' ?></label>
											<?php if ($type === 'select'): ?>
												<select
												class="form-control form-control-sm"
												id="<?= esc($name) ?>"
												name="<?= esc($name) ?>"
												<?= $required ? 'required' : '' ?> <?= $step3DisabledAttr ?>>
												<option value="">Seleccione...</option>
												<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
													<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
														<option value="<?= esc($optValue, 'attr') ?>" <?= ($cfg['value'] ?? null) == $optValue ? 'selected' : '' ?>>
															<?= esc($optLabel) ?>
														</option>
													<?php endforeach; ?>
												<?php endif; ?>
											</select>
										<?php else: ?>
											<input
												type="<?= $type === 'datetime' ? 'datetime-local' : esc($type) ?>"
												class="form-control form-control-sm"
												id="<?= esc($name) ?>"
												name="<?= esc($name) ?>"
												value="<?= esc($cfg['value'] ?? '', 'attr') ?>"
												<?= $required ? 'required' : '' ?> <?= $step3DisabledAttr ?>>
										<?php endif; ?>
										<div class="invalid-feedback">Campo obligatorio.</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php
						$paso3_completo = !empty($derechos_campos['derechos_tramite']['value'])
							&& !empty($derechos_campos['derechos_revol_cliente']['value'])
							&& !empty($derechos_campos['derechos_refer_banc']['value']);
						$arr_status = [
							11 => 1, 22 => 2, 25 => 3, 26 => 3, 27 => 3,
							23 => 4, 28 => 5, 20 => 6, 21 => 7, 29 => 1
						];
						$traStatusIdForStep = (int) ($tra_status_id ?? 0);
						$step_actual = isset($arr_status[$traStatusIdForStep]) ? $arr_status[$traStatusIdForStep] : 1;
						$canAuthorizeStep3 = can_authorize_tramite($user_roles ?? [], $user_permissions ?? [])
							&& $step_actual <= 3
							&& puede_editar_modulo($user_roles ?? [], $tra_status_id, 'boton_aprobar_tramite', $reembolso_status_id ?? 0, $cobro_status_id ?? 0, 3)
							&& !$isReadOnlyStep3;
						$showApprovalStep3 = $step_actual <= 3 && !$isReadOnlyStep3;
					?>

					<?php if (is_array($user_roles ?? null) && in_array('Super Admin', $user_roles, true)): ?>
						<div class="debug-info-container" style="display: none;">
							<div class="alert" style="background:#fff3cd;border:1px solid #ffeeba;border-radius:8px;">
								<h6 style="margin:0 0 8px 0;font-weight:700;">
									<i class="fas fa-bug"></i> Debug paso3
								</h6>
								<div style="font-size:12px;line-height:1.4;">
									<div><strong>tra_status_id:</strong> <?= esc($tra_status_id ?? '') ?></div>
									<div><strong>step_actual:</strong> <?= esc($step_actual ?? '') ?></div>
									<div><strong>paso3_completo:</strong> <?= !empty($paso3_completo) ? 'true' : 'false' ?></div>
									<div><strong>can_important_pasar_a_pagos:</strong> <?= has_permission('important_pasar_a_pagos', $user_permissions ?? [], $user_roles ?? []) ? 'true' : 'false' ?></div>
									<div><strong>can_boton_aprobar_tramite:</strong> <?= puede_editar_modulo($user_roles ?? [], $tra_status_id ?? 0, 'boton_aprobar_tramite', $reembolso_status_id ?? 0, $cobro_status_id ?? 0, 3) ? 'true' : 'false' ?></div>
									<div><strong>user_roles_count:</strong> <?= isset($user_roles) && is_array($user_roles) ? count($user_roles) : 0 ?></div>
									<div><strong>user_perms_count:</strong> <?= isset($user_permissions) && is_array($user_permissions) ? count($user_permissions) : 0 ?></div>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($showApprovalStep3): ?>
							<div class="sgl-approval-wrap" id="approvalWrap">
								<?php if ($canAuthorizeStep3): ?>
								<div class="alert alert-info approval-ready" id="approvalReady" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border: 2px solid #4caf50; border-radius: 12px; padding: 20px; display: <?= $paso3_completo ? 'flex' : 'none' ?>; align-items: center; gap: 20px; box-shadow: 0 4px 6px rgba(76, 175, 80, 0.15);">
									<div style="flex-shrink: 0;">
										<i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50;"></i>
									</div>
									<div style="flex: 1;">
										<h5 style="margin: 0 0 8px 0; color: #2e7d32; font-weight: 700;">
											<i class="fas fa-clipboard-check"></i> Informacion Completa
										</h5>
										<p style="margin: 0 0 15px 0; color: #1b5e20; font-size: 0.95rem;">
											Los datos de pago de derechos estan completos. El tramite esta listo para ser aprobado y continuar al siguiente paso.
										</p>
										<button type="button"
											class="btn-wizard btn-success btn-lg approval-button"
											onclick="confirmAprobarTramite(<?= (int) $id ?>);">
											<i class="fas fa-check-double"></i> Aprobar Tramite
										</button>
									</div>
								</div>
								<div class="alert alert-warning approval-pending" id="approvalPending" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border: 2px solid #ff9800; border-radius: 12px; padding: 20px; display: <?= $paso3_completo ? 'none' : 'flex' ?>; align-items: center; gap: 20px; box-shadow: 0 4px 6px rgba(255, 152, 0, 0.15);">
									<div style="flex-shrink: 0;">
										<i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ff9800;"></i>
									</div>
									<div style="flex: 1;">
										<h5 style="margin: 0 0 8px 0; color: #e65100; font-weight: 700;">
											<i class="fas fa-info-circle"></i> Informacion Incompleta
										</h5>
										<p style="margin: 0; color: #bf360c; font-size: 0.95rem;">
											Para aprobar el tramite, primero debes completar y guardar los siguientes campos obligatorios:
										</p>
										<ul id="approvalMissingList" style="margin: 10px 0 0 0; color: #bf360c; font-size: 0.9rem;">
											<?php if (empty($derechos_campos['derechos_tramite']['value'])): ?>
												<li><strong>Monto pago de derechos</strong></li>
											<?php endif; ?>
											<?php if (empty($derechos_campos['derechos_revol_cliente']['value'])): ?>
												<li><strong>Forma de Pago</strong></li>
											<?php endif; ?>
											<?php if (empty($derechos_campos['derechos_refer_banc']['value'])): ?>
												<li><strong>Referencia Bancaria</strong></li>
											<?php endif; ?>
										</ul>
									</div>
								</div>
								<?php else: ?>
								<div class="alert approval-no-permission" id="approvalNoPermission" style="background: linear-gradient(135deg, #eef2ff 0%, #dbeafe 100%); border: 2px solid #3b82f6; border-radius: 12px; padding: 20px; display: <?= $paso3_completo ? 'flex' : 'none' ?>; align-items: center; gap: 20px; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.15);">
									<div style="flex-shrink: 0;">
										<i class="fas fa-user-lock" style="font-size: 48px; color: #2563eb;"></i>
									</div>
									<div style="flex: 1;">
										<h5 style="margin: 0 0 8px 0; color: #1d4ed8; font-weight: 700;">
											<i class="fas fa-info-circle"></i> Tramite en espera de autorizacion
										</h5>
										<p style="margin: 0; color: #1e3a8a; font-size: 0.95rem;">
											La informacion del paso 3 ya esta guardada, pero este tramite aun no ha sido autorizado y no tienes permiso para aprobarlo.
										</p>
									</div>
								</div>
								<div class="alert alert-warning approval-pending" id="approvalPending" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border: 2px solid #ff9800; border-radius: 12px; padding: 20px; display: <?= $paso3_completo ? 'none' : 'flex' ?>; align-items: center; gap: 20px; box-shadow: 0 4px 6px rgba(255, 152, 0, 0.15);">
									<div style="flex-shrink: 0;">
										<i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ff9800;"></i>
									</div>
									<div style="flex: 1;">
										<h5 style="margin: 0 0 8px 0; color: #e65100; font-weight: 700;">
											<i class="fas fa-info-circle"></i> Informacion Incompleta
										</h5>
										<p style="margin: 0; color: #bf360c; font-size: 0.95rem;">
											Para dejar el tramite listo para autorizacion, primero debes completar y guardar los siguientes campos obligatorios:
										</p>
										<ul id="approvalMissingList" style="margin: 10px 0 0 0; color: #bf360c; font-size: 0.9rem;">
											<?php if (empty($derechos_campos['derechos_tramite']['value'])): ?>
												<li><strong>Monto pago de derechos</strong></li>
											<?php endif; ?>
											<?php if (empty($derechos_campos['derechos_revol_cliente']['value'])): ?>
												<li><strong>Forma de Pago</strong></li>
											<?php endif; ?>
											<?php if (empty($derechos_campos['derechos_refer_banc']['value'])): ?>
												<li><strong>Referencia Bancaria</strong></li>
											<?php endif; ?>
										</ul>
									</div>
								</div>
								<?php endif; ?>
							</div>
					<?php endif; ?>
							</div>
						</div>
						<div class="dropzone-column">
							<div class="dropzone-sticky">
								<h5 class="dropzone-title">
									<i class="fas fa-cloud-upload-alt"></i> Documentos de Derechossss
									<?= perm_audit_tag('can_upload_dropzone_pago_derechos') ?>
								</h5>
								<?php if (!empty($can_section_pago_derechos)): ?>
									<?php if (!empty($can_upload_derechos) && !empty($can_upload_dropzone_pago_derechos) && !$isReadOnlyStep3): ?>
										<div class="dropzone-container">
											<div class="dropzone dropzone-documentos dropzone-compact" id="miDropzone">
												<div class="dz-default dz-message">
													<button class="dz-button" type="button">
														<img src="/public/assets/src/images/upload.svg" class="dz-icon" alt="Subir Archivo">
														<p class="dz-text">Arrastra archivos aqui o haz clic</p>
														<p class="dz-subtext">Documentos de pago de derechos</p>
													</button>
												</div>
											</div>
										</div>
										<button id="btnSubirDocumentos" class="btnSubir">
											<i class="fas fa-cloud-upload-alt"></i> Subir Archivos
										</button>
										<div class="delete-notice">
											<i class="fas fa-info-circle"></i>
											<h6>Para eliminar un archivo, solicitalo al administrador</h6>
										</div>
									<?php endif; ?>
									<!-- Galeria de Imagenes -->
									<div class="gallery-preview" id="documentos-container">
										<?php if (!empty($pago_derechos_db) && is_array($pago_derechos_db)): ?>
											<?php foreach ($pago_derechos_db as $doc): ?>
												<?php
													$fileName = (string) ($doc['file'] ?? '');
													$docType = (string) ($doc['doc_type'] ?? ($doc['comprobante_final'] ?? ''));
													$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
													$isImage = in_array($fileExt, ['jpg','jpeg','png','gif','webp'], true);
													$fileUrl = base_url('/assets/uploads/pago_derechos/' . $id . '/' . $fileName);
												?>
												<div class="file-preview" data-file="<?= esc($fileName) ?>" data-doc-type="<?= esc($docType) ?>" style="border:1px solid #ddd;border-radius:5px;padding:5px;background-color:#f9f9f9;display:inline-block;margin:4px;text-align:center;">
													<a href="<?= esc($fileUrl) ?>" target="_blank">
														<?php if ($isImage): ?>
															<img src="<?= esc($fileUrl) ?>" alt="<?= esc($fileName) ?>" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">
														<?php else: ?>
															<i class="far fa-file" style="font-size:32px;color:#6b7280;"></i>
														<?php endif; ?>
													</a>
													<p style="font-size:10px;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
														<?= esc($fileName) ?>
													</p>
												</div>
											<?php endforeach; ?>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			</div>
			</section>
		</div>
</form>
