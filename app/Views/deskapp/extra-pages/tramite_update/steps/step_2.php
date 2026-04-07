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
						</div>
					</div>
