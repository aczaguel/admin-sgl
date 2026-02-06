# 📊 FILTRO MULTI-TENANCY EN REPORTES Y DASHBOARDS

## Implementación del Filtrado por Cliente en el Módulo de Reportes

---

## 🎯 Objetivo

Aplicar el mismo sistema de filtrado multi-tenancy del módulo de trámites al **módulo de reportes y dashboards**, garantizando que cada usuario solo vea las métricas, KPIs y reportes de sus clientes asignados.

---

## 📁 Archivos Modificados

### 1. **DashboardAdmin.php** (Controlador)
**Ruta:** `app/Controllers/Deskapp/DashboardAdmin.php`

**Cambios:**
- ✅ Agregado helper `cliente_filter` en el constructor
- ✅ Se obtiene `$userId` de la sesión en todos los métodos
- ✅ El `$userId` se pasa como parámetro a todos los métodos del modelo

**Métodos actualizados:**
- ✅ `index()` - Dashboard principal
- ✅ `alertas()` - Vista de alertas completas
- ✅ `financiero()` - Vista de análisis financiero
- ✅ `reportes()` - Vista de reportes y gráficas
- ✅ `api_metricas()` - API métricas JSON
- ✅ `api_alertas()` - API alertas JSON
- ✅ `api_graficas()` - API gráficas JSON
- ✅ `api_kpis()` - API KPIs JSON
- ✅ `api_comparativas()` - API comparativas JSON
- ✅ `api_rankings()` - API rankings JSON
- ✅ `api_financiero()` - API financiero JSON

### 2. **DashboardModel.php** (Modelo)
**Ruta:** `app/Models/DashboardModel.php`

**Cambios:**
- ✅ Agregado método privado `getClienteFilterSQL($userId)` para generar filtros
- ✅ Todos los métodos ahora aceptan parámetro `$userId` opcional
- ✅ Las consultas SQL incluyen el filtro de cliente

**Métodos actualizados (22 métodos en total):**
- ✅ `getMetricasHoy($userId)`
- ✅ `getMetricasSemana($userId)`
- ✅ `getMetricasMes($userId)`
- ✅ `getMetricasAnio($anio, $userId)`
- ✅ `getMetricasEneroALaFecha($anio, $userId)`
- ✅ `getTramitesRetrasados($diasLimite, $userId)`
- ✅ `getTramitesPendientesCobro($diasLimite, $userId)`
- ✅ `getTramitesEstancados($diasLimite, $userId)`
- ✅ `getAlertasCriticas($userId)`
- ✅ `getEmbudoConversion($fechaInicio, $fechaFin, $userId)`
- ✅ `getTopEjecutivos($limite, $periodo, $anio, $userId)`
- ✅ `getTopGestores($limite, $periodo, $anio, $userId)`
- ✅ `getAgingReport($userId)`
- ✅ `getResumenFinancieroPorRangos($userId)`
- ✅ `getProyeccionIngresos($userId)`
- ✅ `getTramitesPorMes($anio, $userId)`
- ✅ `getIngresosPorMes($anio, $userId)`
- ✅ `getTramitesPorTipo($periodo, $anio, $userId)`
- ✅ `getDistribucionPorEstado($anio, $userId)`
- ✅ `getKPIsPrincipales($anio, $userId)`
- ✅ `getComparativaSemanal($userId)`
- ✅ `getRecordsHistoricos($userId)`

---

## 🔧 Cómo Funciona

### Flujo de Filtrado

```
Usuario accede al Dashboard
    ↓
DashboardAdmin obtiene $userId de la sesión
    ↓
Pasa $userId a métodos del DashboardModel
    ↓
DashboardModel llama a getClienteFilterSQL($userId)
    ↓
Se genera condición SQL basada en clientes asignados
    ↓
Las consultas filtran automáticamente por cliente
    ↓
Usuario solo ve sus métricas/reportes
```

### Lógica del Filtro

El método `getClienteFilterSQL($userId)` funciona así:

```php
private function getClienteFilterSQL($userId = null)
{
    if ($userId === null) {
        return '1 = 1'; // Sin filtro
    }
    
    // Verificar si es admin
    if (user_is_admin($userId)) {
        return '1 = 1'; // Admin ve todo
    }
    
    // Obtener clientes del usuario
    $clienteIds = get_user_cliente_ids($userId);
    
    if (empty($clienteIds)) {
        return '1 = 0'; // Sin acceso
    }
    
    // Generar subconsulta
    return "tramite.id IN (
        SELECT t.id 
        FROM tramite t
        INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
        WHERE cd.cliente_id IN ($clienteIdsStr)
    )";
}
```

