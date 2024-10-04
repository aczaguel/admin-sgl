
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

  $(document).ready(function() {
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
  });

  document.addEventListener('DOMContentLoaded', function() {
      flatpickr('.datetime-picker', {
          enableTime: true,
          dateFormat: "Y-m-d H:i",
      });
      
      // Function to load dependent data
      function loadDependentData(type, parentId, targetId, selectedId = null) {
          fetch(`/deskapp/tramites/getDependentData/${type}/${parentId}`)
              .then(response => response.json())
              .then(data => {
                  const targetElement = document.getElementById(targetId);
                  targetElement.innerHTML = '<option value="">Seleccione...</option>';
                  data.forEach(item => {
                      const option = document.createElement('option');
                      option.value = item.id;
                      option.text = item.nombre; // Adjust according to your table structure
                      if (selectedId && selectedId == item.id) {
                          option.selected = true;
                      }
                      targetElement.appendChild(option);
                  });
              })
              .catch(error => console.error('Error:', error));
      }

      // Add event listeners for your parent fields
      document.getElementById('empresa_gestora_id').addEventListener('change', function() {
          loadDependentData('gestor', this.value, 'gestor_id');
      });

      document.getElementById('cli_directo_id').addEventListener('change', function() {
          loadDependentData('ejecutivo', this.value, 'cli_directo_ejecutivo_id');
      });

      // Initial load for update forms
      const empresaGestoraId = document.getElementById('empresa_gestora_id').value;
      if (empresaGestoraId) {
          loadDependentData('gestor', empresaGestoraId, 'gestor_id', gestorId);
      }

      const cliDirectoId = document.getElementById('cli_directo_id').value;
      //const ejecutivoId = '<?php echo isset($fields['cli_directo_ejecutivo_id']['value']) ? $fields['cli_directo_ejecutivo_id']['value'] : ''; ?>';
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
    onStepChanged: function (event, currentIndex) {
      // Guarda el índice de la pestaña actual en localStorage
      localStorage.setItem("wizardStep", currentIndex);
    }
  });
  
  var savedStep = localStorage.getItem("wizardStep");

  // Si hay un paso guardado, mostrarlo al recargar la página
  if (savedStep !== null) {
      $("#wizard").steps("setStep", parseInt(savedStep));
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

  // $('#tramiteForm').on('submit', function(e) {
  //   e.preventDefault(); // Evitar que el formulario haga un submit normal
  //   // Recoger los datos del formulario
  //   var formData = $(this).serialize();
  //   $.ajax({
  //       url: '/deskapp/tramites/update_save/' + tramite_id, // URL a donde va la solicitud
  //       type: 'POST',
  //       data: formData, // Datos del formulario
  //       success: function(response) {
  //           // Manejamos la respuesta del servidor
  //           $('#tramite_mensaje').html(response.message); // Muestra la respuesta en un div
  //           $('#tramite_respuesta').show(); // Mostramos el alert
  //           // Ocultar el mensaje automáticamente después de 5 segundos
  //           setTimeout(function() {
  //               $('#tramite_respuesta').fadeOut('slow'); // Desaparece suavemente
  //           }, 3000); // 3000 milisegundos = 3 segundos
  //       },
  //       error: function(xhr, status, error) {
  //           // Manejamos el error si ocurre
  //           console.log(xhr.responseText);
  //           $('#tramite_mensaje_error').html(response.message);
  //           $('#tramite_respuesta_error').show(); // Mostramos el alert
            
  //           // Ocultar el mensaje automáticamente después de 5 segundos
  //           setTimeout(function() {
  //               $('#tramite_respuesta_error').fadeOut('slow'); // Desaparece suavemente
  //           }, 5000); // 5000 milisegundos = 5 segundos
  //       }
  //   });
  // });

  $('#gestorForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
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

  $('#finalForm').on('submit', function(e) {
    e.preventDefault(); // Evitar que el formulario haga un submit normal
    // Recoger los datos del formulario
    var formData = $(this).serialize();
    $.ajax({
        url: '/deskapp/tramites/update_final_save/' + tramite_id, // URL a donde va la solicitud
        type: 'POST',
        data: formData, // Datos del formulario
        success: function(response) {
          if(response.success === true){
            $('#final_mensaje').html(response.message); 
            $('#final_respuesta').show();
            
            setTimeout(function() {
                $('#final_respuesta').fadeOut('slow'); 
            }, 3000); 
          }else{
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
            $('#final_mensaje_error').html(response.message);
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


});