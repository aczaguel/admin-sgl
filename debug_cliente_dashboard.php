<?php
/**
 * Script de Debug: Por qué Rol Cliente no ve Dashboard Cliente
 * 
 * Ejecutar: php debug_cliente_dashboard.php
 */

// ============================================================================
// 1. VERIFICACIÓN DE BASE DE DATOS
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "1. VERIFICACIÓN DE BASE DE DATOS\n";
echo str_repeat("=", 80) . "\n";

$mysqli = new \mysqli('localhost', 'admin', 'contraseña_segura', 'procedures');
if ($mysqli->connect_error) {
    die("❌ Error de conexión: " . $mysqli->connect_error);
}

// Verificar permiso
$result = $mysqli->query("SELECT id, status FROM us_permissions WHERE permission_name = 'menu_dashboard_cliente'");
$perm = $result->fetch_assoc();
if ($perm) {
    echo "✅ Permiso 'menu_dashboard_cliente' existe (ID: {$perm['id']}, Status: {$perm['status']})\n";
} else {
    echo "❌ Permiso 'menu_dashboard_cliente' NO existe en BD\n";
}

// Verificar rol
$result = $mysqli->query("SELECT id FROM us_roles WHERE role_name = 'Cliente'");
$role = $result->fetch_assoc();
if ($role) {
    echo "✅ Rol 'Cliente' existe (ID: {$role['id']})\n";
} else {
    echo "❌ Rol 'Cliente' NO existe en BD\n";
}

// Verificar asignación rol → permiso
if ($perm && $role) {
    $result = $mysqli->query(
        "SELECT id FROM us_role_permissions 
         WHERE role_id = {$role['id']} AND permission_id = {$perm['id']}"
    );
    $assignment = $result->fetch_assoc();
    if ($assignment) {
        echo "✅ Asignación Cliente → menu_dashboard_cliente está registrada\n";
    } else {
        echo "❌ PROBLEMA: Asignación Cliente → menu_dashboard_cliente NO existe\n";
        echo "   SOLUCIÓN: Ejecutar assign_sidebar_permissions_roles.sql\n";
    }
}

// ============================================================================
// 2. VERIFICACIÓN DE USUARIOS CON ROL CLIENTE
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "2. USUARIOS CON ROL CLIENTE\n";
echo str_repeat("=", 80) . "\n";

$result = $mysqli->query(
    "SELECT u.id, u.username, u.firstname 
     FROM users u
     JOIN us_user_roles ur ON u.id = ur.user_id
     JOIN us_roles r ON r.id = ur.role_id
     WHERE r.role_name = 'Cliente'
     LIMIT 5"
);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
    echo "  - ID: {$row['id']}, Username: {$row['username']}, Name: {$row['firstname']}\n";
}

if (empty($users)) {
    echo "⚠️  No hay usuarios con rol Cliente\n";
} else {
    echo "✅ Total usuarios con rol Cliente: " . count($users) . "\n";
}

// ============================================================================
// 3. VERIFICACIÓN DE PERMISOS POR USUARIO
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "3. PERMISOS CARGADOS POR USUARIO (Primera prueba)\n";
echo str_repeat("=", 80) . "\n";

if (!empty($users)) {
    $testUser = $users[0];
    $userId = $testUser['id'];
    $username = $testUser['username'];
    
    echo "Testando usuario: {$username} (ID: {$userId})\n\n";
    
    // Query igual a UserModel::getUserPermissions()
    $query = "
        SELECT DISTINCT p.permission_name
        FROM users u
        INNER JOIN us_user_roles ur ON u.id = ur.user_id
        INNER JOIN us_roles r ON ur.role_id = r.id
        INNER JOIN us_role_permissions rp ON r.id = rp.role_id
        INNER JOIN us_permissions p ON rp.permission_id = p.id
        WHERE u.id = {$userId} AND p.status = 1
        ORDER BY p.permission_name
    ";
    
    $result = $mysqli->query($query);
    $perms = [];
    while ($row = $result->fetch_assoc()) {
        $perms[] = $row['permission_name'];
    }
    
    echo "Permisos cargados para {$username}:\n";
    foreach ($perms as $perm) {
        $marker = ($perm === 'menu_dashboard_cliente') ? '⭐' : '  ';
        echo "{$marker} - {$perm}\n";
    }
    
    if (in_array('menu_dashboard_cliente', $perms)) {
        echo "\n✅ menu_dashboard_cliente ESTÁ en permisos cargados\n";
    } else {
        echo "\n❌ menu_dashboard_cliente NO ESTÁ en permisos cargados\n";
        echo "   SOLUCIÓN: Ejecutar assign_sidebar_permissions_roles.sql\n";
    }
}

// ============================================================================
// 4. PERMISOS SIN FILTRO DE STATUS
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "4. PERMISOS SIN FILTRO DE STATUS (Diagnóstico)\n";
echo str_repeat("=", 80) . "\n";

