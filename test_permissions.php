<?php
require 'vendor/autoload.php';

// Cargar config de base de datos
$db = new \mysqli('localhost', 'admin', 'contraseña_segura', 'procedures');
if ($db->connect_error) {
    die("Error BD: " . $db->connect_error);
}

// Usuario Cliente (id=11)
$userId = 11;

// Simular UserModel::getUserPermissions()
$query = "
SELECT DISTINCT p.permission_name
FROM users u
INNER JOIN us_user_roles ur ON u.id = ur.user_id
INNER JOIN us_roles r ON ur.role_id = r.id
INNER JOIN us_role_permissions rp ON r.id = rp.role_id
INNER JOIN us_permissions p ON rp.permission_id = p.id
WHERE u.id = $userId AND p.status = 1
ORDER BY p.permission_name
";

$result = $db->query($query);
$perms = [];
while ($row = $result->fetch_assoc()) {
    $perms[] = $row['permission_name'];
}

echo "=== Permisos del usuario Cliente (ID: $userId) ===\n";
echo json_encode($perms, JSON_PRETTY_PRINT) . "\n";
echo "\nTotal: " . count($perms) . " permisos\n";

// Buscar específicamente menu_dashboard_cliente
if (in_array('menu_dashboard_cliente', $perms)) {
    echo "✅ ENCONTRADO: menu_dashboard_cliente\n";
} else {
    echo "❌ NO ENCONTRADO: menu_dashboard_cliente\n";
}

$db->close();
