<!DOCTYPE html>
<html>
<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8">
    <title>Nuevo Trámite - Admin SGL</title>

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
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/public/assets/vendors/styles/style.css">

    <style>
        .wizard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .wizard-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }

        .wizard-step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            font-size: 20px;
            transition: all 0.3s;
        }

        .wizard-step.active .step-number {
            background: #007bff;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .wizard-step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .wizard-step.completed .step-number::after {
            content: '✓';
            position: absolute;
        }

        .step-title {
            font-weight: 600;
            color: #999;
            transition: all 0.3s;
        }

        .wizard-step.active .step-title {
            color: #007bff;
        }

        .wizard-step.completed .step-title {
            color: #28a745;
        }

        .wizard-content {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-height: 500px;
        }

        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: #333;
        }

        .form-group label.required::after {
            content: ' *';
            color: #dc3545;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            padding: 12px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }

        .file-upload-area {
            border: 3px dashed #e0e0e0;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }

        .file-upload-area.dragover {
            border-color: #28a745;
            background: #d4edda;
        }

        .file-list {
            margin-top: 20px;
        }

        .file-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .file-item-icon {
            width: 40px;
            height: 40px;
            background: #007bff;
            color: white;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .file-item-info {
            flex: 1;
        }

        .file-item-name {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .file-item-size {
            font-size: 12px;
            color: #666;
        }

        .file-item-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-item-remove:hover {
            background: #c82333;
        }

        .wizard-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
        }

        .btn-wizard {
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-prev {
            background: #6c757d;
            color: white;
        }

        .btn-prev:hover {
            background: #5a6268;
        }

        .btn-next {
            background: #007bff;
            color: white;
        }

        .btn-next:hover {
            background: #0056b3;
        }

        .btn-submit {
            background: #28a745;
            color: white;
        }

        .btn-submit:hover {
            background: #218838;
        }

        .summary-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .summary-section h4 {
            color: #007bff;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .summary-item {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-label {
            font-weight: 600;
            width: 200px;
            color: #666;
        }

        .summary-value {
            flex: 1;
            color: #333;
        }

        .auto-save-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
            animation: slideInRight 0.3s;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php 
        echo view('deskapp/includes/_header');
        echo view('deskapp/includes/_sidebar');
    ?>

    <div class="main-container">
        <div class="pd-ltr-20">
            <div class="wizard-container">
                <div class="page-header mb-30">
                    <h2 class="mb-0">Crear Nuevo Trámite</h2>
                    <p class="text-muted">Complete todos los pasos para crear el trámite</p>
                </div>

                <!-- Wizard Steps -->
                <div class="wizard-steps">
                    <div class="wizard-step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-title">Datos del Vehículo</div>
                    </div>
                    <div class="wizard-step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-title">Tipo y Ubicación</div>
                    </div>
                    <div class="wizard-step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-title">Cliente y Asignación</div>
                    </div>
                    <div class="wizard-step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-title">Documentos y Confirmación</div>
                    </div>
                </div>

                <!-- Wizard Content -->
                <form id="wizardForm" enctype="multipart/form-data">
                    <input type="hidden" name="paso_actual" id="paso_actual" value="1">
                    
                    <div class="wizard-content">
                        <!-- PASO 1: Datos del Vehículo -->
                        <div class="step-content active" data-step="1">
                            <h3 class="mb-30">Datos del Vehículo</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required">Folio</label>
                                        <input type="text" class="form-control" name="folio" id="folio" 
                                               value="<?= $folio_sugerido ?>" required>
                                        <small class="text-muted">Se sugiere el folio automático</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required">Contrato</label>
                                        <input type="text" class="form-control" name="contrato" id="contrato" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Unidad</label>
                                        <input type="text" class="form-control" name="unidad" id="unidad">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required">Serie</label>
                                        <input type="text" class="form-control" name="serie" id="serie" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Placas</label>
                                        <input type="text" class="form-control" name="placas" id="placas">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Observaciones Iniciales</label>
                                <textarea class="form-control" name="observaciones" id="observaciones" rows="4" 
                                          placeholder="Ingrese cualquier observación relevante..."></textarea>
                            </div>
                        </div>

                        <!-- PASO 2: Tipo y Ubicación -->
                        <div class="step-content" data-step="2">
                            <h3 class="mb-30">Tipo y Ubicación del Trámite</h3>
                            
                            <div class="form-group">
                                <label class="required">Tipo de Trámite</label>
                                <select class="form-control" name="tra_tipos_id" id="tra_tipos_id" required>
                                    <option value="">Seleccione un tipo de trámite</option>
                                    <?php foreach ($tra_tipos as $tipo): ?>
                                        <option value="<?= $tipo['id'] ?>"><?= $tipo['tipo_tramite'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required">Entidad</label>
                                        <select class="form-control" name="entidad_id" id="entidad_id" required>
                                            <option value="">Seleccione una entidad</option>
                                            <?php foreach ($entidades as $entidad): ?>
                                                <option value="<?= $entidad['id'] ?>"><?= $entidad['entidad'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required">Municipio</label>
                                        <select class="form-control" name="ent_municipio_id" id="ent_municipio_id" required disabled>
                                            <option value="">Primero seleccione una entidad</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 3: Cliente y Asignación -->
                        <div class="step-content" data-step="3">
                            <h3 class="mb-30">Cliente y Asignación</h3>
                            
                            <div class="form-group">
                                <label class="required">Cliente Directo</label>
                                <select class="form-control" name="cli_directo_id" id="cli_directo_id" required>
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= $cliente['id'] ?>"><?= $cliente['razon_social'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label>Ejecutivo del Cliente</label>
                                <select class="form-control" name="cli_directo_ejecutivo_id" id="cli_directo_ejecutivo_id" disabled>
                                    <option value="">Primero seleccione un cliente</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Empresa Gestora</label>
                                <select class="form-control" name="empresa_gestora_id" id="empresa_gestora_id">
                                    <option value="">Seleccione una empresa gestora</option>
                                    <?php foreach ($empresas_gestoras as $empresa): ?>
                                        <option value="<?= $empresa['id'] ?>"><?= $empresa['razon_social'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label>Gestor</label>
                                <select class="form-control" name="gestor_id" id="gestor_id" disabled>
                                    <option value="">Primero seleccione una empresa gestora</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label>Usuario Responsable</label>
                                <select class="form-control" name="user_id" id="user_id">
                                    <option value="">Seleccione un usuario</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= $usuario['id'] ?>"><?= $usuario['nombre'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- PASO 4: Documentos y Confirmación -->
                        <div class="step-content" data-step="4">
                            <h3 class="mb-30">Documentos y Confirmación</h3>
                            
                            <div class="form-group">
                                <label>Subir Documentos</label>
                                <div class="file-upload-area" id="fileUploadArea">
                                    <i class="icon-copy fa fa-cloud-upload" style="font-size: 48px; color: #007bff; margin-bottom: 15px;"></i>
                                    <p style="font-size: 16px; margin-bottom: 10px;">Arrastra archivos aquí o haz clic para seleccionar</p>
                                    <p style="font-size: 13px; color: #666;">PDF, JPG, PNG - Máximo 10MB por archivo</p>
                                    <input type="file" id="fileInput" name="documentos[]" multiple accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                                </div>
                                <div class="file-list" id="fileList"></div>
                            </div>

                            <hr class="my-4">

                            <h4 class="mb-20">Resumen del Trámite</h4>

                            <div class="summary-section">
                                <h4><i class="icon-copy fa fa-car"></i> Datos del Vehículo</h4>
                                <div class="summary-item">
                                    <div class="summary-label">Folio:</div>
                                    <div class="summary-value" id="summary_folio">-</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Contrato:</div>
                                    <div class="summary-value" id="summary_contrato">-</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Unidad:</div>
                                    <div class="summary-value" id="summary_unidad">-</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Serie:</div>
                                    <div class="summary-value" id="summary_serie">-</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Placas:</div>
                                    <div class="summary-value" id="summary_placas">-</div>
                                </div>
                            </div>

                            <div class="summary-section">
                                <h4><i class="icon-copy fa fa-map-marker"></i> Tipo y Ubicación</h4>
                                <div class="summary-item">
                                    <div class="summary-label">Tipo de Trámite:</div>
                                    <div class="summary-value" id="summary_tipo_tramite">-</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Municipio:</div>
                                    <div class="summary-value" id="summary_municipio">-</div>
                                </div>
                            </div>

                            <div class="summary-section">
                                <h4><i class="icon-copy fa fa-users"></i> Cliente y Asignación</h4>
                                <div class="summary-item">
                                    <div class="summary-label">Cliente:</div>
                                    <div class="summary-value" id="summary_cliente">-</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Ejecutivo del Cliente:</div>
                                    <div class="summary-value" id="summary_ejecutivo">-</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Empresa Gestora:</div>
                                    <div class="summary-value" id="summary_empresa_gestora">-</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Gestor:</div>
                                    <div class="summary-value" id="summary_gestor">-</div>
                                </div>
                            </div>

                            <?php if (!empty($observaciones)): ?>
                            <div class="summary-section">
                                <h4><i class="icon-copy fa fa-comment"></i> Observaciones</h4>
                                <div class="summary-item">
                                    <div class="summary-value" id="summary_observaciones">-</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Wizard Actions -->
                    <div class="wizard-actions">
                        <button type="button" class="btn-wizard btn-prev" id="btnPrev" style="display: none;">
                            <i class="icon-copy fa fa-arrow-left"></i> Anterior
                        </button>
                        <div>
                            <button type="button" class="btn-wizard btn-next" id="btnNext">
                                Siguiente <i class="icon-copy fa fa-arrow-right"></i>
                            </button>
                            <button type="submit" class="btn-wizard btn-submit" id="btnSubmit" style="display: none;">
                                <i class="icon-copy fa fa-check"></i> Crear Trámite
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <?php echo view('deskapp/includes/_footer'); ?>
        </div>
    </div>

    <!-- Auto-save Indicator -->
    <div class="auto-save-indicator" id="autoSaveIndicator">
        <i class="icon-copy fa fa-check-circle"></i> Borrador guardado automáticamente
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Creando trámite...</p>
        </div>
    </div>

    <!-- js -->
    <script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/core.js"></script>
    <script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/script.min.js"></script>
    <script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/process.js"></script>
    <script src="<?php echo base_url(); ?>/public/assets/vendors/scripts/layout-settings.js"></script>

    <script>
        const WizardManager = {
            currentStep: 1,
            totalSteps: 4,
            uploadedFiles: [],
            autoSaveTimer: null,

            init() {
                this.setupEventListeners();
                this.loadDraft();
                this.startAutoSave();
            },

            setupEventListeners() {
                // Navegación
                document.getElementById('btnNext').addEventListener('click', () => this.nextStep());
                document.getElementById('btnPrev').addEventListener('click', () => this.prevStep());
                document.getElementById('wizardForm').addEventListener('submit', (e) => this.submitForm(e));

                // Dependientes
                const onDependentChange = (selector, callback) => {
                    const element = document.getElementById(selector);
                    if (!element) {
                        return;
                    }

                    if (window.jQuery) {
                        window.jQuery(element).off('change.sglDependent').on('change.sglDependent', function () {
                            callback(this.value || '');
                        });
                        return;
                    }

                    element.addEventListener('change', (e) => callback(e.target.value || ''));
                };

                onDependentChange('entidad_id', (value) => this.loadMunicipios(value));
                onDependentChange('cli_directo_id', (value) => this.loadEjecutivos(value));
                onDependentChange('empresa_gestora_id', (value) => this.loadGestores(value));

                // File upload
                const fileUploadArea = document.getElementById('fileUploadArea');
                const fileInput = document.getElementById('fileInput');
                
                fileUploadArea.addEventListener('click', () => fileInput.click());
                fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
                
                fileUploadArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    fileUploadArea.classList.add('dragover');
                });
                
                fileUploadArea.addEventListener('dragleave', () => {
                    fileUploadArea.classList.remove('dragover');
                });
                
                fileUploadArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    fileUploadArea.classList.remove('dragover');
                    this.handleFileSelect(e);
                });
            },

            nextStep() {
                if (!this.validateStep(this.currentStep)) {
                    return;
                }

                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                    this.updateWizard();
                    if (this.currentStep === this.totalSteps) {
                        this.updateSummary();
                    }
                }
            },

            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                    this.updateWizard();
                }
            },

            updateWizard() {
                // Actualizar steps
                document.querySelectorAll('.wizard-step').forEach((step, index) => {
                    const stepNum = index + 1;
                    step.classList.remove('active', 'completed');
                    
                    if (stepNum < this.currentStep) {
                        step.classList.add('completed');
                    } else if (stepNum === this.currentStep) {
                        step.classList.add('active');
                    }
                });

                // Actualizar contenido
                document.querySelectorAll('.step-content').forEach((content) => {
                    content.classList.remove('active');
                    if (parseInt(content.dataset.step) === this.currentStep) {
                        content.classList.add('active');
                    }
                });

                // Actualizar botones
                document.getElementById('btnPrev').style.display = this.currentStep > 1 ? 'block' : 'none';
                document.getElementById('btnNext').style.display = this.currentStep < this.totalSteps ? 'block' : 'none';
                document.getElementById('btnSubmit').style.display = this.currentStep === this.totalSteps ? 'block' : 'none';

                // Actualizar campo paso_actual
                document.getElementById('paso_actual').value = this.currentStep;

                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            validateStep(step) {
                const stepContent = document.querySelector(`.step-content[data-step="${step}"]`);
                const requiredFields = stepContent.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    field.classList.remove('is-invalid');
                    const feedback = field.nextElementSibling;
                    
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = 'Este campo es requerido';
                        }
                        isValid = false;
                    }
                });

                if (!isValid) {
                    alert('Por favor complete todos los campos requeridos');
                }

                return isValid;
            },

            updateSummary() {
                // Datos del vehículo
                document.getElementById('summary_folio').textContent = document.getElementById('folio').value || '-';
                document.getElementById('summary_contrato').textContent = document.getElementById('contrato').value || '-';
                document.getElementById('summary_unidad').textContent = document.getElementById('unidad').value || '-';
                document.getElementById('summary_serie').textContent = document.getElementById('serie').value || '-';
                document.getElementById('summary_placas').textContent = document.getElementById('placas').value || '-';

                // Tipo y ubicación
                const tipoTramite = document.getElementById('tra_tipos_id');
                document.getElementById('summary_tipo_tramite').textContent = 
                    tipoTramite.options[tipoTramite.selectedIndex]?.text || '-';

                const municipio = document.getElementById('ent_municipio_id');
                document.getElementById('summary_municipio').textContent = 
                    municipio.options[municipio.selectedIndex]?.text || '-';

                // Cliente y asignación
                const cliente = document.getElementById('cli_directo_id');
                document.getElementById('summary_cliente').textContent = 
                    cliente.options[cliente.selectedIndex]?.text || '-';

                const ejecutivo = document.getElementById('cli_directo_ejecutivo_id');
                document.getElementById('summary_ejecutivo').textContent = 
                    ejecutivo.options[ejecutivo.selectedIndex]?.text || '-';

                const empresa = document.getElementById('empresa_gestora_id');
                document.getElementById('summary_empresa_gestora').textContent = 
                    empresa.options[empresa.selectedIndex]?.text || '-';

                const gestor = document.getElementById('gestor_id');
                document.getElementById('summary_gestor').textContent = 
                    gestor.options[gestor.selectedIndex]?.text || '-';

                // Observaciones
                const obs = document.getElementById('observaciones').value;
                if (obs) {
                    document.getElementById('summary_observaciones').textContent = obs;
                }
            },

            async submitForm(e) {
                e.preventDefault();

                if (!this.validateStep(this.currentStep)) {
                    return;
                }

                if (!confirm('¿Está seguro de crear este trámite?')) {
                    return;
                }

                document.getElementById('loadingOverlay').style.display = 'flex';

                const formData = new FormData(document.getElementById('wizardForm'));

                try {
                    const response = await fetch('<?= base_url() ?>/deskapp/tramitewizard/guardar', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    document.getElementById('loadingOverlay').style.display = 'none';

                    if (result.success) {
                        alert(`¡Trámite creado exitosamente!\nFolio: ${result.folio}`);
                        this.deleteDraft();
                        window.location.href = '<?= base_url() ?>/deskapp/tramitewizard/listado';
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    document.getElementById('loadingOverlay').style.display = 'none';
                    alert('Error al crear el trámite: ' + error.message);
                }
            },

            async loadMunicipios(entidadId) {
                const municipioSelect = document.getElementById('ent_municipio_id');
                municipioSelect.disabled = true;
                municipioSelect.innerHTML = '<option value="">Cargando...</option>';
                if (window.SglSelectEnhancer) {
                    window.SglSelectEnhancer.refresh(municipioSelect);
                }

                try {
                    const formData = new FormData();
                    formData.append('entidad_id', entidadId);

                    const response = await fetch('<?= base_url() ?>/deskapp/tramitewizard/get_municipios', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    municipioSelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                    result.municipios.forEach(mun => {
                        municipioSelect.innerHTML += `<option value="${mun.id}">${mun.municipio}</option>`;
                    });
                    municipioSelect.disabled = false;
                    if (window.SglSelectEnhancer) {
                        window.SglSelectEnhancer.refresh(municipioSelect);
                    }
                } catch (error) {
                    municipioSelect.innerHTML = '<option value="">Error al cargar</option>';
                    if (window.SglSelectEnhancer) {
                        window.SglSelectEnhancer.refresh(municipioSelect);
                    }
                }
            },

            async loadEjecutivos(clienteId) {
                const ejecutivoSelect = document.getElementById('cli_directo_ejecutivo_id');
                ejecutivoSelect.disabled = true;
                ejecutivoSelect.innerHTML = '<option value="">Cargando...</option>';
                if (window.SglSelectEnhancer) {
                    window.SglSelectEnhancer.refresh(ejecutivoSelect);
                }

                try {
                    const formData = new FormData();
                    formData.append('cliente_id', clienteId);

                    const response = await fetch('<?= base_url() ?>/deskapp/tramitewizard/get_ejecutivos_cliente', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    ejecutivoSelect.innerHTML = '<option value="">Seleccione un ejecutivo</option>';
                    result.ejecutivos.forEach(ej => {
                        ejecutivoSelect.innerHTML += `<option value="${ej.id}">${ej.nombre}</option>`;
                    });
                    ejecutivoSelect.disabled = false;
                    if (window.SglSelectEnhancer) {
                        window.SglSelectEnhancer.refresh(ejecutivoSelect);
                    }
                } catch (error) {
                    ejecutivoSelect.innerHTML = '<option value="">Error al cargar</option>';
                    if (window.SglSelectEnhancer) {
                        window.SglSelectEnhancer.refresh(ejecutivoSelect);
                    }
                }
            },

            async loadGestores(empresaId) {
                const gestorSelect = document.getElementById('gestor_id');
                gestorSelect.disabled = true;
                gestorSelect.innerHTML = '<option value="">Cargando...</option>';
                if (window.SglSelectEnhancer) {
                    window.SglSelectEnhancer.refresh(gestorSelect);
                }

                try {
                    const formData = new FormData();
                    formData.append('empresa_id', empresaId);

                    const response = await fetch('<?= base_url() ?>/deskapp/tramitewizard/get_gestores', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    gestorSelect.innerHTML = '<option value="">Seleccione un gestor</option>';
                    result.gestores.forEach(ges => {
                        gestorSelect.innerHTML += `<option value="${ges.id}">${ges.nombre}</option>`;
                    });
                    gestorSelect.disabled = false;
                    if (window.SglSelectEnhancer) {
                        window.SglSelectEnhancer.refresh(gestorSelect);
                    }
                } catch (error) {
                    gestorSelect.innerHTML = '<option value="">Error al cargar</option>';
                    if (window.SglSelectEnhancer) {
                        window.SglSelectEnhancer.refresh(gestorSelect);
                    }
                }
            },

            handleFileSelect(e) {
                const files = e.target.files || e.dataTransfer.files;
                
                Array.from(files).forEach(file => {
                    if (file.size > 10 * 1024 * 1024) {
                        alert(`El archivo ${file.name} excede el tamaño máximo de 10MB`);
                        return;
                    }

                    this.uploadedFiles.push(file);
                    this.renderFileList();
                });
            },

            renderFileList() {
                const fileList = document.getElementById('fileList');
                fileList.innerHTML = '';

                this.uploadedFiles.forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item';
                    fileItem.innerHTML = `
                        <div class="file-item-icon">
                            <i class="icon-copy fa fa-file"></i>
                        </div>
                        <div class="file-item-info">
                            <div class="file-item-name">${file.name}</div>
                            <div class="file-item-size">${this.formatFileSize(file.size)}</div>
                        </div>
                        <button type="button" class="file-item-remove" onclick="WizardManager.removeFile(${index})">
                            <i class="icon-copy fa fa-trash"></i>
                        </button>
                    `;
                    fileList.appendChild(fileItem);
                });
            },

            removeFile(index) {
                this.uploadedFiles.splice(index, 1);
                this.renderFileList();
            },

            formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            },

            startAutoSave() {
                setInterval(() => {
                    this.saveDraft();
                }, 30000); // Auto-guardar cada 30 segundos
            },

            async saveDraft() {
                const formData = new FormData(document.getElementById('wizardForm'));

                try {
                    await fetch('<?= base_url() ?>/deskapp/tramitewizard/guardar_borrador', {
                        method: 'POST',
                        body: formData
                    });

                    const indicator = document.getElementById('autoSaveIndicator');
                    indicator.style.display = 'block';
                    setTimeout(() => {
                        indicator.style.display = 'none';
                    }, 2000);
                } catch (error) {
                    console.error('Error al guardar borrador:', error);
                }
            },

            async loadDraft() {
                try {
                    const response = await fetch('<?= base_url() ?>/deskapp/tramitewizard/recuperar_borrador');
                    const result = await response.json();

                    if (result.success && result.borrador) {
                        if (confirm('Se encontró un borrador guardado. ¿Desea recuperarlo?')) {
                            Object.keys(result.borrador).forEach(key => {
                                const field = document.querySelector(`[name="${key}"]`);
                                if (field) {
                                    field.value = result.borrador[key];
                                }
                            });

                            this.currentStep = result.paso_actual || 1;
                            this.updateWizard();
                        }
                    }
                } catch (error) {
                    console.error('Error al cargar borrador:', error);
                }
            },

            async deleteDraft() {
                try {
                    await fetch('<?= base_url() ?>/deskapp/tramitewizard/eliminar_borrador', {
                        method: 'POST'
                    });
                } catch (error) {
                    console.error('Error al eliminar borrador:', error);
                }
            }
        };

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            WizardManager.init();
        });
    </script>
</body>
</html>
