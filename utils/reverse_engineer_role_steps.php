<?php
/**
 * Ingeniería inversa: Rol -> Steps tocados
 *
 * Lee roles y sus permisos desde DB (us_roles, us_role_permissions, us_permissions)
 * Construye un catálogo de permisos por step (1-5) con la misma heurística del mapa
 * y luego compara por intersección: si un rol tiene al menos 1 permiso del step,
 * entonces ese rol "toca" ese step.
 *
 * Uso:
 *   php utils/reverse_engineer_role_steps.php
 *
 * Salida:
 *   - ACL_ROLE_STEP_REVERSE.md (en raíz)
 */

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Cannot resolve repo root\n");
    exit(1);
}

$envPath = $root . '/.env';
if (!is_file($envPath)) {
    fwrite(STDERR, "Missing .env at repo root\n");
    exit(1);
}

$reportPath = $root . '/ACL_ROLE_STEP_REVERSE.md';

function starts_with(string $haystack, string $needle): bool
{
    if ($needle === '') {
        return true;
    }
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

function parseEnvFile(string $envPath): array
{
    $env = [];
    $lines = file($envPath, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return $env;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        $val = trim($val, "\"'");
        if ($key !== '') {
            $env[$key] = $val;
        }
    }

    return $env;
}

function assignStepForPerm(string $permName): ?int
{
    $p = strtolower($permName);

    $contains = static function (string $haystack, string $needle): bool {
        return $needle !== '' && strpos($haystack, $needle) !== false;
    };

    // Paso 5: proceso final / cobro / concluido
    if (
        $contains($p, 'section_final_costos') ||
        $contains($p, 'final_tramite') ||
        $contains($p, 'cobro_cliente') ||
        $contains($p, 'final_costos') ||
        $contains($p, 'concluido') ||
        $contains($p, 'concluir') ||
        $contains($p, 'cancelar')
    ) {
        return 5;
    }

    // Paso 4: pago gestor / autorizaciones
    if (
        $contains($p, 'section_pago_gestor') ||
        $contains($p, 'pago_gestor') ||
        $contains($p, 'editar_pago_gestor') ||
        $contains($p, 'pasar_a_pagos')
    ) {
        return 4;
    }

    // Paso 3: pago derechos / línea / docs de pago
    if (
        $contains($p, 'section_pago_derechos') ||
        $contains($p, 'pago_derechos') ||
        $contains($p, 'linea_captura') ||
        $contains($p, 'documentos_pago')
    ) {
        return 3;
    }

    // Paso 2: gestor / asignaciones
    if (
        $contains($p, 'section_asigna_gestor') ||
        $contains($p, 'tramite_view_gestor')
    ) {
        return 2;
    }

    // Paso 1: base del trámite
    if (
        $contains($p, 'read_tramite') ||
        $contains($p, 'listar_tramite') ||
        $contains($p, 'create_tramite') ||
        $contains($p, 'editar_tramite') ||
        $contains($p, 'delete_tramite') ||
        $contains($p, 'export_tramite') ||
        $contains($p, 'print_tramite') ||
        $contains($p, 'clone_tramite') ||
        $contains($p, 'section_inicial_datos') ||
        $contains($p, 'editar_tramite_principal') ||
        $contains($p, 'editar_tramite_asociado')
    ) {
        return 1;
    }

    // Menús: solo los del flujo
    if (starts_with($p, 'menu_')) {
        if ($contains($p, 'proceso_final')) {
            return 5;
        }
        if ($contains($p, 'tramites')) {
            return 1;
        }
        return null;
    }

    // Listados: final vs normal
    if (starts_with($p, 'listar_')) {
        if ($contains($p, 'final_tramite') || $contains($p, 'concluido')) {
            return 5;
        }
        if ($contains($p, 'tramite')) {
            return 1;
        }
        return null;
    }

    // Export/print: final vs normal
    if (starts_with($p, 'export_') || starts_with($p, 'print_')) {
        if ($contains($p, 'final_tramite')) {
            return 5;
        }
        if ($contains($p, 'tramite')) {
            return 1;
        }
        return null;
    }

    return null;
}

function pdoOrFail(array $env): PDO
{
    $dbHost = $env['database.default.hostname'] ?? 'localhost';
    $dbName = $env['database.default.database'] ?? 'procedures';
    $dbUser = $env['database.default.username'] ?? 'root';
    $dbPass = $env['database.default.password'] ?? '';
    $dbPort = (int)($env['database.default.port'] ?? 3306);

    $dsn = sprintf('mysql:host=%s;dbname=%s;port=%d;charset=utf8mb4', $dbHost, $dbName, $dbPort);

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (Throwable $e) {
        fwrite(STDERR, "DB connection failed: {$e->getMessage()}\n");
        exit(1);
    }
}

function tableHasColumn(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE :col");
    $stmt->execute(['col' => $column]);
    $row = $stmt->fetch();
    return !empty($row);
}

$env = parseEnvFile($envPath);
$pdo = pdoOrFail($env);

$permHasStatus = tableHasColumn($pdo, 'us_permissions', 'status');

// 1) Catálogo de permisos (activos si aplica) => tabla por step
$permSql = 'SELECT permission_name FROM us_permissions';
if ($permHasStatus) {
    $permSql .= ' WHERE status = 1';
}
$permSql .= ' ORDER BY permission_name ASC';

$allPerms = $pdo->query($permSql)->fetchAll();

$stepPerms = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
$adminPerms = [];

foreach ($allPerms as $row) {
    $perm = trim((string)($row['permission_name'] ?? ''));
    if ($perm === '') {
        continue;
    }
    $step = assignStepForPerm($perm);
    if ($step === null) {
        $adminPerms[$perm] = true;
        continue;
    }
    $stepPerms[$step][$perm] = true;
}

foreach ($stepPerms as $s => $set) {
    $perms = array_keys($set);
    sort($perms, SORT_STRING);
    $stepPerms[$s] = $perms;
}

$adminPermsList = array_keys($adminPerms);
sort($adminPermsList, SORT_STRING);

// 2) Roles
$roles = $pdo->query('SELECT id, role_name FROM us_roles ORDER BY role_name ASC')->fetchAll();

// 3) Permisos por rol
$rolePermStmt = $pdo->prepare(
    'SELECT p.permission_name'
    . ' FROM us_role_permissions rp'
    . ' INNER JOIN us_permissions p ON p.id = rp.permission_id'
    . ' WHERE rp.role_id = :role_id'
    . ($permHasStatus ? ' AND p.status = 1' : '')
    . ' ORDER BY p.permission_name ASC'
);

$matrix = []; // role_name => [step=>bool]
$details = []; // role_name => [step=>[perms]]

foreach ($roles as $r) {
    $roleId = (int)($r['id'] ?? 0);
    $roleName = (string)($r['role_name'] ?? '');
    if ($roleId <= 0 || $roleName === '') {
        continue;
    }

    $rolePermStmt->execute(['role_id' => $roleId]);
    $rpRows = $rolePermStmt->fetchAll();

    $rolePerms = [];
    foreach ($rpRows as $row) {
        $p = trim((string)($row['permission_name'] ?? ''));
        if ($p !== '') {
            $rolePerms[$p] = true;
        }
    }
    $rolePermList = array_keys($rolePerms);
    sort($rolePermList, SORT_STRING);

    $matrix[$roleName] = [1 => false, 2 => false, 3 => false, 4 => false, 5 => false];
    $details[$roleName] = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 'admin' => []];

    // Comparación por intersección rolePerms vs stepPerms[step]
    foreach ([1, 2, 3, 4, 5] as $step) {
        $matches = array_values(array_intersect($rolePermList, $stepPerms[$step]));
        if (!empty($matches)) {
            $matrix[$roleName][$step] = true;
            $details[$roleName][$step] = $matches;
        }
    }

    // Opcional: permisos que no caen en pasos 1-5
    $adminMatches = array_values(array_intersect($rolePermList, $adminPermsList));
    if (!empty($adminMatches)) {
        $details[$roleName]['admin'] = $adminMatches;
    }
}

