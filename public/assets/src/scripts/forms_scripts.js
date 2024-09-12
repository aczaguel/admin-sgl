
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
      //const gestorId = '<?php echo isset($fields['gestor_id']['value']) ? $fields['gestor_id']['value'] : ''; ?>';
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
  document.getElementById('tramiteForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form from submitting the traditional way

    let form = event.target;
    let formData = new FormData(form);
    let hasErrors = false;

    // Remove existing error messages
    form.querySelectorAll('.error-message').forEach(function(el) {
      el.textContent = '';
    });

    // Perform form validation
    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      hasErrors = true;
    }

    // Show error message if there are errors
    if (hasErrors) {
      let errorAlert = document.createElement('div');
      errorAlert.className = 'alert alert-danger';
      errorAlert.innerHTML = '<strong>Error:</strong> Por favor, corrija los campos marcados.';
      form.prepend(errorAlert);
      return;
    }

    // Submit form via fetch
    fetch(form.action, {
      method: form.method,
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    }).then(response => response.json())
    .then(data => {
      if (data.success) {
          // Redirect or show success message
          window.location.href = data.redirect;// '/public/deskapp/tramites/tramite';
      } else {
          // Create a container for error messages
          let errorAlert = document.createElement('div');
          errorAlert.className = 'alert alert-danger alert-dismissible fade show';
          errorAlert.setAttribute('role', 'alert');
          
          // Add the close button
          let closeButton = document.createElement('button');
          closeButton.type = 'button';
          closeButton.className = 'btn-close';
          closeButton.setAttribute('data-bs-dismiss', 'alert');
          closeButton.setAttribute('aria-label', 'Close');
  
          let errorList = '<strong>Error:</strong> No se pudo guardar el trámite. Por favor, revise los campos marcados.';// <ul>
          
          if (data !== "undefined" && data.success === false) {
            if (typeof data.message !== "undefined") {
                errorList += `<li>${data.message}</li>`;
            } else {
                if (typeof data.errors !== "undefined") {
                    // Recorre los errores específicos de los campos
                    for (let field in data.errors) {
                        if (data.errors.hasOwnProperty(field)) {
                            errorList += `<li>${data.errors[field]}</li>`;
                        }
                    }
                } else {
                    errorList += `<li>Ocurrió un error desconocido.</li>`;
                }
            }
        }

          // Show server-side validation errors
          // for (let field in data.errors) {
          //     let errorElement = form.querySelector(`[id="${field}"]`).closest('.col-sm-8').querySelector('.error-message');
          //     if (!errorElement) {
          //         // Create an error message element if it doesn't exist
          //         errorElement = document.createElement('div');
          //         errorElement.className = 'error-message';
          //         form.querySelector(`[id="${field}"]`).closest('.col-sm-8').appendChild(errorElement);
          //     }
          //     errorElement.textContent = data.errors[field];
          //  
          // Append the error message to the error list
          //     errorList += `<li>${data.errors[field]}</li>`;
          // }
          // errorList += '</ul>';

          errorAlert.innerHTML = errorList;
  
          // Append the close button to the alert
          //errorAlert.appendChild(closeButton);
  
          // Prepend the error alert to the form
          form.prepend(errorAlert);
      }
  }).catch(error => {
      let errorAlert = document.createElement('div');
      errorAlert.className = 'alert alert-danger alert-dismissible fade show';
      errorAlert.setAttribute('role', 'alert');
      
      // Add the close button
      let closeButton = document.createElement('button');
      closeButton.type = 'button';
      closeButton.className = 'btn-close';
      closeButton.setAttribute('data-bs-dismiss', 'alert');
      closeButton.setAttribute('aria-label', 'Close');
  
      errorAlert.innerHTML = `<strong>Error:</strong> Ocurrió un error al guardar el trámite. ${error.message}`;
      
      // Append the close button to the alert
      //errorAlert.appendChild(closeButton);
  
      form.prepend(errorAlert);
  });
  });

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
  $('.select2').select2({
      placeholder: 'Seleccione una opción',
      allowClear: true,
      width: '100%',
      dropdownCssClass: 'bootstrap-select',  // Aplica las clases de Bootstrap al dropdown
      selectionCssClass: 'form-control'      // Asegura que el input tenga el estilo de form-control
  });
});