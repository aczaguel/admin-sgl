# Dashboard Administrativo - Sistema SGL

## 📊 Descripción

Módulo completo de Dashboard Administrativo que permite al administrador del sistema monitorear en tiempo real el estado de los trámites, identificar problemas, y obtener métricas detalladas sobre el rendimiento del sistema.

## 🚀 Características Principales

### 1. **Indicadores Clave de Rendimiento (KPIs)**
- Trámites activos en el sistema
- Tasa de conversión mensual
- Tiempo promedio de gestión
- Monto pendiente de cobro

### 2. **Sistema de Alertas Críticas**
- ⚠️ **Trámites Retrasados**: Más de 30 días en proceso
- 💰 **Pendientes de Cobro**: Facturados hace más de 15 días sin cobrar
- 🔴 **Trámites Estancados**: Sin movimiento por más de 7 días

### 3. **Métricas por Período**
- **Hoy**: Métricas del día actual
- **Esta Semana**: Resumen semanal con comparativas
- **Este Mes**: Análisis mensual completo
- **Enero a la Fecha**: Vista acumulada del año

### 4. **Análisis Financiero**
- **Aging Report**: Cuentas por cobrar clasificadas por antigüedad
  - 0-15 días
  - 16-30 días
  - 31-60 días
  - 61-90 días
  - Más de 90 días
- Proyección de ingresos
- Análisis de rentabilidad

### 5. **Visualizaciones y Gráficas**
- Embudo de conversión (Ingresados → Cobrados)
- Distribución de trámites por estado
- Top 5 ejecutivos más eficientes
- Top 5 gestores con mejor desempeño
- Gráficas de tendencias por mes

### 6. **Reportes Exportables**
- Exportación a Excel
- Exportación a PDF
- Impresión de reportes
- Copia de datos

## 📁 Estructura de Archivos

```
app/
├── Controllers/
│   └── Deskapp/
│       └── DashboardAdmin.php          # Controlador principal
├── Models/
│   └── DashboardModel.php              # Modelo con todas las queries
├── Views/
│   └── deskapp/
│       └── dashboard/
│           ├── dashboard_admin.php     # Vista principal
│           ├── alertas.php             # Vista de alertas
│           └── financiero.php          # Vista financiera
└── Config/
    └── Routes.php                      # Rutas configuradas

public/
└── assets/
    └── js/
        └── dashboard-admin.js          # JavaScript para interactividad
```

## 🔧 Instalación

### Requisitos
- PHP 7.4 o superior
- CodeIgniter 4
- MySQL/MariaDB
- Librería ApexCharts (incluida)
- DataTables (incluida)

### Configuración

1. Los archivos ya están creados en sus ubicaciones correspondientes
2. Las rutas están configuradas en `app/Config/Routes.php`
3. No requiere configuración adicional de base de datos (usa la existente)

## 🌐 Acceso al Dashboard

### URLs Principales

1. **Dashboard Principal**
   ```
   /deskapp/dashboardadmin
   ```

2. **Alertas Críticas**
   ```
   /deskapp/dashboardadmin/alertas
   ```

3. **Análisis Financiero**
   ```
   /deskapp/dashboardadmin/financiero
   ```

4. **Reportes**
   ```
   /deskapp/dashboardadmin/reportes
   ```

### APIs JSON (para integración)

```javascript
// Obtener métricas
GET /deskapp/dashboardadmin/api_metricas?periodo=mes

// Obtener alertas
GET /deskapp/dashboardadmin/api_alertas?tipo=retrasados

// Obtener KPIs
GET /deskapp/dashboardadmin/api_kpis


## Smoke Local PHP 8.2

Para validar el login real y las APIs JSON del dashboard administrativo dentro del runtime Docker PHP 8.2:

```bash
DOCKER_APP_PORT=18080 docker compose up -d app
./admin/internal-json-smoke.sh
```

Ese smoke autentica al usuario demo y valida, entre otras, estas rutas:

- `GET /deskapp/dashboardadmin/api_kpis`
- `GET /deskapp/dashboardadmin/api_metricas`
// Obtener gráficas
GET /deskapp/dashboardadmin/api_graficas?tipo=embudo

// Obtener KPIs
GET /deskapp/dashboardadmin/api_kpis

// Obtener rankings
GET /deskapp/dashboardadmin/api_rankings?tipo=ejecutivos&limite=10

// Obtener datos financieros
GET /deskapp/dashboardadmin/api_financiero
```

## 📊 Uso del Dashboard

### Vista Principal

La vista principal muestra:
- Resumen de KPIs en tarjetas de colores
- Métricas desglosadas por período (Hoy, Semana, Mes, Año)
- Alertas críticas en pestañas
- Embudo de conversión
- Distribución por estado
- Top 5 ejecutivos y gestores

### Alertas

