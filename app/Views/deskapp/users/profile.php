<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Mi Perfil - Admin SGL</title>
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url(); ?>/public/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url(); ?>/public/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url(); ?>/public/assets/vendors/images/favicon-16x16.png">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/style.css">
	
	<style>
	.profile-photo-container {
		position: relative;
		display: inline-block;
		margin: 20px 0;
	}
	.avatar-photo {
		width: 150px;
		height: 150px;
		border-radius: 50%;
		object-fit: cover;
		border: 4px solid #fff;
		box-shadow: 0 2px 10px rgba(0,0,0,0.1);
	}
	.edit-avatar-btn {
		position: absolute;
		bottom: 10px;
		right: 10px;
		background: #1b00ff;
		color: white;
		border-radius: 50%;
		width: 40px;
		height: 40px;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		box-shadow: 0 2px 8px rgba(0,0,0,0.2);
		transition: all 0.3s;
		border: none;
	}
	.edit-avatar-btn:hover {
		background: #1400cc;
		transform: scale(1.1);
	}
	.delete-avatar-btn {
		position: absolute;
		top: 10px;
		right: 10px;
		background: #dc3545;
		color: white;
		border-radius: 50%;
		width: 35px;
		height: 35px;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		box-shadow: 0 2px 8px rgba(0,0,0,0.2);
		transition: all 0.3s;
		font-size: 14px;
	}
	.delete-avatar-btn:hover {
		background: #c82333;
		transform: scale(1.1);
	}
	.image-preview-container {
		max-width: 100%;
		margin: 15px 0;
		display: none;
		text-align: center;
	}
	.image-preview {
		max-width: 100%;
		max-height: 300px;
		border-radius: 8px;
		border: 2px solid #e9ecef;
	}
	.password-strength {
		height: 5px;
		margin-top: 5px;
		border-radius: 3px;
		transition: all 0.3s;
	}
	.password-strength.weak { background: #dc3545; width: 33%; }
	.password-strength.medium { background: #ffc107; width: 66%; }
	.password-strength.strong { background: #28a745; width: 100%; }
	.debug-role-switcher {
		margin-top: 24px;
		padding: 18px;
		background: #fff8e1;
		border: 1px solid #f1d58a;
		border-radius: 12px;
		box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
	}
	.debug-role-switcher h5 {
		color: #7a4b00;
	}
	.debug-role-switcher p,
	.debug-role-switcher small,
	.debug-role-switcher label {
		color: #5f4308;
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
						<div class="col-md-12">
							<div class="title">
								<h4>Mi Perfil</h4>
							</div>
							<nav aria-label="breadcrumb" role="navigation">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="<?= base_url('deskapp/dashboard') ?>">Home</a></li>
									<li class="breadcrumb-item active">Perfil</li>
								</ol>
							</nav>
						</div>
					</div>
				</div>

				<!-- Mensajes -->
				<?php if (session()->getFlashdata('success')): ?>
					<div class="alert alert-success alert-dismissible fade show">
						<strong><?= session()->getFlashdata('success') ?></strong>
						<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
					</div>
				<?php endif; ?>

				<?php if (session()->getFlashdata('error')): ?>
					<div class="alert alert-danger alert-dismissible fade show">
						<strong><?= session()->getFlashdata('error') ?></strong>
						<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
					</div>
				<?php endif; ?>

				<?php if (session()->getFlashdata('errors')): ?>
					<div class="alert alert-danger alert-dismissible fade show">
						<strong>Errores:</strong>
						<ul class="mb-0 mt-2">
							<?php foreach (session()->getFlashdata('errors') as $error): ?>
								<li><?= esc($error) ?></li>
							<?php endforeach; ?>
						</ul>
						<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
					</div>
				<?php endif; ?>

				<div class="row">
					<!-- Sidebar -->
					<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 mb-30">
						<div class="pd-20 card-box height-100-p">
							<div class="profile-photo text-center">
								<div class="profile-photo-container">
									<img src="/public/<?= $user['avatar'] ?? 'uploads/avatars/default.png' ?>" 
										 alt="Avatar" class="avatar-photo" id="currentAvatar">
									<label for="avatarUpload" class="edit-avatar-btn" title="Cambiar foto">
										<i class="fa fa-camera"></i>
									</label>
									<?php if (!empty($user['avatar']) && $user['avatar'] !== 'uploads/avatars/default.png'): ?>
										<div class="delete-avatar-btn" id="deleteAvatarBtn" title="Eliminar foto">
											<i class="fa fa-trash"></i>
										</div>
									<?php endif; ?>
								</div>
							</div>
							
							<h5 class="text-center h5 mb-0 mt-3">
								<?= esc($user['firstname']) ?> 
								<?= esc($user['midname']) ?> 
								<?= esc($user['lastname']) ?>
							</h5>
							<p class="text-center text-muted font-14">@<?= esc($user['username']) ?></p>
							
							<div class="profile-info">
								<h5 class="mb-20 h5 text-blue">Información de Contacto</h5>
								<ul>
									<li><span>Email:</span> <?= esc($user['email']) ?></li>
									<li><span>Teléfono:</span> <?= esc($user['phone'] ?? 'No especificado') ?></li>
									<li><span>País:</span> México</li>
								</ul>
							</div>

							<?php if (!empty($debugRoleSwitcherEnabled)): ?>
								<div class="debug-role-switcher">
									<h5 class="h5 mb-2"><i class="fa fa-bug"></i> Rol Debug</h5>
									<p class="mb-2">Rol efectivo actual: <strong><?= esc($debugSelectedRoleName ?: 'Admin') ?></strong> + <strong>Debug</strong></p>
									<form action="<?= base_url('users/switch_debug_role') ?>" method="post" class="mb-2">
										<div class="form-group mb-2">
											<label for="debugRoleSelect" class="font-weight-bold">Cambiar rol para probar accesos</label>
											<select name="role_id" id="debugRoleSelect" class="form-control">
												<?php foreach (($debugRoleOptions ?? []) as $roleOption): ?>
													<option value="<?= (int) ($roleOption['id'] ?? 0) ?>" <?= (int) ($debugSelectedRoleId ?? 0) === (int) ($roleOption['id'] ?? 0) ? 'selected' : '' ?>>
														<?= esc($roleOption['role_name'] ?? '') ?>
													</option>
												<?php endforeach; ?>
											</select>
										</div>
										<button type="submit" class="btn btn-sm btn-dark">
											<i class="fa fa-exchange"></i> Aplicar rol
										</button>
									</form>
									<small class="d-block">El marcador Debug permanece activo para que puedas seguir cambiando de rol incluso después de recargar.</small>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<!-- Formularios -->
					<div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 mb-30">
						<div class="card-box height-100-p overflow-hidden">
							<div class="profile-tab height-100-p">
								<div class="tab height-100-p">
									<ul class="nav nav-tabs customtab" role="tablist">
										<li class="nav-item">
											<a class="nav-link active" data-toggle="tab" href="#info">
												<i class="icon-copy fa fa-user"></i> Información Personal
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-toggle="tab" href="#password">
												<i class="icon-copy fa fa-lock"></i> Cambio de Contraseña
											</a>
										</li>
									</ul>
									
									<div class="tab-content">
										<!-- Tab Info Personal -->
										<div class="tab-pane fade show active" id="info" role="tabpanel">
											<div class="pd-20">
												<form action="<?= base_url('users/update_profile') ?>" method="post" 
													  enctype="multipart/form-data" id="profileForm">
													<h4 class="text-blue h5 mb-20">Actualizar Información</h4>
													
													<input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display: none;">
													
													<div class="image-preview-container" id="imagePreviewContainer">
														<img id="imagePreview" class="image-preview" alt="Vista previa">
														<br>
														<button type="button" class="btn btn-sm btn-danger mt-2" id="cancelImageBtn">
															<i class="fa fa-times"></i> Cancelar
														</button>
													</div>
													
													<div class="row">
														<div class="col-md-12">
															<div class="form-group">
																<label>Username <small class="text-muted">(No editable)</small></label>
																<input class="form-control" type="text" value="<?= esc($user['username']) ?>" readonly>
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-md-6">
															<div class="form-group">
																<label>Nombre <span class="text-danger">*</span></label>
																<input class="form-control" type="text" name="firstname" 
																	   value="<?= esc($user['firstname']) ?>" required>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group">
																<label>Apellido Paterno</label>
																<input class="form-control" type="text" name="midname" 
																	   value="<?= esc($user['midname'] ?? '') ?>">
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-md-12">
															<div class="form-group">
																<label>Apellido Materno <span class="text-danger">*</span></label>
																<input class="form-control" type="text" name="lastname" 
																	   value="<?= esc($user['lastname']) ?>" required>
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-md-6">
															<div class="form-group">
																<label>Email <span class="text-danger">*</span></label>
																<input class="form-control" type="email" name="email" 
																	   value="<?= esc($user['email']) ?>" required>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group">
																<label>Teléfono</label>
																<input class="form-control" type="text" name="phone" 
																	   value="<?= esc($user['phone'] ?? '') ?>" placeholder="5512345678">
															</div>
														</div>
													</div>
													
													<div class="form-group">
														<button type="submit" class="btn btn-primary">
															<i class="fa fa-save"></i> Guardar Cambios
														</button>
													</div>
												</form>
											</div>
										</div>

										<!-- Tab Contraseña -->
										<div class="tab-pane fade" id="password" role="tabpanel">
											<div class="pd-20">
												<form action="<?= base_url('users/update_password') ?>" method="post" id="passwordForm">
													<h4 class="text-blue h5 mb-20">Cambiar Contraseña</h4>
													
													<div class="form-group">
														<label>Contraseña Actual <span class="text-danger">*</span></label>
														<input class="form-control" type="password" name="current_password" required>
													</div>
													
													<div class="form-group">
														<label>Nueva Contraseña <span class="text-danger">*</span></label>
														<input class="form-control" type="password" name="new_password" 
															   id="newPassword" minlength="8" required>
														<div class="password-strength" id="passwordStrength"></div>
														<small class="form-text text-muted">
															Mínimo 8 caracteres (mayúsculas, minúsculas y números)
														</small>
													</div>
													
													<div class="form-group">
														<label>Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
														<input class="form-control" type="password" name="confirm_password" 
															   id="confirmPassword" required>
														<small class="form-text" id="passwordMatch"></small>
													</div>
													
													<div class="form-group">
														<button type="submit" class="btn btn-primary" id="changePasswordBtn">
															<i class="fa fa-lock"></i> Cambiar Contraseña
														</button>
													</div>
												</form>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php echo view('deskapp/includes/_footer'); ?>
		</div>
	</div>

	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/script.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/process.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/layout-settings.js"></script>
	
	<script>
	$(document).ready(function() {
		// Preview de imagen
		$('#avatarUpload').on('change', function(e) {
			const file = e.target.files[0];
			if (file) {
				if (file.size > 2048000) {
					alert('La imagen no debe superar 2MB');
					this.value = '';
					return;
				}
				if (!file.type.match('image.*')) {
					alert('Por favor selecciona una imagen válida');
					this.value = '';
					return;
				}
				const reader = new FileReader();
				reader.onload = function(event) {
					$('#imagePreview').attr('src', event.target.result);
					$('#imagePreviewContainer').fadeIn();
					$('#currentAvatar').attr('src', event.target.result);
				};
				reader.readAsDataURL(file);
			}
		});
		
		// Cancelar imagen
		$('#cancelImageBtn').on('click', function() {
			$('#avatarUpload').val('');
			$('#imagePreviewContainer').fadeOut();
			$('#currentAvatar').attr('src', '/public/<?= $user["avatar"] ?? "uploads/avatars/default.png" ?>');
		});
		
		// Eliminar avatar
		$('#deleteAvatarBtn').on('click', function() {
			if (confirm('¿Eliminar foto de perfil?')) {
				$.post('<?= base_url("users/delete_avatar") ?>', function(response) {
					if (response.success) {
						$('#currentAvatar').attr('src', '/public/' + response.avatar);
						$('#deleteAvatarBtn').fadeOut();
						alert(response.message);
						location.reload();
					}
				}, 'json').fail(function() {
					alert('Error al eliminar el avatar');
				});
			}
		});
		
		// Fortaleza de contraseña
		$('#newPassword').on('keyup', function() {
			const password = $(this).val();
			const strength = $('#passwordStrength');
			let level = 0;
			if (password.length >= 8) level++;
			if (/[a-z]/.test(password) && /[A-Z]/.test(password)) level++;
			if (/\d/.test(password)) level++;
			strength.removeClass('weak medium strong');
			if (level === 1) strength.addClass('weak');
			else if (level === 2) strength.addClass('medium');
			else if (level === 3) strength.addClass('strong');
		});
		
		// Verificar coincidencia
		$('#confirmPassword').on('keyup', function() {
			const newPass = $('#newPassword').val();
			const confirmPass = $(this).val();
			const matchText = $('#passwordMatch');
			if (confirmPass.length > 0) {
				if (newPass === confirmPass) {
					matchText.text('✓ Coinciden').css('color', '#28a745');
					$('#changePasswordBtn').prop('disabled', false);
				} else {
					matchText.text('✗ No coinciden').css('color', '#dc3545');
					$('#changePasswordBtn').prop('disabled', true);
				}
			} else {
				matchText.text('');
				$('#changePasswordBtn').prop('disabled', false);
			}
		});
		
		// Auto-ocultar alertas
		setTimeout(function() {
			$('.alert-dismissible').fadeOut('slow');
		}, 5000);
	});
	</script>
</body>
</html>
