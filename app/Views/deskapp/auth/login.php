<!DOCTYPE html>
<html>
<head>
	<!-- Basic Page Info -->
	<meta charset="utf-8">
	<title>SGL - Sistema de Gestión de Trámites | Iniciar Sesión</title>

	<!-- Site favicon -->
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url(); ?>/public/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url(); ?>/public/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url(); ?>/public/assets/vendors/images/favicon-16x16.png">

	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- Google Font -->
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/style.css">

	<style>
		:root {
			--primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			--primary-color: #667eea;
			--primary-dark: #5568d3;
			--secondary-color: #764ba2;
		}

		body.login-page {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			position: relative;
			overflow-x: hidden;
		}

		/* Animated background */
		body.login-page::before {
			content: '';
			position: absolute;
			width: 200%;
			height: 200%;
			top: -50%;
			left: -50%;
			background: 
				radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px),
				radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
			background-size: 50px 50px;
			background-position: 0 0, 25px 25px;
			animation: backgroundMove 20s linear infinite;
			z-index: 0;
		}

		@keyframes backgroundMove {
			0% { transform: translate(0, 0); }
			100% { transform: translate(50px, 50px); }
		}

		.login-header {
			background: rgba(255, 255, 255, 0.95);
			backdrop-filter: blur(10px);
			padding: 20px 0;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
			position: relative;
			z-index: 10;
		}

		.brand-logo {
			padding: 10px 0;
		}

		.brand-logo a {
			display: inline-block;
			transition: transform 0.3s ease;
		}

		.brand-logo a:hover {
			transform: scale(1.05);
		}

		.brand-logo img {
			max-height: 80px;
			width: auto;
			object-fit: contain;
			filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.1));
			transition: all 0.3s ease;
		}

		.brand-logo a:hover img {
			filter: drop-shadow(0 4px 12px rgba(102, 126, 234, 0.3));
		}

		.login-wrap {
			position: relative;
			z-index: 1;
			min-height: calc(100vh - 100px);
		}

		.login-box {
			background: rgba(255, 255, 255, 0.98) !important;
			backdrop-filter: blur(20px);
			border-radius: 20px !important;
			padding: 40px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
			border: 1px solid rgba(255, 255, 255, 0.3);
			animation: slideUp 0.6s ease-out;
		}

		@keyframes slideUp {
			from {
				opacity: 0;
				transform: translateY(30px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.login-title {
			margin-bottom: 30px;
		}

		.login-title h2 {
			background: var(--primary-gradient);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			background-clip: text;
			font-weight: 700;
			font-size: 32px;
			margin-bottom: 10px;
		}

		.login-title p {
			color: #64748b;
			font-size: 15px;
			margin: 0;
		}

		.input-group.custom {
			margin-bottom: 20px;
			position: relative;
		}

		.input-group.custom .form-control {
			border: 2px solid #e2e8f0;
			border-radius: 12px;
			padding: 14px 20px 14px 50px;
			font-size: 15px;
			transition: all 0.3s ease;
			background: #f8fafc;
		}

		.input-group.custom .form-control:focus {
			border-color: var(--primary-color);
			background: white;
			box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
		}

		.input-group.custom .input-group-append {
			position: absolute;
			left: 0;
			top: 0;
			height: 100%;
			z-index: 10;
			pointer-events: none;
		}

		.input-group.custom .input-group-text {
			background: transparent;
			border: none;
			padding: 0 0 0 18px;
			display: flex;
			align-items: center;
			color: var(--primary-color);
			font-size: 20px;
		}

		.btn-primary {
			background: var(--primary-gradient) !important;
			border: none !important;
			border-radius: 12px;
			padding: 14px 20px;
			font-size: 16px;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			transition: all 0.3s ease;
			box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
		}

		.btn-primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
		}

		.btn-primary:active {
			transform: translateY(0);
		}

		.alert {
			border-radius: 12px;
			border: none;
			padding: 15px 20px;
			margin-bottom: 20px;
			animation: shake 0.5s ease;
		}

		@keyframes shake {
			0%, 100% { transform: translateX(0); }
			25% { transform: translateX(-10px); }
			75% { transform: translateX(10px); }
		}

		.alert-danger {
			background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
			color: white;
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.alert-danger::before {
			content: '\f071';
			font-family: 'Font Awesome 6 Free';
			font-weight: 900;
			font-size: 20px;
		}

		.login-image-container {
			padding: 40px;
			animation: float 6s ease-in-out infinite;
		}

		@keyframes float {
			0%, 100% { transform: translateY(0); }
			50% { transform: translateY(-20px); }
		}

		.login-image-container img {
			filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.2));
		}

		/* Responsive */
		@media (max-width: 768px) {
			.login-box {
				padding: 30px 20px;
			}

			.login-title h2 {
				font-size: 26px;
			}

			.login-image-container {
				display: none;
			}
		}

		/* Loading spinner */
		.btn-primary.loading {
			position: relative;
			pointer-events: none;
			color: transparent !important;
		}

		.btn-primary.loading::after {
			content: '';
			position: absolute;
			width: 20px;
			height: 20px;
			top: 50%;
			left: 50%;
			margin-left: -10px;
			margin-top: -10px;
			border: 3px solid rgba(255, 255, 255, 0.3);
			border-top-color: white;
			border-radius: 50%;
			animation: spin 0.8s linear infinite;
		}

		@keyframes spin {
			to { transform: rotate(360deg); }
		}

		/* Footer text */
		.login-footer {
			text-align: center;
			margin-top: 30px;
			color: white;
			font-size: 14px;
		}

		.login-footer a {
			color: white;
			text-decoration: underline;
		}
	</style>
</head>
<body class="login-page">
	<div class="login-header box-shadow">
		<div class="container-fluid d-flex justify-content-between align-items-center">
			<div class="brand-logo">
				<a href="login">
					<img width="85%" src="<?php echo base_url(); ?>/public/assets/vendors/images/logoes_sgt.jpg" alt="">
				</a>
			</div>
			<div class="login-menu">
				<!-- <ul>
					<li><a href="<?php echo base_url('deskapp/register'); ?>">Register</a></li>
				</ul> -->
			</div>
		</div>
	</div>
	<div class="login-wrap d-flex align-items-center flex-wrap justify-content-center">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-md-6 col-lg-7 login-image-container">
					<img src="<?php echo base_url(); ?>/public/assets/vendors/images/login-page-img.png" alt="Login Illustration">
				</div>
				<div class="col-md-6 col-lg-5">
					<?php if(session()->getFlashdata('msg')):?>
						<div class="alert alert-danger">
							<?= session()->getFlashdata('msg') ?>
						</div>
					<?php endif;?>
					
					<div class="login-box bg-white box-shadow border-radius-10">
						<div class="login-title">
							<h2 class="text-center">Bienvenido</h2>
							<p class="text-center">Ingresa tus credenciales para continuar</p>
						</div>
						<form method="post" action="<?php echo base_url() ?>/deskapp/login/auth" id="loginForm">
							<div class="input-group custom">
								<input name="username" type="text" class="form-control form-control-lg" placeholder="Usuario" required autocomplete="username">
								<div class="input-group-append custom">
									<span class="input-group-text"><i class="fas fa-user"></i></span>
								</div>
							</div>
							<div class="input-group custom">
								<input name="password" type="password" id="passwordField" class="form-control form-control-lg" placeholder="Contraseña" required autocomplete="current-password">
								<div class="input-group-append custom">
									<span class="input-group-text"><i class="fas fa-lock"></i></span>
								</div>
								<button type="button" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #667eea; z-index: 11; cursor: pointer;">
									<i class="fas fa-eye" id="eyeIcon"></i>
								</button>
							</div>
							<div class="row">
								<div class="col-sm-12">
									<div class="input-group mb-0">
										<button class="btn btn-primary btn-lg btn-block" type="submit" id="submitBtn">
											<i class="fas fa-sign-in-alt mr-2"></i> Iniciar Sesión
										</button>
									</div>
								</div>
							</div>
						</form>
					</div>
					
					<div class="login-footer">
						<p>&copy; <?= date('Y') ?> SGL - Sistema de Gestión de Trámites</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- js -->
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/script.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/process.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/layout-settings.js"></script>
	
	<script>
		// Toggle password visibility
		document.getElementById('togglePassword').addEventListener('click', function() {
			const passwordField = document.getElementById('passwordField');
			const eyeIcon = document.getElementById('eyeIcon');
			
			if (passwordField.type === 'password') {
				passwordField.type = 'text';
				eyeIcon.classList.remove('fa-eye');
				eyeIcon.classList.add('fa-eye-slash');
			} else {
				passwordField.type = 'password';
				eyeIcon.classList.remove('fa-eye-slash');
				eyeIcon.classList.add('fa-eye');
			}
		});

		// Form submit with loading state
		document.getElementById('loginForm').addEventListener('submit', function() {
			const submitBtn = document.getElementById('submitBtn');
			submitBtn.classList.add('loading');
			submitBtn.disabled = true;
		});

		// Add focus animation to inputs
		document.querySelectorAll('.form-control').forEach(input => {
			input.addEventListener('focus', function() {
				this.parentElement.style.transform = 'scale(1.02)';
				this.parentElement.style.transition = 'transform 0.2s ease';
			});
			
			input.addEventListener('blur', function() {
				this.parentElement.style.transform = 'scale(1)';
			});
		});
	</script>
</body>
</html>