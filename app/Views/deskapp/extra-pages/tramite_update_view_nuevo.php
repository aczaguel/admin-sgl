<?= $this->extend('layout/main') ?>

<?= $this->section('additional_css') ?>

<?php $assets = base_url('/public/assets'); ?>
<?php if (!empty($css_files)) {
	foreach ($css_files as $file) { ?>
		<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
	<?php }
} ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css">
<link rel="stylesheet" href="<?= $assets ?>/src/plugins/sweetalert2/sweetalert2.css">
<link rel="stylesheet" href="<?= $assets ?>/src/styles/dropzone.css">
<link rel="stylesheet" href="<?= $assets ?>/src/styles/wizard_modern.css?v=<?= time(); ?>">
<link rel="stylesheet" href="<?= $assets ?>/src/styles/jquery.steps.css">

<link rel="stylesheet" href="<?= $assets ?>/src/styles/tramite_update_view_nuevo.css?v=<?= time(); ?>">

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
	// ReadOnly GLOBAL: solo cuando el trámite ya no debe modificarse en ninguna sección.
	// (23 = Pago a Gestor, 28 = Cobro a Cliente) son pasos activos, no deberían forzar readOnly global.
	$isReadOnlyMode = in_array((int) ($tra_status_id ?? 0), [20, 21], true);
	// ReadOnly por paso: cuando el trámite ya avanzó a un paso superior.
	// Esto evita que se editen pasos previos aunque el wizard permita navegar.
	$stepActualForReadOnly = (int) ($step ?? ($step_actual ?? 0));
	if ($stepActualForReadOnly <= 0) {
		$stepActualForReadOnly = 1;
	}
	$maxStepForReadOnly = 3;
	$readOnlySteps = [];
	for ($s = 1; $s <= $maxStepForReadOnly; $s++) {
		$readOnlySteps[(string) $s] = (!empty($isReadOnlyMode)) || ($stepActualForReadOnly > $s);
	}
	if (empty($can_edit_principal)) {
		// Si el usuario no puede editar el principal, el wizard se muestra en modo solo-lectura.
		for ($s = 1; $s <= $maxStepForReadOnly; $s++) {
			$readOnlySteps[(string) $s] = true;
		}
	}
	$formatValue = static function ($cfg) {
		$value = $cfg['value'] ?? '';
		$type = $cfg['type'] ?? 'text';
		if ($type === 'select') {
			$options = $cfg['options'] ?? [];
			if (is_array($options) && array_key_exists($value, $options)) {
				return $options[$value];
			}
		}
		if ($value === null || $value === '') {
			return '--';
		}
		return $value;
	};
	$formatDateTime = static function ($dateTime) {
		if (empty($dateTime)) {
			return '-- / -- / ----';
		}
		$ts = strtotime((string) $dateTime);
		if (empty($ts)) {
			return '-- / -- / ----';
		}
		return date('d/m/Y H:i', $ts);
	};
	$folioValue = $folio ?? ($fields['folio']['value'] ?? null);
	$contratoValue = $fields['contrato']['value'] ?? ($contrato ?? null);

	// Soporte para vistas separadas: los wrappers definen $only_section.
	// Mapeo sección -> step (para current_step y endpoints de guardado).
	$only_section = $only_section ?? null;
	$onlySectionStep = 0;
	if (!empty($only_section)) {
		$map = [
			'evidencias_finales' => 4,
			'generales' => 1,
			'asigna_gestor' => 2,
			'pago_derechos' => 3,
			'pago_gestor' => 5,
		];
		$onlySectionStep = (int) ($map[$only_section] ?? 0);
	}
	$isOnlySectionView = $onlySectionStep > 0;
	$readonlySectionsForAdvancedView = static function (string $section) use ($user_permissions, $user_roles) : bool {
		switch ($section) {
			case 'generales':
				return has_permission('section_inicial_datos', $user_permissions ?? [], $user_roles ?? []);
			case 'asigna_gestor':
				return has_permission('section_asigna_gestor', $user_permissions ?? [], $user_roles ?? []);
			case 'pago_derechos':
				return has_permission('section_pago_derechos', $user_permissions ?? [], $user_roles ?? []);
			case 'pago_gestor':
				return has_permission('section_pago_gestor', $user_permissions ?? [], $user_roles ?? []);
			default:
				return false;
		}
	};
