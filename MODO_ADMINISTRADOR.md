# 👑 MODO ADMINISTRADOR - ACCESO COMPLETO SIN RESTRICCIONES

## 📋 Descripción

Los usuarios con rol **admin** o **superadmin** tienen acceso completo a **TODOS** los clientes y trámites del sistema, sin necesidad de estar asignados explícitamente en la tabla `cliente_user`.

---

## 🎯 ¿Cómo Funciona?

### Flujo Normal (Usuarios con Restricción)

```
Usuario Normal → Consulta cliente_user → Obtiene [10, 15, 20] → Ve solo esos clientes
```

### Flujo Administrador (Sin Restricción)

```
Usuario Admin → Se detecta rol admin → NO se consulta cliente_user → Ve TODOS los clientes
```

---

## 🔑 Roles que Tienen Acceso Completo

La función `user_is_admin()` verifica estos roles (case-insensitive):

- `admin`
- `Admin`
- `superadmin`
- `SuperAdmin`

Si el usuario tiene **cualquiera** de estos roles, tiene acceso completo.

---

## 💻 Implementación Técnica

### 1. Verificación de Rol Admin

```php
// Verificar si el usuario es administrador
if (user_is_admin()) {
    // Este usuario tiene acceso completo
    echo "Acceso Total";
} else {
    // Usuario con restricciones
    echo "Acceso Limitado";
}
```

### 2. Retorno NULL = Acceso Total

Cuando un usuario es administrador:

```php
$clienteIds = get_user_cliente_ids();
// Si es admin: $clienteIds = null (no array vacío!)
// Si NO es admin: $clienteIds = [10, 15, 20] o []

if ($clienteIds === null) {
    echo "Usuario administrador - Acceso total";
}
```

### 3. Filtros SQL Automáticos

Para administradores, el SQL generado es:

```sql
-- Usuario administrador
WHERE 1 = 1  -- Sin filtro, ve todo

-- Usuario normal con clientes [10, 15]
WHERE tramite.id IN (
    SELECT t.id 
    FROM cliente_user cu
    WHERE cu.cliente_id IN (10, 15)
    ...
)
```

---

## 📚 Ejemplos de Uso

### Ejemplo 1: Listado de Trámites

```php
public function listarTramites()
{
    $db = \Config\Database::connect();
    $builder = $db->table('tramite');
    
    // Aplicar filtro (automáticamente detecta si es admin)
    $builder = apply_cliente_filter($builder);
    
    // Si es admin: NO se aplica filtro (ve todo)
    // Si NO es admin: Se aplica filtro por sus clientes
    
    $tramites = $builder->get()->getResult();
    return view('tramites', ['tramites' => $tramites]);
}
```

### Ejemplo 2: Verificar Acceso a Cliente Específico

```php
$clienteId = 100;

if (has_access_to_cliente($clienteId)) {
    // Admin: siempre true
    // Usuario normal: true solo si tiene asignado el cliente 100
    echo "Tiene acceso";
} else {
    echo "No tiene acceso";
}
```

### Ejemplo 3: Validar Acceso a Trámite

```php
$tramiteId = 500;

if (validate_tramite_access($tramiteId)) {
    // Admin: siempre true (puede ver cualquier trámite)
    // Usuario normal: true solo si el trámite es de sus clientes
    $tramite = $model->find($tramiteId);
} else {
    return redirect()->back()->with('error', 'Acceso denegado');
}
```

### Ejemplo 4: Mostrar Opciones Diferentes

```php
$session = session();
$isAdmin = user_is_admin();

// En la vista
<?php if ($isAdmin): ?>
    <a href="/admin/todos-tramites">Ver Todos los Trámites</a>
    <a href="/admin/todos-clientes">Gestionar Todos los Clientes</a>
<?php else: ?>
    <a href="/mis-tramites">Mis Trámites</a>
<?php endif; ?>
```

---

## ⚙️ Configuración de Roles

### Verificar Roles del Usuario

En la base de datos:

```sql
-- Ver roles de un usuario
SELECT u.username, r.role_name
FROM users u
INNER JOIN us_user_roles ur ON u.id = ur.user_id
INNER JOIN us_roles r ON ur.role_id = r.id
WHERE u.id = 5;
```

### Asignar Rol Admin

Desde el CRUD de usuarios (`/deskapp/users/users`):

1. Editar usuario
2. En campo "Roles", seleccionar "admin" o "superadmin"
3. Guardar

O directamente en base de datos:

```sql
-- Encontrar ID del rol admin
SELECT id FROM us_roles WHERE role_name = 'admin';
-- Resultado ejemplo: id = 1

-- Asignar rol admin al usuario ID 5
INSERT INTO us_user_roles (user_id, role_id) VALUES (5, 1);
```

---

## 🔍 Debugging

### Verificar si un Usuario es Admin

```php
// En un controlador o método de prueba
$userId = 5;
$isAdmin = user_is_admin($userId);

log_message('info', sprintf(
    'Usuario %d es admin: %s',
    $userId,
    $isAdmin ? 'SÍ' : 'NO'
));

// Ver roles
$userModel = new \App\Models\UserModel();
$roles = $userModel->getUserRoles($userId);
log_message('info', 'Roles: ' . implode(', ', $roles));
```

### Ver Qué Retorna get_user_cliente_ids()

```php
$userId = 5;
$clienteIds = get_user_cliente_ids($userId);

if ($clienteIds === null) {
    echo "Usuario es ADMIN (acceso total)";
} elseif (empty($clienteIds)) {
    echo "Usuario SIN clientes asignados (sin acceso)";
} else {
    echo "Usuario con clientes: " . implode(', ', $clienteIds);
}
```

