					<?php $step23Complete = !empty($step2_complete) && !empty($step3_complete); ?>
					<div class="wizard-section" data-step="2">
						<div class="sgl-step-center">
							<div class="sgl-step-form-ribbon <?= $step23Complete ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="2">
								<div class="sgl-icon"><i class="<?= $step23Complete ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
								<div class="sgl-text">
									<?= $step23Complete ? 'Datos completos' : 'Asigna gestor y completa el pago de derechos' ?>
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
													<?= $required ? 'required' : '' ?>>
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

							<div id="tramiteStep3Message" class="alert mt-3" style="display:none;"></div>
							<div class="sgl-step-form-ribbon mt-3 <?= !empty($step3_complete) ? 'is-complete' : 'is-incomplete' ?>" data-ribbon-step="3">
								<div class="sgl-icon"><i class="<?= !empty($step3_complete) ? 'fas fa-check' : 'fas fa-exclamation' ?>"></i></div>
								<div class="sgl-text">
									<?= !empty($step3_complete) ? 'Pago de derechos completo' : 'Configura los pagos de derechos y su evidencia' ?>
								</div>
							</div>
							<div class="form-dropzone-grid mt-3">
								<div class="form-column">
									<div class="sgl-soft-panel">
										<p class="sgl-soft-panel-title">Pago de Derechos</p>
										<div class="row">
										<?php if (!empty($derechos_campos) && is_array($derechos_campos)): ?>
											<?php foreach ($derechos_campos as $name => $cfg): ?>
												<?php
													$type = $cfg['type'] ?? 'text';
													$required = !empty($cfg['required']);
												?>
												<div class="col-lg-4 col-md-6 col-sm-12 mb-2">
													<div class="form-group">
														<label for="<?= esc($name) ?>"><?= esc($cfg['label'] ?? ucfirst($name)) ?><?= $required ? ' *' : '' ?></label>
														<?php if ($type === 'select'): ?>
															<select
																class="form-control form-control-sm"
																id="<?= esc($name) ?>"
																name="<?= esc($name) ?>"
																<?= $required ? 'required' : '' ?>>
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
																<?= $required ? 'required' : '' ?>>
														<?php endif; ?>
														<div class="invalid-feedback">Campo obligatorio.</div>
													</div>
												</div>
											<?php endforeach; ?>
										<?php endif; ?>
										</div>
									</div>

									<?php
										$paso3_completo = !empty($derechos_campos['derechos_tramite']['value'])
											&& !empty($derechos_campos['derechos_revol_cliente']['value'])
											&& !empty($derechos_campos['derechos_refer_banc']['value']);
										$arr_status = [
											11 => 1, 22 => 2, 25 => 3, 26 => 3, 27 => 3,
											23 => 4, 28 => 5, 20 => 6, 21 => 7, 29 => 1
										];
										$statusIdInt = (int) ($tra_status_id ?? 0);
										$step_actual = $arr_status[$statusIdInt] ?? 1;
										$canAuthorizeStep3 = has_permission('important_pasar_a_pagos', $user_permissions ?? [], $user_roles ?? [])
											&& $step_actual <= 3
											&& puede_editar_modulo($user_roles ?? [], $tra_status_id, 'boton_aprobar_tramite', $reembolso_status_id, $cobro_status_id, 3);
										$showApprovalStep3 = $step_actual <= 3;
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
															La informacion del pago de derechos ya esta guardada, pero este tramite aun no ha sido autorizado y no tienes permiso para aprobarlo.
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

								<div class="dropzone-column">
									<div class="dropzone-sticky">
										<h5 class="dropzone-title">
											<i class="fas fa-cloud-upload-alt"></i> Documentos de Derechos
										</h5>
										<?php if (!empty($can_section_pago_derechos)): ?>
											<?php if (!empty($can_upload_derechos) && !empty($can_upload_dropzone_pago_derechos) && empty($isReadOnlyMode) && ($step_actual ?? 1) <= 3): ?>
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
												<button id="btnSubirDocumentos" class="btnSubir" type="button">
													<i class="fas fa-cloud-upload-alt"></i> Subir Archivos
												</button>
												<div class="delete-notice">
													<i class="fas fa-info-circle"></i>
													<h6>Para eliminar un archivo, solicitalo al administrador</h6>
												</div>
											<?php endif; ?>
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
												<?php else: ?>
													<div class="text-muted">No hay archivos cargados.</div>
												<?php endif; ?>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