?>


<div class="main-container">
	<div class="pd-ltr-20 xs-pd-20-10">
		<div class="tramite-header-modern sgl-header-compact">
			<div class="header-top-row">
				<div class="d-flex align-items-center" style="gap:8px;flex-wrap:wrap;">
					<div class="status-badge status-id">
						<i class="fas fa-hashtag"></i>
						ID: <?= esc(isset($id) && $id !== '' ? (string) $id : '--') ?>
					</div>
					<div class="folio-badge">
						<i class="fas fa-file-alt"></i>
						Folio: <?= esc($folioValue !== null && $folioValue !== '' ? $folioValue : 'TR-0000-000') ?>
					</div>
					<div class="folio-badge">
						<i class="fas fa-file-signature"></i>
						Contrato: <?= esc($contratoValue !== null && $contratoValue !== '' ? $contratoValue : '--') ?>
					</div>
				</div>
				<div class="status-badge status-<?php
					$statusClass = 'en-proceso';
					if (isset($tra_status_id)) {
						if ((int) $tra_status_id === 20) $statusClass = 'concluido';
						elseif ((int) $tra_status_id === 21) $statusClass = 'cancelado';
						elseif (in_array((int) $tra_status_id, [27, 28], true)) $statusClass = 'urgente';
						elseif ((int) $tra_status_id === 29) $statusClass = 'cotizacion';
					}
					echo $statusClass;
				?>">
					<i class="fas fa-circle"></i>
					<?= esc(isset($tra_status) && $tra_status !== '' ? $tra_status : 'En Proceso') ?>
				</div>
			</div>

			<div class="badges-wrap" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
				<div style="opacity:.9;font-weight:700;">
					Tipos ligados:
				</div>
				<div id="headerTiposLigados" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;"></div>
			</div>

			<?php $canHeaderHistorialActividad = has_permission('tramite_detalle_quick_actions_historial_actividad_ver', $user_permissions ?? [], $user_roles ?? []); ?>
			<?php if ($canHeaderHistorialActividad || has_permission('important_cancelar_tramite', $user_permissions ?? [], $user_roles ?? []) || has_permission('important_concluir_tramite', $user_permissions ?? [], $user_roles ?? [])): ?>
				<div class="header-actions">
					<?php if ($canHeaderHistorialActividad): ?>
						<button type="button" class="btn-modern btn-info" onclick="window.location.href='<?= site_url('/deskapp/tramites/audit_timeline/' . (int) ($id ?? 0)) ?>'">
							<i class="fas fa-stream"></i>
							Ver Historial de Actividad
						</button>
						<?= perm_audit_tag('tramite_detalle_quick_actions_historial_actividad_ver') ?>
					<?php endif; ?>

					<?php if (has_permission('important_cancelar_tramite', $user_permissions ?? [], $user_roles ?? [])): ?>
						<?php if ((int) ($tra_status_id ?? 0) === 11): ?>
							<button type="button" class="btn-modern btn-warning" onclick="changeStatusTramite(<?= (int) ($id ?? 0) ?>, 29)">
								<i class="fas fa-file-invoice"></i>
								Es solo Cotizacion
							</button>
							<?= perm_audit_tag('important_cancelar_tramite') ?>
						<?php endif; ?>
						<?php if (!in_array((int) ($tra_status_id ?? 0), [20, 21], true)): ?>
							<button type="button" class="btn-modern btn-danger" data-toggle="modal" data-target="#Medium-modal">
								<i class="fas fa-times-circle"></i>
								Cancelar Tramite
							</button>
							<?= perm_audit_tag('important_cancelar_tramite') ?>
						<?php endif; ?>
					<?php endif; ?>

					<?php if (has_permission('important_concluir_tramite', $user_permissions ?? [], $user_roles ?? [])): ?>
						<?php if (in_array((int) ($tra_status_id ?? 0), [28], true)): ?>
							<button type="button" class="btn-modern btn-success" onclick="concluirTramite(<?= (int) ($id ?? 0) ?>, 20)">
								<i class="fas fa-check-circle"></i>
								Concluir Tramite
							</button>
							<?= perm_audit_tag('important_concluir_tramite') ?>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="timeline-info">
				<div class="timeline-item">
					<div class="timeline-icon">
						<i class="fas fa-calendar-plus"></i>
					</div>
					<div class="timeline-content">
						<h6>Fecha Creación</h6>
						<p><?= esc($formatDateTime($created_at ?? null)) ?></p>
					</div>
				</div>

				<div class="timeline-item">
					<div class="timeline-icon">
						<i class="fas fa-play-circle"></i>
					</div>
					<div class="timeline-content">
						<h6>Fecha Inicio</h6>
						<p><?= esc($formatDateTime($started_at ?? null)) ?></p>
					</div>
				</div>

				<div class="timeline-item">
					<div class="timeline-icon">
						<i class="fas fa-file-contract"></i>
					</div>
					<div class="timeline-content">
						<h6>Tipo de Trámite</h6>
						<p><?= esc(!empty($tipo_tramite) ? (string) $tipo_tramite : 'N/A') ?></p>
					</div>
				</div>

				<div class="timeline-item">
					<div class="timeline-icon">
						<i class="fas fa-user-tie"></i>
					</div>
					<div class="timeline-content">
						<h6>Gestor Asignado</h6>
						<p><?= esc(!empty($gestor) ? (string) $gestor : 'Sin asignar') ?></p>
					</div>
				</div>

				<div class="timeline-item">
					<div class="timeline-icon">
						<i class="fas fa-building"></i>
					</div>
					<div class="timeline-content">
						<h6>Cliente</h6>
						<p><?= esc(!empty($cliente) ? (string) $cliente : 'N/A') ?></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Detalle rápido (Acciones rápidas) -->
		<?php
			helper(['permissions']);
			$detailRoles = !empty($user_roles) ? $user_roles : ($session->get('user_roles') ?? []);
			$detailPerms = !empty($user_permissions) ? $user_permissions : ($session->get('user_permissions') ?? []);
			$canSectionPagoGestor = !empty($can_section_pago_gestor) || has_permission('section_pago_gestor', $detailPerms, $detailRoles);
			$canSectionFinalCostos = !empty($can_section_final_costos) || has_permission('section_final_costos', $detailPerms, $detailRoles);

			$canQuickDocumentos = has_permission('quick_action_documentos', $detailPerms, $detailRoles);
			$canQuickBitacora = has_permission('quick_action_bitacora', $detailPerms, $detailRoles);
			$canQuickHistorialActividad = has_permission('tramite_detalle_quick_actions_historial_actividad_ver', $detailPerms, $detailRoles);
			$canQuickPagosDerecho = has_permission('quick_action_pagos_derecho', $detailPerms, $detailRoles);
			$canQuickPagoGestor = has_permission('quick_action_pago_gestor', $detailPerms, $detailRoles);
			$canQuickEvidenciasFinales = has_permission('quick_action_evidencias_finales', $detailPerms, $detailRoles);
			$canQuickCobrosCliente = has_permission('quick_action_cobros_cliente', $detailPerms, $detailRoles);
			$canListCobroCliente = has_permission('list_cobro_cliente', $detailPerms, $detailRoles);

			$canSeePagoGestorBtn = $canQuickPagoGestor && $canSectionPagoGestor;
			$canSeeEvidenciasFinalesBtn = $canQuickEvidenciasFinales && $canSectionFinalCostos;
			// Para ver el CRUD en modal basta con `section_final_costos`.
			// `quick_action_cobros_cliente` es requerido para add/edit/delete, pero no necesariamente para ver.
			$canSeeCobroClienteBtn = $canSectionFinalCostos && ($canQuickCobrosCliente || $canListCobroCliente);
			$canSeeStatusQuickActions = (!empty($tra_status_id) && in_array((int) $tra_status_id, [23, 27, 28, 20, 21], true));

			$canSeeAnyQuickAction = (
				$canQuickDocumentos
				|| $canQuickBitacora
				|| $canQuickHistorialActividad
				|| $canQuickPagosDerecho
				|| ($canSeeStatusQuickActions && ($canSeePagoGestorBtn || $canSeeEvidenciasFinalesBtn || $canSeeCobroClienteBtn))
			);
		?>

		<?php if (!empty($canSeeAnyQuickAction)): ?>
			<div class="quick-actions-ribbon">
				<div class="ribbon-title">
					<i class="fas fa-bolt"></i>
					<span>Detalle rápido</span>
				</div>
				<div class="ribbon-buttons">
					<?php if ($canQuickDocumentos): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-documentos">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
								<i class="fas fa-folder-open"></i>
							</div>
							<span class="ribbon-label">Documentos</span>
							<?= perm_audit_tag('quick_action_documentos') ?>
						</button>
					<?php endif; ?>

					<?php if ($canQuickBitacora): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-bitacora">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
								<i class="fas fa-history"></i>
							</div>
							<span class="ribbon-label">Bitácora</span>
							<?= perm_audit_tag('quick_action_bitacora') ?>
						</button>
					<?php endif; ?>

					<?php if ($canQuickHistorialActividad): ?>
						<button type="button" class="ribbon-btn" onclick="window.location.href='<?= site_url('/deskapp/tramites/audit_timeline/' . (int) $id) ?>'">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #5b86e5 0%, #36d1dc 100%);">
								<i class="fas fa-stream"></i>
							</div>
							<span class="ribbon-label">Historial Actividad</span>
							<?= perm_audit_tag('tramite_detalle_quick_actions_historial_actividad_ver') ?>
						</button>
					<?php endif; ?>

					<?php if ($canQuickPagosDerecho): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pagos-derecho">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
								<i class="fas fa-receipt"></i>
							</div>
							<span class="ribbon-label">Pagos Derecho</span>
							<?= perm_audit_tag('quick_action_pagos_derecho') ?>
						</button>
					<?php endif; ?>

					<?php if ($canSeeStatusQuickActions && $canSeePagoGestorBtn): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-pago-gestor">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
								<i class="fas fa-hand-holding-usd"></i>
							</div>
							<span class="ribbon-label">Pago Gestor</span>
							<?= perm_audit_tag('quick_action_pago_gestor') ?>
						</button>
					<?php endif; ?>

					<?php if ($canSeeStatusQuickActions && $canSeeEvidenciasFinalesBtn): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-evidencias-finales">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
								<i class="fas fa-check-double"></i>
							</div>
							<span class="ribbon-label">Evidencias Finales</span>
							<?= perm_audit_tag('quick_action_evidencias_finales') ?>
						</button>
					<?php endif; ?>

					<?php if ($canSeeStatusQuickActions && $canSeeCobroClienteBtn): ?>
						<button type="button" class="ribbon-btn" data-toggle="modal" data-target="#modal-cobro-cliente">
							<div class="ribbon-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
								<i class="fas fa-money-check-alt"></i>
							</div>
							<span class="ribbon-label">Cobros Cliente</span>
							<?= perm_audit_tag('quick_action_cobros_cliente') ?>
							<?= perm_audit_tag('list_cobro_cliente') ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="pd-20 card-box mb-30 sgl-page-tight">
			<?php if (!empty($isOnlySectionView) && $onlySectionStep === 4): ?>
				<?php // Paso 4 dedicado: mostrar 1-3 readonly y el modulo de evidencias finales. ?>
				<div class="sgl-readonly-mode">
					<?php $showSection = $readonlySectionsForAdvancedView; ?>
					<?php include APPPATH . 'Views/deskapp/extra-pages/tramitesn/partials/steps_readonly_1_3.php'; ?>
				</div>
				<?php include APPPATH . 'Views/deskapp/extra-pages/tramitesn/partials/step_4_evidencias_finales.php'; ?>
			<?php elseif (!empty($isOnlySectionView) && $onlySectionStep === 5): ?>
				<?php // Paso 5 dedicado: mostrar 1-3 readonly y Pago a Gestor sin dropzone. ?>
				<div class="sgl-readonly-mode">
					<?php $showSection = $readonlySectionsForAdvancedView; ?>
					<?php include APPPATH . 'Views/deskapp/extra-pages/tramitesn/partials/steps_readonly_1_3.php'; ?>
				</div>
				<?php include APPPATH . 'Views/deskapp/extra-pages/tramitesn/partials/step_5_pago_gestor.php'; ?>
			<?php else: ?>
				<form id="tramiteNuevoForm" method="post" action="<?= site_url('/deskapp/tramitesn/update_save/' . (int) ($id ?? 0)) ?>">
					<input type="hidden" id="current_step" name="current_step" value="<?= (int) ($isOnlySectionView ? $onlySectionStep : ($step ?? ($step_actual ?? 1))) ?>">
					<div id="tramiteNuevoMessage" class="alert" style="display:none;"></div>

					<?php if (empty($isOnlySectionView)): ?>
						<div id="wizard" class="wizard-modern">
							<h3>Paso 1</h3>
							<section>
								<?= $this->include('deskapp/extra-pages/tramite_update/steps/step_1') ?>
							</section>

							<h3>Paso 2</h3>
							<section>
								<?= $this->include('deskapp/extra-pages/tramite_update/steps/step_2') ?>
							</section>

							<h3>Paso 3</h3>
							<section>
								<?= $this->include('deskapp/extra-pages/tramite_update/steps/step_3') ?>
							</section>
						</div>
					<?php else: ?>
						<?php if ($onlySectionStep === 1): ?>
							<?= $this->include('deskapp/extra-pages/tramite_update/steps/step_1') ?>
						<?php elseif ($onlySectionStep === 2): ?>
							<?= $this->include('deskapp/extra-pages/tramite_update/steps/step_2') ?>
						<?php elseif ($onlySectionStep === 3): ?>
							<?= $this->include('deskapp/extra-pages/tramite_update/steps/step_3') ?>
						<?php endif; ?>

						<div class="d-flex justify-content-end mt-3">
							<button type="submit" class="btn btn-success btn-sm sgl-btn-pill">
								<i class="fas fa-save"></i> Guardar
							</button>
						</div>
					<?php endif; ?>
				</form>
			<?php endif; ?>

			<?= $this->include('deskapp/extra-pages/tramite_update/steps/step_5') ?>
		</div>
	</div>
