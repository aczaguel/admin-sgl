# 🚀 GUÍA RÁPIDA: ASIGNAR CLIENTES A USUARIOS

## ✅ Implementación Completa

Se ha implementado el filtrado multi-tenancy en el controlador de Trámites. Los usuarios ahora **solo verán trámites de sus clientes asignados**.

---

## 📋 Métodos con Filtro Implementado

Los siguientes métodos ya tienen el filtrado activo:

| Método | Ruta | Descripción |
|--------|------|-------------|
| `tramite()` | `/deskapp/tramites/tramite` | Listado principal de trámites |
| `tramite_2024()` | `/deskapp/tramites/tramite_2024` | Trámites del 2024 |
| `tramite_2025()` | `/deskapp/tramites/tramite_2025` | Trámites del 2025 |
| `finalizados()` | `/deskapp/tramites/finalizados` | Trámites finalizados |
| `tenencias()` | `/deskapp/tramites/tenencias` | Tenencias |
| `cotizaciones()` | `/deskapp/tramites/cotizaciones` | Cotizaciones |
| `update($id)` | `/deskapp/tramites/update/{id}` | Editar trámite (validación) |

---

## 🎯 Cómo Asignar Clientes a Usuarios

### Opción 1: Desde el Panel Administrativo (RECOMENDADO)

1. **Ir al CRUD de Usuarios:**
   ```
   Menú → Usuarios → Gestión de Usuarios
   O directamente: /deskapp/users/users
   ```

2. **Editar un Usuario:**
   - Click en el botón "Editar" (lápiz) del usuario
   - O crear un nuevo usuario

3. **Asignar Clientes:**
   - En el formulario verás un campo **"Clientes"** (multiselect)
   - Selecciona uno o varios clientes
   - El usuario solo verá trámites de estos clientes

4. **Guardar:**
   - Click en "Guardar"
   - La asignación se guarda automáticamente en `cliente_user`

### Opción 2: Directamente en Base de Datos

```sql
-- Insertar relación usuario-cliente
INSERT INTO cliente_user (user_id, cliente_id, created_at, updated_at) 
VALUES (5, 10, NOW(), NOW());

-- Asignar múltiples clientes a un usuario
INSERT INTO cliente_user (user_id, cliente_id, created_at, updated_at) 
VALUES 
    (5, 10, NOW(), NOW()),
    (5, 15, NOW(), NOW()),
    (5, 20, NOW(), NOW());
```

---

## 🔍 Verificar Asignaciones

### Ver Clientes Asignados a un Usuario

```sql
-- Ver qué clientes tiene asignado el usuario ID 5
SELECT 
    u.id as usuario_id,
    u.username,
    c.id as cliente_id,
    c.nombre as cliente_nombre
FROM users u
INNER JOIN cliente_user cu ON u.id = cu.user_id
INNER JOIN cliente c ON cu.cliente_id = c.id
WHERE u.id = 5;
```

### Ver Usuarios Asignados a un Cliente

```sql
-- Ver qué usuarios están asignados al cliente ID 10
SELECT 
    c.id as cliente_id,
    c.nombre as cliente_nombre,
    u.id as usuario_id,
    u.username,
    CONCAT(u.firstname, ' ', u.lastname) as nombre_completo
FROM cliente c
INNER JOIN cliente_user cu ON c.id = cu.cliente_id
INNER JOIN users u ON cu.user_id = u.id
WHERE c.id = 10;
```

### Ver Usuarios SIN Clientes Asignados

```sql
-- Usuarios que no tienen ningún cliente asignado
SELECT 
    u.id,
    u.username,
    CONCAT(u.firstname, ' ', u.lastname) as nombre_completo
FROM users u
LEFT JOIN cliente_user cu ON u.id = cu.user_id
WHERE cu.id IS NULL
  AND u.status = 1;
```

---

## 🧪 Prueba de Funcionamiento

### Paso 1: Crear Escenario de Prueba

