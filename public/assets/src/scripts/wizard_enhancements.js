/**
 * ============================================================================
 * MEJORAS DEL WIZARD - INTERACCIONES MODERNAS
 * ============================================================================
 */

(function() {
    'use strict';
    
    // Esperar a que el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        
        // ========================================================================
        // ACTUALIZAR PROGRESO DE STEPS
        // ========================================================================
        function updateStepProgress() {
            const wizard = document.querySelector('.wizard-modern');
            if (!wizard) return;
            
            const stepsUl = wizard.querySelector('.steps ul');
            if (!stepsUl) return;
            
            const steps = stepsUl.querySelectorAll('li');
            let currentStepIndex = 0;
            
            steps.forEach((step, index) => {
                if (step.classList.contains('current')) {
                    currentStepIndex = index + 1;
                }
            });
            
            stepsUl.setAttribute('data-progress', currentStepIndex);
        }
        
        // ========================================================================
        // ICONOS PARA CADA PASO
        // ========================================================================
        const stepIcons = {
            'Información': 'fa-info-circle',
            'Gestor': 'fa-user-tie',
            'Pago de Derechos': 'fa-credit-card',
            'Pago a Gestor': 'fa-hand-holding-usd',
            'Cobro a Cliente': 'fa-money-bill-wave'
        };
        
        function enhanceStepTitles() {
            const wizard = document.querySelector('.wizard-modern');
            if (!wizard) return;
            
            const steps = wizard.querySelectorAll('.steps ul li a');
            
            steps.forEach(link => {
                const stepNumber = link.querySelector('.number');
                if (!stepNumber) return;
                
                // Crear contenedor para el número
                const numberContainer = document.createElement('div');
                numberContainer.className = 'step-number';
                
                // Obtener el título del paso
                const stepTitle = link.textContent.trim();
                const iconClass = stepIcons[stepTitle] || 'fa-circle';
                
                // Crear el icono
                const icon = document.createElement('i');
                icon.className = `fas ${iconClass}`;
                
                // Reemplazar el número con el contenedor
                const numberText = stepNumber.textContent;
                numberContainer.innerHTML = numberText;
                stepNumber.replaceWith(numberContainer);
                
                // Crear el contenedor del título
                const titleContainer = document.createElement('div');
                titleContainer.className = 'step-title';
                titleContainer.textContent = stepTitle;
                
                link.appendChild(titleContainer);
            });
        }
        
        // ========================================================================
        // SMOOTH SCROLL EN TABS
        // ========================================================================
        function setupSmoothScrollTabs() {
            const tabLinks = document.querySelectorAll('.tabs-modern .nav-link');
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Scroll suave hacia arriba cuando se cambia de tab
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            });
        }
        
        // ========================================================================
        // MEJORAR DROPZONE
        // ========================================================================
        function enhanceDropzones() {
            const dropzones = document.querySelectorAll('.dropzone');
            
            dropzones.forEach(dz => {
                if (!dz.classList.contains('dropzone-modern')) {
                    dz.classList.add('dropzone-modern');
                }
                
                // Añadir mensajes más descriptivos
                const message = dz.querySelector('.dz-message');
                if (message && !message.querySelector('.dz-text')) {
                    const textDiv = document.createElement('div');
                    textDiv.className = 'dz-text';
                    textDiv.textContent = 'Arrastra archivos aquí o haz clic para seleccionar';
                    
                    const subtext = document.createElement('div');
                    subtext.className = 'dz-subtext';
                    subtext.textContent = 'PDF, JPG, PNG - Máximo 10MB';
                    
                    message.appendChild(textDiv);
                    message.appendChild(subtext);
                }
            });
        }
        
        // ========================================================================
        // VALIDACIÓN EN TIEMPO REAL
        // ========================================================================
        function setupRealtimeValidation() {
            const forms = document.querySelectorAll('.wizard-modern form');
            
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
                
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        validateField(this);
                    });
                    
                    input.addEventListener('input', function() {
                        if (this.classList.contains('is-invalid')) {
                            validateField(this);
                        }
                    });
                });
            });
        }
        
        function validateField(field) {
            const formGroup = field.closest('.form-group-modern') || field.closest('.form-group');
            if (!formGroup) return;
            
            const feedback = formGroup.querySelector('.invalid-feedback');
            
            if (!field.value.trim() && field.hasAttribute('required')) {
                field.classList.add('is-invalid');
                if (feedback) {
                    feedback.textContent = 'Este campo es requerido';
                    feedback.style.display = 'block';
                }
            } else {
                field.classList.remove('is-invalid');
                if (feedback) {
                    feedback.style.display = 'none';
                }
            }
        }
        
        // ========================================================================
        // LOADING STATES EN BOTONES
        // ========================================================================
        function setupButtonLoadingStates() {
            const submitButtons = document.querySelectorAll('button[type="submit"], .btn-submit');
            
            submitButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (this.form && !this.form.checkValidity()) {
                        return;
                    }
                    
                    // Añadir estado de carga
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                    this.disabled = true;
                    
                    // Restaurar después de 5 segundos (por si hay error)
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }, 5000);
                });
            });
        }
        
        // ========================================================================
        // INICIALIZAR MEJORAS
        // ========================================================================
        function init() {
            updateStepProgress();
            enhanceStepTitles();
            setupSmoothScrollTabs();
            enhanceDropzones();
            setupRealtimeValidation();
            setupButtonLoadingStates();
            enhanceModals(); // Nueva función para modals
            
            // Actualizar progreso cuando cambien los pasos
            const wizard = document.querySelector('.wizard-modern');
            if (wizard) {
                const observer = new MutationObserver(function(mutations) {
                    updateStepProgress();
                });
                
                observer.observe(wizard, {
                    attributes: true,
                    subtree: true,
                    attributeFilter: ['class']
                });
            }
        }
        
        // ========================================================================
        // MEJORAS PARA MODALS
        // ========================================================================
        function enhanceModals() {
            // Agregar animación de entrada a los modals
            $('.modal').on('show.bs.modal', function(e) {
                const modal = $(this);
                modal.find('.modal-dialog').removeClass('animate-out').addClass('animate-in');
            });
            
            // Animación de salida
            $('.modal').on('hide.bs.modal', function(e) {
                const modal = $(this);
                modal.find('.modal-dialog').addClass('animate-out');
            });
            
            // Efecto ripple en action cards y ribbon buttons
            $('.action-card, .ribbon-btn').on('click', function(e) {
                const card = $(this);
                const ripple = $('<span class="ripple-effect"></span>');
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                ripple.css({
                    left: x + 'px',
                    top: y + 'px'
                });
                
                card.append(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
            
            // Lazy load de contenido de modals para mejor rendimiento
            $('.modal').on('shown.bs.modal', function() {
                const modal = $(this);
                // Inicializar tablas GroceryCRUD dentro del modal si existen
                const tables = modal.find('table.display');
                if (tables.length > 0 && typeof $.fn.DataTable !== 'undefined') {
                    tables.each(function() {
                        if (!$.fn.DataTable.isDataTable(this)) {
                            // La tabla ya debe estar inicializada por GroceryCRUD
                            // Solo forzar un redraw
                            $(this).DataTable().columns.adjust().draw();
                        }
                    });
                }
            });
        }
        
        // Ejecutar inicialización
        init();
        
        // Exponer función para actualización manual si es necesario
        window.updateWizardProgress = updateStepProgress;
    });
})();
