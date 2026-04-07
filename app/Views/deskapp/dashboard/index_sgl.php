<?= $this->extend('layout/main') ?>
<?= $this->section('additional_css') ?>
<!-- DataTables CSS -->
<?php $assets = base_url('/public/assets'); ?>
<link rel="stylesheet" href="<?= $assets ?>/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= $assets ?>/src/plugins/datatables/css/responsive.bootstrap4.min.css">

<style>
.alert-badge {
	display: inline-block;
	padding: 5px 10px;
	border-radius: 4px;
	font-weight: 600;
	font-size: 12px;
}
.alert-critico { background-color: #dc3545; color: white; }
.alert-alto { background-color: #ff6b6b; color: white; }
.alert-medio { background-color: #ffc107; color: #212529; }
.filter-card {
	background: #f8f9fa;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 20px;
}
.stat-card {
	border-left: 4px solid #1b00ff;
	transition: transform 0.2s;
}
.stat-card:hover {
	transform: translateY(-5px);
	box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.table-sm td, .table-sm th {
	padding: 0.5rem;
}
.team-showcase {
	background: linear-gradient(135deg, #fff7ed 0%, #ffffff 55%, #eff6ff 100%);
	border: 1px solid rgba(15, 23, 42, 0.08);
	border-radius: 22px;
	padding: 28px;
	box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
	position: relative;
	overflow: hidden;
}
.team-showcase:before {
	content: "";
	position: absolute;
	inset: auto -60px -60px auto;
	width: 180px;
	height: 180px;
	background: radial-gradient(circle, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 72%);
	pointer-events: none;
}
.team-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
	gap: 18px;
	position: relative;
	z-index: 1;
}
.team-card {
	background: rgba(255, 255, 255, 0.9);
	border: 1px solid rgba(148, 163, 184, 0.22);
	border-radius: 18px;
	padding: 18px 14px;
	text-align: center;
	box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.team-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
}
.team-card.is-founder {
	background: linear-gradient(180deg, #fff 0%, #fff7ed 100%);
	border-color: rgba(249, 115, 22, 0.25);
}
.team-avatar {
	width: 86px;
	height: 86px;
	margin: 0 auto 14px;
	border-radius: 50%;
	overflow: hidden;
	border: 4px solid rgba(255, 255, 255, 0.95);
	box-shadow: 0 8px 18px rgba(15, 23, 42, 0.14);
	background: #e2e8f0;
}
.team-avatar img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}
.team-name {
	color: #0f172a;
	font-size: 15px;
	font-weight: 700;
	line-height: 1.35;
	margin-bottom: 6px;
}
.team-meta {
	color: #475569;
	font-size: 12px;
	letter-spacing: 0.04em;
	text-transform: uppercase;
}
.team-badge {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 6px 10px;
	border-radius: 999px;
	background: #fff1f2;
	color: #be123c;
	font-size: 11px;
	font-weight: 700;
	margin-top: 10px;
}
.team-badge.is-systems {
	background: #eff6ff;
	color: #1d4ed8;
}
@media (max-width: 767.98px) {
	.team-showcase {
		padding: 22px 18px;
	}
	.team-grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 14px;
	}
	.team-avatar {
		width: 74px;
		height: 74px;
	}
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="main-container">
	<div class="pd-ltr-20">
		<!-- Banner de Bienvenida Moderno -->
		<div class="row mb-30">
			<div class="col-md-12">
				<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3); position: relative; overflow: hidden;">
					<!-- Patrón de fondo decorativo -->
					<div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; z-index: 0;"></div>
					<div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.08); border-radius: 50%; z-index: 0;"></div>
					
					<div class="row align-items-center" style="position: relative; z-index: 1;">
						<!-- Columna de Bienvenida -->
						<div class="col-md-8 mb-20 mb-md-0">
							<div class="d-flex align-items-center mb-15">
								<div style="width: 70px; height: 70px; border-radius: 50%; overflow: hidden; margin-right: 20px; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.2); background: white;">
									<img src="/public/<?= esc($session->get('avatar')) ?>" alt="<?= esc($session->get('firstname')) ?>" style="width: 100%; height: 100%; object-fit: cover;">
								</div>
								<div>
									<p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 14px; font-weight: 500; letter-spacing: 1px;">
										<?php 
										$hora = date('H');
										if ($hora >= 5 && $hora < 12) echo 'BUENOS DÍAS';
										elseif ($hora >= 12 && $hora < 19) echo 'BUENAS TARDES';
										else echo 'BUENAS NOCHES';
										?>
									</p>
									<h2 class="text-white mb-0" style="font-size: 32px; font-weight: 700; line-height: 1.2;">
										<?= esc($session->get('firstname')) ?> <?= esc($session->get('lastname')) ?>
									</h2>
								</div>
							</div>
							<p class="text-white mb-0" style="font-size: 15px; line-height: 1.6; opacity: 0.95; max-width: 500px;">
								<i class="fa fa-check-circle" style="margin-right: 5px;"></i>
								Panel de control actualizado con métricas en tiempo real y gestión inteligente de trámites.
							</p>
							<div class="mt-15">
								<span class="badge" style="background: rgba(255,255,255,0.25); color: white; padding: 8px 15px; font-size: 12px; border-radius: 20px; backdrop-filter: blur(10px);">
									<i class="fa fa-calendar"></i> <?= strftime('%A, %d de %B de %Y') ?>
								</span>
								<span class="badge ml-10" style="background: rgba(255,255,255,0.25); color: white; padding: 8px 15px; font-size: 12px; border-radius: 20px; backdrop-filter: blur(10px);">
									<i class="fa fa-clock-o"></i> <span id="currentTime"></span>
								</span>
							</div>
						</div>
						
						<!-- Columna de Quick Stats -->
						<div class="col-md-4">
							<div class="row">
								<div class="col-6 mb-15">
									<div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
										<i class="fa fa-users" style="font-size: 24px; color: white; margin-bottom: 8px; opacity: 0.9;"></i>
										<h3 class="text-white mb-0" style="font-size: 28px; font-weight: 700;"><?= count($clientes_lista ?? []) ?></h3>
										<p class="text-white mb-0" style="font-size: 11px; opacity: 0.8; margin-top: 5px;">Clientes</p>
									</div>
								</div>
								<div class="col-6 mb-15">
									<div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
										<i class="fa fa-file-text" style="font-size: 24px; color: white; margin-bottom: 8px; opacity: 0.9;"></i>
										<h3 class="text-white mb-0" style="font-size: 28px; font-weight: 700;">
											<?php 
											$total_tramites = 0;
											if (is_array($graph)) {
												foreach ($graph as $item) {
													if (isset($item['total'])) {
														$total_tramites += $item['total'];
													}
												}
											}
											echo $total_tramites;
											?>
										</h3>
										<p class="text-white mb-0" style="font-size: 11px; opacity: 0.8; margin-top: 5px;">Trámites</p>
									</div>
								</div>
								<div class="col-6">
									<div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
										<i class="fa fa-exclamation-triangle" style="font-size: 24px; color: #ffc107; margin-bottom: 8px;"></i>
										<h3 class="text-white mb-0" style="font-size: 28px; font-weight: 700;"><?= count($tramites_retrasados ?? []) ?></h3>
										<p class="text-white mb-0" style="font-size: 11px; opacity: 0.8; margin-top: 5px;">Retrasados</p>
									</div>
								</div>
								<div class="col-6">
									<div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
										<i class="fa fa-shield" style="font-size: 24px; color: #28a745; margin-bottom: 8px;"></i>
										<h3 class="text-white mb-0" style="font-size: 28px; font-weight: 700;"><?= count($session->get('user_roles') ?? []) ?></h3>
										<p class="text-white mb-0" style="font-size: 11px; opacity: 0.8; margin-top: 5px;">Roles Activos</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<script>
		// Actualizar hora en tiempo real
		function updateTime() {
			const now = new Date();
			const hours = String(now.getHours()).padStart(2, '0');
			const minutes = String(now.getMinutes()).padStart(2, '0');
			const seconds = String(now.getSeconds()).padStart(2, '0');
			const timeElement = document.getElementById('currentTime');
			if (timeElement) {
				timeElement.textContent = `${hours}:${minutes}:${seconds}`;
			}
		}
		updateTime();
		setInterval(updateTime, 1000);
		</script>

		<div class="row mb-30">
			<div class="col-md-12">
				<div class="team-showcase">
					<div class="d-flex flex-wrap align-items-center justify-content-between mb-25" style="position: relative; z-index: 1; gap: 12px;">
						<div>
							<div style="font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; color: #ea580c; font-weight: 700; margin-bottom: 6px;">Equipo activo</div>
							<h4 style="margin: 0; color: #0f172a; font-weight: 700;">Personas que mueven la operación día a día</h4>
						</div>
						<div style="background: rgba(255,255,255,0.8); border: 1px solid rgba(148,163,184,0.22); border-radius: 999px; padding: 8px 14px; color: #334155; font-size: 12px; font-weight: 600;">
							<?= count($team_members ?? []) ?> perfiles visibles
						</div>
					</div>

					<?php if (!empty($team_members)): ?>
						<div class="team-grid">
							<?php foreach ($team_members as $member): ?>
								<div class="team-card <?= (int) ($member['id'] ?? 0) === 4 ? 'is-founder' : '' ?>">
									<div class="team-avatar">
										<img src="/public/<?= esc($member['avatar']) ?>" alt="<?= esc($member['display_name']) ?>">
									</div>
									<div class="team-name"><?= esc($member['display_name']) ?></div>
									<div class="team-meta">Equipo activo</div>
									<?php if ((int) ($member['id'] ?? 0) === 6): ?>
										<div class="team-badge"><i class="fa fa-star"></i> Fundador</div>
									<?php elseif ((int) ($member['id'] ?? 0) === 4): ?>
										<div class="team-badge is-systems"><i class="fa fa-cogs"></i> Sistemas</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<div style="position: relative; z-index: 1; color: #475569; font-size: 14px;">No hay perfiles activos con foto para mostrar.</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Cuadro informativo de reglas de semáforos -->
		<div class="row mb-30">
			<div class="col-md-12">
				<div class="card-box pd-20" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
					<div class="row align-items-center">
						<div class="col-md-12 text-center mb-20">
							<h4 class="text-white mb-0" style="font-weight: 600;">
								<i class="fas fa-traffic-light"></i> Reglas de Semáforos de Trámites
							</h4>
						</div>
					</div>
					<div class="row">
						<!-- Locales -->
						<div class="col-md-6">
							<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; backdrop-filter: blur(10px);">
								<h5 class="text-white mb-15" style="font-weight: 600;">
									<i class="fas fa-map-marker-alt"></i> Trámites Locales
								</h5>
								<div class="row">
									<div class="col-6 mb-10">
										<div style="background: rgba(255,255,255,0.95); border-radius: 6px; padding: 12px; border-left: 4px solid #28a745;">
											<div style="color: #28a745; font-weight: bold; font-size: 14px;">
												<i class="fas fa-circle"></i> VERDE
											</div>
											<div style="color: #666; font-size: 12px; margin-top: 5px;">
												Menos de 5 días
											</div>
										</div>
									</div>
									<div class="col-6 mb-10">
										<div style="background: rgba(255,255,255,0.95); border-radius: 6px; padding: 12px; border-left: 4px solid #ffc107;">
											<div style="color: #ffc107; font-weight: bold; font-size: 14px;">
												<i class="fas fa-circle"></i> AMARILLO
											</div>
											<div style="color: #666; font-size: 12px; margin-top: 5px;">
												Entre 5 y 7 días
											</div>
										</div>
									</div>
									<div class="col-6 mb-10">
										<div style="background: rgba(255,255,255,0.95); border-radius: 6px; padding: 12px; border-left: 4px solid #dc3545;">
											<div style="color: #dc3545; font-weight: bold; font-size: 14px;">
												<i class="fas fa-circle"></i> ROJO
											</div>
											<div style="color: #666; font-size: 12px; margin-top: 5px;">
												Entre 8 y 11 días
											</div>
										</div>
									</div>
									<div class="col-6 mb-10">
										<div style="background: rgba(255,255,255,0.95); border-radius: 6px; padding: 12px; border-left: 4px solid #6f42c1;">
											<div style="color: #6f42c1; font-weight: bold; font-size: 14px;">
												<i class="fas fa-circle"></i> VIOLETA
											</div>
											<div style="color: #666; font-size: 12px; margin-top: 5px;">
												Más de 12 días
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<!-- Foráneos -->
						<div class="col-md-6">
							<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; backdrop-filter: blur(10px);">
								<h5 class="text-white mb-15" style="font-weight: 600;">
									<i class="fas fa-globe"></i> Trámites Foráneos
								</h5>
								<div class="row">
									<div class="col-6 mb-10">
										<div style="background: rgba(255,255,255,0.95); border-radius: 6px; padding: 12px; border-left: 4px solid #28a745;">
											<div style="color: #28a745; font-weight: bold; font-size: 14px;">
												<i class="fas fa-circle"></i> VERDE
											</div>
											<div style="color: #666; font-size: 12px; margin-top: 5px;">
												Menos de 10 días
											</div>
										</div>
									</div>
									<div class="col-6 mb-10">
										<div style="background: rgba(255,255,255,0.95); border-radius: 6px; padding: 12px; border-left: 4px solid #ffc107;">
											<div style="color: #ffc107; font-weight: bold; font-size: 14px;">
												<i class="fas fa-circle"></i> AMARILLO
											</div>
											<div style="color: #666; font-size: 12px; margin-top: 5px;">
												Entre 10 y 12 días
											</div>
										</div>
									</div>
									<div class="col-6 mb-10">
										<div style="background: rgba(255,255,255,0.95); border-radius: 6px; padding: 12px; border-left: 4px solid #dc3545;">
											<div style="color: #dc3545; font-weight: bold; font-size: 14px;">
												<i class="fas fa-circle"></i> ROJO
											</div>
											<div style="color: #666; font-size: 12px; margin-top: 5px;">
												Entre 13 y 15 días
											</div>
										</div>
									</div>
									<div class="col-6 mb-10">
										<div style="background: rgba(255,255,255,0.95); border-radius: 6px; padding: 12px; border-left: 4px solid #6f42c1;">
											<div style="color: #6f42c1; font-weight: bold; font-size: 14px;">
												<i class="fas fa-circle"></i> VIOLETA
											</div>
											<div style="color: #666; font-size: 12px; margin-top: 5px;">
												Más de 16 días
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="text-center mt-15">
						<small style="opacity: 0.9;">
							<i class="fas fa-info-circle"></i> Los trámites se clasifican automáticamente según los días transcurridos desde su creación
						</small>
					</div>
				</div>
			</div>
		</div>

		<!-- Filtros -->
		<div class="filter-card">
			<!-- DEBUG INFO - Solo visible con toggle activo -->
			<?php
				helper(['permissions']);
				$canDebugAudit = has_permission('debug_perm_audit_tags', $session->get('user_permissions'), $session->get('user_roles'));
			?>
			<?php if (!empty($canDebugAudit)): ?>
				<div class="debug-info-container" style="display: none;">
					<div class="alert" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
						<h5 class="text-white mb-3"><i class="fas fa-bug"></i> <strong>Debug Info - Dashboard</strong></h5>
						<div class="row">
							<div class="col-md-6">
								<p class="mb-2"><strong><i class="fas fa-user"></i> User ID:</strong> <span class="badge badge-light" style="color: #667eea;"><?= $session->get('id') ?></span></p>
								<p class="mb-2"><strong><i class="fas fa-shield-alt"></i> Roles:</strong> 
									<?php foreach ($session->get('user_roles') ?? [] as $role): ?>
										<span class="badge badge-success" style="background: #28a745; margin-right: 5px;"><?= $role ?></span>
									<?php endforeach; ?>
								</p>
								<p class="mb-2"><strong><i class="fas fa-building"></i> Clientes asignados:</strong> 
									<?php if (is_null($clientes_asignados)): ?>
										<span class="badge badge-warning" style="background: #ffc107; color: #000; font-size: 13px;">
											<i class="fas fa-unlock"></i> TODOS LOS CLIENTES (Acceso completo - Super Admin/Admin)
										</span>
									<?php else: ?>
										<span class="badge badge-info" style="background: #17a2b8;"><?= json_encode($clientes_asignados) ?></span>
									<?php endif; ?>
								</p>
								<p class="mb-0"><strong><i class="fas fa-filter"></i> Filtros activos:</strong><br>
									Cliente: <span class="badge badge-light" style="color: #667eea;"><?= $cliente_id_filtro ?: 'Todos' ?></span>, 
									Tipo: <span class="badge badge-light" style="color: #667eea;"><?= $tipo_tramite_id_filtro ?: 'Todos' ?></span>
								</p>
							</div>
							<div class="col-md-6">
								<p class="mb-2"><strong><i class="fas fa-list"></i> Clientes lista:</strong> <span class="badge" style="background: #667eea; font-size: 14px;"><?= count($clientes_lista ?? []) ?></span> registros</p>
								<p class="mb-2"><strong><i class="fas fa-clipboard-list"></i> Tipos trámite:</strong> <span class="badge" style="background: #667eea; font-size: 14px;"><?= count($tipos_tramite ?? []) ?></span> registros</p>
								<p class="mb-2"><strong><i class="fas fa-chart-pie"></i> Resumen clientes:</strong> <span class="badge" style="background: #667eea; font-size: 14px;"><?= count($resumen_clientes ?? []) ?></span> registros</p>
								<p class="mb-2"><strong><i class="fas fa-chart-bar"></i> Resumen tipos:</strong> <span class="badge" style="background: #667eea; font-size: 14px;"><?= count($resumen_tipos ?? []) ?></span> registros</p>
								<p class="mb-0"><strong><i class="fas fa-exclamation-triangle"></i> Trámites retrasados:</strong> <span class="badge badge-danger" style="font-size: 14px;"><?= count($tramites_retrasados ?? []) ?></span> registros</p>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
			
			<form method="GET" action="<?= base_url('deskapp/dashboard'); ?>" class="row align-items-end">
				<div class="col-md-4 mb-3 mb-md-0">
					<label class="font-weight-bold">Cliente</label>
					<select name="cliente_id" class="form-control">
						<option value="">Todos los clientes</option>
						<?php if (!empty($clientes_lista)): ?>
							<?php foreach ($clientes_lista as $cliente): ?>
								<option value="<?= $cliente['id'] ?>" <?= ($cliente_id_filtro == $cliente['id']) ? 'selected' : '' ?>>
									<?= esc($cliente['nombre']) ?>
								</option>
							<?php endforeach; ?>
						<?php else: ?>
							<option disabled>No hay clientes disponibles</option>
						<?php endif; ?>
					</select>
				</div>
				<div class="col-md-4 mb-3 mb-md-0">
					<label class="font-weight-bold">Tipo de Servicio</label>
					<select name="tipo_tramite_id" class="form-control">
						<option value="">Todos los tipos</option>
						<?php foreach ($tipos_tramite as $tipo): ?>
							<option value="<?= $tipo['id'] ?>" <?= ($tipo_tramite_id_filtro == $tipo['id']) ? 'selected' : '' ?>>
								<?= esc($tipo['nombre']) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-4">
					<button type="submit" class="btn btn-primary btn-block">
						<i class="fa fa-filter"></i> Filtrar
					</button>
					<?php if ($cliente_id_filtro || $tipo_tramite_id_filtro): ?>
						<a href="<?= base_url('deskapp/dashboard'); ?>" class="btn btn-secondary btn-block mt-2">
							<i class="fa fa-times"></i> Limpiar
						</a>
					<?php endif; ?>
				</div>
			</form>
		</div>

		<!-- Semáforos Locales -->
		<div class="row">
			<div class="col-xl-12 mb-20">
				<h2 class="h4 text-blue"><i class="fas fa-map-marker-alt"></i> Trámites Locales</h2>
			</div>
			
			<div class="col-xl-3 col-md-6 mb-30">
				<div class="card-box height-100-p widget-style1">
					<div class="d-flex flex-wrap align-items-center">
						<div class="progress-data">
							<div id="chart"></div>
						</div>
						<div class="widget-data">
							<div class="h4 mb-0">Recientes</div>
							<div class="weight-600 font-14 text-success"><?= $graph['local']['verde'] ?? 0 ?></div>
							<small class="text-muted">< 5 días</small>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-30">
				<div class="card-box height-100-p widget-style1">
					<div class="d-flex flex-wrap align-items-center">
						<div class="progress-data">
							<div id="chart2"></div>
						</div>
						<div class="widget-data">
							<div class="h4 mb-0">Alerta</div>
							<div class="weight-600 font-14 text-warning"><?= $graph['local']['amarillo'] ?? 0 ?></div>
							<small class="text-muted">5-7 días</small>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-30">
				<div class="card-box height-100-p widget-style1">
					<div class="d-flex flex-wrap align-items-center">
						<div class="progress-data">
							<div id="chart3"></div>
						</div>
						<div class="widget-data">
							<div class="h4 mb-0">Retrasados</div>
							<div class="weight-600 font-14 text-danger"><?= $graph['local']['rojo'] ?? 0 ?></div>
							<small class="text-muted">8-11 días</small>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-30">
				<div class="card-box height-100-p widget-style1">
					<div class="d-flex flex-wrap align-items-center">
						<div class="progress-data">
							<div id="chart4"></div>
						</div>
						<div class="widget-data">
							<div class="h4 mb-0">Críticos</div>
							<div class="weight-600 font-14" style="color: #6f42c1;"><?= $graph['local']['violeta'] ?? 0 ?></div>
							<small class="text-muted">> 12 días</small>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Semáforos Foráneos -->
		<div class="row">
			<div class="col-xl-12 mb-20">
				<h2 class="h4 text-blue"><i class="fas fa-globe"></i> Trámites Foráneos</h2>
			</div>
			<div class="col-xl-3 col-md-6 mb-30">
				<div class="card-box height-100-p widget-style1">
					<div class="d-flex flex-wrap align-items-center">
						<div class="progress-data">
							<div id="chartf1"></div>
						</div>
						<div class="widget-data">
							<div class="h4 mb-0">Recientes</div>
							<div class="weight-600 font-14 text-success"><?= $graph['foraneo']['verde'] ?? 0 ?></div>
							<small class="text-muted">< 10 días</small>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-30">
				<div class="card-box height-100-p widget-style1">
					<div class="d-flex flex-wrap align-items-center">
						<div class="progress-data">
							<div id="chartf2"></div>
						</div>
						<div class="widget-data">
							<div class="h4 mb-0">Alerta</div>
							<div class="weight-600 font-14 text-warning"><?= $graph['foraneo']['amarillo'] ?? 0 ?></div>
							<small class="text-muted">10-12 días</small>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-30">
				<div class="card-box height-100-p widget-style1">
					<div class="d-flex flex-wrap align-items-center">
						<div class="progress-data">
							<div id="chartf3"></div>
						</div>
						<div class="widget-data">
							<div class="h4 mb-0">Retrasados</div>
							<div class="weight-600 font-14 text-danger"><?= $graph['foraneo']['rojo'] ?? 0 ?></div>
							<small class="text-muted">13-15 días</small>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-30">
				<div class="card-box height-100-p widget-style1">
					<div class="d-flex flex-wrap align-items-center">
						<div class="progress-data">
							<div id="chartf4"></div>
						</div>
						<div class="widget-data">
							<div class="h4 mb-0">Críticos</div>
							<div class="weight-600 font-14" style="color: #6f42c1;"><?= $graph['foraneo']['violeta'] ?? 0 ?></div>
							<small class="text-muted">> 16 días</small>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Resumen por Cliente y Tipo de Servicio -->
		<div class="row">
			<!-- Resumen por Cliente -->
			<div class="col-xl-6 mb-30">
				<div class="card-box pd-20">
					<h4 class="h4 mb-20 text-blue"><i class="fas fa-users"></i> Resumen por Cliente</h4>
					<div class="table-responsive">
						<table class="table table-sm table-hover">
							<thead>
								<tr>
									<th>Cliente</th>
									<th class="text-center">Total</th>
									<th class="text-center">En Proceso</th>
									<th class="text-center">Retrasados</th>
								</tr>
							</thead>
							<tbody>
								<?php if (!empty($resumen_clientes)): ?>
									<?php foreach ($resumen_clientes as $cliente): ?>
										<tr>
											<td><strong><?= esc($cliente['cliente_nombre']) ?></strong></td>
											<td class="text-center"><span class="badge badge-secondary"><?= $cliente['total'] ?></span></td>
											<td class="text-center"><span class="badge badge-primary"><?= $cliente['en_proceso'] ?></span></td>
											<td class="text-center">
												<?php if ($cliente['retrasados'] > 0): ?>
													<span class="badge badge-danger"><?= $cliente['retrasados'] ?></span>
												<?php else: ?>
													<span class="badge badge-success">0</span>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php else: ?>
									<tr><td colspan="4" class="text-center text-muted">No hay datos disponibles</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Resumen por Tipo de Servicio -->
			<div class="col-xl-6 mb-30">
				<div class="card-box pd-20">
					<h4 class="h4 mb-20 text-blue"><i class="fas fa-th-list"></i> Resumen por Tipo de Servicio</h4>
					<div class="table-responsive">
						<table class="table table-sm table-hover">
							<thead>
								<tr>
									<th>Tipo</th>
									<th class="text-center">Total</th>
									<th class="text-center">En Proceso</th>
									<th class="text-center">Retrasados</th>
								</tr>
							</thead>
							<tbody>
								<?php if (!empty($resumen_tipos)): ?>
									<?php foreach ($resumen_tipos as $tipo): ?>
										<tr>
											<td><strong><?= esc($tipo['tipo_nombre']) ?></strong></td>
											<td class="text-center"><span class="badge badge-secondary"><?= $tipo['total'] ?></span></td>
											<td class="text-center"><span class="badge badge-primary"><?= $tipo['en_proceso'] ?></span></td>
											<td class="text-center">
												<?php if ($tipo['retrasados'] > 0): ?>
													<span class="badge badge-danger"><?= $tipo['retrasados'] ?></span>
												<?php else: ?>
													<span class="badge badge-success">0</span>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php else: ?>
									<tr><td colspan="4" class="text-center text-muted">No hay datos disponibles</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- Trámites Retrasados (Top 20) -->
		<div class="row">
			<div class="col-xl-12 mb-30">
				<div class="card-box pd-20">
					<h4 class="h4 mb-20 text-danger"><i class="fas fa-exclamation-triangle"></i> Trámites con Mayor Retraso</h4>
					<div class="table-responsive">
						<table class="table table-sm table-striped table-hover">
							<thead class="bg-light">
								<tr>
									<th>Folio</th>
									<th>Cliente</th>
									<th>Tipo</th>
									<th>Status</th>
									<th class="text-center">Días</th>
									<th class="text-center">Ubicación</th>
									<th class="text-center">Nivel</th>
								</tr>
							</thead>
							<tbody>
								<?php if (!empty($tramites_retrasados)): ?>
									<?php foreach ($tramites_retrasados as $tramite): ?>
										<tr>
											<td><strong><?= esc($tramite['folio']) ?></strong></td>
											<td><?= esc($tramite['cliente']) ?></td>
											<td><?= esc($tramite['tipo']) ?></td>
											<td><?= esc($tramite['status']) ?></td>
											<td class="text-center"><span class="badge badge-dark"><?= $tramite['dias_transcurridos'] ?></span></td>
											<td class="text-center">
												<?php if ($tramite['es_local']): ?>
													<span class="badge badge-info">Local</span>
												<?php else: ?>
													<span class="badge badge-secondary">Foráneo</span>
												<?php endif; ?>
											</td>
											<td class="text-center">
												<?php if ($tramite['nivel_alerta'] == 'critico'): ?>
													<span class="alert-badge alert-critico">CRÍTICO</span>
												<?php elseif ($tramite['nivel_alerta'] == 'alto'): ?>
													<span class="alert-badge alert-alto">ALTO</span>
												<?php else: ?>
													<span class="alert-badge alert-medio">MEDIO</span>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php else: ?>
									<tr><td colspan="7" class="text-center text-success">
										<i class="fas fa-check-circle"></i> ¡Excelente! No hay trámites retrasados
									</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- Actividad Mensual -->
		<div class="row">
			<div class="col-xl-12 mb-30">
				<div class="card-box height-100-p pd-20">
					<h4 class="h4 mb-20 text-blue"><i class="fas fa-chart-line"></i> Actividad Mensual (Últimos 6 Meses)</h4>
					<div id="chart5"></div>
				</div>
			</div>
		</div>
		
		<!-- footer -->
		<?php echo view('deskapp/includes/_footer'); ?>
	</div>
</div>

<script>
	var graphData = <?= json_encode($graph ?? []); ?>;
	var perMonth = <?= json_encode($perMonth ?? []); ?>;
	
	// Inicializar estado de debug en el dashboard
	document.addEventListener('DOMContentLoaded', function() {
		// Verificar si existe el modo debug en localStorage
		const debugMode = localStorage.getItem('debugMode') === 'true';
		
		// Mostrar/ocultar divs de debug según el estado
		const debugDivs = document.querySelectorAll('.debug-info-container');
		debugDivs.forEach(div => {
			div.style.display = debugMode ? 'block' : 'none';
		});
		
		console.log('Dashboard loaded - Debug mode:', debugMode ? 'ON' : 'OFF');
	});
</script>

<?= $this->endSection() ?>

<script src="<?= $assets ?>/src/plugins/apexcharts/apexcharts.min.js"></script>
<script src="<?= $assets ?>/vendors/scripts/dashboard.js"></script>