```sql
-- 1. Tener dos clientes
SELECT id, nombre FROM cliente LIMIT 2;
-- Ejemplo: Cliente A (id=10), Cliente B (id=20)

-- 2. Tener dos usuarios
SELECT id, username FROM users WHERE status = 1 LIMIT 2;
-- Ejemplo: Usuario 1 (id=5), Usuario 2 (id=8)

-- 3. Asignar clientes
-- Usuario 1 → Cliente A
INSERT INTO cliente_user (user_id, cliente_id) VALUES (5, 10);

-- Usuario 2 → Cliente B
INSERT INTO cliente_user (user_id, cliente_id) VALUES (8, 20);
```

### Paso 2: Verificar Trámites

```sql
-- Ver trámites del Cliente A
SELECT t.id, t.folio, cd.razon_social, c.nombre
FROM tramite t
INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
INNER JOIN cliente c ON cd.cliente_id = c.id
WHERE c.id = 10
LIMIT 5;

-- Ver trámites del Cliente B
SELECT t.id, t.folio, cd.razon_social, c.nombre
FROM tramite t
INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
INNER JOIN cliente c ON cd.cliente_id = c.id
WHERE c.id = 20
LIMIT 5;
```

### Paso 3: Probar en el Sistema

1. **Login como Usuario 1:**
   - Ir a `/deskapp/tramites/tramite`
   - Debe ver SOLO trámites del Cliente A
   - NO debe ver trámites del Cliente B

2. **Login como Usuario 2:**
   - Ir a `/deskapp/tramites/tramite`
   - Debe ver SOLO trámites del Cliente B
   - NO debe ver trámites del Cliente A

3. **Intentar Acceso Directo:**
   - Usuario 1 intenta: `/deskapp/tramites/update/{ID_TRAMITE_CLIENTE_B}`
   - Debe ser redirigido con error: "⛔ No tienes permiso"

---

## 👑 Usuarios Administradores

Los usuarios con rol **admin** o **superadmin** ven **TODO** automáticamente:

### Asignar Rol Admin

```sql
-- 1. Encontrar el ID del rol admin
SELECT id, role_name FROM us_roles WHERE role_name = 'admin';
-- Ejemplo: id = 1

-- 2. Asignar al usuario
INSERT INTO us_user_roles (user_id, role_id) VALUES (5, 1);
```

### Verificar Roles

```sql
-- Ver roles de un usuario
SELECT 
    u.id,
    u.username,
    r.role_name
FROM users u
INNER JOIN us_user_roles ur ON u.id = ur.user_id
INNER JOIN us_roles r ON ur.role_id = r.id
WHERE u.id = 5;
```

---

## 🔧 Troubleshooting

### Problema: Usuario no ve ningún trámite

**Causa:** No tiene clientes asignados o sus clientes no tienen trámites.

**Solución:**
```sql
-- Verificar si tiene clientes
SELECT * FROM cliente_user WHERE user_id = 5;

-- Si está vacío, asignar clientes
INSERT INTO cliente_user (user_id, cliente_id) VALUES (5, 10);

-- Verificar que el cliente tenga trámites
SELECT COUNT(*) as total_tramites
FROM tramite t
INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
WHERE cd.cliente_id = 10;
```

### Problema: Usuario ve trámites de otros clientes

**Causa:** Posiblemente sea administrador.

**Solución:**
```sql
-- Verificar si es admin
SELECT r.role_name
FROM users u
INNER JOIN us_user_roles ur ON u.id = ur.user_id
INNER JOIN us_roles r ON ur.role_id = r.id
WHERE u.id = 5;

-- Si no debe ser admin, eliminar el rol
DELETE FROM us_user_roles 
WHERE user_id = 5 
  AND role_id = (SELECT id FROM us_roles WHERE role_name = 'admin');
```

### Problema: Error "No tienes permiso" al editar

**Causa:** El trámite no pertenece a ninguno de sus clientes.