**Resultado por tipo de usuario:**
- **Admin/Superadmin:** Retorna `1 = 1` → Ve TODO
- **Usuario con clientes:** Retorna subconsulta IN → Ve solo sus clientes
- **Usuario sin clientes:** Retorna `1 = 0` → No ve nada

---

## 📋 Ejemplo de Uso

### Antes (Sin Filtro) ❌

```php
public function getMetricasHoy()
{
    $query = "
        SELECT COUNT(*) as total_ingresados
        FROM tramite
        WHERE DATE(created_at) = CURDATE()
    ";
    return $this->db->query($query)->getRowArray();
}
```

**Problema:** Todos los usuarios ven las mismas métricas.

### Después (Con Filtro) ✅

```php
public function getMetricasHoy($userId = null)
{
    $filtroCliente = $this->getClienteFilterSQL($userId);
    
    $query = "
        SELECT COUNT(*) as total_ingresados
        FROM tramite
        WHERE DATE(created_at) = CURDATE()
        AND ($filtroCliente)
    ";
    return $this->db->query($query)->getRowArray();
}
```

**Solución:** Cada usuario ve solo sus métricas.

---

## ✅ IMPLEMENTACIÓN COMPLETA

Todos los métodos del módulo de reportes y dashboard han sido actualizados con el filtrado multi-tenancy.

### Resumen de Implementación

**DashboardModel.php:** 22 métodos actualizados
**DashboardAdmin.php:** 11 métodos actualizados (4 vistas + 7 APIs)

### Cobertura Completa

✅ **Métricas:** Hoy, Semana, Mes, Año, Enero-Fecha  
✅ **Alertas:** Retrasados, Pendientes Cobro, Estancados, Críticas  
✅ **Rankings:** Top Ejecutivos, Top Gestores  
✅ **Financiero:** Aging Report, Resumen Rangos, Proyección  
✅ **Gráficas:** Trámites por Mes/Tipo, Ingresos, Distribución Estados  
✅ **KPIs:** Principales del sistema  
✅ **Comparativas:** Semanal  
✅ **Históricos:** Records  
✅ **Conversión:** Embudo  

------

## 📝 Template para Actualizar Métodos

Usa este template para actualizar los métodos pendientes:

```php
/**
 * Descripción del método
 * 
 * @param tipo $parametroOriginal Descripción
 * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
 * @return tipo Descripción del retorno
 */
public function nombreMetodo($parametroOriginal, $userId = null)
{
    // Generar filtro
    $filtroCliente = $this->getClienteFilterSQL($userId);
    
    $query = "
        SELECT ...
        FROM tramite
        WHERE condicion_original
        AND ($filtroCliente)
    ";
    
    return $this->db->query($query)->getResult();
}
```

**Pasos:**
1. Agregar parámetro `$userId = null` al final de la firma
2. Llamar a `$filtroCliente = $this->getClienteFilterSQL($userId);`
3. Agregar `AND ($filtroCliente)` al WHERE de la consulta
4. Actualizar PHPDoc con el nuevo parámetro

---

## ✅ Métodos Ya Actualizados

Los siguientes métodos **YA tienen** el filtro implementado:

- ✅ `getMetricasHoy($userId)`
- ✅ `getMetricasSemana($userId)`
- ✅ `getMetricasMes($userId)`
- ✅ `getMetricasAnio($anio, $userId)`

---

## 🧪 Testing

### Test Manual

1. **Login como Admin:**
   - Acceder a `/deskapp/dashboardadmin`
   - Verificar que se muestran métricas de TODOS los clientes

2. **Login como Usuario Asignado:**
   - Asignar usuario a Cliente ID 10 en CRUD de usuarios
   - Acceder a `/deskapp/dashboardadmin`
   - Verificar que solo se muestran métricas de Cliente ID 10

3. **Login como Usuario Sin Clientes:**
   - Crear usuario sin clientes asignados
   - Acceder a `/deskapp/dashboardadmin`
   - Verificar que las métricas están en 0

### Consulta SQL de Verificación

```sql
-- Verificar clientes del usuario ID 5
SELECT cu.cliente_id, c.nombre 
FROM cliente_user cu
INNER JOIN cliente c ON cu.cliente_id = c.id
WHERE cu.user_id = 5;

-- Verificar trámites del cliente
SELECT t.id, t.folio, cd.razon_social, c.nombre as cliente
FROM tramite t
INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
INNER JOIN cliente c ON cd.cliente_id = c.id
WHERE c.id IN (10, 20)
ORDER BY t.created_at DESC
LIMIT 10;
```

