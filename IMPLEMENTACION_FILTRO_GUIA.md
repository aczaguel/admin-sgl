# 🔧 GUÍA DE IMPLEMENTACIÓN - FILTRO MULTI-TENANCY

## Para Desarrolladores

Esta guía explica cómo implementar correctamente el filtrado por cliente en nuevos controladores y métodos.

---

## 📋 Checklist Rápido

Antes de crear un nuevo controlador o método que maneje trámites:

- [ ] Cargar el helper `cliente_filter` en el constructor
- [ ] Verificar si el usuario tiene restricciones por cliente
- [ ] Aplicar el filtro en las consultas SQL
- [ ] Validar acceso a recursos específicos
- [ ] Registrar intentos de acceso no autorizado
- [ ] Probar con usuarios de diferentes clientes

---

## 🚀 Paso a Paso: Implementación en Nuevo Controlador

### 1. Cargar el Helper

En el constructor del controlador:

```php
<?php
namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

class MiNuevoControlador extends BaseController
{
    public function __construct() {
        helper(['form', 'url', 'cliente_filter']); // ← Agregar esto
    }
}
```

### 2. Verificar Restricciones del Usuario

Determinar si el usuario tiene restricciones por cliente:

```php
public function listarTramites()
{
    $session = session();
    $userId = $session->get('id');
    
    // Obtener clientes del usuario
    $clienteIds = get_user_cliente_ids($userId);
    
    // Si tiene clientes asignados, aplicar filtro
    $aplicarFiltro = !empty($clienteIds);
    
    // O verificar si es cliente externo
    $esCliente = is_user_cliente($userId);
}
```

### 3. Aplicar Filtro en Query Builder

**Método 1: Usando función helper**

```php
$db = \Config\Database::connect();
$builder = $db->table('tramite as t');

// Aplicar filtro automáticamente
$builder = apply_cliente_filter($builder, $userId);

// Continuar con la consulta
$builder->select('t.*, tt.tipo_tramite');
$builder->join('tra_tipos tt', 't.tra_tipos_id = tt.id');
$tramites = $builder->get()->getResult();
```

**Método 2: Usando SQL directo**

```php
$filterSql = get_cliente_filter_sql($userId);

$builder = $db->table('tramite');
$builder->where($filterSql, null, false);
$tramites = $builder->get()->getResult();
```

### 4. Aplicar Filtro en GroceryCrud

#### 4.1 Filtrar Registros del Listado

```php
public function tramitesCrud()
{
    $session = session();
    $userId = $session->get('id');
    
    $crud = $this->_getGroceryCrudEnterprise();
    $crud->setTable('tramite');
    
    // Verificar si debe aplicar filtro
    $clienteIds = get_user_cliente_ids($userId);
    
    if (!empty($clienteIds)) {
        // Usuario con restricciones, aplicar filtro
        $filterSql = get_cliente_filter_sql($userId);
        $crud->where($filterSql, null, false);
    }
    // Si no tiene clientes, no aplicar filtro (puede ver todo)
    
    $output = $crud->render();
    return $this->_example_output($output);
}
```

#### 4.2 Filtrar Dropdowns en Formularios (setRelation)

**⚠️ CRÍTICO PARA CONFIDENCIALIDAD:**
Los dropdowns de GroceryCrud deben mostrar solo los clientes asignados al usuario para evitar exponer nombres de otros clientes:

```php
public function tramitesCrud()
{
    $session = session();
    $userId = $session->get('id');
    
    $crud = $this->_getGroceryCrudEnterprise();
    $crud->setTable('tramite');
    
    // 1. Filtrar listado principal
    $filterSql = get_cliente_filter_sql($userId);
    $crud->where($filterSql, null, false);
    
    // 2. Filtrar dropdown de clientes directos
    // PROTECCIÓN DE CONFIDENCIALIDAD: Solo mostrar clientes asignados
    $clienteRelationFilter = get_cliente_relation_filter($userId);
    if ($clienteRelationFilter !== null) {
        // Usuario tiene restricciones, aplicar filtro
        $crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
    } else {
        // Usuario admin/sin restricciones, mostrar todos
        $crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
    }
    $crud->displayAs('cli_directo_id','Cliente Directo');
    
    $output = $crud->render();
    return $this->_example_output($output);
}
```

**Explicación del Filtro de Relaciones:**
- `get_cliente_relation_filter()` retorna `NULL` para admins (sin filtro)
- Para usuarios normales, retorna `['cliente_id' => [1, 2, 3]]`
- GroceryCrud usa esto como WHERE condition en el SELECT del dropdown
- Resultado: Dropdown solo muestra `cli_directo` de clientes asignados

**Ejemplo de SQL Generado:**
```sql
-- Para usuario con clientes 1 y 2:
SELECT id, razon_social 
FROM cli_directo 
WHERE cliente_id IN (1, 2)
ORDER BY razon_social

-- Para admin (sin filtro):
SELECT id, razon_social 
FROM cli_directo 
ORDER BY razon_social
```