</div>

<?php if (has_permission('important_cancelar_tramite', $user_permissions ?? [], $user_roles ?? [])): ?>
	<div class="modal fade" id="Medium-modal" tabindex="-1" role="dialog" aria-labelledby="cancelTramiteLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="cancelTramiteLabel">Cancelar Tramite</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="motivo" class="form-label">Motivo de Cancelacion</label>
						<textarea class="form-control" id="motivo" rows="3" required></textarea>
						<div id="cancelError" class="text-danger small mt-2" style="display:none;"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
					<button type="button" class="btn btn-primary" id="saveCancelBtn">Continuar</button>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<!-- Modal: Cambiar tipo principal -->
<div class="modal fade" id="modalEditPrincipalTipo" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Cambiar tipo de trámite principal</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<label class="form-label">Nuevo tipo</label>
				<select id="principalTipoSelect" class="form-control">
					<option value="">Seleccione...</option>
					<?php if (!empty($tra_tipos_options) && is_array($tra_tipos_options)): ?>
						<?php foreach ($tra_tipos_options as $tipoId => $tipoLabel): ?>
							<option value="<?= (int) $tipoId ?>"><?= esc($tipoLabel) ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<small class="text-muted d-block mt-2">Este cambio actualiza el tipo principal del trámite.</small>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-primary" id="btnGuardarPrincipalTipo">Guardar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Cambiar tipo asociado -->
