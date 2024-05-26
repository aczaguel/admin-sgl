$(document).ready(function() {
  "use strict";
  console.log("comenzando");
  // Asignar evento onclick utilizando delegación de eventos

  $('body').on('click', '#gc-form-costo_total', function() {
    console.log('Se hizo clic en gc-form-costo_total');
    $('#gc-form-costo_total').prop('readonly', true);
  });
  $('body').on('keyup', '#gc-form-costo_gestoria', function() {
      console.log('Se hizo clic en gc-form-costo_gestoria');
      var suma = parseInt($("#gc-form-costo_gestoria").val()) + parseInt($("#gc-form-impuesto_gestoria").val()) + parseInt($("#gc-form-derechos_tramite").val()) + parseInt($("#gc-form-comision_derechos").val()) 
      $("#gc-form-costo_total").val(suma)
  });

  $('body').on('keyup', '#gc-form-impuesto_gestoria', function() {
    console.log('Se hizo clic en gc-form-impuesto_gestoria');
    var suma = parseInt($("#gc-form-costo_gestoria").val()) + parseInt($("#gc-form-impuesto_gestoria").val()) + parseInt($("#gc-form-derechos_tramite").val()) + parseInt($("#gc-form-comision_derechos").val()); 
    $("#gc-form-costo_total").val(suma)
  });

  $('body').on('keyup', '#gc-form-derechos_tramite', function() {
    console.log('Se hizo clic en gc-form-derechos_tramite');
    var suma = parseInt($("#gc-form-costo_gestoria").val()) + parseInt($("#gc-form-impuesto_gestoria").val()) + parseInt($("#gc-form-derechos_tramite").val()) + parseInt($("#gc-form-comision_derechos").val());
    $("#gc-form-costo_total").val(suma);
  });

  $('body').on('keyup', '#gc-form-comision_derechos', function() {
    console.log('Se hizo clic en gc-form-comision_derechos');
    var suma = parseInt($("#gc-form-costo_gestoria").val()) + parseInt($("#gc-form-impuesto_gestoria").val()) + parseInt($("#gc-form-derechos_tramite").val()) + parseInt($("#gc-form-comision_derechos").val());
    $("#gc-form-costo_total").val(suma);
  });
});

// // Obtener el elemento padre estático
// var contenedor = document.getElementById('contenedor');

// // Asignar el evento onclick al elemento padre
// contenedor.addEventListener('click', function(event) {
//     // Verificar si el evento proviene del elemento hijo "costo_referencia"
//     if (event.target.id === 'costo_gestoria') {
//         // Tu lógica aquí cuando se hace clic en "costo_referencia"
//         console.log('Se hizo clic en costo_referencia');
//     }
// });