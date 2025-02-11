$(document).ready(function() {
  "use strict";
  // Asignar evento onclick utilizando delegación de eventos

  $('body').on('click', '#gc-form-costo_total', function() {
    console.log('Se hizo clic en gc-form-costo_total');
    $('#gc-form-costo_total').prop('readonly', true);
  });
  $('body').on('keyup', '#gc-form-costo_gestoria', function() {
      console.log('Se hizo clic en gc-form-costo_gestoria');
      var suma = parseFloat($("#gc-form-costo_gestoria").val()) + parseFloat($("#gc-form-impuesto_gestoria").val()) + parseFloat($("#gc-form-derechos_tramite").val()) + parseFloat($("#gc-form-comision_derechos").val()) 
      $("#gc-form-costo_total").val(suma)
  });

  $('body').on('keyup', '#gc-form-impuesto_gestoria', function() {
    console.log('Se hizo clic en gc-form-impuesto_gestoria');
    var suma = parseFloat($("#gc-form-costo_gestoria").val()) + parseFloat($("#gc-form-impuesto_gestoria").val()) + parseFloat($("#gc-form-derechos_tramite").val()) + parseFloat($("#gc-form-comision_derechos").val()); 
    $("#gc-form-costo_total").val(suma)
  });

  $('body').on('keyup', '#gc-form-derechos_tramite', function() {
    console.log('Se hizo clic en gc-form-derechos_tramite');
    var suma = parseFloat($("#gc-form-costo_gestoria").val()) + parseFloat($("#gc-form-impuesto_gestoria").val()) + parseFloat($("#gc-form-derechos_tramite").val()) + parseFloat($("#gc-form-comision_derechos").val());
    $("#gc-form-costo_total").val(suma);
  });

  $('body').on('keyup', '#gc-form-comision_derechos', function() {
    console.log('Se hizo clic en gc-form-comision_derechos');
    var suma = parseFloat($("#gc-form-costo_gestoria").val()) + parseFloat($("#gc-form-impuesto_gestoria").val()) + parseFloat($("#gc-form-derechos_tramite").val()) + parseFloat($("#gc-form-comision_derechos").val());
    $("#gc-form-costo_total").val(suma);
  });

  $('#costo_total').on("click", function() {
    console.log('Se hizo clic en costo_total');
    $('#costo_total').prop('readonly', true);
  });

  
  // $('#costo_gestoria').on("keyup", function() {
  //     console.log('Se hizo clic en costo_gestoria');
  //     var suma = parseFloat($("#costo_gestoria").val()) + parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#comision_derechos").val()) 
  //     $("#costo_total").val(suma)
  // });
  // $('#impuesto_gestoria').on("keyup", function() {
  //   console.log('Se hizo clic en impuesto_gestoria');
  //   var suma = parseFloat($("#costo_gestoria").val()) + parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#comision_derechos").val()); 
  //   $("#costo_total").val(suma)
  // });
  // $('#derechos_tramite').on("keyup", function() {
  //   console.log('Se hizo clic en derechos_tramite');
  //   var suma = parseFloat($("#costo_gestoria").val()) + parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#comision_derechos").val());
  //   $("#costo_total").val(suma);
  // });
   


  // $('#gestor_total_pago').on("click", function() {
  //   console.log('Se hizo clic en gestor_total_pago');
  //   $('#gestor_total_pago').prop('readonly', true);
  // });
  // $('#costo_gestoria').on("keyup", function() {
  //   console.log('Se hizo clic en costo_gestoria');
  //   var suma = parseFloat($("#costo_gestoria").val()) + parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#gestoria_comision").val()); 
  //   $("#gestor_total_pago").val(suma)
  // });
  // $('#impuesto_gestoria').on("keyup", function() {
  //   console.log('Se hizo clic en impuesto_gestoria');
  //   var suma = parseFloat($("#costo_gestoria").val()) + parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#gestoria_comision").val()); 
  //   $("#gestor_total_pago").val(suma)
  // });
  // $('#gestoria_comision').on("keyup", function() {
  //   console.log('Se hizo clic en gestoria_comision');
  //   var suma = parseFloat($("#costo_gestoria").val()) + parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#gestoria_comision").val()); 
  //   $("#gestor_total_pago").val(suma)
  // });

// Detectar cambios en los campos costo_tramite y deposito_gestor
$('#costo_tramite, #deposito_gestor').on('input', function () {
  // Obtener los valores de los campos
  let costoTramite = parseFloat($('#costo_tramite').val()) || 0;
  let depositoGestor = parseFloat($('#deposito_gestor').val()) || 0;

  // Calcular la diferencia
  let saldoPendiente = costoTramite - depositoGestor;

  // Asignar el valor calculado al campo col_a_favor
  $('#col_a_favor').val(saldoPendiente);

  // Variables para definir los colores
  let colorFondo = '';
  let colorTexto = 'black';  // Cambié a negro para mayor contraste en fondos claros
  console.log("saldoPendiente ", saldoPendiente);
  // Determinar los colores y comportamiento según el resultado
  if (saldoPendiente < 0) {
      colorFondo = '#FF9999';  // Rojo claro si es negativo
      $('#reembolso_status_id').val('21').trigger('change');;  // Establecer "Pendiente" en el select
      $('#reembolso_status_id').prop('disabled', true);  // Bloquear el campo
  } else if (saldoPendiente > 0) {
      console.log("debería ser azul claro ");
      colorFondo = '#87CEEB';  // Azul claro si es positivo
      $('#reembolso_status_id').val('21').trigger('change');;  // Establecer "Pendiente" en el select
      if($('#reembolso_status_id').val() === 24){
        $('#reembolso_status_id').prop('disabled', true);  // Bloquear el campo
      }

  } else {
      colorFondo = 'lightgray';  // Gris claro si es cero
      $('#reembolso_status_id').val('').trigger('change');;  // Volver a la opción "Seleccione"
      $('#reembolso_status_id').prop('disabled', false);  // Desbloquear el campo
  }

  // Aplicar los colores al campo col_a_favor
  $('#col_a_favor').css({
      'background-color': colorFondo,
      'color': colorTexto
  });

  // Aplicar los colores al campo reembolso_status_id si está en la opción "Pendiente"
  if ($('#reembolso_status_id').val() === '21') {
      let select2Container = $('#reembolso_status_id').next('.select2-container');
      select2Container.find('.select2-selection').css({
          'background-color': colorFondo,
          'color': colorTexto,
          'border-radius': '5px',
          'padding': '5px'
      });
  } else {
      let select2Container = $('#reembolso_status_id').next('.select2-container');
      select2Container.find('.select2-selection').css({
          'background-color': '',
          'color': '',
          'border-radius': '5px',
          'padding': '5px'
      });
  }
});

// Permitir editar el campo reembolso_status_id si el usuario lo cambia manualmente
$('#reembolso_status_id').on('change', function () {
  if ($(this).val() !== '21') {
      $(this).prop('disabled', false);  // Desbloquear si seleccionan otra opción
      $(this).css('background-color', '');  // Quitar el color de fondo
      $(this).css('color', '');  // Quitar el color del texto
  }
});

  $('#gestor_total_pago').on("click", function() {
    console.log('Se hizo clic en gestor_total_pago');
    $('#gestor_total_pago').prop('readonly', true);
  });

  $('#impuesto_gestoria').on("keyup", function() {
    console.log('Se hizo clic en impuesto_gestoria');
    var suma = parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#gestoria_comision").val()); 
    $("#gestor_total_pago").val(suma)
  });

  $('#gestoria_comision').on("keyup", function() {
    console.log('Se hizo clic en gestoria_comision');
    var suma = parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#gestoria_comision").val()); 
    $("#gestor_total_pago").val(suma)
  });
  

function obtenerValorNumerico(selector) {
    var valor = $(selector).val();
    return valor ? parseFloat(valor) : 0;  // Devuelve 0 si el valor está vacío o no es válido
}

$('#costo_total').on("click", function() {
    console.log('Se hizo clic en costo_total');
    $('#costo_total').prop('readonly', true);
});

$('#costo_gestoria, #costo_pago_cliente, #comision_derechos').on("keyup", function() {
    console.log('Se modificó un campo');

    // Obtener y validar los valores
    var costoGestoria = obtenerValorNumerico("#costo_gestoria");
    var costoPagoCliente = obtenerValorNumerico("#costo_pago_cliente");
    var comisionDerechos = obtenerValorNumerico("#comision_derechos");

    // Sumar los valores
    var suma = costoGestoria + costoPagoCliente + comisionDerechos;

    // Actualizar el campo costo_total
    $("#costo_total").val(suma);
});



  $(document).ajaxSend(function(event, jqxhr, settings) {
    if (settings.url.includes('ajax_list')) {
        // Agrega un delay de 2 segundos antes de enviar la solicitud
        jqxhr.abort(); // Cancela la solicitud original

        setTimeout(function() {
            $.ajax(settings); // Vuelve a enviar la solicitud después del delay
        }, 5000); // 2 segundos
    }
  });

});