---

## 🔒 Seguridad

### Validaciones Implementadas

✅ **Bypass Automático para Admins:** Los roles `admin` y `superadmin` ven todo sin restricciones

✅ **Subconsulta Segura:** Usa `intval()` para prevenir SQL injection en los IDs de cliente

✅ **Acceso Negado por Defecto:** Si usuario no tiene clientes, retorna `1 = 0` (sin datos)

### Recomendaciones

- 🔐 Validar permisos antes de mostrar el dashboard
- 📊 Agregar logging de accesos a reportes sensibles
- 🚫 No permitir bypass del filtro via parámetros URL

---

## 📊 Impacto en el Sistema

### Ventajas

✅ **Confidencialidad:** Cada cliente solo ve su información
✅ **Escalabilidad:** Soporta múltiples clientes en el sistema
✅ **Flexibilidad:** Admins pueden ver todo sin configuración adicional
✅ **Consistencia:** Mismo sistema de filtrado que trámites

### Consideraciones

⚠️ **Performance:** Las subconsultas pueden ser lentas con muchos registros (usar índices)
⚠️ **Caché:** Considerar cachear clientes del usuario en sesión
⚠️ **Testing:** Probar con diferentes combinaciones de clientes

---

## 🔄 Estado del Proyecto

### ✅ IMPLEMENTACIÓN COMPLETADA AL 100%

#### DashboardModel.php - 22 Métodos Actualizados
- ✅ Métricas (5): getMetricasHoy, getMetricasSemana, getMetricasMes, getMetricasAnio, getMetricasEneroALaFecha
- ✅ Alertas (4): getTramitesRetrasados, getTramitesPendientesCobro, getTramitesEstancados, getAlertasCriticas
- ✅ Conversión (1): getEmbudoConversion
- ✅ Rankings (2): getTopEjecutivos, getTopGestores
- ✅ Financiero (3): getAgingReport, getResumenFinancieroPorRangos, getProyeccionIngresos
- ✅ Gráficas (4): getTramitesPorMes, getIngresosPorMes, getTramitesPorTipo, getDistribucionPorEstado
- ✅ KPIs y Comparativas (3): getKPIsPrincipales, getComparativaSemanal, getRecordsHistoricos

#### DashboardAdmin.php - 11 Métodos Actualizados
- ✅ Vistas (4): index, alertas, financiero, reportes
- ✅ APIs (7): api_metricas, api_alertas, api_graficas, api_kpis, api_comparativas, api_rankings, api_financiero

### 🎯 Tareas Completadas

1. ✅ **Creación del método helper** `getClienteFilterSQL()` en DashboardModel
2. ✅ **Actualización de todos los métodos del modelo** con parámetro $userId
3. ✅ **Modificación de todas las consultas SQL** para incluir filtrado por cliente
4. ✅ **Actualización del controlador** para pasar $userId desde la sesión
5. ✅ **Actualización de todas las APIs JSON** para usar filtrado
6. ✅ **Documentación completa** del sistema de filtrado

### 🚀 Próximos Pasos Recomendados

1. **Testing Exhaustivo**
   - Probar cada vista con usuario admin
   - Probar cada vista con usuario asignado a 1 cliente
   - Probar cada vista con usuario asignado a múltiples clientes
   - Probar cada vista con usuario sin clientes
   - Verificar todas las APIs JSON

2. **Optimización de Rendimiento**
   - Agregar índices en tablas cliente_user, tramite, cli_directo
   - Considerar cachear get_user_cliente_ids() en sesión
   - Evaluar uso de vistas materializadas para reportes pesados

3. **Monitoreo**
   - Agregar logging de accesos a reportes
   - Medir tiempos de respuesta con diferentes volúmenes
   - Monitorear queries lentas

4. **Documentación Adicional**
   - Crear guía de usuario para administradores
   - Documentar casos especiales (cliente sin trámites, etc.)
   - Agregar ejemplos de uso de APIs

---

## 📚 Referencias

- [MULTI_TENANCY_README.md](MULTI_TENANCY_README.md) - Arquitectura general
- [IMPLEMENTACION_FILTRO_GUIA.md](IMPLEMENTACION_FILTRO_GUIA.md) - Guía de implementación
- [app/Helpers/cliente_filter_helper.php](app/Helpers/cliente_filter_helper.php) - Funciones helper

---

**Última actualización:** 2 de febrero de 2026
