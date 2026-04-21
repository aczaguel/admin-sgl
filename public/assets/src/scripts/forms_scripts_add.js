
"use strict";

var lastDuplicateModalTrigger = null;

function moveFocusOutsideDuplicateModal(modalEl) {
  var activeElement = document.activeElement;

  if (activeElement && modalEl && modalEl.contains(activeElement) && typeof activeElement.blur === 'function') {
    activeElement.blur();
  }

  if (lastDuplicateModalTrigger && typeof lastDuplicateModalTrigger.focus === 'function') {
    lastDuplicateModalTrigger.focus();
    return;
  }

  if (document.body && typeof document.body.focus === 'function') {
    document.body.focus();
  }
}

function ensureDuplicateModalReady() {
  var modalEl = document.getElementById('duplicateConfirmModal');

  if (!modalEl) {
    console.error('[ensureDuplicateModalReady] Modal element not found');
    return null;
  }

  if (modalEl.parentNode !== document.body) {
    document.body.appendChild(modalEl);
    console.log('[ensureDuplicateModalReady] Modal moved to document.body');
  }

  modalEl.style.position = 'fixed';
  modalEl.style.inset = '0';
  modalEl.style.zIndex = '200000';

  var dialogEl = modalEl.querySelector('.modal-dialog');
  if (dialogEl) {
    dialogEl.style.zIndex = '200001';
    dialogEl.style.margin = '1.75rem auto';
  }

  var contentEl = modalEl.querySelector('.modal-content');
  if (contentEl) {
    contentEl.style.zIndex = '200002';
  }

  if (!modalEl.dataset.focusHandlersBound) {
    modalEl.addEventListener('hide.bs.modal', function() {
      moveFocusOutsideDuplicateModal(modalEl);
    });

    modalEl.addEventListener('hidden.bs.modal', function() {
      moveFocusOutsideDuplicateModal(modalEl);
    });

    modalEl.dataset.focusHandlersBound = '1';
  }

  return modalEl;
}

function showDuplicateModal() {
  var modalEl = ensureDuplicateModalReady();
  console.log('[showDuplicateModal] modalEl:', modalEl);
  if (!modalEl) {
    console.error('[showDuplicateModal] Modal element not found!');
    return false;
  }

  console.log('[showDuplicateModal] window.bootstrap:', typeof window.bootstrap);
  console.log('[showDuplicateModal] window.jQuery:', typeof window.jQuery);

  // Bootstrap 5
  if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
    console.log('[showDuplicateModal] Using Bootstrap 5 Modal API');
    try {
      var modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
      modalInstance.show();
      console.log('[showDuplicateModal] Bootstrap 5 show() called');
      return true;
    } catch (e) {
      console.error('[showDuplicateModal] Bootstrap 5 error:', e);
    }
  }

  // Bootstrap 4/jQuery plugin
  if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
    console.log('[showDuplicateModal] Using jQuery/Bootstrap 4 Modal API');
    try {
      window.jQuery('#duplicateConfirmModal').modal('show');
      console.log('[showDuplicateModal] jQuery modal show() called');
      return true;
    } catch (e) {
      console.error('[showDuplicateModal] jQuery error:', e);
    }
  }

  // Fallback simple - force CSS
  console.log('[showDuplicateModal] Using CSS fallback');
  modalEl.classList.add('show');
  modalEl.style.display = 'block';
  modalEl.style.opacity = '1';
  modalEl.style.visibility = 'visible';
  modalEl.style.backgroundColor = 'rgba(0, 0, 0, 0.45)';
  modalEl.removeAttribute('aria-hidden');
  modalEl.setAttribute('aria-modal', 'true');
  document.body.classList.add('modal-open');
  
  // Force backdrop if not exists
  if (!document.querySelector('.modal-backdrop')) {
    var backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    document.body.appendChild(backdrop);
  }
  
  console.log('[showDuplicateModal] CSS fallback applied. Modal display:', modalEl.style.display);
  return true;
}

function hideDuplicateModal() {
  var modalEl = document.getElementById('duplicateConfirmModal');
  if (!modalEl) {
    return;
  }

  moveFocusOutsideDuplicateModal(modalEl);

  if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
    try {
      var modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
      modalInstance.hide();
      return;
    } catch (e) {
      console.error('[hideDuplicateModal] Bootstrap 5 error:', e);
    }
  }

  if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
    try {
      window.jQuery('#duplicateConfirmModal').modal('hide');
      return;
    } catch (e) {
      console.error('[hideDuplicateModal] jQuery error:', e);
    }
  }

  modalEl.classList.remove('show');
  modalEl.style.display = 'none';
  modalEl.style.opacity = '0';
  modalEl.style.visibility = 'hidden';
  modalEl.setAttribute('aria-hidden', 'true');
  modalEl.removeAttribute('aria-modal');
  document.body.classList.remove('modal-open');
  
  var backdrop = document.querySelector('.modal-backdrop');
  if (backdrop) {
    backdrop.remove();
  }
}