**Aplicar a Múltiples Relaciones:**
```php
// Filtrar cliente directo
$clienteRelationFilter = get_cliente_relation_filter($userId);
if ($clienteRelationFilter !== null) {
    $crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
} else {
    $crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
}

// El ejecutivo se filtra automáticamente por setDependentRelation
$crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
$crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');
```

### 5. Validar Acceso a Recurso Específico

Al ver, editar o eliminar un trámite específico:

```php
public function verTramite($tramiteId)
{
    // Validar que el ID sea numérico
    if (!is_numeric($tramiteId)) {
        return redirect()->back()->with('error', 'ID inválido');
    }
    
    $session = session();
    $userId = $session->get('id');
    
    // Validar acceso
    if (!validate_tramite_access($tramiteId, $userId)) {
        // Registrar intento no autorizado
        log_unauthorized_access_attempt('tramite', $tramiteId, $userId);
        
        return redirect()->to('/tramites')
            ->with('error', '⛔ No tienes permiso para ver este trámite');
    }
    
    // Si llegamos aquí, el usuario tiene acceso
    $tramiteModel = new \App\Models\TramitesModel();
    $tramite = $tramiteModel->find($tramiteId);
    
    return view('tramites/detalle', ['tramite' => $tramite]);
}
```

### 6. Manejo de Formularios de Edición

Al actualizar un trámite:

```php
public function actualizarTramite($tramiteId)
{
    // Validar acceso ANTES de procesar el formulario
    if (!validate_tramite_access($tramiteId)) {
        log_unauthorized_access_attempt('tramite', $tramiteId);
        return redirect()->back()->with('error', 'Acceso denegado');
    }
    
    // Validar datos del formulario
    $validation = \Config\Services::validation();
    
    if (!$this->validate([
        'folio' => 'required',
        'unidad' => 'required',
        // ... más reglas
    ])) {
        return redirect()->back()->withInput()->with('errors', $validation->getErrors());
    }
    
    // Procesar actualización
    $tramiteModel = new \App\Models\TramitesModel();
    $data = [
        'folio' => $this->request->getPost('folio'),
        'unidad' => $this->request->getPost('unidad'),
        // ... más campos
    ];
    
    $tramiteModel->update($tramiteId, $data);
    
    return redirect()->to('/tramites')->with('success', 'Trámite actualizado');
}
```

---

## 🎯 Ejemplos por Tipo de Consulta

### Consulta Simple

```php
// Sin filtro (MAL) ❌
$tramites = $db->table('tramite')->get()->getResult();

// Con filtro (BIEN) ✅
$builder = $db->table('tramite');
$builder = apply_cliente_filter($builder);
$tramites = $builder->get()->getResult();
```

### Consulta con JOIN

```php
$builder = $db->table('tramite as t');
$builder->select('t.*, tt.tipo_tramite, c.nombre as cliente');
$builder->join('tra_tipos tt', 't.tra_tipos_id = tt.id');
$builder->join('cli_directo cd', 't.cli_directo_id = cd.id');
$builder->join('cliente c', 'cd.cliente_id = c.id');

// Aplicar filtro
$builder = apply_cliente_filter($builder);

$tramites = $builder->get()->getResult();
```

### Consulta con WHERE Adicional

```php
$builder = $db->table('tramite');

// Aplicar filtro por cliente
$builder = apply_cliente_filter($builder);

// Agregar condiciones adicionales
$builder->where('tra_status_id', 1);
$builder->where('fecha_conclusion IS NOT NULL');

$tramites = $builder->get()->getResult();
```

### Consulta Agregada (COUNT, SUM, etc)

```php
$builder = $db->table('tramite as t');
$builder->select('COUNT(*) as total, SUM(costo_total) as monto_total');
$builder->join('cli_directo cd', 't.cli_directo_id = cd.id');
$builder->join('cliente c', 'cd.cliente_id = c.id');

// Aplicar filtro
$clienteIds = get_user_cliente_ids();
if (!empty($clienteIds)) {
    $builder->whereIn('c.id', $clienteIds);
}

$stats = $builder->get()->getRowArray();
```

### Subconsulta

```php
$clienteIds = get_user_cliente_ids();

// Validar que tenga clientes
if (empty($clienteIds)) {
    return []; // Sin acceso
}

$clienteIdsStr = implode(',', array_map('intval', $clienteIds));

$sql = "
    SELECT 
        t.*,
        (SELECT COUNT(*) FROM tra_documentos WHERE tramite_id = t.id) as total_documentos
    FROM tramite t
    INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
    WHERE cd.cliente_id IN ($clienteIdsStr)
";

$query = $db->query($sql);
$tramites = $query->getResult();
```

---

## 🛠️ Herramientas de Debugging

### Ver Clientes Asignados

```php
$userId = session()->get('id');
$clienteIds = get_user_cliente_ids($userId);
log_message('info', 'Usuario ' . $userId . ' tiene clientes: ' . implode(',', $clienteIds));
```

### Ver SQL Generado

```php
$builder = $db->table('tramite');
$builder = apply_cliente_filter($builder);

// Ver SQL antes de ejecutar
$sql = $builder->getCompiledSelect(false);
log_message('debug', 'SQL: ' . $sql);

// Ejecutar
$tramites = $builder->get()->getResult();
```

