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
  
  $('#costo_gestoria').on("keyup", function() {
      console.log('Se hizo clic en costo_gestoria');
      var suma = parseFloat($("#costo_gestoria").val()) + parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#comision_derechos").val()) 
      $("#costo_total").val(suma)
  });

  $('#impuesto_gestoria').on("keyup", function() {
    console.log('Se hizo clic en impuesto_gestoria');
    var suma = parseFloat($("#costo_gestoria").val()) + parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#comision_derechos").val()); 
    $("#costo_total").val(suma)
  });

  // $('#derechos_tramite').on("keyup", function() {
  //   console.log('Se hizo clic en derechos_tramite');
  //   var suma = parseFloat($("#costo_gestoria").val()) + parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#comision_derechos").val());
  //   $("#costo_total").val(suma);
  // });

  $('#comision_derechos').on("keyup", function() {
    console.log('Se hizo clic en comision_derechos');
    var suma = parseFloat($("#costo_gestoria").val()) + parseFloat($("#impuesto_gestoria").val()) + parseFloat($("#comision_derechos").val());
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
