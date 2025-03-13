
"use strict";
function loadDependentData(type, parentId, targetId, selectedId = null) {
    console.log("loadDependentData");
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
    $('#cli_directo_id').change(function() {
        var clienteDirectoId = $(this).val();
        if(clienteDirectoId) {
            $.ajax({
                url: '/public/deskapp/tramites/getEjecutivosByClienteId/' + clienteDirectoId,
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
          url: '/public/deskapp/tramites/getGestoresByEmpresaId/' + empresaGestoraId,
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
        loadDependentData('gestor', $(this).val(), 'gestor_id');
    });

    $('#cli_directo_id').on('change', function() {
        console.log("cargando ejecutivos");
        loadDependentData('ejecutivo', $(this).val(), 'cli_directo_ejecutivo_id');
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
  $('#tramiteForm').on('submit', function(event) {
    event.preventDefault();

    let form = event.target;
    let formData = new FormData(form);
    let hasErrors = false;

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
      if (data.success) {
        window.location.href = data.redirect;
      } else {
        let errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
        errorAlert.setAttribute('role', 'alert');

        let errorList = '<strong>Error:</strong> No se pudo guardar el trámite. Por favor, revise los campos marcados.';
        
        if (data !== "undefined" && data.success === false) {
          if (typeof data.message !== "undefined") {
            errorList += `<li>${mapErrorMessage(data.message)}</li>`;
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

    for (let key in errorMap) {
        if (message.includes(key)) {
            return errorMap[key];
        }
    }
    return 'Ocurrió un error desconocido.';
  }
  // Form submission handler
  // document.getElementById('derechosForm').addEventListener('submit', function(event) {
  //   event.preventDefault(); // Prevent form from submitting the traditional way

  //   let form = event.target;
  //   let formData = new FormData(form);
  //   let hasErrors = false;

  //   // Remove existing error messages
  //   form.querySelectorAll('.error-message').forEach(function(el) {
  //     el.textContent = '';
  //   });

  //   // Perform form validation
  //   if (!form.checkValidity()) {
  //     form.classList.add('was-validated');
  //     hasErrors = true;
  //   }

  //   // Show error message if there are errors
  //   if (hasErrors) {
  //     let errorAlert = document.createElement('div');
  //     errorAlert.className = 'alert alert-danger';
  //     errorAlert.innerHTML = '<strong>Error:</strong> Por favor, corrija los campos marcados.';
  //     form.prepend(errorAlert);
  //     return;
  //   }

  //   // Submit form via fetch
  //   fetch(form.action, {
  //     method: form.method,
  //     body: formData,
  //     headers: {
  //       'X-Requested-With': 'XMLHttpRequest'
  //     }
  //   }).then(response => response.json())
  //   .then(data => {
  //     if (data.success) {
  //         // Redirect or show success message
  //         window.location.href = data.redirect;// '/public/deskapp/tramites/tramite';
  //     } else {
  //         // Create a container for error messages
  //         let errorAlert = document.createElement('div');
  //         errorAlert.className = 'alert alert-danger alert-dismissible fade show';
  //         errorAlert.setAttribute('role', 'alert');
          
  //         // Add the close button
  //         let closeButton = document.createElement('button');
  //         closeButton.type = 'button';
  //         closeButton.className = 'btn-close';
  //         closeButton.setAttribute('data-bs-dismiss', 'alert');
  //         closeButton.setAttribute('aria-label', 'Close');
  
  //         let errorList = '<strong>Error:</strong> No se pudo guardar el trámite. Por favor, revise los campos marcados.';// <ul>
          
  //         if (data !== "undefined" && data.success === false) {
  //           if (typeof data.message !== "undefined") {
  //               errorList += `<li>${data.message}</li>`;
  //           } else {
  //               if (typeof data.errors !== "undefined") {
  //                   // Recorre los errores específicos de los campos
  //                   for (let field in data.errors) {
  //                       if (data.errors.hasOwnProperty(field)) {
  //                           errorList += `<li>${data.errors[field]}</li>`;
  //                       }
  //                   }
  //               } else {
  //                   errorList += `<li>Ocurrió un error desconocido.</li>`;
  //               }
  //           }
  //       }

  //         // Show server-side validation errors
  //         // for (let field in data.errors) {
  //         //     let errorElement = form.querySelector(`[id="${field}"]`).closest('.col-sm-8').querySelector('.error-message');
  //         //     if (!errorElement) {
  //         //         // Create an error message element if it doesn't exist
  //         //         errorElement = document.createElement('div');
  //         //         errorElement.className = 'error-message';
  //         //         form.querySelector(`[id="${field}"]`).closest('.col-sm-8').appendChild(errorElement);
  //         //     }
  //         //     errorElement.textContent = data.errors[field];
  //         //  
  //         // Append the error message to the error list
  //         //     errorList += `<li>${data.errors[field]}</li>`;
  //         // }
  //         // errorList += '</ul>';

  //         errorAlert.innerHTML = errorList;
  
  //         // Append the close button to the alert
  //         //errorAlert.appendChild(closeButton);
  
  //         // Prepend the error alert to the form
  //         form.prepend(errorAlert);
  //     }
  //   }).catch(error => {
  //       let errorAlert = document.createElement('div');
  //       errorAlert.className = 'alert alert-danger alert-dismissible fade show';
  //       errorAlert.setAttribute('role', 'alert');
        
  //       // Add the close button
  //       let closeButton = document.createElement('button');
  //       closeButton.type = 'button';
  //       closeButton.className = 'btn-close';
  //       closeButton.setAttribute('data-bs-dismiss', 'alert');
  //       closeButton.setAttribute('aria-label', 'Close');
    
  //       errorAlert.innerHTML = `<strong>Error:</strong> Ocurrió un error al guardar el trámite. ${error.message}`;
        
  //       // Append the close button to the alert
  //       //errorAlert.appendChild(closeButton);
    
  //       form.prepend(errorAlert);
  //   });
  // });

  // document.getElementById('bancarioForm').addEventListener('submit', function(event) {
  //   event.preventDefault(); // Prevent form from submitting the traditional way

  //   let form = event.target;
  //   let formData = new FormData(form);
  //   let hasErrors = false;

  //   // Remove existing error messages
  //   form.querySelectorAll('.error-message').forEach(function(el) {
  //     el.textContent = '';
  //   });

  //   // Perform form validation
  //   if (!form.checkValidity()) {
  //     form.classList.add('was-validated');
  //     hasErrors = true;
  //   }

  //   // Show error message if there are errors
  //   if (hasErrors) {
  //     let errorAlert = document.createElement('div');
  //     errorAlert.className = 'alert alert-danger';
  //     errorAlert.innerHTML = '<strong>Error:</strong> Por favor, corrija los campos marcados.';
  //     form.prepend(errorAlert);
  //     return;
  //   }

  //   // Submit form via fetch
  //   fetch(form.action, {
  //     method: form.method,
  //     body: formData,
  //     headers: {
  //       'X-Requested-With': 'XMLHttpRequest'
  //     }
  //   }).then(response => response.json())
  //   .then(data => {
  //     if (data.success) {
  //         // Redirect or show success message
  //         window.location.href = data.redirect;// '/public/deskapp/tramites/tramite';
  //     } else {
  //         // Create a container for error messages
  //         let errorAlert = document.createElement('div');
  //         errorAlert.className = 'alert alert-danger alert-dismissible fade show';
  //         errorAlert.setAttribute('role', 'alert');
          
  //         // Add the close button
  //         let closeButton = document.createElement('button');
  //         closeButton.type = 'button';
  //         closeButton.className = 'btn-close';
  //         closeButton.setAttribute('data-bs-dismiss', 'alert');
  //         closeButton.setAttribute('aria-label', 'Close');
  
  //         let errorList = '<strong>Error:</strong> No se pudo guardar el trámite. Por favor, revise los campos marcados.';// <ul>
          
  //         if (data !== "undefined" && data.success === false) {
  //           if (typeof data.message !== "undefined") {
  //               errorList += `<li>${data.message}</li>`;
  //           } else {
  //               if (typeof data.errors !== "undefined") {
  //                   // Recorre los errores específicos de los campos
  //                   for (let field in data.errors) {
  //                       if (data.errors.hasOwnProperty(field)) {
  //                           errorList += `<li>${data.errors[field]}</li>`;
  //                       }
  //                   }
  //               } else {
  //                   errorList += `<li>Ocurrió un error desconocido.</li>`;
  //               }
  //           }
  //       }

  //         errorAlert.innerHTML = errorList;
  //         form.prepend(errorAlert);
  //     }
  //   }).catch(error => {
  //       let errorAlert = document.createElement('div');
  //       errorAlert.className = 'alert alert-danger alert-dismissible fade show';
  //       errorAlert.setAttribute('role', 'alert');
        
  //       // Add the close button
  //       let closeButton = document.createElement('button');
  //       closeButton.type = 'button';
  //       closeButton.className = 'btn-close';
  //       closeButton.setAttribute('data-bs-dismiss', 'alert');
  //       closeButton.setAttribute('aria-label', 'Close');
    
  //       errorAlert.innerHTML = `<strong>Error:</strong> Ocurrió un error al guardar el trámite. ${error.message}`;
        
  //       // Append the close button to the alert
  //       //errorAlert.appendChild(closeButton);
    
  //       form.prepend(errorAlert);
  //   });
  // });

  // document.getElementById('finalForm').addEventListener('submit', function(event) {
  //   event.preventDefault(); // Prevent form from submitting the traditional way

  //   let form = event.target;
  //   let formData = new FormData(form);
  //   let hasErrors = false;

  //   // Remove existing error messages
  //   form.querySelectorAll('.error-message').forEach(function(el) {
  //     el.textContent = '';
  //   });

  //   // Perform form validation
  //   if (!form.checkValidity()) {
  //     form.classList.add('was-validated');
  //     hasErrors = true;
  //   }

  //   // Show error message if there are errors
  //   if (hasErrors) {
  //     let errorAlert = document.createElement('div');
  //     errorAlert.className = 'alert alert-danger';
  //     errorAlert.innerHTML = '<strong>Error:</strong> Por favor, corrija los campos marcados.';
  //     form.prepend(errorAlert);
  //     return;
  //   }

  //   // Submit form via fetch
  //   fetch(form.action, {
  //     method: form.method,
  //     body: formData,
  //     headers: {
  //       'X-Requested-With': 'XMLHttpRequest'
  //     }
  //   }).then(response => response.json())
  //   .then(data => {
  //     if (data.success) {
  //         // Redirect or show success message
  //         window.location.href = data.redirect;// '/public/deskapp/tramites/tramite';
  //     } else {
  //         // Create a container for error messages
  //         let errorAlert = document.createElement('div');
  //         errorAlert.className = 'alert alert-danger alert-dismissible fade show';
  //         errorAlert.setAttribute('role', 'alert');
          
  //         // Add the close button
  //         let closeButton = document.createElement('button');
  //         closeButton.type = 'button';
  //         closeButton.className = 'btn-close';
  //         closeButton.setAttribute('data-bs-dismiss', 'alert');
  //         closeButton.setAttribute('aria-label', 'Close');
  
  //         let errorList = '<strong>Error:</strong> No se pudo guardar el trámite. Por favor, revise los campos marcados.';// <ul>
          
  //         if (data !== "undefined" && data.success === false) {
  //           if (typeof data.message !== "undefined") {
  //               errorList += `<li>${data.message}</li>`;
  //           } else {
  //               if (typeof data.errors !== "undefined") {
  //                   // Recorre los errores específicos de los campos
  //                   for (let field in data.errors) {
  //                       if (data.errors.hasOwnProperty(field)) {
  //                           errorList += `<li>${data.errors[field]}</li>`;
  //                       }
  //                   }
  //               } else {
  //                   errorList += `<li>Ocurrió un error desconocido.</li>`;
  //               }
  //           }
  //       }
  //         errorAlert.innerHTML = errorList;
  //         form.prepend(errorAlert);
  //     }
  //   }).catch(error => {
  //       let errorAlert = document.createElement('div');
  //       errorAlert.className = 'alert alert-danger alert-dismissible fade show';
  //       errorAlert.setAttribute('role', 'alert');
        
  //       // Add the close button
  //       let closeButton = document.createElement('button');
  //       closeButton.type = 'button';
  //       closeButton.className = 'btn-close';
  //       closeButton.setAttribute('data-bs-dismiss', 'alert');
  //       closeButton.setAttribute('aria-label', 'Close');
    
  //       errorAlert.innerHTML = `<strong>Error:</strong> Ocurrió un error al guardar el trámite. ${error.message}`;

  //       form.prepend(errorAlert);
  //   });
  // });

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

function concluirTramite(tramiteId, status_id) {
    // Realizar el AJAX previo para verificar el reembolso_status_id
    $.ajax({
        url: '/deskapp/tramites/check_reembolso_status', // Ruta para consultar el estado de reembolso
        type: 'POST',
        data: {
            tramite_id: tramiteId,
            csrf_token: $('meta[name="csrf_token"]').attr('content')
        },
        success: function(response) {
            let mensajeConfirmacion;
            console.log("concluirTramite");
            console.log("response", response);
            // Verificar si hay reembolso pendiente
            if (response.reembolso_pendiente) {
                mensajeConfirmacion = 'Este trámite tiene un reembolso pendiente. ¿Estás seguro de que deseas cambiar el estatus?';
            } else {
                mensajeConfirmacion = '¿Estás seguro de que deseas cambiar el estatus de este trámite?';
            }

            // Mostrar el mensaje en el confirm()
            if (confirm(mensajeConfirmacion)) {
                // Realizar la segunda solicitud AJAX para cambiar el estatus
                $.ajax({
                    url: '/deskapp/tramites/change_status',
                    type: 'POST',
                    data: {
                        tramite_id: tramiteId,
                        status_id: status_id,
                        csrf_token: $('meta[name="csrf_token"]').attr('content')
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
        },
        error: function() {
            alert('Ocurrió un error al consultar el estado del trámite.');
        }
    });
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
        // Inicializar Dropzone
      if (Dropzone.instances.length > 0) {
        Dropzone.instances.forEach(function (dz) {
            dz.destroy(); // Destruir instancias previas para evitar conflictos
        });
      }
      // Reactivación de Grocery CRUD
      if (typeof groceryCrud !== 'undefined') {
        groceryCrud();
      }   

      $(document).ready(function () {
        // Renombrados por categoría
        const renamedFilesDocumentos = {};
        const renamedFilesGestor = {};
        const renamedFilesCliente = {};
        Dropzone.autoDiscover = false;
        // Dropzone para Documentos
        const dropzoneDocumentos = new Dropzone(".dropzone-documentos", {
            url: "/deskapp/tramites/upload_comprobante/" + tramite_id,
            autoProcessQueue: false,
            maxFilesize: 10,
            // acceptedFiles: "image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/xml",
            acceptedFiles: ".xml,.jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx", // Agrega .xml aquí
            addRemoveLinks: true,
            dictRemoveFile: "Quitar",

            renameFile: function (file) {
                const randomHex = '-' + Array.from(crypto.getRandomValues(new Uint8Array(3)))
                    .map(byte => byte.toString(16).padStart(2, '0'))
                    .join('');
                const originalName = file.name.split('.').slice(0, -1).join('.');
                const extension = file.name.split('.').pop();
                const newname = originalName + randomHex + '.' + extension;

                if (file.upload) {
                    renamedFilesDocumentos[file.upload.uuid] = newname;
                }
                return newname;
            },

            init: function () {
                this.on("removedfile", function (file) {
                    const renamedName = file.upload ? file.upload.filename : null;
                    if (!renamedName) return;

                    $.ajax({
                        url: "/deskapp/tramites/delete_comprobante",
                        type: "POST",
                        data: { tramite_id: tramite_id, file: renamedName },
                        success: function (response) {
                            if (response.success) {
                                console.log(response.message);
                                $(`#documentos-container img[data-file="${renamedName}"]`).remove();
                            }
                        },
                        error: function () {
                            console.error("Error al eliminar archivo.");
                        },
                    });

                    if (file.upload) delete renamedFilesDocumentos[file.upload.uuid];
                });

                this.on("success", function (file, response) {
                    console.log("success upload");
                    console.log(response);
                    console.log(file);
                
                    if (response.success && response.filePath) {
                        // Obtener la extensión del archivo
                        const filePath = response.filePath || file.name; // Prioriza el filePath si está disponible
                        const fileExtension = filePath.split('.').pop().toLowerCase();
                
                        // Verificar si el archivo es una imagen
                        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension);
                
                        // Crear el HTML dinámico
                        const filePreview = `
                            <div class="col-md-1 mb-3 text-center">
                                <div class="file-preview" style="border: 1px solid #ddd; border-radius: 5px; padding: 5px; background-color: #f9f9f9;">
                                    ${isImage ? `
                                        <a href="${response.filePath}" target="_blank">
                                            <img src="${response.filePath}" 
                                                 alt="${response.filePath || file.name}" 
                                                 class="img-thumbnail" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        </a>
                                    ` : `
                                        <a href="${response.filePath}" target="_blank">
                                            <img src="${response.icon || '/path/to/default-icon.png'}" 
                                                 alt="File Icon" 
                                                 class="img-thumbnail" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        </a>
                                    `}
                                    <p style="font-size: 10px; margin-top: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        ${file.upload.filename}
                                    </p>
                                </div>
                            </div>
                        `;
                
                        // Agregar el HTML al contenedor
                        $("#documentos-container").append(filePreview);
                    }
                });
                
            },
        });

        $("#btnSubirDocumentos").on("click", function () {
            if (dropzoneDocumentos.files.length > 0) {
                dropzoneDocumentos.processQueue();
            } else {
                console.log("No hay archivos para subir en documentos.");
            }
        });

        // Dropzone para Pago a Gestor
        const dropzoneGestor = new Dropzone(".dropzone-gestor", {
            url: "/deskapp/tramites/upload_pago_gestor/" + tramite_id,
            autoProcessQueue: false,
            maxFilesize: 10,
            // acceptedFiles: "image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/xml",
            acceptedFiles: ".xml,.jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx", // Agrega .xml aquí
            addRemoveLinks: true,
            dictRemoveFile: "Quitar",

            renameFile: function (file) {
                const randomHex = '-' + Array.from(crypto.getRandomValues(new Uint8Array(3)))
                    .map(byte => byte.toString(16).padStart(2, '0'))
                    .join('');
                const originalName = file.name.split('.').slice(0, -1).join('.');
                const extension = file.name.split('.').pop();
                const newname = originalName + randomHex + '.' + extension;

                if (file.upload) {
                    renamedFilesGestor[file.upload.uuid] = newname;
                }
                return newname;
            },

            init: function () {
                this.on("removedfile", function (file) {
                    const renamedName = file.upload ? file.upload.filename : null;
                    if (!renamedName) return;

                    $.ajax({
                        url: "/deskapp/tramites/delete_pago_gestor",
                        type: "POST",
                        data: { tramite_id: tramite_id, file: renamedName },
                        success: function (response) {
                            if (response.success) {
                                console.log(response.message);
                                $(`#gestor-container img[data-file="${renamedName}"]`).remove();
                            }
                        },
                        error: function () {
                            console.error("Error al eliminar archivo.");
                        },
                    });

                    if (file.upload) delete renamedFilesGestor[file.upload.uuid];
                });

                this.on("success", function (file, response) {
                    if (response.success && response.filePath) {
                        const imgElement = `
                            <img src="${response.filePath}" alt="${response.originalName}" data-file="${response.fileName}" class="uploaded-img">
                        `;
                        $("#gestor-container").append(imgElement);
                    }
                });
            },
        });


        $("#btnSubirGestor").on("click", function () {
            if (dropzoneGestor.files.length > 0) {
                dropzoneGestor.processQueue();
            } else {
                console.log("No hay archivos para subir en pago a gestor.");
            }
        });

        // Dropzone para Cobro a Cliente
        const dropzoneCliente = new Dropzone(".dropzone-cliente", {
            url: "/deskapp/tramites/upload_cobro_cliente/" + tramite_id,
            autoProcessQueue: false,
            maxFilesize: 10,
            // acceptedFiles: "image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/xml,",
            acceptedFiles: ".xml,.jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx", // Agrega .xml aquí

            addRemoveLinks: true,
            dictRemoveFile: "Quitar",

            renameFile: function (file) {
                const randomHex = '-' + Array.from(crypto.getRandomValues(new Uint8Array(3)))
                    .map(byte => byte.toString(16).padStart(2, '0'))
                    .join('');
                const originalName = file.name.split('.').slice(0, -1).join('.');
                const extension = file.name.split('.').pop();
                const newname = originalName + randomHex + '.' + extension;

                if (file.upload) {
                    renamedFilesCliente[file.upload.uuid] = newname;
                }
                return newname;
            },

            init: function () {
                this.on("removedfile", function (file) {
                    const renamedName = file.upload ? file.upload.filename : null;
                    if (!renamedName) return;

                    $.ajax({
                        url: "/deskapp/tramites/delete_cobro_cliente",
                        type: "POST",
                        data: { tramite_id: tramite_id, file: renamedName },
                        success: function (response) {
                            if (response.success) {
                                console.log(response.message);
                                $(`#cliente-container img[data-file="${renamedName}"]`).remove();
                            }
                        },
                        error: function () {
                            console.error("Error al eliminar archivo.");
                        },
                    });

                    if (file.upload) delete renamedFilesCliente[file.upload.uuid];
                });

                this.on("success", function (file, response) {
                    if (response.success && response.filePath) {
                        const imgElement = `
                            <img src="${response.filePath}" alt="${response.originalName}" data-file="${response.fileName}" class="uploaded-img">
                        `;
                        $("#cliente-container").append(imgElement);
                    }
                });
            },
        });

        $("#btnSubirCliente").on("click", function () {
            if (dropzoneCliente.files.length > 0) {
                dropzoneCliente.processQueue();
            } else {
                console.log("No hay archivos para subir en cobro a cliente.");
            }
        });


    });



      // Por ejemplo, inicializar un plugin de jQuery o un evento
      $('.datepicker').datepicker();
      $('.select2').select2();
    }
  // var savedStep = localStorage.getItem("wizardStep");
  
  // Si hay un paso guardado, mostrarlo al recargar la página
  if (wiz_step !== null) {
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

  $('#tramiteForm').on('submit', function(e) {
    e.preventDefault(); // Evitar que el formulario haga un submit normal
    // Recoger los datos del formulario
    var formData = $(this).serialize();
    console.log("formData", formData);
    if (typeof tramite_id !== 'undefined' && tramite_id) {
      var url = '/deskapp/tramites/update_save/' + tramite_id;
    } else {
        var url = '/deskapp/tramites/insert';
    }
  

    $.ajax({
        url: url, // URL a donde va la solicitud
        type: 'POST',
        data: formData, // Datos del formulario
        success: function(response) {
            console.log("response ", response);
            if (response.from == 'insert') {
                window.location.href = response.redirect;
            } 
            // Manejamos la respuesta del servidor
            $('#tramite_mensaje').html(response.message); // Muestra la respuesta en un div
            $('#tramite_respuesta').show(); // Mostramos el alert
            // Ocultar el mensaje automáticamente después de 5 segundos
            setTimeout(function() {
                $('#tramite_respuesta').fadeOut('slow'); // Desaparece suavemente
            }, 3000); // 3000 milisegundos = 3 segundos
        },
        error: function(xhr, status, error) {
            // Manejamos el error si ocurre
            console.log(xhr.responseText);
            $('#tramite_mensaje_error').html(response.message);
            $('#tramite_respuesta_error').show(); // Mostramos el alert
            
            // Ocultar el mensaje automáticamente después de 5 segundos
            setTimeout(function() {
                $('#tramite_respuesta_error').fadeOut('slow'); // Desaparece suavemente
            }, 5000); // 5000 milisegundos = 5 segundos
        }
    });
  });


  // $('#tramiteForm').on('submit', function(event) {
  //   event.preventDefault();

  //   let form = event.target;
  //   let formData = new FormData(form);
  //   let hasErrors = false;

  //   form.querySelectorAll('.error-message').forEach(el => el.textContent = '');

  //   if (!form.checkValidity()) {
  //     form.classList.add('was-validated');
  //     hasErrors = true;
  //   }

  //   if (hasErrors) {
  //     let errorAlert = document.createElement('div');
  //     errorAlert.className = 'alert alert-danger';
  //     errorAlert.innerHTML = '<strong>Error:</strong> Por favor, corrija los campos marcados.';
  //     form.prepend(errorAlert);
  //     return;
  //   }

  //   fetch(form.action, {
  //     method: form.method,
  //     body: formData,
  //     headers: { 'X-Requested-With': 'XMLHttpRequest' }
  //   })
  //   .then(response => response.json())
  //   .then(data => {
  //     if (data.success) {
  //       window.location.href = data.redirect;
  //     } else {
  //       let errorAlert = document.createElement('div');
  //       errorAlert.className = 'alert alert-danger alert-dismissible fade show';
  //       errorAlert.setAttribute('role', 'alert');

  //       let errorList = '<strong>Error:</strong> No se pudo guardar el trámite. Por favor, revise los campos marcados.';
        
  //       if (data !== "undefined" && data.success === false) {
  //         if (typeof data.message !== "undefined") {
  //           errorList += `<li>${mapErrorMessage(data.message)}</li>`;
  //         } else if (typeof data.errors !== "undefined") {
  //           for (let field in data.errors) {
  //             if (data.errors.hasOwnProperty(field)) {
  //               errorList += `<li>${data.errors[field]}</li>`;
  //             }
  //           }
  //         } else {
  //           errorList += `<li>Ocurrió un error desconocido.</li>`;
  //         }
  //       }

  //       errorAlert.innerHTML = errorList;
  //       form.prepend(errorAlert);
  //     }
  //   })
  //   .catch(error => {
  //     let errorAlert = document.createElement('div');
  //     errorAlert.className = 'alert alert-danger alert-dismissible fade show';
  //     errorAlert.setAttribute('role', 'alert');
  //     errorAlert.innerHTML = `<strong>Error:</strong> Ocurrió un error al guardar el trámite. ${error.message}`;
  //     form.prepend(errorAlert);
  //   });
  // });

  $('#gestorForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    console.log("gestorForm");
    $.ajax({
        url: '/deskapp/tramites/update_gestor_save/' + tramite_id, 
        type: 'POST',
        data: formData, 
        success: function(response) {
            if(response.success === true){
                $('#gestor_mensaje').html(response.message); 
                $('#gestor_respuesta').show();
                
                setTimeout(function() {
                    $('#gestor_respuesta').fadeOut('slow'); 
                }, 3000); 
                setTimeout(function() {
                    location.reload();
              }, 500); 

            }else{
                $('#gestor_mensaje_error').html("Favor de revisar los campos requeridos");
                $('#gestor_respuesta_error').show();
                setTimeout(function() {
                    $('#gestor_respuesta_error').fadeOut('slow'); 
                }, 5000);
            }
        },
        error: function(xhr, status, error) {
            $('#gestor_mensaje_error').html(response.message);
            $('#gestor_respuesta_error').show();

            setTimeout(function() {
                $('#gestor_respuesta_error').fadeOut('slow'); 
            }, 5000);
        }
    });
  });

  $('#derechosForm').on('submit', function(e) {
    e.preventDefault(); // Evitar que el formulario haga un submit normal
    // Recoger los datos del formulario
    var formData = $(this).serialize();
    console.log(formData);
    $.ajax({
        url: '/deskapp/tramites/update_derechos_save/' + tramite_id, // URL a donde va la solicitud
        type: 'POST',
        data: formData, // Datos del formulario
        success: function(response) {
          if(response.success === true){
            $('#derechos_mensaje').html(response.message); 
            $('#derechos_respuesta').show();
            
            setTimeout(function() {
                $('#derechos_respuesta').fadeOut('slow'); 
            }, 3000); 
          }else{
              $('#derechos_mensaje_error').html("Favor de revisar los campos requeridos");
              $('#derechos_respuesta_error').show();
              setTimeout(function() {
                  $('#derechos_respuesta_error').fadeOut('slow'); 
              }, 5000);
          }
        },
        error: function(xhr, status, error) {
            // Manejamos el error si ocurre
            console.log(xhr.responseText);
            $('#derechos_mensaje_error').html(response.message);
            $('#derechos_respuesta_error').show(); // Mostramos el alert
            
            // Ocultar el mensaje automáticamente después de 5 segundos
            setTimeout(function() {
                $('#derechos_respuesta_error').fadeOut('slow'); // Desaparece suavemente
            }, 5000); // 5000 milisegundos = 5 segundos
        }
    });
  });

  $('#bancarioForm').on('submit', function(e) {
    e.preventDefault(); // Evitar que el formulario haga un submit normal
    // Recoger los datos del formulario
    var formData = $(this).serialize();
    $.ajax({
        url: '/deskapp/tramites/update_bancario_save/' + tramite_id, // URL a donde va la solicitud
        type: 'POST',
        data: formData, // Datos del formulario
        success: function(response) {
          if(response.success === true){
            $('#bancario_mensaje').html(response.message); 
            $('#bancario_respuesta').show();
            
            setTimeout(function() {
                $('#bancario_respuesta').fadeOut('slow'); 
            }, 3000); 
          }else{
              $('#bancario_mensaje_error').html("Favor de revisar los campos requeridos");
              $('#bancario_respuesta_error').show();
              setTimeout(function() {
                  $('#bancario_respuesta_error').fadeOut('slow'); 
              }, 5000);
          }
        },
        error: function(xhr, status, error) {
            // Manejamos el error si ocurre
            console.log(xhr.responseText);
            $('#bancario_mensaje_error').html(response.message);
            $('#bancario_respuesta_error').show(); // Mostramos el alert
            
            // Ocultar el mensaje automáticamente después de 5 segundos
            setTimeout(function() {
                $('#bancario_respuesta_error').fadeOut('slow'); // Desaparece suavemente
            }, 5000); // 5000 milisegundos = 5 segundos
        }
    });
  });

  $('#pagoGestorForm').on('submit', function(e) {
    e.preventDefault(); // Evitar que el formulario haga un submit normal

    // Crear un objeto FormData para recoger todos los datos, incluyendo archivos
    var formData = new FormData(this);

    $.ajax({
        url: '/deskapp/tramites/update_pago_gestor/' + tramite_id, // URL a donde va la solicitud
        type: 'POST',
        data: formData, // Datos del formulario incluyendo archivos
        processData: false, // Evitar que jQuery procese los datos
        contentType: false, // Evitar que jQuery establezca el tipo de contenido, será automático con FormData
        success: function(response) {
            if(response.success === true){
                $('#pago_gestor_mensaje').html(response.message); 
                $('#pago_gestor_respuesta').show();

                setTimeout(function() {
                    $('#pago_gestor_respuesta').fadeOut('slow'); 
                }, 3000); 
            } else {
                $('#pago_gestor_mensaje_error').html("Favor de revisar los campos requeridos");
                $('#pago_gestor_respuesta_error').show();
                setTimeout(function() {
                    $('#pago_gestor_respuesta_error').fadeOut('slow'); 
                }, 5000);
            }
        },
        error: function(xhr, status, error) {
            // Manejamos el error si ocurre
            console.log(xhr.responseText);
            $('#pago_gestor_mensaje_error').html(xhr.responseText);
            $('#pago_gestor_respuesta_error').show(); // Mostramos el alert
            
            // Ocultar el mensaje automáticamente después de 5 segundos
            setTimeout(function() {
                $('#pago_gestor_respuesta_error').fadeOut('slow'); // Desaparece suavemente
            }, 5000); // 5000 milisegundos = 5 segundos
        }
    });
  });

  $('#finalForm').on('submit', function(e) {
      e.preventDefault(); // Evitar que el formulario haga un submit normal

      $("#costo_gestoria_hidden").val($("#costo_gestoria").val());

      // Crear un objeto FormData para recoger todos los datos, incluyendo archivos
      var formData = new FormData(this);

      $.ajax({
          url: '/deskapp/tramites/update_final_save/' + tramite_id, // URL a donde va la solicitud
          type: 'POST',
          data: formData, // Datos del formulario incluyendo archivos
          processData: false, // Evitar que jQuery procese los datos
          contentType: false, // Evitar que jQuery establezca el tipo de contenido, será automático con FormData
          success: function(response) {
              if(response.success === true){
                  $('#final_mensaje').html(response.message); 
                  $('#final_respuesta').show();

                  setTimeout(function() {
                      $('#final_respuesta').fadeOut('slow'); 
                  }, 3000); 
              } else {
                  $('#final_mensaje_error').html("Favor de revisar los campos requeridos");
                  $('#final_respuesta_error').show();
                  setTimeout(function() {
                      $('#final_respuesta_error').fadeOut('slow'); 
                  }, 5000);
              }
          },
          error: function(xhr, status, error) {
              // Manejamos el error si ocurre
              console.log(xhr.responseText);
              $('#final_mensaje_error').html(xhr.responseText);
              $('#final_respuesta_error').show(); // Mostramos el alert
              
              // Ocultar el mensaje automáticamente después de 5 segundos
              setTimeout(function() {
                  $('#final_respuesta_error').fadeOut('slow'); // Desaparece suavemente
              }, 5000); // 5000 milisegundos = 5 segundos
          }
      });
  });

  $('#uploadForm').on('submit', function(e) {
    e.preventDefault(); // Evitar el comportamiento de envío normal del formulario

    // Crear un FormData para enviar el archivo y otros datos
    var formData = new FormData(this);
    var tramite_id = $('input[name="tramite_id"]').val(); // Obtener el ID del trámite

    $.ajax({
        url: '/deskapp/tramites/upload_comprobante/' + tramite_id, // URL para la solicitud
        type: 'POST',
        data: formData, // Los datos del formulario incluyendo el archivo
        contentType: false, // No establecer tipo de contenido, jQuery lo hará por nosotros
        processData: false, // No procesar los datos, jQuery lo hará por nosotros
        success: function(response) {
            if (response.success === true) {
                // Mostrar mensaje de éxito
                $('#upload_mensaje').html(response.message).show();
                $('#upload_respuesta').show();
                // Ocultar mensaje después de 3 segundos
                setTimeout(function() {
                    $('#upload_respuesta').fadeOut('slow');
                }, 3000);
            } else {
                // Mostrar mensaje de error si hay problemas con la validación
                $('#upload_mensaje_error').html(response.errors ? response.errors.image : "Error al subir la imagen").show();
                $('#upload_respuesta_error').show();
                setTimeout(function() {
                    $('#upload_respuesta_error').fadeOut('slow');
                }, 5000);
            }
        },
        error: function(xhr, status, error) {
            // Manejar errores de la solicitud AJAX
            console.log(xhr.responseText);
            $('#upload_mensaje_error').html("Error al procesar la solicitud").show();
            setTimeout(function() {
                $('#upload_mensaje_error').fadeOut('slow');
            }, 5000);
        }
    });
  }); 

  $('#empresa_gestora_id').on('change', function() {
    loadDependentData('gestor', $(this).val(), 'gestor_id');
  });

  $('#cli_directo_id').on('change', function() {
    loadDependentData('ejecutivo', $(this).val(), 'cli_directo_ejecutivo_id');
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

$(document).ready(function() {
  $('#costo_total').on('input', function () {
    // Permitir solo números y hasta dos decimales
    let value = $(this).val();
    
    // Usar expresión regular para permitir solo dos decimales
    if (!/^\d*\.?\d{0,2}$/.test(value)) {
        $(this).val(value.slice(0, -1)); // Quita el último carácter ingresado si no cumple el patrón
    }
  });

  $('#costo_gestoria').on('input', function () {
    // Permitir solo números y hasta dos decimales
    let value = $(this).val();
    
    // Usar expresión regular para permitir solo dos decimales
    if (!/^\d*\.?\d{0,2}$/.test(value)) {
        $(this).val(value.slice(0, -1)); // Quita el último carácter ingresado si no cumple el patrón
    }
  });

  $('#impuesto_gestoria').on('input', function () {
    // Permitir solo números y hasta dos decimales
    let value = $(this).val();
    
    // Usar expresión regular para permitir solo dos decimales
    if (!/^\d*\.?\d{0,2}$/.test(value)) {
        $(this).val(value.slice(0, -1)); // Quita el último carácter ingresado si no cumple el patrón
    }
  });

  $('#comision_derechos').on('input', function () {
    // Permitir solo números y hasta dos decimales
    let value = $(this).val();
    
    // Usar expresión regular para permitir solo dos decimales
    if (!/^\d*\.?\d{0,2}$/.test(value)) {
        $(this).val(value.slice(0, -1)); // Quita el último carácter ingresado si no cumple el patrón
    }
  });

  $('#iva').on('input', function () {
    // Permitir solo números y hasta dos decimales
    let value = $(this).val();
    
    // Usar expresión regular para permitir solo dos decimales
    if (!/^\d*\.?\d{0,2}$/.test(value)) {
        $(this).val(value.slice(0, -1)); // Quita el último carácter ingresado si no cumple el patrón
    }
  });

  $('#costo_pago_cliente').on('input', function () {
    // Permitir solo números y hasta dos decimales
    let value = $(this).val();
    
    // Usar expresión regular para permitir solo dos decimales
    if (!/^\d*\.?\d{0,2}$/.test(value)) {
        $(this).val(value.slice(0, -1)); // Quita el último carácter ingresado si no cumple el patrón
    }
  });

  $('#derechos_tramite').on('input', function () {
    // Permitir solo números y hasta dos decimales
    let value = $(this).val();
    
    // Usar expresión regular para permitir solo dos decimales
    if (!/^\d*\.?\d{0,2}$/.test(value)) {
        $(this).val(value.slice(0, -1)); // Quita el último carácter ingresado si no cumple el patrón
    }
  });

  $('#gestoria_comision').on('input', function () {
    // Permitir solo números y hasta dos decimales
    let value = $(this).val();
    
    // Usar expresión regular para permitir solo dos decimales
    if (!/^\d*\.?\d{0,2}$/.test(value)) {
        $(this).val(value.slice(0, -1)); // Quita el último carácter ingresado si no cumple el patrón
    }
  });

  $('#costo_paqueteria').on('input', function () {
    // Permitir solo números y hasta dos decimales
    let value = $(this).val();
    
    // Usar expresión regular para permitir solo dos decimales
    if (!/^\d*\.?\d{0,2}$/.test(value)) {
        $(this).val(value.slice(0, -1)); // Quita el último carácter ingresado si no cumple el patrón
    }
  });

  $("#reembolso_status_id").on("change", function () {
    var selectedValue = $(this).val();
    $("#reembolso_status_id_hidden").val(selectedValue);
});

  $('#gestor_total_pago').on('input', function () {
    // Permitir solo números y hasta dos decimales
    let value = $(this).val();
    // Usar expresión regular para permitir solo dos decimales
    if (!/^\d*\.?\d{0,2}$/.test(value)) {
        $(this).val(value.slice(0, -1)); // Quita el último carácter ingresado si no cumple el patrón
    }
  });

});


let previousResponseCobroCliente = null; // Variable global para almacenar el estado anterior
function fetchCobroClienteFiles() {
    $.ajax({
        url: `/deskapp/tramites/getCobroClienteFiles/${tramite_id}`, // Ruta del endpoint
        method: 'GET',
        success: function (response) {

            // Convertimos la respuesta y el estado anterior a una cadena JSON para comparación
            const currentResponse = JSON.stringify(response);

            // Si el estado actual es igual al anterior, no hacer nada
            if (currentResponse === previousResponseCobroCliente) {
                // console.log("No hay cambios, no se actualiza el contenido.");
                return;
            }

            // Actualizamos la variable global con el estado actual
            previousResponseCobroCliente = currentResponse;

            // Limpia el contenedor actual
            $("#cliente-container").empty();

            // Itera sobre los resultados y genera el HTML
            response.forEach(file => {
                const filePreview = `
                    <div class="col-md-1 mb-3 text-center">
                        <div class="file-preview" style="border: 1px solid #ddd; border-radius: 5px; padding: 5px; background-color: #f9f9f9;">
                            <a href="${file.existing_path}" target="_blank">
                                <img src="${file.icon}" 
                                     alt="${file.name}" 
                                     class="img-thumbnail" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            </a>
                            <p style="font-size: 10px; margin-top: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${file.name}
                            </p>
                        </div>
                    </div>
                `;
                $("#cliente-container").append(filePreview);
            });
        },
        error: function (xhr, status, error) {
            console.error("Error fetching files:", error);
        }
    });
}

let previousResponsePagoGestor = null; // Variable global para almacenar el estado anterior
function fetchPagoGestorFiles() {
    $.ajax({
        url: `/deskapp/tramites/getPagoGestorFiles/${tramite_id}`, // Ruta del endpoint
        method: 'GET',
        success: function (response) {

            // Convertimos la respuesta y el estado anterior a una cadena JSON para comparación
            const currentResponse = JSON.stringify(response);

            // Si el estado actual es igual al anterior, no hacer nada
            if (currentResponse === previousResponsePagoGestor) {
                // console.log("No hay cambios, no se actualiza el contenido.");
                return;
            }

            // Actualizamos la variable global con el estado actual
            previousResponsePagoGestor = currentResponse;

            // Limpia el contenedor actual
            $("#gestor-container").empty();

            // Itera sobre los resultados y genera el HTML
            response.forEach(file => {
                const filePreview = `
                    <div class="col-md-1 mb-3 text-center">
                        <div class="file-preview" style="border: 1px solid #ddd; border-radius: 5px; padding: 5px; background-color: #f9f9f9;">
                            <a href="${file.existing_path}" target="_blank">
                                <img src="${file.icon}" 
                                     alt="${file.name}" 
                                     class="img-thumbnail" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            </a>
                            <p style="font-size: 10px; margin-top: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${file.name}
                            </p>
                        </div>
                    </div>
                `;
                $("#gestor-container").append(filePreview);
            });
        },
        error: function (xhr, status, error) {
            console.error("Error fetching files:", error);
        }
    });
}

let previousResponseDerechos = null; // Variable global para almacenar el estado anterior
function fetchPagoDerechosFiles() {
    $.ajax({
        url: `/deskapp/tramites/getPagoDerechosFiles/${tramite_id}`, // Ruta del endpoint
        method: 'GET',
        success: function (response) {

            // Convertimos la respuesta y el estado anterior a una cadena JSON para comparación
            const currentResponse = JSON.stringify(response);

            // Si el estado actual es igual al anterior, no hacer nada
            if (currentResponse === previousResponseDerechos) {
                // console.log("No hay cambios, no se actualiza el contenido.");
                return;
            }

            // Actualizamos la variable global con el estado actual
            previousResponseDerechos = currentResponse;

            // Limpia el contenedor actual
            $("#documentos-container").empty();

            // Itera sobre los resultados y genera el HTML
            response.forEach(file => {
                const filePreview = `
                    <div class="col-md-1 mb-3 text-center">
                        <div class="file-preview" style="border: 1px solid #ddd; border-radius: 5px; padding: 5px; background-color: #f9f9f9;">
                            <a href="${file.existing_path}" target="_blank">
                                <img src="${file.icon}" 
                                     alt="${file.name}" 
                                     class="img-thumbnail" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            </a>
                            <p style="font-size: 10px; margin-top: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${file.name}
                            </p>
                        </div>
                    </div>
                `;
                $("#documentos-container").append(filePreview);
            });
        },
        error: function (xhr, status, error) {
            console.error("Error fetching files:", error);
        }
    });
}

// Ejecutar la función cada 3 segundos
fetchPagoDerechosFiles();
fetchPagoGestorFiles();
fetchCobroClienteFiles();
setInterval(fetchPagoDerechosFiles, 3000);
setInterval(fetchPagoGestorFiles, 3000);
setInterval(fetchCobroClienteFiles, 3000);

$(document).ready(function () {
    var tramiteId = tramite_id; // ID del trámite cargado en la URL
    var serviceList = $("#service-list");

    // 🔹 Cargar los tipos de servicio desde el backend y devolver una promesa
    function loadServiceTypes() {
        return $.ajax({
            url: "/deskapp/tramites/get_service_types",
            type: "GET",
            dataType: "json"
        }).done(function (data) {
            window.availableServices = data; // Guardamos los datos en una variable global
        }).fail(function () {
            alert("Error al cargar los tipos de servicio.");
        });
    }

    // 🔹 Cargar los servicios asociados a este trámite y devolver una promesa
    function loadServicesByTramite() {
        return $.ajax({
            url: "/deskapp/tramites/get_services_by_tramite/" + tramiteId,
            type: "GET",
            dataType: "json"
        }).done(function (data) {
            data.forEach(service => {
                addServiceRow(service.id, service.tra_tipos_id);
                addCostRow(service.id, service.costo_tramite || 0);
            });
        }).fail(function () {
            alert("Error al cargar los servicios del trámite.");
        });
    }



    // 🔹 Asegurar que primero cargue `loadServiceTypes()` antes de llamar a `loadServicesByTramite()`
    loadServiceTypes().then(() => {
        return loadServicesByTramite();
    });

    // 🔹 Agregar un nuevo servicio dinámicamente
    $("#add-service").click(function () {
        addServiceRow("");
    });

    // 🔹 Función para agregar un servicio al DOM con Select2
    function addServiceRow(asociadoId, selectedId) {
        if (!window.availableServices) {
            console.error("Los servicios aún no están cargados.");
            return;
        }

        var options = '<option value="">Seleccione un servicio</option>';
        for (const [key, value] of Object.entries(window.availableServices)) {
            options += `<option value="${key}" ${selectedId == key ? "selected" : ""}>${value}</option>`;
        }

        var row = `
        <div class="row" id="tipo_tramite_asociado_${asociadoId}">
            <div class="col-md-6">
                <div class="service-item">
                    <select class="form-control service-select">
                        ${options}
                    </select>
                    <button type="button" class="remove-service btn btn-danger btn-sm" data-asociado-id="${asociadoId}">X</button>
                </div>
            </div>
        </div>
        `;
        serviceList.append(row);

        // Inicializar Select2 en el nuevo select
        serviceList.find(".service-select").last().select2({
            width: '100%',
            placeholder: "Seleccione un servicio",
            allowClear: true
        });

        // También agregar su campo de costo
        addCostRow(asociadoId, 0);
    }
    // 🔹 Función para agregar un campo de costo
    function addCostRow(asociadoId, costoInicial) {
        if ($("#costo_tra_asoc_" + asociadoId).length > 0) return; // Evita duplicados

        var costosContainer = $("#gestor_costos_tipo_servicio");

        costosContainer.append(`
            <div class="cost-item d-flex align-items-center mb-2" id="costo_tra_asoc_${asociadoId}">
                <span class="service-name flex-grow-1">Servicio ${asociadoId}</span>
                <input type="number" class="form-control cost-input text-end mx-2" 
                    step="0.01" value="${costoInicial}" 
                    data-id="${asociadoId}" onkeyup="sumarCostos()">
                <button type="button" class="btn btn-success btn-sm save-cost" data-id="${asociadoId}">Guardar</button>
            </div>
        `);
        sumarCostos(); // Actualizar sumatoria cuando se agrega un nuevo costo
    }

    // 🔹 Eliminar un servicio con confirmación
    $(document).on("click", ".remove-service", function () {
        var serviceItem = $(this).closest(".service-item");
        var asociadoId = $(this).data("asociado-id");

        // Mensaje de confirmación
        var confirmDelete = confirm("⚠️ Al eliminar este tipo de servicio, también se borrarán los costos asociados. ¿Estás seguro?");

        if (!confirmDelete) {
            return; // Si el usuario cancela, no hacemos nada
        }
        console.log("asociadoId", asociadoId);

        // Si el usuario confirma, ejecutar la eliminación
        if (asociadoId) {
            $.ajax({
                url: "/deskapp/tramites/delete_service",
                type: "POST",
                data: { asociado_id: asociadoId },
                success: function () {
                    console.log("Eliminando tipo_tramite_asociado_", asociadoId);
                    $(`#tipo_tramite_asociado_${asociadoId}`).remove(); // Eliminar servicio
                    $(`#costo_tra_asoc_${asociadoId}`).remove(); // Eliminar costo
                    sumarCostos(); // Recalcular la sumatoria
                    alert(`✅ Tipo de servicio eliminado correctamente. ${asociadoId}`);
                },
                error: function () {
                    alert("❌ Error al eliminar el servicio.");
                }
            });
        } else {
            $(`#tipo_tramite_asociado_${asociadoId}`).remove();
            $(`#costo_tra_asoc_${asociadoId}`).remove();
        }
    });


    // 🔹 Guardar los servicios seleccionados
    $("#save-services").click(function () {
        var selectedServices = [];
        $(".service-select").each(function () {
            var serviceId = $(this).val();
            if (serviceId) {
                selectedServices.push(serviceId);
            }
        });

        if (selectedServices.length === 0) {
            alert("Seleccione al menos un servicio.");
            return;
        }

        $.ajax({
            url: "/deskapp/tramites/save_services",
            type: "POST",
            data: { tramite_id: tramiteId, services: selectedServices },
            success: function () {
                alert("Servicios guardados exitosamente.");
                window.location.reload();
            },
            error: function () {
                alert("Error al guardar los servicios.");
            }
        });
    });
});


$(document).ready(function () {
    var tramiteId = tramite_id; // ID del trámite cargado en la URL
    var costosContainer = $("#gestor_costos_tipo_servicio");
    var totalCostoInput = $("#costo_tramite");
    var totalCostoInput2 = $("#costo_tramite_total");

    // 🔹 Cargar los costos de los tipos de servicio asociados al trámite
    function loadServiceCosts() {
        $.ajax({
            url: "/deskapp/tramites/get_service_costs_by_tramite/" + tramiteId,
            type: "GET",
            dataType: "json",
            success: function (data) {
                costosContainer.empty(); // Limpiar antes de agregar

                if (data.length === 0) {
                    costosContainer.append("<p style='color:red;'>Necesita ligar un tipo de servicio en el Paso 1.</p>");
                    return;
                }
                console.log("data" , data);
                data.forEach(service => {
                    costosContainer.append(`
                        <div class="cost-item d-flex align-items-center mb-2" id="costo_tra_asoc_${service.id}">
                            <span class="service-name flex-grow-1">${service.tipo_tramite}</span>
                            <input type="number" class="form-control cost-input text-end mx-2" 
                                step="0.01" value="${service.costo_tramite || 0}" 
                                data-id="${service.id}" onkeypress="sumarCostos()">
                            <button type="button" class="btn btn-success btn-sm save-cost" data-id="${service.id}">Guardar</button>
                        </div>
                    `);
                });

                // Ejecutamos la suma inicial
                sumarCostos();
            },
            error: function () {
                costosContainer.append("<p class='text-danger'>Error al cargar los costos.</p>");
            }
        });
    }

    // 🔹 Función para sumar los costos en tiempo real
    window.sumarCostos = function () {
        var total = 0;
        $(".cost-input").each(function () {
            var value = parseFloat($(this).val()) || 0;
            total += value;
        });

        $("#costo_tramite").val(total.toFixed(2)); // Mostrar sumatoria con 2 decimales
        $("#costo_tramite_total").val(total.toFixed(2)); // Mostrar sumatoria con 2 decimales

        // Se suman también los otros datos
        calcularTotalPagoGestorLoad(total);
    };

    function calcularTotalPagoGestorLoad(total) {
        // Obtener valores de los otros campos
        let impuestoGestoria = parseFloat($("#impuesto_gestoria").val()) || 0;
        let gestoriaComision = parseFloat($("#gestoria_comision").val()) || 0;
        let costoPaqueteria = parseFloat($("#costo_paqueteria").val()) || 0;
        
        // Calcular la suma total
        let sumaTotal = impuestoGestoria + gestoriaComision + costoPaqueteria + total;
        
        // Asignar el valor calculado al campo gestor_total_pago
        $("#gestor_total_pago").val(sumaTotal);
        $("#costo_gestoria").val($("#costo_tramite").val());
    }

    // 🔹 Guardar cambios en los costos
    $(document).on("click", ".save-cost", function () {
        var costId = $(this).data("id");
        var newCost = $(this).siblings(".cost-input").val();

        $.ajax({
            url: "/deskapp/tramites/update_service_cost",
            type: "POST",
            data: { id: costId, costo_tramite: newCost },
            success: function () {
                alert("✅ Costo actualizado correctamente.");
                sumarCostos(); // Actualizar la sumatoria después de guardar
            },
            error: function () {
                alert("❌ Error al actualizar el costo.");
            }
        });
    });

    // 🔹 Cargar datos iniciales
    loadServiceCosts();
});
