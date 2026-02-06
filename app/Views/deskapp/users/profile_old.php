<!DOCTYPE html>
<html>
<head>
	<!-- Basic Page Info -->
	<meta charset="utf-8">
	<title>DeskApp - Bootstrap Admin Dashboard HTML Template</title>

	<!-- Site favicon -->
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url(); ?>/public/assets/vendors/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url(); ?>/public/assets/vendors/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url(); ?>/public/assets/vendors/images/favicon-16x16.png">

	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- Google Font -->
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/core.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/icon-font.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/src/plugins/cropperjs/dist/cropper.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/style.css">

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-119386393-1"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'UA-119386393-1');
	</script>
</head>
<body>
	<!-- echo header,rightsidebar,leftsidebar and loader -->
	<?php 
		echo view('deskapp/includes/_header');
		echo view('deskapp/includes/_sidebar');

	?>

	<div class="main-container">
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">
				<div class="page-header">
					<div class="row">
						<div class="col-md-12 col-sm-12">
							<div class="title">
								<h4>Profile</h4>
							</div>
							<nav aria-label="breadcrumb" role="navigation">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="index.html">Home</a></li>
									<li class="breadcrumb-item active" aria-current="page">Profile</li>
								</ol>
							</nav>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 mb-30">
						<div class="pd-20 card-box height-100-p">
							<div class="profile-photo">
								<a href="modal" data-toggle="modal" data-target="#modal" class="edit-avatar"><i class="fa fa-pencil"></i></a>
								<img src="/public/<?= $user['avatar'] ?>" alt="" class="avatar-photo">
								<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-body pd-5">
												<div class="img-container">
													<img id="image" src="/public/<?= $user['avatar'] ?>" alt="Picture">
												</div>
											</div>
											<div class="modal-footer">
												<input type="submit" value="Update" class="btn btn-primary">
												<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
											</div>
										</div>
									</div>
								</div>
							</div>
							<h5 class="text-center h5 mb-0"><?= isset($user['firstname']) ? esc($user['firstname']) : '' ?> <?= isset($user['midname']) ? esc($user['midname']) : '' ?> <?= isset($user['lastname']) ? esc($user['lastname']) : '' ?></h5>
							<!-- <p class="text-center text-muted font-14">Lorem ipsum dolor sit amet</p> -->
							<div class="profile-info">
								<h5 class="mb-20 h5 text-blue">Información de contacto</h5>
								<ul>
									<li>
										<span>Correo electrónico:</span>
										<?= isset($user['email']) ? esc($user['email']) : '' ?>
									</li>
									<li>
										<span>Número:</span>
										<?= isset($user['phone']) ? esc($user['phone']) : '' ?>
									</li>
									<li>
										<span>País:</span>
										México
									</li>
									<!-- <li>
										<span>Address:</span>
										1807 Holden Street<br>
										San Diego, CA 92115
									</li> -->
								</ul>
							</div>
							<!-- <div class="profile-social">
								<h5 class="mb-20 h5 text-blue">Social Links</h5>
								<ul class="clearfix">
									<li><a href="#" class="btn" data-bgcolor="#3b5998" data-color="#ffffff"><i class="fa fa-facebook"></i></a></li>
									<li><a href="#" class="btn" data-bgcolor="#1da1f2" data-color="#ffffff"><i class="fa fa-twitter"></i></a></li>
									<li><a href="#" class="btn" data-bgcolor="#007bb5" data-color="#ffffff"><i class="fa fa-linkedin"></i></a></li>
									<li><a href="#" class="btn" data-bgcolor="#f46f30" data-color="#ffffff"><i class="fa fa-instagram"></i></a></li>
									<li><a href="#" class="btn" data-bgcolor="#c32361" data-color="#ffffff"><i class="fa fa-dribbble"></i></a></li>
									<li><a href="#" class="btn" data-bgcolor="#3d464d" data-color="#ffffff"><i class="fa fa-dropbox"></i></a></li>
									<li><a href="#" class="btn" data-bgcolor="#db4437" data-color="#ffffff"><i class="fa fa-google-plus"></i></a></li>
									<li><a href="#" class="btn" data-bgcolor="#bd081c" data-color="#ffffff"><i class="fa fa-pinterest-p"></i></a></li>
									<li><a href="#" class="btn" data-bgcolor="#00aff0" data-color="#ffffff"><i class="fa fa-skype"></i></a></li>
									<li><a href="#" class="btn" data-bgcolor="#00b489" data-color="#ffffff"><i class="fa fa-vine"></i></a></li>
								</ul>
							</div> -->
							<!-- <div class="profile-skills">
								<h5 class="mb-20 h5 text-blue">Key Skills</h5>
								<h6 class="mb-5 font-14">HTML</h6>
								<div class="progress mb-20" style="height: 6px;">
									<div class="progress-bar" role="progressbar" style="width: 90%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
								<h6 class="mb-5 font-14">Css</h6>
								<div class="progress mb-20" style="height: 6px;">
									<div class="progress-bar" role="progressbar" style="width: 70%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
								<h6 class="mb-5 font-14">jQuery</h6>
								<div class="progress mb-20" style="height: 6px;">
									<div class="progress-bar" role="progressbar" style="width: 60%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
								<h6 class="mb-5 font-14">Bootstrap</h6>
								<div class="progress mb-20" style="height: 6px;">
									<div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
							</div> -->
						</div>
					</div>
					<div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 mb-30">
						<div class="card-box height-100-p overflow-hidden">
						<div class="profile-tab height-100-p">
    <div class="tab height-100-p">
        <ul class="nav nav-tabs customtab" role="tablist">
            <!-- Pestaña de Información Personal -->
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#info" role="tab">Información Personal</a>
            </li>
            <!-- Pestaña de Cambio de Contraseña -->
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#password" role="tab">Cambio de Contraseña</a>
            </li>
        </ul>
        <div class="tab-content">
            <!-- Tab de Información Personal -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <div class="profile-setting">
                    <form action="<?= base_url('users/update_profile') ?>" method="post" enctype="multipart/form-data">
                        <ul class="profile-edit-list row">
                            <li class="weight-500 col-md-12">
                                <h4 class="text-blue h5 mb-20">Información Personal</h4>
                                
                                <!-- Username -->
                                <div class="form-group">
                                    <label>Username</label>
                                    <input class="form-control form-control-lg" type="text" name="username" 
                                        value="<?= isset($user['username']) ? esc($user['username']) : '' ?>" required readonly>
                                </div>
                                
                                <!-- First Name -->
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input class="form-control form-control-lg" type="text" name="firstname" 
                                        value="<?= isset($user['firstname']) ? esc($user['firstname']) : '' ?>" required>
                                </div>
                                
                                <!-- Middle Name -->
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input class="form-control form-control-lg" type="text" name="midname" 
                                        value="<?= isset($user['midname']) ? esc($user['midname']) : '' ?>">
                                </div>
                                
                                <!-- Last Name -->
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input class="form-control form-control-lg" type="text" name="lastname" 
                                        value="<?= isset($user['lastname']) ? esc($user['lastname']) : '' ?>" required>
                                </div>
                                
                                <!-- Email -->
                                <div class="form-group">
                                    <label>Email</label>
                                    <input class="form-control form-control-lg" type="email" name="email" 
                                        value="<?= isset($user['email']) ? esc($user['email']) : '' ?>" required>
                                </div>
                                
                                <!-- Phone -->
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input class="form-control form-control-lg" type="text" name="phone" 
                                        value="<?= isset($user['phone']) ? esc($user['phone']) : '' ?>"
                                        placeholder="Format: 1234567890">
                                </div>
                                
                                <!-- Avatar Upload -->
                                <div class="form-group">
                                    <label>Profile Picture</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="avatar" name="avatar">
                                        <label class="custom-file-label" for="avatar">Seleccionar archivo</label>
                                    </div>
                                    <?php if(isset($user['avatar']) && !empty($user['avatar'])): ?>
                                        <small class="form-text text-muted">
                                            Current: <a href="/public/<?= $user['avatar'] ?>" target="_blank">Ver imagen</a>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group mb-4">
                                    <button type="submit" class="btn btn-primary">Actualizar Información</button>
                                </div>
                            </li>
                        </ul>
                    </form>
                </div>
            </div>

            <!-- Tab de Cambio de Contraseña -->
            <div class="tab-pane fade" id="password" role="tabpanel">
                <div class="profile-setting">
                    <form action="<?= base_url('users/update_password') ?>" method="post">
                        <ul class="profile-edit-list row">
                            <li class="weight-500 col-md-12">
                                <h4 class="text-blue h5 mb-20">Cambio de Contraseña</h4>
                                
                                <!-- Current Password -->
                                <div class="form-group">
                                    <label>Contraseña actual</label>
                                    <input class="form-control form-control-lg" type="password" name="current_password" required>
                                </div>
                                
                                <!-- New Password -->
                                <div class="form-group">
                                    <label>Nueva contraseña</label>
                                    <input class="form-control form-control-lg" type="password" name="new_password" 
                                        placeholder="Mínimo 8 caracteres" minlength="8" required>
                                </div>
                                
                                <!-- Confirm New Password -->
                                <div class="form-group">
                                    <label>Confirmar nueva contraseña</label>
                                    <input class="form-control form-control-lg" type="password" name="confirm_password" required>
                                </div>
                                
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                                </div>
                            </li>
                        </ul>
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
			<!-- footer -->
			<?php echo view('deskapp/includes/_footer'); ?>
		</div>
	</div>
	<!-- js -->
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/core.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/script.min.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/process.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/layout-settings.js"></script>
	<script src="<?php echo base_url(); ?>/public/assets/src/plugins/cropperjs/dist/cropper.js"></script>
	<script>
		window.addEventListener('DOMContentLoaded', function () {
			var image = document.getElementById('image');
			var cropBoxData;
			var canvasData;
			var cropper;

			$('#modal').on('shown.bs.modal', function () {
				cropper = new Cropper(image, {
					autoCropArea: 0.5,
					dragMode: 'move',
					aspectRatio: 3 / 3,
					restore: false,
					guides: false,
					center: false,
					highlight: false,
					cropBoxMovable: false,
					cropBoxResizable: false,
					toggleDragModeOnDblclick: false,
					ready: function () {
						cropper.setCropBoxData(cropBoxData).setCanvasData(canvasData);
					}
				});
			}).on('hidden.bs.modal', function () {
				cropBoxData = cropper.getCropBoxData();
				canvasData = cropper.getCanvasData();
				cropper.destroy();
			});
		});
	</script>
</body>
</html>