function loadDependentData(type, parentId, targetId, selectedId = null) {
    if (!parentId || parentId === 'null') {
      return;
    }
    if (!document.getElementById(targetId)) {
      return;
    }
  $.ajax({
      url: `/deskapp/tramites/getDependentData/${type}/${parentId}`,
      method: 'GET',
      dataType: 'json',
      success: function(data) {
          const $targetElement = $(`#${targetId}`);
          $targetElement.empty().append('<option value="">Seleccione...</option>');
          $.each(data, function(index, item) {
              const $option = $('<option></option>')
                  .val(item.id)
                  .text(item.nombre); // Ajustar según la estructura de tu tabla
              if (selectedId && selectedId == item.id) {
                  $option.prop('selected', true);
              }
              $targetElement.append($option);
          });
      },
      error: function(xhr, status, error) {
          console.error('Error:', error);
      }
  });
}

  $(document).ready(function() {
    ensureDuplicateModalReady();

    $('#cli_directo_id').change(function() {
        var clienteDirectoId = $(this).val();
        if(clienteDirectoId) {
            $.ajax({
                url: '/deskapp/tramites/getEjecutivosByClienteId/' + clienteDirectoId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#cli_directo_ejecutivo_id').empty();
                    $('#cli_directo_ejecutivo_id').append('<option value="">Seleccione un Ejecutivo</option>');
                    $.each(data, function(key, value) {
                        $('#cli_directo_ejecutivo_id').append('<option value="'+ key +'">'+ value +'</option>');
                    });
                }
            });
        } else {
            $('#cli_directo_ejecutivo_id').empty();
            $('#cli_directo_ejecutivo_id').append('<option value="">Seleccione un Ejecutivo</option>');
        }
    });

    $('#empresa_gestora_id').change(function() {
      var empresaGestoraId = $(this).val();
      if(empresaGestoraId) {
        $.ajax({
          url: '/deskapp/tramites/getGestoresByEmpresaId/' + empresaGestoraId,
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#gestor_id').empty();
            $('#gestor_id').append('<option value="">Seleccione un Gestor</option>');
            $.each(data, function(key, value) {
              $('#gestor_id').append('<option value="'+ key +'">'+ value +'</option>');
            });
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error loading gestores: ' + textStatus);
          }
        });
      } else {
        $('#gestor_id').empty();
        $('#gestor_id').append('<option value="">Seleccione un Gestor</option>');
      }
    });
});

  $(document).ready(function() {
    // Inicializar flatpickr
    $('.datetime-picker').flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

    // Función para cargar datos dependientes
    

    // Agregar listeners para los campos padres
    $('#empresa_gestora_id').on('change', function() {
      var value = $(this).val();
      if (value) {
        loadDependentData('gestor', value, 'gestor_id');
      }
    });

    $('#cli_directo_id').on('change', function() {
      var value = $(this).val();
      if (value) {
        loadDependentData('ejecutivo', value, 'cli_directo_ejecutivo_id');
      }
    });

    // Carga inicial para formularios de actualización
    const empresaGestoraId = $('#empresa_gestora_id').val();
    if (empresaGestoraId) {
        loadDependentData('gestor', empresaGestoraId, 'gestor_id', gestorId);
    }

    const cliDirectoId = $('#cli_directo_id').val();
    // const ejecutivoId = '<?php echo isset($fields['cli_directo_ejecutivo_id']['value']) ? $fields['cli_directo_ejecutivo_id']['value'] : ''; ?>';
    if (cliDirectoId) {
        loadDependentData('ejecutivo', cliDirectoId, 'cli_directo_ejecutivo_id', ejecutivoId);
    }
  });


  // Form submission handler
  $('#tramiteForm').on('submit.sglAddFetch', function(event) {
    event.preventDefault();

    let form = event.target;
    let formData = new FormData(form);
    let submitter = event.originalEvent ? event.originalEvent.submitter : null;
    let hasErrors = false;

    lastDuplicateModalTrigger = submitter;

    if (submitter && submitter.name) {
      formData.set(submitter.name, submitter.value);
    }

    form.querySelectorAll('.error-message').forEach(el => el.textContent = '');

    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      hasErrors = true;
    }

    if (hasErrors) {
      let errorAlert = document.createElement('div');
      errorAlert.className = 'alert alert-danger';
      errorAlert.innerHTML = '<strong>Error:</strong> Por favor, corrija los campos marcados.';
      form.prepend(errorAlert);
      return;
    }

    fetch(form.action, {
      method: form.method,
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
      console.log('[Form Response]', data);
      if (data.success) {
        window.location.href = data.redirect;
      } else if (data.confirmable && data.message) {
        // Mostrar modal de confirmación para duplicados
        console.log('[Duplicate Detected] confirmable:', data.confirmable, 'message:', data.message);
        pendingFormData = formData;
        pendingFormAction = form.action;
        
        // Llenar detalles del trámite duplicado
        console.log('[Filling Modal Details]');
        document.getElementById('duplicateContrato').textContent = data.message.contrato_existente || '';
        document.getElementById('duplicateTipo').textContent = data.message.tipo_tramite_existente || '';
        document.getElementById('duplicateSerie').textContent = data.message.serie_existente || '';
        document.getElementById('duplicateUsuario').textContent = data.message.nombre_usuario_existente || '';
        document.getElementById('duplicateFecha').textContent = data.message.created_at_existente || '';
        
        console.log('[About to call showDuplicateModal]');
        // Mostrar la modal (compat Bootstrap 5/4)
        var opened = showDuplicateModal();
        console.log('[showDuplicateModal returned]', opened);
        
        if (!opened) {
          console.log('[Modal failed, using confirm() fallback]');
          var proceed = window.confirm('El tramite esta repetido en contrato y en tipo de tramite. Deseas continuar de todas maneras?');
          if (proceed) {
            formData.append('force_duplicate_confirm', '1');
            fetch(form.action, {
              method: form.method,
              body: formData,
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(retryData => {
              if (retryData.success) {
                window.location.href = retryData.redirect;
              }
            });
          }
        }
      } else {
        let errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
        errorAlert.setAttribute('role', 'alert');

        let errorList = '<strong>Error:</strong> No se pudo guardar el trámite.' +
          '<div class="mt-2"><ul class="mb-0 pl-3">';
        
        if (data !== "undefined" && data.success === false) {
          if (typeof data.message !== "undefined") {
            errorList += formatErrorMessage(data.message);
          } else if (typeof data.errors !== "undefined") {
            for (let field in data.errors) {
              if (data.errors.hasOwnProperty(field)) {
                errorList += `<li>${data.errors[field]}</li>`;
              }
            }
          } else {
            errorList += `<li>Ocurrió un error desconocido.</li>`;
          }
        }
        errorList += '</ul></div>';
        errorAlert.innerHTML = errorList;
        form.prepend(errorAlert);
      }
    })
    .catch(error => {
      let errorAlert = document.createElement('div');
      errorAlert.className = 'alert alert-danger alert-dismissible fade show';
      errorAlert.setAttribute('role', 'alert');
      errorAlert.innerHTML = `<strong>Error:</strong> Ocurrió un error al guardar el trámite. ${error.message}`;
      form.prepend(errorAlert);
    });
  });

  // Manejador para el botón de confirmación en la modal de duplicados
  $('#confirmDuplicateBtn').on('click', function() {
    console.log('[confirmDuplicateBtn clicked] pendingFormData:', pendingFormData);
    if (!pendingFormData) {
      console.error('[confirmDuplicateBtn] NO pendingFormData found!');
      return;
    }

    if (typeof this.blur === 'function') {
      this.blur();
    }
    
    console.log('[confirmDuplicateBtn] Adding force_duplicate_confirm flag');
    // Agregar el flag de confirmación
    pendingFormData.append('force_duplicate_confirm', '1');
    
    console.log('[confirmDuplicateBtn] Calling hideDuplicateModal()');
    // Cerrar la modal
    hideDuplicateModal();
    
    console.log('[confirmDuplicateBtn] Sending fetch to:', pendingFormAction);
    // Re-enviar el formulario con la confirmación
    fetch(pendingFormAction, {
      method: 'POST',
      body: pendingFormData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
      console.log('[confirmDuplicateBtn Response]', data);
      if (data.success) {
        console.log('[confirmDuplicateBtn] Success! Redirecting to:', data.redirect);
        window.location.href = data.redirect;
      } else {
        console.error('[confirmDuplicateBtn] Error response:', data);
        // Mostrar error si ocurre algo
        let errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
        errorAlert.setAttribute('role', 'alert');
        errorAlert.innerHTML = `<strong>Error:</strong> ${data.message || 'Ocurrió un error al guardar el trámite.'}`;
        document.getElementById('tramiteForm').prepend(errorAlert);
      }
    })
    .catch(error => {
      console.error('[confirmDuplicateBtn Catch Error]:', error);
      let errorAlert = document.createElement('div');
      errorAlert.className = 'alert alert-danger alert-dismissible fade show';
      errorAlert.setAttribute('role', 'alert');
      errorAlert.innerHTML = `<strong>Error:</strong> ${error.message}`;
      document.getElementById('tramiteForm').prepend(errorAlert);
    });
  });

      function formatErrorMessage(message) {
      if (message && typeof message === 'object') {
        let items = '';
        if (message.contrato_existente) {
          items += '<li><strong>Contrato:</strong> ' + message.contrato_existente + '</li>';
        }
        if (message.serie_existente) {
          items += '<li><strong>Serie existente:</strong> ' + message.serie_existente + '</li>';
        }
        if (message.tipo_tramite_existente) {
          items += '<li><strong>Tipo:</strong> ' + message.tipo_tramite_existente + '</li>';
        }
        if (message.nombre_usuario_existente) {
          items += '<li><strong>Creado por:</strong> ' + message.nombre_usuario_existente + '</li>';
        }
        if (message.created_at_existente) {
          items += '<li><strong>Fecha:</strong> ' + message.created_at_existente + '</li>';
        }
        if (message.id_existente) {
          items += '<li><a href="/deskapp/tramites/update/' + message.id_existente + '" target="_blank">Abrir trámite existente</a></li>';
        }
        return items || '<li>Ocurrió un error desconocido.</li>';
      }

      return '<li>' + mapErrorMessage(message) + '</li>';
      }

      function mapErrorMessage(message) {
    const errorMap = {
      'ent_municipio_id': 'Hubo un error en el campo Municipio',
      'tra_tipos_id': 'El tipo de trámite es requerido',
      'cli_directo_id': 'El cliente directo es requerido',
      'cli_directo_ejecutivo_id': 'El ejecutivo del cliente es requerido',
      'contrato': 'El campo contrato es requerido',
      'unidad': 'El campo unidad es requerido',
      'serie': 'El campo serie es requerido',
      'placas': 'El campo placas es requerido'
    };

    if (typeof message !== 'string') {
      return 'Ocurrió un error desconocido.';
    }

    for (let key in errorMap) {
      if (message.includes(key)) {
        return errorMap[key];
      }
    }
    return 'Ocurrió un error desconocido.';
    }

  function authorizeTramite(tramiteId, status_id) {
    if (confirm('¿Estás seguro de que deseas autorizar este trámite?')) {
        $.ajax({
            url: '/deskapp/tramites/autorizar', // Ruta hacia la función en el controlador
            type: 'POST',
            data: {
                tramite_id: tramiteId,
                status_id: status_id,
                csrf_token: $('meta[name="csrf_token"]').attr('content') // Asegúrate de incluir el token CSRF
            },
            success: function(response) {
                if (response.success) {
                    alert('Trámite autorizado correctamente.');
                    location.reload(); // Recargar la página para actualizar la lista
                } else {
                    alert('Ocurrió un error al autorizar el trámite.');
                }
            },
            error: function() {
                alert('Ocurrió un error en la solicitud.');
            }
        });
    }
}

function changeStatusTramite(tramiteId, status_id) {
  if (confirm('¿Estás seguro de que deseas cambiar el estatus de este trámite?')) {
      $.ajax({
          url: '/deskapp/tramites/change_status', // Ruta hacia la función en el controlador
          type: 'POST',
          data: {
              tramite_id: tramiteId,
              status_id: status_id,
              csrf_token: $('meta[name="csrf_token"]').attr('content') // Asegúrate de incluir el token CSRF
          },
          success: function(response) {
              if (response.success) {
                  alert('Estatus del trámite actualizado correctamente.');
                  location.reload(); // Recargar la página para actualizar la lista
              } else {
                  alert('Ocurrió un error al cambiar el estatus del trámite.');
              }
          },
          error: function() {
              alert('Ocurrió un error en la solicitud.');
          }
    });
  }
}

function cancelarTramite(tramiteId, status_id, motivo) {
  if (confirm('¿Estás seguro de que deseas cancelar este trámite?')) {
      $.ajax({
          url: '/deskapp/tramites/cancelar_tramite', // Ruta hacia la función en el controlador
          type: 'POST',
          data: {
              tramite_id: tramiteId,
              status_id: status_id,
              motivo: motivo,
              csrf_token: $('meta[name="csrf_token"]').attr('content') // Asegúrate de incluir el token CSRF
          },
          success: function(response) {
              if (response.success) {
                  alert('Trámite cancelado correctamente.');
                  location.reload(); // Recargar la página para actualizar la lista
              } else {
                  alert('Ocurrió un error al cancelar el trámite.');
              }
          },
          error: function() {
              alert('Ocurrió un error en la solicitud de cancelación.');
          }
      });
  }
}

$(document).ready(function() {
  $.fn.steps.setStep = function (step)
  {
    var self = $(this);
    if (!self.data('plugin_steps')) {
      return;
    }
    var currentIndex = self.steps('getCurrentIndex');
    // Calculates the number of missing steps to get to the desired step
    var missingSteps = Math.abs(step - currentIndex);
    // The method then determines whether to navigate forward or backward to the desired step by checking if the step parameter is greater than the current index
    var direction = step > currentIndex ? 'next' : 'previous';
    // Move forward or backward by one step each time the loop runs, until it reaches the desired step
    for(var i = 0; i < missingSteps; i++){
      self.steps(direction);
    } 
  };
  if ($.fn.steps && $("#wizard").length) {
  $("#wizard").steps({
    headerTag: "h3",
    bodyTag: "section",
    transitionEffect: "slideLeft",
    autoFocus: true,
    enableFinishButton: false,
    labels: {
      finish: "Finalizar",
      next: "Siguiente",
      previous: "Anterior",
      loading: "Cargando..."
    },
    
    onInit: function () {
        // Mueve los botones a la parte superior
        // var $buttonContainer = $(".aiia-wizard-buttons");
        // $("#wizard").prepend($buttonContainer);
        reinitializeScripts();
    },
  
    // onStepChanged: function (event, currentIndex) {
    //   // Guarda el índice de la pestaña actual en localStorage
    //   // localStorage.setItem("wizardStep", currentIndex);
    // },
    onStepChanging: function (event, currentIndex, newIndex) {
      // Validar o inicializar scripts antes de cambiar de paso
      return true; // Devuelve true para permitir el cambio de paso
    },
    onStepChanged: function (event, currentIndex, priorIndex) {
        // Reejecutar inicializaciones cuando se cambia de paso
        // reinitializeScripts();
    }

    
  });
  }
    // Reorganiza los elementos
    var wizard = $(".wizard");
    var steps = wizard.find(".steps");      // Pasos
    var actions = wizard.find(".actions"); // Botones
    var content = wizard.find(".content"); // Contenido

    // Cambiar el orden en el DOM
    wizard.append(steps);   // Mover pasos al principio
    wizard.append(actions); // Mover botones después de los pasos
    wizard.append(content); // Mover contenido al final
    function reinitializeScripts() {
        
      // Reactivación de Grocery CRUD
      if (typeof groceryCrud !== 'undefined') {
        groceryCrud();
      }   


      // Por ejemplo, inicializar un plugin de jQuery o un evento
      $('.datepicker').datepicker();
      $('.select2').select2();
    }
  // var savedStep = localStorage.getItem("wizardStep");
  
  // Si hay un paso guardado, mostrarlo al recargar la página
  if (typeof wiz_step !== 'undefined' && wiz_step !== null && $("#wizard").data('plugin_steps')) {
      console.log("wiz_step", wiz_step);
      $("#wizard").steps("setStep", parseInt(wiz_step));
  }

  $('.select2').select2({
      placeholder: 'Seleccione una opción',
      allowClear: true,
      width: '100%',
      dropdownCssClass: 'bootstrap-select',  // Aplica las clases de Bootstrap al dropdown
      selectionCssClass: 'form-control'      // Asegura que el input tenga el estilo de form-control
  });
  flatpickr('.datetime-picker', {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
  });

  $('#empresa_gestora_id').on('change', function() {
    var value = $(this).val();
    if (value) {
      loadDependentData('gestor', value, 'gestor_id');
    }
  });

  $('#cli_directo_id').on('change', function() {
    var value = $(this).val();
    if (value) {
      loadDependentData('ejecutivo', value, 'cli_directo_ejecutivo_id');
    }
  });

  $('#saveCancelBtn').on('click', function() {
    // Obtener los valores del formulario
    // var tramiteId = $('#tramite_id').val();
    var statusId = 21;
    var motivo = $('#motivo').val();

    // Validar si el campo motivo está lleno
    if (motivo.trim() === '') {
        alert('Por favor, ingresa el motivo de la cancelación.');
        return;
    }
    
    cancelarTramite(tramite_id, statusId, motivo);
    
  });

});

window.addEventListener('gcrud.datagrid.ready', () => {
    console.log('datagrid ready triggered');
});