### Ver SQL Generado

```php
$builder = $db->table('tramite');
$builder = apply_cliente_filter($builder);

// Ver el SQL
$sql = $builder->getCompiledSelect(false);
log_message('debug', 'SQL generado: ' . $sql);

// Ejecutar
$tramites = $builder->get()->getResult();
```

---

## 📊 Comparación de Comportamiento

| Acción | Usuario Normal | Usuario Admin |
|--------|---------------|---------------|
| `get_user_cliente_ids()` | `[10, 15, 20]` | `null` |
| `has_access_to_cliente(100)` | `false` (si no tiene) | `true` (siempre) |
| `validate_tramite_access(500)` | Valida pertenencia | `true` (siempre) |
| `apply_cliente_filter($builder)` | Aplica WHERE filtro | NO aplica filtro |
| SQL WHERE | `tramite.id IN (...)` | `1 = 1` (sin filtro) |
| Resultado | Solo sus trámites | TODOS los trámites |

---

## 🛡️ Seguridad

### ¿Es Seguro?

✅ **SÍ**, siempre que:
1. Los roles admin/superadmin se asignen solo a personal de confianza
2. Se revisen periódicamente las asignaciones de roles
3. Se auditen los accesos de usuarios admin

### Recomendaciones

1. **Limitar cantidad de admins:**
   ```sql
   -- Ver cuántos admins hay
   SELECT COUNT(*) FROM us_user_roles WHERE role_id IN (
       SELECT id FROM us_roles WHERE role_name IN ('admin', 'superadmin')
   );
   ```

2. **Auditar accesos:**
   ```php
   // Registrar cuando un admin accede a datos
   if (user_is_admin()) {
       log_message('info', sprintf(
           'Admin %d (%s) accedió a trámite %d',
           $userId,
           $username,
           $tramiteId
       ));
   }
   ```

3. **Separar roles:**
   - `admin` = Acceso completo para gestión diaria
   - `superadmin` = Acceso para configuración del sistema

---

## 🚫 Qué NO Hacer

### ❌ NO Asumir que Array Vacío = Admin

```php
// ❌ MAL
if (empty(get_user_cliente_ids())) {
    // Esto NO significa que sea admin
    // Puede ser un usuario sin clientes asignados
}

// ✅ BIEN
if (get_user_cliente_ids() === null) {
    // Esto SÍ significa que es admin
}

// ✅ MEJOR
if (user_is_admin()) {
    // Forma explícita y clara
}
```

### ❌ NO Hardcodear IDs de Usuario

```php
// ❌ MAL
if ($userId == 1 || $userId == 2) {
    // Acceso admin
}

// ✅ BIEN
if (user_is_admin($userId)) {
    // Verificación basada en roles
}
```

---

## 🎓 Casos de Uso

### 1. Dashboard Administrativo

```php
public function dashboardAdmin()
{
    if (!user_is_admin()) {
        return redirect()->to('/dashboard')
            ->with('error', 'Solo administradores');
    }
    
    // Estadísticas globales
    $db = \Config\Database::connect();
    
    $totalTramites = $db->table('tramite')->countAllResults();
    $totalClientes = $db->table('cliente')->countAllResults();
    $totalUsuarios = $db->table('users')->countAllResults();
    
    return view('admin/dashboard', [
        'totalTramites' => $totalTramites,
        'totalClientes' => $totalClientes,
        'totalUsuarios' => $totalUsuarios
    ]);
}
```

### 2. Reportes Globales

```php
public function reporteGeneral()
{
    if (!user_is_admin()) {
        return redirect()->back()
            ->with('error', 'Solo administradores');
    }
    
    $db = \Config\Database::connect();
    
    // Estadísticas por cliente (todos)
    $builder = $db->table('cliente c');
    $builder->select('c.nombre, COUNT(t.id) as total_tramites');
    $builder->join('cli_directo cd', 'c.id = cd.cliente_id', 'left');
    $builder->join('tramite t', 'cd.id = t.cli_directo_id', 'left');
    $builder->groupBy('c.id');
    
    // NO se aplica filtro porque es admin
    $stats = $builder->get()->getResult();
    
    return view('reportes/general', ['stats' => $stats]);
}
```

### 3. Gestión de Cualquier Trámite

```php
public function editarTramite($tramiteId)
{
    // Validar acceso (admin siempre tiene acceso)
    if (!validate_tramite_access($tramiteId)) {
        return redirect()->back()
            ->with('error', 'No tienes acceso');
    }
    
    // Si llegamos aquí:
    // - Admin: puede editar cualquier trámite
    // - Usuario normal: solo si es de sus clientes
    
    $tramite = $this->tramiteModel->find($tramiteId);
    return view('tramites/editar', ['tramite' => $tramite]);
}
```

---

## 📝 Resumen

✅ **Los administradores:**
- NO necesitan estar en `cliente_user`
- Ven TODOS los clientes y trámites
- NO tienen filtros aplicados automáticamente
- Se identifican por roles: `admin` o `superadmin`

✅ **Los helpers automáticamente:**
- Detectan si el usuario es admin
- Retornan `null` en `get_user_cliente_ids()` para admins
- Aplican `WHERE 1 = 1` (sin filtro) en el SQL
- Retornan `true` en todas las validaciones de acceso

✅ **Para implementar:**
- Solo usar las funciones del helper
- No necesitas verificar manualmente el rol
- El sistema lo hace automáticamente

---

**Última actualización:** 2 de febrero de 2026
