<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Timeline de Bitacora</title>
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
		font-size: 28px;
		font-weight: 700;
	}
	.audit-stat-label {
		font-size: 12px;
		opacity: 0.9;
		margin-top: 5px;
	}
	.filter-box {
		background: #ffffff;
		border: 1px solid #e0e0e0;
		border-radius: 8px;
		padding: 20px;
		margin-bottom: 30px;
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
				<div class="page-header">
					<div class="row">
						<div class="col-md-8">
							<div class="title">
								<h4><i class="fa fa-history"></i> Timeline de Bitacora</h4>
							</div>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="<?= base_url('deskapp/dashboard') ?>">Home</a></li>
									<li class="breadcrumb-item active">Bitacora</li>
								</ol>
							</nav>
						</div>
					</div>
				</div>

				<div class="filter-box">
					<form method="get" action="<?= base_url('bitacora/timeline') ?>">
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label>Folio</label>
									<input type="text" name="folio" class="form-control" value="<?= esc($filters['folio'] ?? '') ?>" placeholder="Ej: 064474">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>Tramite ID</label>
									<input type="text" name="tramite_id" class="form-control" value="<?= esc($filters['tramite_id'] ?? '') ?>" placeholder="Ej: 108">
								</div>
							</div>
							<div class="col-md-4 d-flex align-items-end">
								<div class="form-group mb-0">
									<button type="submit" class="btn btn-primary">Filtrar</button>
									<a href="<?= base_url('bitacora/timeline') ?>" class="btn btn-outline-secondary">Limpiar</a>
								</div>
							</div>
						</div>
						<small class="text-muted">Usa un filtro para evitar cargar demasiados datos.</small>
					</form>
				</div>

				<div class="audit-summary">
					<div class="row">
						<div class="col-md-3 audit-stat">
							<div class="audit-stat-number"><?= number_format($total_changes) ?></div>
							<div class="audit-stat-label">Cambios Totales</div>
						</div>
						<div class="col-md-3 audit-stat">
							<div class="audit-stat-number"><?= esc($last_modifier['username'] ?? 'N/A') ?></div>
							<div class="audit-stat-label">Ultimo Modificador</div>
						</div>
						<div class="col-md-3 audit-stat">
							<div class="audit-stat-number">
								<?= $last_modifier ? date('d/m/Y', strtotime($last_modifier['modified_at'])) : 'N/A' ?>
							</div>
							<div class="audit-stat-label">Ultima Modificacion</div>
						</div>
						<div class="col-md-3 audit-stat">
							<div class="audit-stat-number"><?= count($summary) ?></div>
							<div class="audit-stat-label">Tipos de Cambios</div>
						</div>
					</div>
				</div>

				<?php if (!empty($summary)): ?>
					<div class="card-box mb-30">
						<div class="pd-20">
							<h5 class="text-blue h5">Resumen por Tipo de Accion</h5>
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
													<?= $item['last_occurrence'] ? date('d/m/Y H:i', strtotime($item['last_occurrence'])) : 'N/A' ?>
												</small>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<div class="card-box mb-30">
					<div class="pd-20">
						<h5 class="text-blue h5">Historial Completo</h5>
						<p class="mb-0">Movimientos registrados en bitacora</p>
					</div>
					<div class="pb-20 px-20">
						<?php if (!empty($bitacora_log)): ?>
							<?php
							$grouped_logs = [];
							foreach ($bitacora_log as $log) {
								$timestamp = $log['created_at'] ?? '';
								$userKey = ($log['user_id'] ?? '0') . '_' . $timestamp;
								if (!isset($grouped_logs[$userKey])) {
									$nameParts = array_filter([
										$log['firstname'] ?? '',
										$log['lastname'] ?? '',
									]);
									$displayName = trim(implode(' ', $nameParts));
									if ($displayName === '') {
										$displayName = $log['username'] ?? 'N/A';
									}

									$grouped_logs[$userKey] = [
										'timestamp' => $timestamp,
										'user_id' => $log['user_id'] ?? null,
										'username' => $displayName,
										'user_email' => $log['email'] ?? null,
										'tipo' => $log['tipo'] ?? 'unknown',
										'origen' => $log['origen'] ?? 'tramite',
										'folio_tramite' => $log['folio_tramite'] ?? null,
										'tramite_id' => $log['tramite_id'] ?? null,
										'changes' => [],
									];
								}

								$cambios = $log['cambios'] ?? [];
								if (is_array($cambios)) {
									foreach ($cambios as $field => $values) {
										if (!is_array($values)) {
											continue;
										}
										$grouped_logs[$userKey]['changes'][] = [
											'field_name' => $field,
											'old_value' => $values['valor_original'] ?? null,
											'new_value' => $values['valor_nuevo'] ?? null,
										];
									}
								}
							}
							?>
							<div class="timeline-audit">
								<?php foreach ($grouped_logs as $group): ?>
									<?php $changeCount = count($group['changes']); ?>
									<div class="timeline-item">
										<div class="timeline-icon bg-<?= get_action_color($group['tipo']) ?>">
											<i class="fa <?= get_action_icon($group['tipo']) ?>"></i>
										</div>
										<div class="timeline-content">
											<div class="timeline-header">
												<div>
													<span class="timeline-title">
														<?= $changeCount > 0 ? $changeCount . ' campos' : 'Sin cambios detectados' ?>
													</span>
													<span class="badge badge-<?= get_action_color($group['tipo']) ?> ml-2">
														<?= ucfirst($group['tipo']) ?>
													</span>
													<span class="badge badge-secondary ml-1">
														<?= esc($group['origen']) ?>
													</span>
												</div>
												<span class="timeline-time">
													<?= $group['timestamp'] ? date('d/m/Y H:i:s', strtotime($group['timestamp'])) : 'N/A' ?>
												</span>
											</div>
											<div class="timeline-body">
												<strong>Usuario:</strong> <?= esc($group['username']) ?>
												<?php if (!empty($group['user_email'])): ?>
													(<?= esc($group['user_email']) ?>)
												<?php endif; ?>
												<br>
												<strong>Folio:</strong> <?= esc($group['folio_tramite'] ?? 'N/A') ?>
												<strong class="ml-2">Tramite ID:</strong> <?= esc($group['tramite_id'] ?? 'N/A') ?>

												<?php if ($changeCount > 0): ?>
													<div class="timeline-changes-list mt-2">
														<?php foreach ($group['changes'] as $change): ?>
															<div class="timeline-change mb-2" style="background-color: #f9f9f9; padding: 10px; border-left: 3px solid #667eea; border-radius: 3px;">
																<strong><?= esc($change['field_name']) ?>:</strong><br>
																<span style="color: #dc3545;">- <?= esc($change['old_value'] ?? 'N/A') ?></span><br>
																<span style="color: #28a745;">+ <?= esc($change['new_value'] ?? 'N/A') ?></span>
															</div>
														<?php endforeach; ?>
													</div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else: ?>
							<div class="alert alert-info">
								<i class="fa fa-info-circle"></i> No hay registros de bitacora.
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
