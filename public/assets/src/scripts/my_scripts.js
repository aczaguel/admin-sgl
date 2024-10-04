$(document).ready(function() {
  "use strict";
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


  $('body').on('click', '#costo_total', function() {
    console.log('Se hizo clic en costo_total');
    $('#costo_total').prop('readonly', true);
  });
  $('body').on('keyup', '#costo_gestoria', function() {
      console.log('Se hizo clic en costo_gestoria');
      var suma = parseInt($("#costo_gestoria").val()) + parseInt($("#impuesto_gestoria").val()) + parseInt($("#derechos_tramite").val()) + parseInt($("#comision_derechos").val()) 
      $("#costo_total").val(suma)
  });

  $('body').on('keyup', '#impuesto_gestoria', function() {
    console.log('Se hizo clic en impuesto_gestoria');
    var suma = parseInt($("#costo_gestoria").val()) + parseInt($("#impuesto_gestoria").val()) + parseInt($("#derechos_tramite").val()) + parseInt($("#comision_derechos").val()); 
    $("#costo_total").val(suma)
  });

  $('body').on('keyup', '#derechos_tramite', function() {
    console.log('Se hizo clic en derechos_tramite');
    var suma = parseInt($("#costo_gestoria").val()) + parseInt($("#impuesto_gestoria").val()) + parseInt($("#derechos_tramite").val()) + parseInt($("#comision_derechos").val());
    $("#costo_total").val(suma);
  });

  $('body').on('keyup', '#comision_derechos', function() {
    console.log('Se hizo clic en comision_derechos');
    var suma = parseInt($("#costo_gestoria").val()) + parseInt($("#impuesto_gestoria").val()) + parseInt($("#derechos_tramite").val()) + parseInt($("#comision_derechos").val());
    $("#costo_total").val(suma);
  });

      // function applyBackgroundColors() {
      //     // Encuentra todas las celdas con el contenido específico y aplica el estilo al <td>
      //     document.querySelectorAll('td:has(span.background-verde)').forEach(function(td) {
      //         td.style.backgroundColor = '#a4c3b2';
      //     });
      //     document.querySelectorAll('td:has(span.background-amarillo)').forEach(function(td) {
      //         td.style.backgroundColor = '#f5e1a4';
      //     });
      //     document.querySelectorAll('td:has(span.background-rojo)').forEach(function(td) {
      //         td.style.backgroundColor = '#dba498';
      //     });
      //     document.querySelectorAll('td:has(span.background-violeta)').forEach(function(td) {
      //         td.style.backgroundColor = '#b3a2c9';
      //     });
      // }

      // // Ejecuta la función después de un breve retraso para asegurar que la tabla haya cargado
      // setTimeout(applyBackgroundColors, 1000);

      // // Opcional: Reintenta la aplicación de estilos cada segundo, en caso de cargas más lentas
      // let retries = 5;
      // const interval = setInterval(function() {
      //     if (retries > 0) {
      //         applyBackgroundColors();
      //         retries--;
      //     } else {
      //         clearInterval(interval);
      //     }
      // }, 1000);
});