Muestra tres tipos de alertas en tablas separadas con DataTables:
- Puede ordenar, buscar y filtrar
- Exportar a Excel, PDF, CSV
- Imprimir reportes
- Enlaces directos a los trámites

### Análisis Financiero

- Resumen visual de cuentas por cobrar
- Aging report completo con clasificación por días
- Gráfica de barras de distribución
- Totales y subtotales
- Exportación a múltiples formatos

## 🎨 Personalización

### Modificar Límites de Alertas

Editar en `app/Models/DashboardModel.php`:

```php
// Cambiar días para trámites retrasados (default: 30)
$this->getTramitesRetrasados(45);

// Cambiar días para pendientes de cobro (default: 15)
$this->getTramitesPendientesCobro(20);

// Cambiar días para trámites estancados (default: 7)
$this->getTramitesEstancados(10);
```

### Modificar Auto-Refresh

Editar en `public/assets/js/dashboard-admin.js`:

```javascript
const DASHBOARD_CONFIG = {
    autoRefreshInterval: 300000, // Cambiar a milisegundos deseados
    // 300000 = 5 minutos
    // 600000 = 10 minutos
};
```

### Agregar Nuevas Métricas

1. Agregar query en `DashboardModel.php`:
```php
public function getMiNuevaMetrica() {
    $query = "SELECT ... FROM tramite WHERE ...";
    return $this->db->query($query)->getResultArray();
}
```

2. Agregar método en `DashboardAdmin.php`:
```php
public function api_mi_metrica() {
    $data = $this->dashboardModel->getMiNuevaMetrica();
    return $this->response->setJSON($data);
}
```

3. Actualizar vista para mostrar los datos

## 🔒 Seguridad

- Todas las rutas están protegidas con el filtro `auth`
- Los datos son escapados con `esc()` en las vistas
- Las queries usan prepared statements
- Validación de sesión en todas las peticiones

## 🐛 Solución de Problemas

### El dashboard no carga
1. Verificar que la sesión esté activa
2. Revisar permisos del usuario
3. Verificar conexión a base de datos

### Las gráficas no se muestran
1. Verificar que ApexCharts esté cargado
2. Revisar la consola del navegador para errores
3. Verificar que los datos existan en la base de datos

### Las alertas no se actualizan
1. Verificar la configuración de auto-refresh
2. Comprobar que las queries retornen datos
3. Revisar los límites de días configurados

## 📈 Queries Principales

El modelo incluye las siguientes categorías de queries:

1. **Métricas por Período**
   - `getMetricasHoy()`
   - `getMetricasSemana()`
   - `getMetricasMes()`
   - `getMetricasAnio()`
   - `getMetricasEneroALaFecha()`

2. **Alertas**
   - `getTramitesRetrasados($diasLimite)`
   - `getTramitesPendientesCobro($diasLimite)`
   - `getTramitesEstancados($diasLimite)`

3. **Análisis Financiero**
   - `getAgingReport()`
   - `getResumenFinancieroPorRangos()`
   - `getProyeccionIngresos()`

4. **Rankings**
   - `getTopEjecutivos($limite, $periodo)`
   - `getTopGestores($limite, $periodo)`

5. **Visualizaciones**
   - `getEmbudoConversion($fechaInicio, $fechaFin)`
   - `getTramitesPorMes($anio)`
   - `getIngresosPorMes($anio)`
   - `getDistribucionPorEstado()`

6. **Comparativas**
   - `getComparativaSemanal()`

7. **Records**
   - `getRecordsHistoricos()`

## 🎯 Beneficios

1. **Visibilidad Total**: El administrador ve todo lo que está pasando
2. **Alertas Proactivas**: Identifica problemas antes de que se agraven
3. **Toma de Decisiones**: Datos en tiempo real para decisiones informadas
4. **Eficiencia Operativa**: Detecta cuellos de botella
5. **Control Financiero**: Monitoreo completo de cuentas por cobrar
6. **Motivación del Equipo**: Rankings y comparativas de desempeño

## 📞 Soporte

Para preguntas o problemas:
1. Revisar la consola del navegador (F12)
2. Revisar logs de PHP
3. Verificar permisos de usuario en el sistema

## 🔄 Actualizaciones Futuras

Posibles mejoras:
- Notificaciones push en tiempo real
- Integración con WhatsApp/Email para alertas
- Dashboard personalizable por usuario
- Más filtros y opciones de búsqueda
- Machine Learning para predicciones
- Exportación automática programada
- Integración con sistema de tickets

## 📝 Notas Importantes

- El dashboard se actualiza automáticamente cada 5 minutos (configurable)
- Los datos se consultan directamente de la base de datos en tiempo real
- No requiere caché adicional
- Compatible con todos los navegadores modernos
- Responsivo para tablets y móviles
- Las exportaciones incluyen todos los datos visibles

---

**Versión:** 1.0.0  
**Fecha:** Enero 2026  
**Sistema:** SGL - Sistema de Gestión de Trámites
