<hr>
<div class="footer-wrap pd-20 mb-20 card-box text-center">
    <div class="d-flex justify-content-center align-items-center">
        <span class="mr-2">&copy; 2024 SGL - Sistema de Gestión de Trámites - Administrador</span>
       
    </div>
</div>


<!-- Incluye los estilos de Bootstrap y Font Awesome -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- SweetAlert2 para modales -->
<link rel="stylesheet" href="<?php echo base_url(); ?>/public/assets/src/plugins/sweetalert2/sweetalert2.css">
<script src="<?php echo base_url(); ?>/public/assets/src/plugins/sweetalert2/sweetalert2.all.js"></script>

<!-- Script para búsqueda de auditoría de trámite -->
<script type="text/javascript">
// Asegurar que la función está en el scope global
window.buscarAuditoria = function() {
    console.log('buscarAuditoria called'); // Debug
    
    if (typeof Swal === 'undefined') {
        alert('SweetAlert2 no está cargado. Por favor recarga la página.');
        return;
    }
    
    Swal.fire({
        title: 'Buscar Auditoría de Trámite',
        html: `
            <div class="form-group text-left">
                <label for="tramite_id_audit" class="font-weight-bold">ID del Trámite:</label>
                <input type="number" id="tramite_id_audit" class="form-control" placeholder="Ingresa el ID del trámite" min="1">
                <small class="text-muted">Puedes encontrar el ID en la URL al editar un trámite</small>
            </div>
            <div class="form-group text-left mt-3">
                <label for="folio_audit" class="font-weight-bold">O buscar por Folio:</label>
                <input type="text" id="folio_audit" class="form-control" placeholder="Ej: ABC123456" style="text-transform: uppercase;">
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-search"></i> Buscar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#1b00ff',
        cancelButtonColor: '#6c757d',
        width: '500px',
        didOpen: () => {
            const tramiteInput = document.getElementById('tramite_id_audit');
            const folioInput = document.getElementById('folio_audit');
            
            // Convertir folio a mayúsculas automáticamente
            folioInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
            
            // Focus en el primer campo
            tramiteInput.focus();
            
            // Enter en los campos
            tramiteInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    Swal.clickConfirm();
                }
            });
            folioInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    Swal.clickConfirm();
                }
            });
        },
        preConfirm: () => {
            const tramiteId = document.getElementById('tramite_id_audit').value;
            const folio = document.getElementById('folio_audit').value.trim();
            
            if (!tramiteId && !folio) {
                Swal.showValidationMessage('Debes ingresar el ID del trámite o el folio');
                return false;
            }
            
            if (folio) {
                // Buscar por folio primero
                return fetch(`<?= base_url('deskapp/tramites/buscar_por_folio') ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ folio: folio })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.tramite_id) {
                        return { tramite_id: data.tramite_id, folio: data.folio };
                    } else {
                        throw new Error(data.message || 'Folio no encontrado');
                    }
                })
                .catch(error => {
                    Swal.showValidationMessage(`Error: ${error.message}`);
                    return false;
                });
            }
            
            return { tramite_id: tramiteId };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const tramiteId = result.value.tramite_id;
            const folio = result.value.folio || '';
            
            // Mostrar loading
            Swal.fire({
                title: 'Cargando...',
                html: `Obteniendo auditoría del trámite${folio ? ' <strong>' + folio + '</strong>' : ''}`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Redirigir al timeline
            window.open(`<?= base_url('deskapp/tramites/audit_timeline') ?>/${tramiteId}`, '_blank');
            
            // Cerrar el loading después de medio segundo
            setTimeout(() => {
                Swal.close();
            }, 500);
        }
    });
};

// Log para debug
console.log('buscarAuditoria function loaded:', typeof window.buscarAuditoria);
</script>
