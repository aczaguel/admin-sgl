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
										<select id="tra_tipos_add" class="form-control form-control-sm">
											<option value="">Seleccione...</option>
											<?php if (!empty($tra_tipos_options) && is_array($tra_tipos_options)): ?>
												<?php foreach ($tra_tipos_options as $tipoId => $tipoLabel): ?>
													<option value="<?= (int) $tipoId ?>"><?= esc($tipoLabel) ?></option>
												<?php endforeach; ?>
											<?php endif; ?>
										</select>
										<button type="button" id="btnAgregarTipo" class="sgl-btn-mini">
											<i class="fas fa-plus"></i> Agregar
										</button>
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
											<?php if (!empty($can_edit_asociado) && !$isPrincipalTipo): ?>
												<button type="button" class="btn btn-sm btn-outline-primary btnCambiarAsociado" data-toggle="modal" data-target="#modalEditAsociadoTipo" title="Cambiar">
													<i class="fas fa-pen"></i>
												</button>
											<?php endif; ?>
											<?php if (!empty($can_delete_asociado) && !$isPrincipalTipo): ?>
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
														<select class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $required ? 'required' : '' ?>>
															<option value="">Seleccione...</option>
																<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
																	<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
																		<option value="<?= esc($optValue, 'attr') ?>" <?= ($cfg['value'] ?? null) == $optValue ? 'selected' : '' ?>><?= esc($optLabel) ?></option>
																	<?php endforeach; ?>
																<?php endif; ?>
														</select>
													<?php else: ?>
														<input type="<?= esc($type) ?>" class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($cfg['value'] ?? '', 'attr') ?>" <?= $required ? 'required' : '' ?>>
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
										<textarea class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" rows="2" <?= $required ? 'required' : '' ?>><?= esc($cfg['value'] ?? '') ?></textarea>
									<?php elseif ($type === 'select'): ?>
										<select class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" <?= $required ? 'required' : '' ?>>
											<option value="">Seleccione...</option>
											<?php if (!empty($cfg['options']) && is_array($cfg['options'])): ?>
												<?php foreach ($cfg['options'] as $optValue => $optLabel): ?>
													<option value="<?= esc($optValue, 'attr') ?>" <?= ($cfg['value'] ?? null) == $optValue ? 'selected' : '' ?>><?= esc($optLabel) ?></option>
												<?php endforeach; ?>
											<?php endif; ?>
										</select>
									<?php else: ?>
										<input type="<?= esc($type) ?>" class="form-control form-control-sm" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($cfg['value'] ?? '', 'attr') ?>" <?= $required ? 'required' : '' ?>>
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