$(document).ready(function() {
  "use strict";
  // Asignar evento onclick utilizando delegación de eventos

  // Detectar cambios en los campos costo_tramite y deposito_gestor
  $('#costo_tramite, #deposito_gestor').on('input', function () {
    // Obtener los valores de los campos
    let costoTramite = parseFloat($('#costo_tramite').val()) || 0;
    let depositoGestor = parseFloat($('#deposito_gestor').val()) || 0;

    // Calcular la diferencia
    let saldoPendiente = depositoGestor - costoTramite;
    let saldoPendienteGestor = costoTramite - depositoGestor;

    // Determinar qué campo debe tener el valor positivo y el otro en cero
    if (saldoPendiente > 0) {
        $('#col_a_favor').val(saldoPendiente);
        $('#col_a_favor_gestor').val(0);
    } else if (saldoPendienteGestor > 0) {
        $('#col_a_favor').val(0);
        $('#col_a_favor_gestor').val(saldoPendienteGestor);
    } else {
        $('#col_a_favor, #col_a_favor_gestor').val(0);
    }

    // Variables para definir los colores
    let colorFondo = '';
    let colorTexto = 'black'; // Mayor contraste en fondos claros

    // Determinar los colores y comportamiento según el saldoPendiente
    if (saldoPendiente < 0) {
        colorFondo = '#FF9999'; // Rojo claro si es negativo
        $('#reembolso_status_id').val('21').trigger('change'); // Establecer "Pendiente"
        $('#reembolso_status_id').prop('readonly', true); // Bloquear el campo
    } else if (saldoPendiente > 0) {
        colorFondo = '#87CEEB'; // Azul claro si es positivo
        $('#reembolso_status_id').val('21').trigger('change'); // Establecer "Pendiente"
        if ($('#reembolso_status_id').val() === '24') {
            $('#reembolso_status_id').prop('readonly', true); // Bloquear el campo si es '24'
        }
    } else {
        colorFondo = 'lightgray'; // Gris claro si es cero
        $('#reembolso_status_id').val('').trigger('change'); // Volver a la opción "Seleccione"
        $('#reembolso_status_id').prop('readonly', false); // Desbloquear el campo
    }

    // Aplicar los colores a los campos
    $('#col_a_favor, #col_a_favor_gestor').css({
        'background-color': colorFondo,
        'color': colorTexto
    });

    // Aplicar estilos al campo reembolso_status_id si está en la opción "Pendiente"
    let select2Container = $('#reembolso_status_id').next('.select2-container');
    if ($('#reembolso_status_id').val() === '21') {
        select2Container.find('.select2-selection').css({
            'background-color': colorFondo,
            'color': colorTexto,
            'border-radius': '5px',
            'padding': '5px'
        });
    } else {
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
        $(this).prop('readonly', false);  // Desbloquear si seleccionan otra opción
        $(this).css('background-color', '');  // Quitar el color de fondo
        $(this).css('color', '');  // Quitar el color del texto
    }
  });

  // Función para calcular la suma total
  function calcularTotalPagoGestor() {
    let totalCostoTramite = 0;

    // Sumar todos los valores de los elementos con la clase costo_tramite
    $('.costo_tramite').each(function () {
        let valor = parseFloat($(this).val()) || 0;
        totalCostoTramite += valor;
    });

    // Obtener valores de los otros campos
    let impuestoGestoria = parseFloat($("#impuesto_gestoria").val()) || 0;
    let gestoriaComision = parseFloat($("#gestoria_comision").val()) || 0;
    let costoPaqueteria = parseFloat($("#costo_paqueteria").val()) || 0;

    // Calcular la suma total
    let sumaTotal = totalCostoTramite + impuestoGestoria + gestoriaComision + costoPaqueteria;

    // Asignar el valor calculado al campo gestor_total_pago
    $("#gestor_total_pago").val(sumaTotal);
    $("#gestor_total_pago_hidden").val(sumaTotal);
  }


  // Detectar cambios en los campos relevantes y recalcular
  $('#impuesto_gestoria, #gestoria_comision, #costo_paqueteria').on("keyup", function () {
    console.log(`Se modificó ${$(this).attr('id')}`);
    calcularTotalPagoGestor();
  });

  // Detectar cambios en cualquier campo con la clase costo_tramite y recalcular
  $(document).on("input", ".costo_tramite", function () {
    console.log('Se modificó un campo con la clase costo_tramite');
    calcularTotalPagoGestor();
  });


  function obtenerValorNumerico(selector) {
      var valor = $(selector).val();
      return valor ? parseFloat(valor) : 0;  // Devuelve 0 si el valor está vacío o no es válido
  }

  $('#costo_total').on("click", function() {
      console.log('Se hizo clic en costo_total');
      $('#costo_total').prop('readonly', true);
  });

  $('#costo_gestoria, #costo_pago_cliente, #comision_derechos, #iva').on("keyup", function() {
      console.log('Se modificó un campo');

      // Obtener y validar los valores
      var costoGestoria = obtenerValorNumerico("#costo_gestoria");
      var costoPagoCliente = obtenerValorNumerico("#costo_pago_cliente");
      var comisionDerechos = obtenerValorNumerico("#comision_derechos");
      var iva = obtenerValorNumerico("#iva");

      // Sumar los valores
      var suma = costoGestoria + costoPagoCliente + comisionDerechos + iva;

      // Actualizar el campo costo_total
      $("#costo_total").val(suma);
      $("#costo_total_hidden").val(suma);
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

  // ─── Normaliza a Title Case los filtros de Grocery CRUD Enterprise ───
  // GC Enterprise renderiza sus dropdowns con React (react-select), por lo que
  // los valores aparecen como <div role="option"> y no como <option> nativas.
  // Se usa MutationObserver para interceptarlos cuando aparecen en el DOM.

  function toTitleCase(text) {
    if (!text || !text.trim()) return text;
    return text.toLowerCase().replace(
      /(^|[\s\u00A0])([\wáéíóúüñÁÉÍÓÚÜÑ])/g,
      function (m, sep, letter) { return sep + letter.toUpperCase(); }
    );
  }

  function normalizeGcNode(node) {
    if (!node || node.nodeType !== 1) return;
    // Opciones del dropdown abierto (react-select)
    if (node.getAttribute('role') === 'option' || node.getAttribute('role') === 'listbox') {
      var opts = (node.getAttribute('role') === 'option') ? [node] : node.querySelectorAll('[role="option"]');
      opts.forEach(function (opt) {
        if (opt.dataset.gcNormalized) return;
        var t = opt.textContent.trim();
        if (t) opt.textContent = toTitleCase(t);
        opt.dataset.gcNormalized = '1';
      });
      return;
    }
    // Valor seleccionado actualmente (singleValue o placeholder de react-select)
    var singleValues = node.querySelectorAll
      ? node.querySelectorAll('[class*="singleValue"], [class*="placeholder"]')
      : [];
    singleValues.forEach(function (el) {
      if (el.dataset.gcNormalized) return;
      var t = el.textContent.trim();
      if (t && t !== '…') el.textContent = toTitleCase(t);
      el.dataset.gcNormalized = '1';
    });
    // Selects nativos (fallback)
    var selects = node.querySelectorAll ? node.querySelectorAll('.grocery-crud select, .grocery-crud-body select') : [];
    selects.forEach(function (select) {
      Array.prototype.forEach.call(select.options, function (option) {
        if (option.dataset.gcNormalized) return;
        var t = option.text;
        if (t.trim()) option.text = toTitleCase(t);
        option.dataset.gcNormalized = '1';
      });
    });
  }

  // Pasada inicial (por si algo ya está en el DOM).
  normalizeGcNode(document.body);

  // Observer: actúa solo sobre nodos añadidos dentro de zonas GC.
  var gcObserver = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      mutation.addedNodes.forEach(function (node) {
        if (node.nodeType === 1) normalizeGcNode(node);
      });
    });
  });
  gcObserver.observe(document.body, { childList: true, subtree: true });

});