<div class="modal fade" id="modalEditAsociadoTipo" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Cambiar tipo de trámite asociado</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="asociadoIdInput" value="">
				<label class="form-label">Nuevo tipo</label>
				<select id="asociadoTipoSelect" class="form-control">
					<option value="">Seleccione...</option>
					<?php if (!empty($tra_tipos_options) && is_array($tra_tipos_options)): ?>
						<?php foreach ($tra_tipos_options as $tipoId => $tipoLabel): ?>
							<option value="<?= (int) $tipoId ?>"><?= esc($tipoLabel) ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<small class="text-muted d-block mt-2">No permite duplicados dentro del mismo trámite.</small>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-primary" id="btnGuardarAsociadoTipo">Guardar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Eliminar asociado -->
<div class="modal fade" id="modalDeleteAsociado" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Eliminar tipo asociado</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="deleteAsociadoIdInput" value="">
				<p class="mb-1">¿Seguro que deseas eliminar este tipo asociado?</p>
				<small class="text-muted d-block">Este cambio solo afecta a los tipos asociados. El tipo principal del trámite se mantiene.</small>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-danger" id="btnConfirmDeleteAsociado">Eliminar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Comprobante final guardado -->
<div class="modal fade" id="modalComprobanteFinal" tabindex="-1" role="dialog" aria-labelledby="modalComprobanteFinalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalComprobanteFinalLabel">Comprobante final guardado</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				El archivo se registro como: <strong id="comprobanteFinalText">tramite_recibido</strong>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
			</div>
		</div>
	</div>
