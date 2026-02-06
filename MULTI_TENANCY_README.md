# 🔐 ARQUITECTURA MULTI-TENANCY - SISTEMA DE GESTIÓN DE TRÁMITES

## 📋 Tabla de Contenidos
- [Introducción](#introducción)
- [Propósito Empresarial](#propósito-empresarial)
- [Arquitectura de Datos](#arquitectura-de-datos)
- [Flujo de Seguridad](#flujo-de-seguridad)
- [Implementación](#implementación)
- [Ejemplos de Uso](#ejemplos-de-uso)
- [Validaciones de Seguridad](#validaciones-de-seguridad)
- [FAQ](#faq)

---

## 🎯 Introducción

Este sistema implementa una **arquitectura multi-tenancy** que permite que múltiples clientes utilicen la misma aplicación mientras mantienen sus datos completamente segregados y confidenciales.

### ¿Qué es Multi-Tenancy?

Multi-tenancy es un modelo arquitectónico donde:
- Una sola instancia de la aplicación sirve a múltiples clientes (tenants)
- Cada cliente tiene sus propios datos aislados
- Los datos de un cliente NO son visibles para otros clientes
- Se optimizan recursos compartiendo la infraestructura

---

## 💼 Propósito Empresarial

### Caso de Uso Real

La **dueña de la empresa** necesita:

1. **Otorgar acceso a sus clientes** al sistema de gestión de trámites
2. **Cada cliente debe tener sus propios ejecutivos** operando exclusivamente sus trámites
3. **Proteger la confidencialidad** entre clientes competidores
4. **Prevenir fugas de información** sensible empresarial

### Ejemplo Práctico

```
Cliente A (Empresa Automotriz ABC):
  ├── Ejecutivo 1: Juan Pérez
  ├── Ejecutivo 2: María García
  └── Solo pueden ver trámites de ABC

Cliente B (Empresa Logística XYZ):
  ├── Ejecutivo 3: Pedro López
  └── Solo puede ver trámites de XYZ

❌ Juan NO puede ver trámites de XYZ
❌ Pedro NO puede ver trámites de ABC
✅ Cada uno trabaja en su propio "espacio" aislado
```

---

## 🏗️ Arquitectura de Datos

### Estructura de Tablas

```
┌─────────────┐
│   users     │  ← Usuarios del sistema (ejecutivos)
└──────┬──────┘
       │
       │ N:N (muchos a muchos)
       │
┌──────▼──────────────┐
│   cliente_user      │  ← Tabla pivote (CRÍTICA)
│                     │
│ - user_id           │  ← FK a users
│ - cliente_id        │  ← FK a cliente
└──────┬──────────────┘
       │
       │
┌──────▼──────┐
│   cliente   │  ← Clientes de la empresa
└──────┬──────┘
       │
       │ 1:N
       │
┌──────▼──────────┐
│  cli_directo    │  ← Clientes directos/finales
└──────┬──────────┘
       │
       │ 1:N
       │
┌──────▼──────┐
│   tramite   │  ← Trámites del sistema
└─────────────┘
```

### Tabla `cliente_user` (Pivote)

Esta es la tabla **MÁS IMPORTANTE** del sistema:

| Campo        | Tipo | Descripción                                    |
|--------------|------|------------------------------------------------|
| `id`         | INT  | ID único del registro                          |
| `user_id`    | INT  | ID del usuario (FK a `users`)                  |
| `cliente_id` | INT  | ID del cliente (FK a `cliente`)                |
| `created_at` | TIMESTAMP | Fecha de asignación                      |
| `updated_at` | TIMESTAMP | Fecha de última modificación             |

**Ejemplo de datos:**

```sql
-- Usuario ID 5 (Juan Pérez) asignado a clientes 10 y 15
INSERT INTO cliente_user (user_id, cliente_id) VALUES (5, 10);
INSERT INTO cliente_user (user_id, cliente_id) VALUES (5, 15);

-- Usuario ID 8 (María García) asignada a cliente 20
INSERT INTO cliente_user (user_id, cliente_id) VALUES (8, 20);
```

Con esta configuración:
- Juan puede ver trámites de clientes 10 y 15
- María puede ver trámites de cliente 20
- Juan NO puede ver trámites de cliente 20
- María NO puede ver trámites de clientes 10 y 15

---

## 🔒 Flujo de Seguridad

### 1. Inicio de Sesión

```php
// app/Controllers/Deskapp/Login.php

// Al hacer login, se cargan los clientes asignados al usuario
$clients_by_user = $model->obtenerClientesPorUsuario($data['id']);
$session->set('clients_by_user', $clients_by_user);

// Ejemplo: $clients_by_user = [10, 15, 20]
```

### 2. Consulta de Trámites

```php
// app/Controllers/Deskapp/Customers.php

// Se aplica filtro WHERE para limitar los resultados
$crud->where("
    tramite.id IN (
        SELECT t.id
        FROM cliente_user cu
        INNER JOIN cliente c ON cu.cliente_id = c.id
        INNER JOIN cli_directo cd ON cd.cliente_id = c.id
        INNER JOIN tramite t ON cd.id = t.cli_directo_id
        WHERE cu.user_id = $myid
    )
");
```

### 3. Validación de Acceso

```php
// Usando el helper cliente_filter_helper.php

// Validar si usuario tiene acceso a un trámite específico
if (!validate_tramite_access($tramiteId, $userId)) {
    log_unauthorized_access_attempt('tramite', $tramiteId, $userId);
    return redirect()->back()->with('error', 'Acceso denegado');
}
```

---

## 💻 Implementación

### Helper: `cliente_filter_helper.php`

Este helper proporciona funciones reutilizables para filtrado:

#### Funciones Disponibles

1. **`get_user_cliente_ids($userId = null)`**
   - Obtiene los IDs de clientes asignados a un usuario
   - Retorna: `array` de IDs
   
   ```php
   $clienteIds = get_user_cliente_ids(); 
   // Resultado: [10, 15, 20]
   ```

2. **`has_access_to_cliente($clienteId, $userId = null)`**
   - Verifica si el usuario tiene acceso a un cliente específico
   - Retorna: `boolean`
   
   ```php
   if (has_access_to_cliente(10)) {
       // Usuario tiene acceso al cliente 10
   }
   ```

3. **`get_cliente_filter_sql($userId = null, $tramiteTable = 'tramite')`**
   - Genera cláusula SQL WHERE para filtrar trámites
   - Retorna: `string` SQL
   
   ```php
   $filter = get_cliente_filter_sql();
   // Resultado: "tramite.id IN (SELECT t.id FROM...)"
   ```

4. **`apply_cliente_filter($builder, $userId = null, $tramiteTable = 'tramite')`**
   - Aplica filtro directamente a un Query Builder
   - Retorna: Query Builder modificado
   
   ```php
   $builder = $db->table('tramite');
   $builder = apply_cliente_filter($builder);
   $tramites = $builder->get()->getResult();
   ```

5. **`validate_tramite_access($tramiteId, $userId = null)`**
   - Valida acceso a un trámite específico
   - Retorna: `boolean`
   
   ```php
   if (!validate_tramite_access($tramiteId)) {
       return redirect()->back()->with('error', 'Acceso denegado');
   }
   ```

6. **`log_unauthorized_access_attempt($resource, $resourceId, $userId = null)`**
   - Registra intentos de acceso no autorizado
   
   ```php
   log_unauthorized_access_attempt('tramite', $tramiteId, $userId);
   ```

### Modelo: `UserModel.php`

Métodos críticos para multi-tenancy:

```php
// Obtener clientes de un usuario
$clienteIds = $userModel->obtenerClientesPorUsuario($userId);

// Verificar si un usuario es un cliente externo
$result = $userModel->isUserClient($userId);
if ($result['is_client']) {
    // Aplicar restricciones adicionales
}
```

---

## 📚 Ejemplos de Uso

### Ejemplo 1: Filtrar Trámites en un Controlador

```php
<?php
namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

class MiControlador extends BaseController
{
    public function __construct() {
        helper(['cliente_filter']);
    }

    public function listarTramites()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tramite as t');
        
        // Aplicar filtro de clientes automáticamente
        $builder = apply_cliente_filter($builder);
        
        // Continuar con la consulta
        $builder->select('t.*, tt.tipo_tramite');
        $builder->join('tra_tipos tt', 't.tra_tipos_id = tt.id');
        
        $tramites = $builder->get()->getResult();
        
        return view('tramites/lista', ['tramites' => $tramites]);
    }
}
```

### Ejemplo 2: Validar Acceso a un Trámite Específico

```php
public function verDetalle($tramiteId)
{
    // Validar acceso
    if (!validate_tramite_access($tramiteId)) {
        log_unauthorized_access_attempt('tramite', $tramiteId);
        return redirect()
            ->to('/tramites')
            ->with('error', '⛔ No tienes permiso para ver este trámite');
    }
    
    // Si llegamos aquí, el usuario tiene acceso
    $tramite = $this->tramiteModel->find($tramiteId);
    return view('tramites/detalle', ['tramite' => $tramite]);
}
```

### Ejemplo 3: Usar en GroceryCrud

```php
public function tramitesCrud()
{
    $crud = $this->_getGroceryCrudEnterprise();
    $crud->setTable('tramite');
    
    // Obtener ID del usuario
    $session = session();
    $userId = $session->get('id');
    
    // Aplicar filtro usando el helper
    $filterSql = get_cliente_filter_sql($userId);
    $crud->where($filterSql, null, false);
    
    $output = $crud->render();
    return $this->_example_output($output);
}
```

### Ejemplo 4: Crear Dashboard por Cliente

```php
public function dashboard()
{
    $session = session();
    $userId = $session->get('id');
    
    // Obtener clientes del usuario
    $clienteIds = get_user_cliente_ids($userId);
    
    $db = \Config\Database::connect();
    
    // Contar trámites por cliente
    $builder = $db->table('tramite t');
    $builder->select('c.nombre, COUNT(t.id) as total');
    $builder->join('cli_directo cd', 't.cli_directo_id = cd.id');
    $builder->join('cliente c', 'cd.cliente_id = c.id');
    $builder->whereIn('c.id', $clienteIds);
    $builder->groupBy('c.id');
    
    $stats = $builder->get()->getResult();
    
    return view('dashboard', ['stats' => $stats]);
}
```

---

## 🛡️ Validaciones de Seguridad

### Checklist de Seguridad

Al implementar nuevas funcionalidades, verifica:

- [ ] **¿La consulta filtra por `cliente_user`?**
- [ ] **¿Se valida el acceso antes de mostrar datos?**
- [ ] **¿Se registran intentos de acceso no autorizado?**
- [ ] **¿Los IDs se validan como enteros?**
- [ ] **¿Se usa el helper `cliente_filter_helper`?**

### Código Peligroso ❌

**NUNCA hagas esto:**

```php
// ❌ MAL - Sin filtrar por cliente
$tramites = $db->table('tramite')->get()->getResult();

// ❌ MAL - SQL injection vulnerable
$clienteIds = implode(',', $_POST['clientes']); // Sin validación
$sql = "WHERE cliente_id IN ($clienteIds)";
```

### Código Seguro ✅

**HAZ esto:**

```php
// ✅ BIEN - Filtrado automático
$builder = $db->table('tramite');
$builder = apply_cliente_filter($builder);
$tramites = $builder->get()->getResult();

// ✅ BIEN - Validación de IDs
$clienteIds = array_filter($clienteIds, 'is_numeric');
$clienteIds = array_map('intval', $clienteIds);
```

---

## ❓ FAQ

### P: ¿Qué pasa si un usuario no tiene clientes asignados?

**R:** El helper retorna un array vacío `[]` y la consulta SQL resultará en `WHERE 1 = 0`, no mostrando ningún trámite.

```php
$clienteIds = get_user_cliente_ids($userId);
// Si no tiene clientes: $clienteIds = []
// SQL generado: WHERE 1 = 0 (no muestra nada)
```

### P: ¿Cómo asigno clientes a un usuario?

**R:** Desde el CRUD de usuarios en `/deskapp/users/users`:
1. Editar o crear usuario
2. En el campo "Clientes" (multiselect), seleccionar los clientes
3. Al guardar, se crean registros en `cliente_user`

### P: ¿Puedo asignar un cliente a múltiples usuarios?

**R:** ✅ Sí, es una relación N:N. Un cliente puede tener múltiples ejecutivos, y un ejecutivo puede trabajar con múltiples clientes.

```
Cliente A → Usuario 1, Usuario 2, Usuario 3
Cliente B → Usuario 1, Usuario 4
```

### P: ¿Cómo evito que los usuarios vean información de otros clientes?

**R:** El sistema lo hace automáticamente si:
1. Usas las funciones del helper `cliente_filter_helper`
2. Aplicas los filtros en todas las consultas de trámites
3. Validas el acceso antes de mostrar detalles

### P: ¿Los logs de acceso no autorizado dónde se guardan?

**R:** En `writable/logs/log-YYYY-MM-DD.log` con nivel `WARNING`:

```
WARNING --> Intento de acceso no autorizado: Usuario 5 (juan.perez) intentó acceder a tramite ID: 1234
```

### P: ¿Qué hacer si necesito que un usuario administrador vea TODO?

**R:** Hay dos opciones:

**Opción 1:** No aplicar el filtro si el usuario tiene rol admin:
```php
if (!has_role('admin')) {
    $builder = apply_cliente_filter($builder);
}
```

**Opción 2:** Asignarle TODOS los clientes en `cliente_user`.

### P: ¿Es seguro contra SQL Injection?

**R:** ✅ Sí, el helper valida y sanitiza los IDs:
```php
// Validación implementada
$clienteIds = array_filter($clienteIds, 'is_numeric');
$clienteIds = array_map('intval', $clienteIds);
```

---

## 📊 Diagrama de Flujo Completo

```
┌─────────────┐
│   Usuario   │
│   Login     │
└──────┬──────┘
       │
       ▼
┌──────────────────────┐
│ Se consultan sus     │
│ clientes en          │
│ cliente_user         │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ IDs se guardan en    │
│ sesión:              │
│ clients_by_user      │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Usuario solicita     │
│ listado de trámites  │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Se aplica filtro SQL │
│ usando cliente_user  │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Solo muestra trámites│
│ de sus clientes      │
│ asignados            │
└──────────────────────┘
```

---

## 🚀 Próximos Pasos

1. **Auditar todos los controladores** para asegurar que aplican filtros
2. **Implementar tests automatizados** para validar la segregación
3. **Agregar alertas** cuando se detecten intentos de acceso no autorizado
4. **Crear reporte de auditoría** de accesos por usuario
5. **Documentar en el manual de usuario** cómo asignar clientes

---

## 📞 Soporte

Para dudas o problemas con la arquitectura multi-tenancy:
- Revisar logs en `writable/logs/`
- Verificar registros en tabla `cliente_user`
- Comprobar que los helpers están cargados
- Validar permisos en sesión

---

**Última actualización:** 1 de febrero de 2026
**Versión:** 1.0.0