**Solución:**
```sql
-- Verificar a qué cliente pertenece el trámite
SELECT 
    t.id as tramite_id,
    t.folio,
    c.id as cliente_id,
    c.nombre as cliente_nombre
FROM tramite t
INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
INNER JOIN cliente c ON cd.cliente_id = c.id
WHERE t.id = 123;

-- Verificar clientes del usuario
SELECT cliente_id FROM cliente_user WHERE user_id = 5;

-- Si el cliente no coincide, asignar el cliente correcto
INSERT INTO cliente_user (user_id, cliente_id) VALUES (5, [ID_CLIENTE_DEL_TRAMITE]);
```

---

## 📊 Estadísticas Útiles

### Clientes por Usuario

```sql
SELECT 
    u.id,
    u.username,
    COUNT(cu.cliente_id) as total_clientes,
    GROUP_CONCAT(c.nombre SEPARATOR ', ') as clientes
FROM users u
LEFT JOIN cliente_user cu ON u.id = cu.user_id
LEFT JOIN cliente c ON cu.cliente_id = c.id
WHERE u.status = 1
GROUP BY u.id
ORDER BY total_clientes DESC;
```

### Usuarios por Cliente

```sql
SELECT 
    c.id,
    c.nombre,
    COUNT(cu.user_id) as total_usuarios,
    GROUP_CONCAT(u.username SEPARATOR ', ') as usuarios
FROM cliente c
LEFT JOIN cliente_user cu ON c.id = cu.cliente_id
LEFT JOIN users u ON cu.user_id = u.id
GROUP BY c.id
ORDER BY total_usuarios DESC;
```

### Trámites por Cliente

```sql
SELECT 
    c.id as cliente_id,
    c.nombre as cliente,
    COUNT(DISTINCT cd.id) as total_clientes_directos,
    COUNT(t.id) as total_tramites
FROM cliente c
LEFT JOIN cli_directo cd ON c.id = cd.cliente_id
LEFT JOIN tramite t ON cd.id = t.cli_directo_id
GROUP BY c.id
ORDER BY total_tramites DESC;
```

---

## 🎓 Casos de Uso

### Caso 1: Ejecutivo de un Solo Cliente

```sql
-- Juan solo trabaja para Cliente A
INSERT INTO cliente_user (user_id, cliente_id) VALUES (5, 10);

-- Juan verá todos los trámites del Cliente A
```

### Caso 2: Ejecutivo de Múltiples Clientes

```sql
-- María trabaja para Clientes A, B y C
INSERT INTO cliente_user (user_id, cliente_id) VALUES 
    (8, 10),
    (8, 15),
    (8, 20);

-- María verá trámites de los tres clientes
```

### Caso 3: Varios Ejecutivos para un Cliente

```sql
-- Cliente A tiene 3 ejecutivos
INSERT INTO cliente_user (user_id, cliente_id) VALUES 
    (5, 10),   -- Juan
    (8, 10),   -- María
    (12, 10);  -- Pedro

-- Todos ven los mismos trámites del Cliente A
```

### Caso 4: Manager con Acceso a Todo

```sql
-- Laura es manager, necesita ver todo
-- Opción 1: Asignar rol admin (recomendado)
INSERT INTO us_user_roles (user_id, role_id) 
VALUES (15, (SELECT id FROM us_roles WHERE role_name = 'admin'));

-- Opción 2: Asignar todos los clientes
INSERT INTO cliente_user (user_id, cliente_id)
SELECT 15, id FROM cliente;
```

---

## 📝 Resumen

✅ **Filtrado implementado** en 7 métodos principales del controlador Tramites  
✅ **Validación de acceso** en método update (edición)  
✅ **Administradores** ven todo automáticamente  
✅ **Usuarios normales** solo ven sus clientes asignados  

**Para empezar:**
1. Asigna clientes a tus usuarios desde `/deskapp/users/users`
2. Los usuarios verán automáticamente solo sus trámites
3. Los admins siguen viendo todo sin cambios

---

**Última actualización:** 2 de febrero de 2026