</div>

<!-- MODALS DE ACCIONES RAPIDAS -->

<div class="modal fade" id="modal-documentos" tabindex="-1" role="dialog" aria-labelledby="modalDocumentosLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
				<h4 class="modal-title" id="modalDocumentosLabel">
					<i class="fas fa-folder-open"></i> Documentos
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_docs)) {
							echo $output_docs;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay documentos disponibles</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-bitacora" tabindex="-1" role="dialog" aria-labelledby="modalBitacoraLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
				<h4 class="modal-title" id="modalBitacoraLabel">
					<i class="fas fa-history"></i> Bitacora
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_bitacora)) {
							echo $output_bitacora;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay registros en la bitacora</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-pagos-derecho" tabindex="-1" role="dialog" aria-labelledby="modalPagosDerechoLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
				<h4 class="modal-title" id="modalPagosDerechoLabel">
					<i class="fas fa-receipt"></i> Pagos de Derecho
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_derechos)) {
							echo $output_derechos;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay documentos de pago de derecho</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<?php if (isset($tra_status_id) && in_array($tra_status_id, [23, 27, 28, 20, 21])) : ?>
<div class="modal fade" id="modal-pago-gestor" tabindex="-1" role="dialog" aria-labelledby="modalPagoGestorLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
				<h4 class="modal-title" id="modalPagoGestorLabel">
					<i class="fas fa-hand-holding-usd"></i> Pago al Gestor
				</h4>
				<button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
			</div>
			<div class="modal-body">
				<div class="pd-20">
					<?php
						if (!empty($output_pago_gestor)) {
							echo $output_pago_gestor;
						} else {
							echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay informacion de pago al gestor</div>';
						}
					?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- FIN MODALS DE ACCIONES RAPIDAS -->

<?= $this->endSection() ?>

<?= $this->section('additional_js') ?>
<script src="<?= $assets ?>/src/plugins/sweetalert2/sweetalert2.all.js"></script>
<?php
	if (!empty($js_files)) {
		foreach ($js_files as $file) { ?>
			<script src="<?php echo $file . "?v=" . time(); ?>"></script>
		<?php }
	}
?>
<script src="<?= $assets ?>/src/scripts/dropzone.js"></script>
<script src="<?= $assets ?>/src/scripts/tramitesn_update_view_nuevo_pre.js?v=<?= time(); ?>"></script>
<script src="<?= $assets ?>/src/plugins/jquery-steps/jquery.steps.js"></script>
<script>
	<?php
		$principalTipoId = 0;
		if (isset($fields) && is_array($fields) && isset($fields['tra_tipos_id']) && is_array($fields['tra_tipos_id'])) {
			$principalTipoId = (int) ($fields['tra_tipos_id']['value'] ?? 0);
		}
	?>

	window.SGL_TRAMITESN_UPDATE_V2 = {
		maxStep: 3,
		onlySectionStep: <?= (int) ($onlySectionStep ?? 0) ?>,
		csrfName: '<?= csrf_token() ?>',
		csrfHash: '<?= csrf_hash() ?>',
		tramiteId: <?= (int) ($id ?? 0) ?>,
		tiposOptions: <?= json_encode($tra_tipos_options ?? []) ?>,
		tiposExistentes: <?= json_encode($servicios_tipos_ids ?? []) ?>,
		principalTipoId: <?= (int) $principalTipoId ?>,
		statusId: <?= (int) ($tra_status_id ?? 0) ?>,
		isLocked: <?= in_array((int) ($tra_status_id ?? 0), [20, 21], true) ? 'true' : 'false' ?>,
		isReadOnlyMode: <?= !empty($isReadOnlyMode) ? 'true' : 'false' ?>,
		readOnlySteps: <?= json_encode($readOnlySteps ?? new stdClass()) ?>,
		requireSavedSteps: { 2: true },
		savedSteps: {
			1: <?= !empty($step1_complete) ? 'true' : 'false' ?>,
			2: <?= !empty($step2_complete) ? 'true' : 'false' ?>,
			3: <?= !empty($step3_complete) ? 'true' : 'false' ?>
		},
		stepActual: <?= (int) ($step ?? ($step_actual ?? 0)) ?>,
		canEditAsociado: <?= (!empty($can_edit_asociado) && !in_array((int) ($tra_status_id ?? 0), [20, 21], true)) ? 'true' : 'false' ?>,
		canDeleteAsociado: <?= (!empty($can_delete_asociado) && !in_array((int) ($tra_status_id ?? 0), [20, 21], true)) ? 'true' : 'false' ?>,
		canEditPagoGestor: <?= (!empty($can_edit_pago_gestor) && !in_array((int) ($tra_status_id ?? 0), [20, 21], true)) ? 'true' : 'false' ?>,
		permissions: {
			canUploadDerechos: <?= !empty($can_upload_derechos) ? 'true' : 'false' ?>,
			canUploadDropzoneDerechos: <?= !empty($can_upload_dropzone_pago_derechos) ? 'true' : 'false' ?>,
			canUploadPagoGestor: <?= !empty($can_upload_pago_gestor) ? 'true' : 'false' ?>,
			canUploadDropzoneEvidenciasFinales: <?= !empty($can_upload_dropzone_evidencias_finales) ? 'true' : 'false' ?>,
			canUploadDropzonePagoGestorDocumentos: <?= !empty($can_upload_dropzone_pago_gestor_documentos) ? 'true' : 'false' ?>,
			canUploadFinalDocs: <?= !empty($can_upload_final_docs) ? 'true' : 'false' ?>
		},
		swalSrc: '<?= $assets ?>/src/plugins/sweetalert2/sweetalert2.all.js',
		urls: {
			servicesAdd: '<?= site_url('/deskapp/tramitesn/services/add') ?>',
			servicesUpdate: '<?= site_url('/deskapp/tramitesn/services/update') ?>',
			servicesDelete: '<?= site_url('/deskapp/tramitesn/services/delete') ?>',
			principalUpdateTipo: '<?= site_url('/deskapp/tramitesn/principal/update_tipo') ?>',
			viewEvidenciasFinales: '<?= site_url('/deskapp/tramitesn/ver_seccion_evidencias_finales/' . (int) ($id ?? 0)) ?>',
			viewPagoGestor: '<?= site_url('/deskapp/tramitesn/ver_seccion_pago_gestor/' . (int) ($id ?? 0)) ?>',
			viewCobroCliente: '<?= site_url('/deskapp/tramitesn/ver_seccion_cobro_cliente/' . (int) ($id ?? 0)) ?>',
			updateSave: '<?= site_url('/deskapp/tramitesn/update_save/' . (int) ($id ?? 0)) ?>',
			updateGestorSave: '<?= site_url('/deskapp/tramitesn/update_gestor_save/' . (int) ($id ?? 0)) ?>',
			updateDerechosSave: '<?= site_url('/deskapp/tramitesn/update_derechos_save/' . (int) ($id ?? 0)) ?>',
			uploadComprobante: '<?= site_url('/deskapp/tramites/upload_comprobante/' . (int) ($id ?? 0)) ?>',
			deleteComprobante: '<?= site_url('/deskapp/tramites/delete_comprobante') ?>',
			getGestoresByEmpresaId: '<?= site_url('/deskapp/tramites/getGestoresByEmpresaId') ?>',
			updatePagoGestor: '<?= site_url('/deskapp/tramitesn/update_pago_gestor/' . (int) ($id ?? 0)) ?>',
			uploadPagoGestor: '<?= site_url('/deskapp/tramitesn/upload_pago_gestor/' . (int) ($id ?? 0)) ?>',
			deletePagoGestor: '<?= site_url('/deskapp/tramitesn/delete_pago_gestor') ?>',
			getServiceCosts: '<?= site_url('/deskapp/tramitesn/get_service_costs_by_tramite/' . (int) ($id ?? 0)) ?>',
			updateServiceCost: '<?= site_url('/deskapp/tramitesn/update_service_cost') ?>',
			uploadFinalDocBase: '<?= site_url('/deskapp/tramitesn/upload_final_doc') ?>/',
			deleteFinalDoc: '<?= site_url('/deskapp/tramitesn/delete_final_doc') ?>'
		}
	};
</script>
<script src="<?= $assets ?>/src/scripts/tramitesn_update_v2.js?v=<?= time(); ?>"></script>
<script src="<?= $assets ?>/src/scripts/tramitesn_update_view_nuevo_post.js?v=<?= time(); ?>"></script>
<?= $this->endSection() ?>
