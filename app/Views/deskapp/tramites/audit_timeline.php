<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Timeline de Auditoría - <?= $folio ?></title>
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/public/assets/vendors/styles/style.css">
	<style>
	.timeline-audit {
		position: relative;
		padding: 20px 0;
	}
	.timeline-audit::before {
		content: '';
		position: absolute;
		left: 30px;
		top: 0;
		bottom: 0;
		width: 2px;
		background: linear-gradient(to bottom, #667eea 0%, #764ba2 100%);
	}
	.timeline-item {
		position: relative;
		padding-left: 70px;
		margin-bottom: 30px;
	}
	.timeline-icon {
		position: absolute;
		left: 18px;
		width: 26px;
		height: 26px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		color: white;
		font-size: 12px;
		z-index: 1;
	}
	.timeline-content {
		background: white;
		border: 1px solid #e0e0e0;
		border-radius: 8px;
		padding: 15px;
		box-shadow: 0 2px 4px rgba(0,0,0,0.08);
	}
	.timeline-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 10px;
		padding-bottom: 10px;
		border-bottom: 1px solid #f0f0f0;
	}
	.timeline-title {
		font-weight: 600;
		color: #333;
		font-size: 14px;
	}
	.timeline-time {
		font-size: 12px;
		color: #999;
	}
	.timeline-body {
		font-size: 13px;
		color: #666;
	}
	.timeline-change {
		background: #f8f9fa;
		padding: 8px 12px;
		border-radius: 4px;
		margin-top: 8px;
		font-family: monospace;
		font-size: 12px;
	}
	.timeline-meta {
		display: flex;
		gap: 15px;
		margin-top: 10px;
		padding-top: 10px;
		border-top: 1px solid #f0f0f0;
		font-size: 11px;
		color: #999;
	}
	.bg-success { background: #28a745; }
	.bg-info { background: #17a2b8; }
	.bg-warning { background: #ffc107; }
	.bg-danger { background: #dc3545; }
	.bg-primary { background: #007bff; }
	.bg-secondary { background: #6c757d; }
	
	.audit-summary {
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		color: white;
		border-radius: 15px;
		padding: 30px;
		margin-bottom: 30px;
	}
	.audit-stat {
		text-align: center;
		padding: 15px;
	}
	.audit-stat-number {
		font-size: 32px;
		font-weight: 700;
	}
	.audit-stat-label {
		font-size: 13px;
		opacity: 0.9;
		margin-top: 5px;
	}
	</style>
</head>
<body>
	<?php 
		echo view('deskapp/includes/_header');
		echo view('deskapp/includes/_sidebar');
	?>

	<div class="main-container">
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">
				<!-- Header -->
				<div class="page-header">
					<div class="row">
						<div class="col-md-8">
							<div class="title">
								<h4><i class="fa fa-history"></i> Timeline de Auditoría</h4>
							</div>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="<?= base_url('deskapp/dashboard') ?>">Home</a></li>
									<li class="breadcrumb-item"><a href="<?= base_url('deskapp/tramites') ?>">Trámites</a></li>
									<li class="breadcrumb-item active">Auditoría - <?= esc($folio) ?></li>
								</ol>
							</nav>
						</div>
						<div class="col-md-4 text-right">
							<a href="<?= base_url('deskapp/tramites/update/' . $tramite_id) ?>" class="btn btn-outline-primary">
								<i class="fa fa-arrow-left"></i> Volver al Trámite
							</a>
						</div>
					</div>
				</div>

				<!-- Resumen de auditoría -->
				<div class="audit-summary">
					<div class="row">
						<div class="col-md-3 audit-stat">
							<div class="audit-stat-number"><?= number_format($total_changes) ?></div>
							<div class="audit-stat-label">Cambios Totales</div>
						</div>
						<div class="col-md-3 audit-stat">
							<div class="audit-stat-number"><?= esc($last_modifier['username'] ?? 'N/A') ?></div>
							<div class="audit-stat-label">Último Modificador</div>
						</div>
						<div class="col-md-3 audit-stat">
							<div class="audit-stat-number">
								<?= $last_modifier ? date('d/m/Y', strtotime($last_modifier['modified_at'])) : 'N/A' ?>
							</div>
							<div class="audit-stat-label">Última Modificación</div>
						</div>
						<div class="col-md-3 audit-stat">
							<div class="audit-stat-number"><?= count($summary) ?></div>
							<div class="audit-stat-label">Tipos de Cambios</div>
						</div>
						<div class="col-md-3 audit-stat">
							<div class="audit-stat-number"><?= (int) ($step_actual_display ?? 1) ?></div>
							<div class="audit-stat-label">Paso Actual</div>
						</div>
					</div>
				</div>

				<!-- Resumen por tipo -->
				<?php if (!empty($summary)): ?>
				<div class="card-box mb-30">
					<div class="pd-20">
						<h5 class="text-blue h5">Resumen por Tipo de Acción</h5>
					</div>
					<div class="pb-20 px-20">
						<div class="row">
							<?php foreach ($summary as $item): ?>
								<div class="col-md-3 mb-3">
									<div class="card text-center" style="border-left: 4px solid #667eea;">
										<div class="card-body">
											<h4 class="mb-0"><?= $item['count'] ?></h4>
											<p class="mb-0 text-muted" style="font-size: 12px;">
												<?= ucfirst($item['action']) ?>
											</p>
											<small class="text-muted">
												<?= date('d/m/Y H:i', strtotime($item['last_occurrence'])) ?>
											</small>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Timeline de cambios -->
				<div class="card-box mb-30">
					<div class="pd-20">
						<h5 class="text-blue h5">Historial Completo de Cambios</h5>
						<p class="mb-0">Todos los movimientos realizados en este trámite</p>
					</div>
					<div class="pb-20 px-20">
						<?php if (!empty($audit_log)): ?>
							<?php
							// Agrupar cambios por timestamp (mismo segundo = misma operación)
							$grouped_logs = [];
							foreach ($audit_log as $log) {
								$timestamp = $log['created_at'];
								$user_key = $log['user_id'] . '_' . $timestamp;
								
								if (!isset($grouped_logs[$user_key])) {
									$grouped_logs[$user_key] = [
										'timestamp' => $timestamp,
										'user_id' => $log['user_id'],
										'username' => $log['username'],
										'user_email' => $log['user_email'],
										'action' => $log['action'],
										'entity_type' => $log['entity_type'],
										'ip_address' => $log['ip_address'],
										'user_agent' => $log['user_agent'],
										'changes' => []
									];
								}
								
								// Agregar el cambio al grupo
								$grouped_logs[$user_key]['changes'][] = [
									'description' => $log['description'],
									'field_name' => $log['field_name'],
									'old_value' => $log['old_value'],
									'new_value' => $log['new_value']
								];
							}
							?>
							<div class="timeline-audit">
								<?php foreach ($grouped_logs as $group): ?>
									<?php 
									$changeCount = count($group['changes']);
									$firstChange = $group['changes'][0];
									?>
									<div class="timeline-item">
										<div class="timeline-icon bg-<?= get_action_color($group['action']) ?>">
											<i class="fa <?= get_action_icon($group['action']) ?>"></i>
										</div>
										<div class="timeline-content">
											<div class="timeline-header">
												<div>
													<?php if ($changeCount > 1): ?>
														<span class="timeline-title">
															<strong><?= $changeCount ?> campos modificados</strong>
														</span>
													<?php else: ?>
														<span class="timeline-title"><?= esc($firstChange['description']) ?></span>
													<?php endif; ?>
													<span class="badge badge-<?= get_action_color($group['action']) ?> ml-2">
														<?= ucfirst($group['action']) ?>
													</span>
													<?php if ($changeCount > 1): ?>
														<span class="badge badge-secondary ml-1">
															<i class="fa fa-layer-group"></i> Agrupado
														</span>
													<?php endif; ?>
												</div>
												<span class="timeline-time">
													<?= date('d/m/Y H:i:s', strtotime($group['timestamp'])) ?>
												</span>
											</div>
											<div class="timeline-body">
												<strong>Usuario:</strong> <?= esc($group['username']) ?>
												<?php if ($group['user_email']): ?>
													(<?= esc($group['user_email']) ?>)
												<?php endif; ?>
												<br>
												<strong>Módulo:</strong> <?= esc($group['entity_type']) ?>
												
												<?php 
												// Obtener metadata del primer cambio (todos en el grupo tienen la misma metadata)
												$firstLog = $audit_log[array_key_first(array_filter($audit_log, function($log) use ($group) {
													return $log['created_at'] == $group['timestamp'] && $log['user_id'] == $group['user_id'];
												}))];
												$metadata = !empty($firstLog['metadata']) ? json_decode($firstLog['metadata'], true) : null;
												?>
												
												<?php if ($metadata && isset($metadata['form_name'])): ?>
													<br>
													<strong>Formulario:</strong> 
													<span class="badge badge-info">
														<i class="fas fa-file-alt"></i> <?= esc($metadata['form_name']) ?>
														<?php if (isset($metadata['form_step'])): ?>
															(Step <?= $metadata['form_step'] ?>)
														<?php endif; ?>
													</span>
												<?php endif; ?>
												
												<?php if ($changeCount > 1): ?>
													<!-- Mostrar todos los cambios en una lista -->
													<div class="timeline-changes-list mt-2">
														<?php foreach ($group['changes'] as $change): ?>
															<?php if ($change['field_name']): ?>
																<div class="timeline-change mb-2" style="background-color: #f9f9f9; padding: 10px; border-left: 3px solid #667eea; border-radius: 3px;">
																	<strong><?= esc($change['field_name']) ?>:</strong><br>
																	<span style="color: #dc3545;">− <?= esc($change['old_value'] ?? 'N/A') ?></span><br>
																	<span style="color: #28a745;">+ <?= esc($change['new_value'] ?? 'N/A') ?></span>
																</div>
															<?php endif; ?>
														<?php endforeach; ?>
													</div>
												<?php else: ?>
													<!-- Mostrar un solo cambio -->
													<?php if ($firstChange['field_name']): ?>
														<div class="timeline-change">
															<strong><?= esc($firstChange['field_name']) ?>:</strong><br>
															<span style="color: #dc3545;">− <?= esc($firstChange['old_value'] ?? 'N/A') ?></span><br>
															<span style="color: #28a745;">+ <?= esc($firstChange['new_value'] ?? 'N/A') ?></span>
														</div>
													<?php endif; ?>
												<?php endif; ?>
											</div>
											<?php if ($group['ip_address'] || $group['user_agent']): ?>
												<div class="timeline-meta">
													<?php if ($group['ip_address']): ?>
														<span><i class="fa fa-globe"></i> <?= esc($group['ip_address']) ?></span>
													<?php endif; ?>
													<?php if ($group['user_agent']): ?>
														<span><i class="fa fa-desktop"></i> <?= substr(esc($group['user_agent']), 0, 50) ?>...</span>
													<?php endif; ?>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="alert alert-info">
								<i class="fa fa-info-circle"></i> No hay registros de auditoría para este trámite.
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php echo view('deskapp/includes/_footer'); ?>
		</div>
	</div>

	<script src="<?= base_url() ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?= base_url() ?>/public/assets/vendors/scripts/script.min.js"></script>
</body>
</html>

<?php
function get_action_color($action) {
	$colors = [
		'insert' => 'success',
		'update' => 'info',
		'delete' => 'danger',
		'upload' => 'primary',
		'status_change' => 'warning',
		'assignment' => 'secondary'
	];
	return $colors[$action] ?? 'secondary';
}

function get_action_icon($action) {
	$icons = [
		'insert' => 'fa-plus-circle',
		'update' => 'fa-edit',
		'delete' => 'fa-trash',
		'upload' => 'fa-cloud-upload',
		'status_change' => 'fa-exchange-alt',
		'assignment' => 'fa-user'
	];
	return $icons[$action] ?? 'fa-info-circle';
}
?>