### Verificar Acceso Manualmente

```php
// En consola o método de prueba
$userId = 5;
$tramiteId = 123;

$tieneAcceso = validate_tramite_access($tramiteId, $userId);
var_dump($tieneAcceso); // true o false
```

---

## ⚠️ Errores Comunes y Soluciones

### Error 1: "No se muestran trámites"

**Causa:** Usuario no tiene clientes asignados

**Solución:**
```php
// Verificar en base de datos
SELECT * FROM cliente_user WHERE user_id = 5;

// Si está vacío, asignar clientes desde CRUD de usuarios
```

### Error 2: "SQL syntax error near IN ()"

**Causa:** Array de clientes vacío genera `IN ()`

**Solución:**
```php
// El helper ya maneja esto, pero si usas SQL manual:
$clienteIds = get_user_cliente_ids();

if (empty($clienteIds)) {
    // Retornar vacío o mostrar mensaje
    return [];
}

$builder->whereIn('c.id', $clienteIds);
```

### Error 3: "Usuario ve trámites de otros clientes"

**Causa:** Falta aplicar el filtro en la consulta

**Solución:**
```php
// SIEMPRE aplicar el filtro cuando se consulten trámites
$builder = apply_cliente_filter($builder);
```

### Error 4: "Filtro se aplica a usuarios admin"

**Causa:** No se verifica el rol antes de aplicar filtro

**Solución:**
```php
$session = session();
$roles = $session->get('user_roles');

// Si es admin, no aplicar filtro
if (!in_array('admin', $roles)) {
    $builder = apply_cliente_filter($builder);
}
```

---

## 🧪 Testing

### Test Manual Básico

1. **Crear dos usuarios con diferentes clientes:**
   - Usuario A → Cliente 1
   - Usuario B → Cliente 2

2. **Crear trámites para cada cliente:**
   - Trámite 100 → Cliente 1
   - Trámite 200 → Cliente 2

3. **Login como Usuario A:**
   - Debe ver Trámite 100
   - NO debe ver Trámite 200

4. **Login como Usuario B:**
   - Debe ver Trámite 200
   - NO debe ver Trámite 100

5. **Intentar acceso directo:**
   - Usuario A intenta ver `/tramites/ver/200`
   - Debe ser redirigido con error

### Test de Código

```php
// En tests/app/Controllers/TramitesTest.php

public function testUsuarioSoloVeSusTramites()
{
    // Crear usuario con cliente específico
    $userId = $this->crearUsuarioConCliente(10);
    
    // Crear trámites
    $tramitePropio = $this->crearTramite(['cliente_id' => 10]);
    $tramiteAjeno = $this->crearTramite(['cliente_id' => 20]);
    
    // Simular login
    $this->actingAs($userId);
    
    // Consultar trámites
    $tramites = $this->getTramites();
    
    // Verificar
    $this->assertContains($tramitePropio, $tramites);
    $this->assertNotContains($tramiteAjeno, $tramites);
}
```

---

## 📊 Performance

### Optimizar Consultas

El filtro genera una subconsulta. Para mejor performance:

```php
// Opción 1: Caché de clientes en sesión (ya implementado)
$clienteIds = session()->get('clients_by_user');

// Opción 2: JOIN directo en lugar de subconsulta
$clienteIds = get_user_cliente_ids();

$builder = $db->table('tramite as t');
$builder->join('cli_directo cd', 't.cli_directo_id = cd.id');
$builder->whereIn('cd.cliente_id', $clienteIds);
```

### Índices Recomendados

```sql
-- Optimizar consultas de filtrado
CREATE INDEX idx_cliente_user_user ON cliente_user(user_id);
CREATE INDEX idx_cliente_user_cliente ON cliente_user(cliente_id);
CREATE INDEX idx_cli_directo_cliente ON cli_directo(cliente_id);
CREATE INDEX idx_tramite_cli_directo ON tramite(cli_directo_id);
```

---

## 📝 Documentación de Código

Usa este template para documentar tus métodos:

```php
/**
 * Método de ejemplo con filtro multi-tenancy
 * 
 * SEGURIDAD: Implementa filtrado por cliente_user
 * 
 * @param int $param Descripción del parámetro
 * @return array Lista de trámites filtrados
 * @throws \Exception Si el usuario no tiene acceso
 */
public function miMetodo($param)
{
    // Validar acceso
    if (!validate_tramite_access($param)) {
        throw new \Exception('Acceso denegado');
    }
    
    // Aplicar filtro
    $builder = $db->table('tramite');
    $builder = apply_cliente_filter($builder);
    
    // Resto del código...
}
```

---

## 🆘 Soporte

Si tienes dudas:
1. Revisa [MULTI_TENANCY_README.md](MULTI_TENANCY_README.md)
2. Revisa el código en `app/Helpers/cliente_filter_helper.php`
3. Busca ejemplos en `app/Controllers/Deskapp/Customers.php`
4. Consulta los logs en `writable/logs/`

---

**Última actualización:** 1 de febrero de 2026