// 4) Render reporte
$md = [];
$md[] = '# Ingeniería inversa: Roles → Steps tocados';
$md[] = '';
$md[] = 'Regla: se construye un catálogo de permisos por step (1–5) y, por cada rol, si tiene al menos un permiso del step entonces “toca” ese step.';
$md[] = '';
$md[] = 'Fuente DB: `us_roles`, `us_role_permissions`, `us_permissions`' . ($permHasStatus ? ' (solo permisos activos `status=1`).' : '.');
$md[] = '';
$md[] = '## Matriz (resumen)';
$md[] = '';
$md[] = '| Rol | Paso 1 | Paso 2 | Paso 3 | Paso 4 | Paso 5 | |';
$md[] = '|---|:---:|:---:|:---:|:---:|:---:|---|';

ksort($matrix, SORT_STRING);
foreach ($matrix as $roleName => $row) {
    $mark = static fn (bool $b): string => $b ? '✅' : '—';
    $count = 0;
    foreach ([1, 2, 3, 4, 5] as $s) {
        if (!empty($row[$s])) $count++;
    }

    $md[] = sprintf(
        '| %s | %s | %s | %s | %s | %s | %d steps |',
        str_replace('|', '\\|', $roleName),
        $mark((bool)$row[1]),
        $mark((bool)$row[2]),
        $mark((bool)$row[3]),
        $mark((bool)$row[4]),
        $mark((bool)$row[5]),
        $count
    );
}

$md[] = '';
$md[] = '## Detalle por rol (permisos que hacen match)';
$md[] = '';

foreach ($details as $roleName => $roleDetail) {
    $md[] = '### ' . $roleName;
    foreach ([1, 2, 3, 4, 5] as $step) {
        $matches = $roleDetail[$step] ?? [];
        $md[] = '';
        $md[] = "- Paso {$step}: " . (empty($matches) ? '—' : ('`' . implode('`, `', $matches) . '`'));
    }

    $adminMatches = $roleDetail['admin'] ?? [];
    $md[] = '';
    $md[] = '- Admin permisos (fuera de pasos 1–5): ' . (empty($adminMatches) ? '—' : ('`' . implode('`, `', $adminMatches) . '`'));
    $md[] = '';
}

$final = implode("\n", $md) . "\n";

if (file_put_contents($reportPath, $final) === false) {
    fwrite(STDERR, "Failed to write report: {$reportPath}\n");
    exit(1);
}

echo "OK: {$reportPath}\n";
