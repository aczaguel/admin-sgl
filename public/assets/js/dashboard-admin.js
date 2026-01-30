/**
 * Dashboard Admin - Scripts de Interactividad
 * Sistema de Gestión SGL
 */

// Configuración global
const DASHBOARD_CONFIG = {
    autoRefreshInterval: 300000, // 5 minutos
    baseUrl: window.location.origin,
    apiPath: '/deskapp/dashboardadmin'
};

// Variables globales
let autoRefreshTimer = null;
let charts = {};

/**
 * Inicializar Dashboard
 */
$(document).ready(function() {
    console.log('Dashboard Admin inicializado');
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Configurar auto-refresh si está habilitado
    if (localStorage.getItem('autoRefresh') === 'true') {
        iniciarAutoRefresh();
    }
    
    // Event listeners
    configurarEventListeners();
});

/**
 * Configurar Event Listeners
 */
function configurarEventListeners() {
    // Toggle auto-refresh
    $('#toggleAutoRefresh').on('change', function() {
        if ($(this).is(':checked')) {
            iniciarAutoRefresh();
            localStorage.setItem('autoRefresh', 'true');
        } else {
            detenerAutoRefresh();
            localStorage.setItem('autoRefresh', 'false');
        }
    });
    
    // Botón de refresh manual
    $('#btnRefresh').on('click', function() {
        actualizarDatos();
    });
    
    // Filtros de período
    $('.filtro-periodo').on('click', function(e) {
        e.preventDefault();
        const periodo = $(this).data('periodo');
        cargarMetricasPorPeriodo(periodo);
    });
}

/**
 * Iniciar Auto-Refresh
 */
function iniciarAutoRefresh() {
    console.log('Auto-refresh iniciado');
    autoRefreshTimer = setInterval(function() {
        actualizarDatos();
    }, DASHBOARD_CONFIG.autoRefreshInterval);
}

/**
 * Detener Auto-Refresh
 */
function detenerAutoRefresh() {
    console.log('Auto-refresh detenido');
    if (autoRefreshTimer) {
        clearInterval(autoRefreshTimer);
        autoRefreshTimer = null;
    }
}

/**
 * Actualizar todos los datos
 */
function actualizarDatos() {
    console.log('Actualizando datos del dashboard...');
    mostrarIndicadorCarga();
    
    // Actualizar métricas principales
    actualizarKPIs();
    
    // Actualizar alertas
    actualizarAlertas();
    
    // Actualizar gráficas
    actualizarGraficas();
    
    ocultarIndicadorCarga();
}

/**
 * Actualizar KPIs principales
 */
function actualizarKPIs() {
    $.ajax({
        url: DASHBOARD_CONFIG.baseUrl + DASHBOARD_CONFIG.apiPath + '/api_kpis',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('KPIs actualizados', data);
            
            // Actualizar valores en la interfaz
            if (data) {
                $('#kpi_tramites_activos').text(formatNumber(data.tramites_activos || 0));
                $('#kpi_tasa_conversion').text((data.tasa_conversion_mes || 0) + '%');
                $('#kpi_tiempo_promedio').text((data.tiempo_promedio_gestion || 0).toFixed(1));
                $('#kpi_monto_pendiente').text('$' + formatMoney(data.monto_pendiente_cobro || 0));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al actualizar KPIs:', error);
        }
    });
}

/**
 * Actualizar alertas críticas
 */