if (!empty($users)) {
    $userId = $users[0]['id'];
    
    $query = "
        SELECT DISTINCT p.permission_name, p.status
        FROM users u
        INNER JOIN us_user_roles ur ON u.id = ur.user_id
        INNER JOIN us_roles r ON ur.role_id = r.id
        INNER JOIN us_role_permissions rp ON r.id = rp.role_id
        INNER JOIN us_permissions p ON rp.permission_id = p.id
        WHERE u.id = {$userId}
        ORDER BY p.status DESC, p.permission_name
    ";
    
    $result = $mysqli->query($query);
    $permsByStatus = ['1' => [], '0' => []];
    
    while ($row = $result->fetch_assoc()) {
        $status = $row['status'] ?? '?';
        $permsByStatus[$status][] = $row['permission_name'];
    }
    
    echo "Permisos con Status=1 (ACTIVOS): " . count($permsByStatus['1']) . "\n";
    if (in_array('menu_dashboard_cliente', $permsByStatus['1'])) {
        echo "  ✅ menu_dashboard_cliente (ACTIVO)\n";
    }
    
    if (!empty($permsByStatus['0'])) {
        echo "\nPermisos con Status=0 (INACTIVOS): " . count($permsByStatus['0']) . "\n";
        if (in_array('menu_dashboard_cliente', $permsByStatus['0'])) {
            echo "  ❌ menu_dashboard_cliente (INACTIVO)\n";
            echo "  SOLUCIÓN: Ejecutar activate_used_permissions.sql\n";
        }
    }
    
    if (!empty($permsByStatus['NULL'])) {
        echo "\nPermisos con Status=NULL: " . count($permsByStatus['NULL']) . "\n";
    }
}

// ============================================================================
// 5. OVERRIDES DE PERMISOS POR USUARIO
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "5. OVERRIDES DE PERMISOS (us_user_permissions)\n";
echo str_repeat("=", 80) . "\n";

if (!empty($users)) {
    foreach ($users as $user) {
        $userId = $user['id'];
        $username = $user['username'];
        
        $result = $mysqli->query(
            "SELECT p.permission_name, up.granted
             FROM us_user_permissions up
             JOIN us_permissions p ON p.id = up.permission_id
             WHERE up.user_id = {$userId}
             ORDER BY p.permission_name"
        );
        
        $overrides = [];
        while ($row = $result->fetch_assoc()) {
            $overrides[] = $row;
        }
        
        if (!empty($overrides)) {
            echo "Usuario {$username} (ID: {$userId}) tiene " . count($overrides) . " override(s):\n";
            foreach ($overrides as $override) {
                $action = $override['granted'] ? 'OTORGADO' : 'DENEGADO';
                echo "  - {$override['permission_name']}: {$action}\n";
            }
        }
    }
}

// ============================================================================
// 6. VERIFICACIÓN DEL SIDEBAR
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "6. VERIFICACIÓN DE SIDEBAR CLIENTE\n";
echo str_repeat("=", 80) . "\n";

$result = $mysqli->query(
    "SELECT p.permission_name, p.status
     FROM us_permissions p
     WHERE p.permission_name IN ('ui_sidebar_cliente', 'menu_dashboard_cliente', 'menu_tramites_cliente')
     ORDER BY p.permission_name"
);

$sidebarPerms = [];
while ($row = $result->fetch_assoc()) {
    $status = $row['status'] ? '✅ ACTIVO' : '❌ INACTIVO';
    echo "  {$row['permission_name']}: {$status}\n";
}

// ============================================================================
// RESUMEN Y RECOMENDACIONES
// ============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "RESUMEN Y RECOMENDACIONES\n";
echo str_repeat("=", 80) . "\n";

$issues = [];

if (!$perm || $perm['status'] != 1) {
    $issues[] = "Permiso menu_dashboard_cliente está inactivo o no existe";
}

if (!empty($users)) {
    $userId = $users[0]['id'];
    $result = $mysqli->query(
        "SELECT COUNT(*) as count FROM us_user_permissions 
         WHERE user_id = {$userId} AND permission_id = {$perm['id']} AND granted = 0"
    );
    $denied = $result->fetch_assoc();
    if ($denied['count'] > 0) {
        $issues[] = "El permiso menu_dashboard_cliente está explícitamente denegado para este usuario";
    }
}

if (empty($issues)) {
    echo "✅ No se encontraron problemas evidentes en la base de datos.\n";
    echo "   El problema podría estar en:\n";
    echo "   1. La sesión no está siendo refrescada (AclRefreshFilter)\n";
    echo "   2. Hay un issue en expand_permission_aliases()\n";
    echo "   3. El usuario está siendo deslogueado/re-loguueado\n";
    echo "   4. Cache de permisos en session storage\n\n";
    echo "   PRÓXIMOS PASOS:\n";
    echo "   - Borrar cookies/sesión del usuario\n";
    echo "   - Intentar login nuevamente\n";
    echo "   - Revisar logs de aplicación\n";
} else {
    echo "❌ Problemas encontrados:\n";
    foreach ($issues as $issue) {
        echo "   - {$issue}\n";
    }
    echo "\n   SOLUCIONES:\n";
    echo "   1. Ejecutar: assign_sidebar_permissions_roles.sql\n";
    echo "   2. Ejecutar: activate_used_permissions.sql\n";
    echo "   3. Clear session: rm writable/session/*\n";
}

$mysqli->close();

echo "\n" . str_repeat("=", 80) . "\n";
echo "Fin del debug\n";
echo str_repeat("=", 80) . "\n\n";