function actualizarAlertas() {
    $.ajax({
        url: DASHBOARD_CONFIG.baseUrl + DASHBOARD_CONFIG.apiPath + '/api_alertas',
        method: 'GET',
        data: { tipo: 'todas' },
        dataType: 'json',
        success: function(data) {
            console.log('Alertas actualizadas', data);
            
            // Actualizar contadores
            if (data.tramites_retrasados) {
                $('#count_retrasados').text(data.tramites_retrasados.length);
            }
            if (data.pendientes_cobro) {
                $('#count_pendientes_cobro').text(data.pendientes_cobro.length);
            }
            if (data.tramites_estancados) {
                $('#count_estancados').text(data.tramites_estancados.length);
            }
            
            // Mostrar notificación si hay alertas críticas
            const totalAlertas = (data.tramites_retrasados?.length || 0) + 
                                (data.pendientes_cobro?.length || 0) + 
                                (data.tramites_estancados?.length || 0);
            
            if (totalAlertas > 0) {
                mostrarNotificacion('info', `Hay ${totalAlertas} alertas que requieren atención`);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al actualizar alertas:', error);
        }
    });
}

/**
 * Actualizar gráficas
 */
function actualizarGraficas() {
    // Actualizar embudo de conversión
    actualizarEmbudoConversion();
    
    // Actualizar distribución por estado
    actualizarDistribucionEstados();
}

/**
 * Actualizar Embudo de Conversión
 */
function actualizarEmbudoConversion() {
    $.ajax({
        url: DASHBOARD_CONFIG.baseUrl + DASHBOARD_CONFIG.apiPath + '/api_graficas',
        method: 'GET',
        data: { tipo: 'embudo' },
        dataType: 'json',
        success: function(data) {
            console.log('Embudo actualizado', data);
            
            if (charts.embudo) {
                // Actualizar datos existentes
                charts.embudo.updateSeries([{
                    data: [
                        data.total_ingresados || 0,
                        data.en_proceso || 0,
                        data.concluidos || 0,
                        data.facturados || 0,
                        data.cobrados || 0
                    ]
                }]);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al actualizar embudo:', error);
        }
    });
}

/**
 * Actualizar Distribución por Estado
 */
function actualizarDistribucionEstados() {
    $.ajax({
        url: DASHBOARD_CONFIG.baseUrl + DASHBOARD_CONFIG.apiPath + '/api_graficas',
        method: 'GET',
        data: { tipo: 'distribucion_estados' },
        dataType: 'json',
        success: function(data) {
            console.log('Distribución actualizada', data);
            
            if (charts.estados && data && data.length > 0) {
                const series = data.map(item => parseInt(item.cantidad));
                const labels = data.map(item => item.tra_status);
                
                charts.estados.updateOptions({
                    series: series,
                    labels: labels
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al actualizar distribución:', error);
        }
    });
}

/**
 * Cargar métricas por período
 */
function cargarMetricasPorPeriodo(periodo) {
    $.ajax({
        url: DASHBOARD_CONFIG.baseUrl + DASHBOARD_CONFIG.apiPath + '/api_metricas',
        method: 'GET',
        data: { periodo: periodo },
        dataType: 'json',
        success: function(data) {
            console.log('Métricas cargadas para periodo:', periodo, data);
            
            // Actualizar la interfaz según el período
            mostrarNotificacion('success', `Datos actualizados para el período: ${periodo}`);
            
            // Actualizar valores dinámicamente
            actualizarMetricasUI(data, periodo);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar métricas por período:', error);
            mostrarNotificacion('error', 'Error al cargar los datos');
        }
    });
}

/**
 * Actualizar métricas en la UI
 */
function actualizarMetricasUI(data, periodo) {
    // Implementar lógica de actualización según el período
    const container = $(`#metricas_${periodo}`);
    
    if (container.length) {
        container.find('.total_ingresados').text(data.total_ingresados || 0);
        container.find('.total_concluidos').text(data.total_concluidos || 0);
        container.find('.total_cobrados').text(data.total_cobrados || 0);
    }
}

/**
 * Mostrar notificación
 */
function mostrarNotificacion(tipo, mensaje) {
    let icono = 'fa-info-circle';
    let color = 'info';
    
    switch(tipo) {
        case 'success':
            icono = 'fa-check-circle';
            color = 'success';
            break;
        case 'error':
            icono = 'fa-exclamation-triangle';
            color = 'danger';
            break;
        case 'warning':
            icono = 'fa-exclamation-circle';
            color = 'warning';
            break;
    }
    
    const notificacion = `
        <div class="alert alert-${color} alert-dismissible fade show" role="alert">
            <i class="icon-copy fa ${icono}"></i> ${mensaje}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    $('#notificaciones-container').prepend(notificacion);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(function() {
        $('#notificaciones-container .alert').first().fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Mostrar indicador de carga
 */
function mostrarIndicadorCarga() {
    if ($('#loading-overlay').length === 0) {
        $('body').append(`
            <div id="loading-overlay" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.3);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Cargando...</span>
                </div>
            </div>
        `);
    } else {
        $('#loading-overlay').show();
    }
}

/**
 * Ocultar indicador de carga
 */
function ocultarIndicadorCarga() {
    $('#loading-overlay').fadeOut();
}

/**
 * Formatear números
 */
function formatNumber(num) {
    return new Intl.NumberFormat('es-MX').format(num);
}

/**
 * Formatear dinero
 */
function formatMoney(amount) {
    return new Intl.NumberFormat('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

/**
 * Exportar datos a Excel
 */
function exportarAExcel(tipo) {
    window.location.href = DASHBOARD_CONFIG.baseUrl + DASHBOARD_CONFIG.apiPath + 
                          '/exportar_excel?tipo=' + tipo;
}

/**
 * Exportar datos a PDF
 */
function exportarAPDF(tipo) {
    window.location.href = DASHBOARD_CONFIG.baseUrl + DASHBOARD_CONFIG.apiPath + 
                          '/exportar_pdf?tipo=' + tipo;
}

/**
 * Comparar períodos
 */
function compararPeriodos(periodo1, periodo2) {
    Promise.all([
        $.ajax({
            url: DASHBOARD_CONFIG.baseUrl + DASHBOARD_CONFIG.apiPath + '/api_metricas',
            data: { periodo: periodo1 }
        }),
        $.ajax({
            url: DASHBOARD_CONFIG.baseUrl + DASHBOARD_CONFIG.apiPath + '/api_metricas',
            data: { periodo: periodo2 }
        })
    ]).then(function(results) {
        const datos1 = results[0];
        const datos2 = results[1];
        
        console.log('Comparación:', {
            [periodo1]: datos1,
            [periodo2]: datos2
        });
        
        // Mostrar comparación en modal o sección dedicada
        mostrarComparacion(periodo1, datos1, periodo2, datos2);
    });
}

/**
 * Mostrar comparación de datos
 */
function mostrarComparacion(periodo1, datos1, periodo2, datos2) {
    // Implementar lógica de visualización de comparación
    console.log('Mostrando comparación entre', periodo1, 'y', periodo2);
}

/**
 * Limpiar y reiniciar dashboard
 */
function reiniciarDashboard() {
    // Detener auto-refresh
    detenerAutoRefresh();
    
    // Limpiar charts
    for (let key in charts) {
        if (charts[key] && charts[key].destroy) {
            charts[key].destroy();
        }
    }
    charts = {};
    
    // Recargar página
    location.reload();
}

/**
 * Guardar referencia a gráfica
 */
function registrarGrafica(nombre, grafica) {
    charts[nombre] = grafica;
}

// Exponer funciones globalmente
window.dashboardAdmin = {
    actualizar: actualizarDatos,
    cargarMetricas: cargarMetricasPorPeriodo,
    exportarExcel: exportarAExcel,
    exportarPDF: exportarAPDF,
    compararPeriodos: compararPeriodos,
    reiniciar: reiniciarDashboard,
    registrarGrafica: registrarGrafica
};

// Manejar errores globales de AJAX
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    console.error('Error en petición AJAX:', settings.url, thrownError);
    
    if (jqxhr.status === 401) {
        mostrarNotificacion('error', 'Sesión expirada. Por favor, inicia sesión nuevamente.');
        setTimeout(function() {
            window.location.href = '/';
        }, 2000);
    }
});
