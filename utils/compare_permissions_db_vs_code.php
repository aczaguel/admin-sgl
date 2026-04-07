<?php

/**
 * Compara permisos en DB (us_permissions.permission_name) vs permisos referenciados en el código.
 *
 * Uso:
 *   php utils/compare_permissions_db_vs_code.php
 *
 * Salidas (en raíz del repo):
 *   - ACL_DB_DIFF.md
 *   - missing_permissions.sql (INSERTs para permisos faltantes en DB)
 *   - activate_used_permissions.sql (UPDATEs para activar permisos usados en código)
 *   - deactivate_unused_permissions.sql (UPDATEs para desactivar permisos sin uso en código)
 *
 * Nota: es un scan estático best-effort; puede haber falsos positivos/negativos.
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

$env = parseEnvFile($envPath);

$dbHost = $env['database.default.hostname'] ?? 'localhost';
$dbName = $env['database.default.database'] ?? 'procedures';
$dbUser = $env['database.default.username'] ?? 'root';
$dbPass = $env['database.default.password'] ?? '';
$dbPort = (int)($env['database.default.port'] ?? 3306);

$codePerms = scanCodeForPermissions($root . '/app');

$dbPermsAll = [];
$dbPermsActive = [];
$dbError = null;
try {
    $dbPermsAll = fetchDbPermissions($dbHost, $dbUser, $dbPass, $dbName, $dbPort, false);
    $dbPermsActive = fetchDbPermissions($dbHost, $dbUser, $dbPass, $dbName, $dbPort, true);
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$codeSet = array_fill_keys($codePerms, true);
$dbAllSet = array_fill_keys($dbPermsAll, true);
$dbActiveSet = array_fill_keys($dbPermsActive, true);

// En runtime, si existe status, solo cuentan los activos.
$missingActiveInDb = array_values(array_diff($codePerms, $dbPermsActive));
$unusedActiveInCode = array_values(array_diff($dbPermsActive, $codePerms));

// Útil para detectar permisos que existen en DB pero quedaron desactivados y el código los requiere.
$inactiveButUsed = array_values(array_intersect($codePerms, array_diff($dbPermsAll, $dbPermsActive)));

// Para inserts de faltantes, usamos “no existe en DB” (no “no está activo”).
$missingInDb = array_values(array_diff($codePerms, $dbPermsAll));

sort($missingInDb, SORT_STRING);
sort($missingActiveInDb, SORT_STRING);
sort($unusedActiveInCode, SORT_STRING);
sort($inactiveButUsed, SORT_STRING);

$reportPath = $root . '/ACL_DB_DIFF.md';
$sqlPath = $root . '/missing_permissions.sql';
$activateSqlPath = $root . '/activate_used_permissions.sql';
$deactivateSqlPath = $root . '/deactivate_unused_permissions.sql';

writeReport($reportPath, [
    'db_ok' => $dbError === null,
    'db_error' => $dbError,
    'db_host' => (string)$dbHost,
    'db_name' => (string)$dbName,
    'code_count' => count($codePerms),
    'db_count_all' => count($dbPermsAll),
    'db_count_active' => count($dbPermsActive),
    'missing' => $missingInDb,
    'missing_active' => $missingActiveInDb,
    'unused_active' => $unusedActiveInCode,
    'inactive_but_used' => $inactiveButUsed,
]);

writeSql($sqlPath, $missingInDb);
writeActivateSql($activateSqlPath, $codePerms);
writeDeactivateSql($deactivateSqlPath, $unusedActiveInCode);

echo "Wrote " . basename($reportPath) . "\n";
echo "Wrote " . basename($sqlPath) . "\n";
echo "Wrote " . basename($activateSqlPath) . "\n";
echo "Wrote " . basename($deactivateSqlPath) . "\n";

// --------------------------- helpers ---------------------------

function parseEnvFile(string $path): array
{
    $out = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return $out;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        // Supports: key = value
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Remove optional quotes
        if ($value !== '' && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }

        // Ignore inline comments after a space-hash
        $hashPos = strpos($value, ' #');
        if ($hashPos !== false) {
            $value = substr($value, 0, $hashPos);
            $value = rtrim($value);
        }

        if ($key !== '') {
            $out[$key] = $value;
        }
    }

    return $out;
}

function fetchDbPermissions(string $host, string $user, string $pass, string $db, int $port, bool $onlyActive): array
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    $mysqli->set_charset('utf8mb4');

    $where = "permission_name IS NOT NULL AND permission_name <> ''";
    if ($onlyActive && dbHasColumn($mysqli, $db, 'us_permissions', 'status')) {
        $where .= " AND status = 1";
    }

    $sql = "SELECT permission_name FROM us_permissions WHERE {$where} ORDER BY permission_name ASC";
    $result = $mysqli->query($sql);

    $perms = [];
    while ($row = $result->fetch_assoc()) {
        $p = trim((string)($row['permission_name'] ?? ''));
        if ($p !== '') {
            $perms[] = $p;
        }
    }

    $result->free();
    $mysqli->close();

    // Deduplicate
    $perms = array_values(array_unique($perms));
    sort($perms, SORT_STRING);
    return $perms;
}

function dbHasColumn(mysqli $mysqli, string $dbName, string $tableName, string $columnName): bool
{
    $sql = "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $dbName, $tableName, $columnName);
    $stmt->execute();
    $res = $stmt->get_result();
    $has = ($res !== false && $res->num_rows > 0);
    $stmt->close();
    return $has;
}

function scanCodeForPermissions(string $appDir): array
{
    $patterns = [
        // has_permission('perm' ...)
        "/has_permission\(\s*'([^']+)'/",
        // has_permission_strict('perm' ...)
        "/has_permission_strict\(\s*'([^']+)'/",
        // acl_require_permission('perm' ...)
        "/acl_require_permission\(\s*'([^']+)'/",
        // acl_throw_if_no_permission('perm' ...)
        "/acl_throw_if_no_permission\(\s*'([^']+)'/",
        // Strings in arrays commonly used for permission configs: 'permissions' => ['a','b']
        // We'll collect any 'something_like_perm' inside bracket arrays near 'permissions'
    ];

    $perms = [];

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($appDir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $file) {
        /** @var SplFileInfo $file */
        if (!$file->isFile()) {
            continue;
        }
        if (strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $content = @file_get_contents($file->getPathname());
        if ($content === false || $content === '') {
            continue;
        }

        foreach ($patterns as $re) {
            if (preg_match_all($re, $content, $m)) {
                foreach ($m[1] as $p) {
                    $p = trim((string)$p);
                    if ($p !== '') {
                        $perms[] = $p;
                    }
                }
            }
        }

        // Heurística extra: capturar permisos dentro del literal 'permissions' => [ ... ]
        if (preg_match_all("/'permissions'\s*=>\s*\[(.*?)\]/s", $content, $blocks)) {
            foreach ($blocks[1] as $block) {
                if (preg_match_all("/'([a-zA-Z0-9_]+)'/", $block, $pm)) {
                    foreach ($pm[1] as $p) {
                        $p = trim((string)$p);
                        if ($p !== '') {
                            $perms[] = $p;
                        }
                    }
                }
            }
        }
    }

    $perms = array_values(array_unique($perms));
    sort($perms, SORT_STRING);
    return $perms;
}

function writeReport(string $path, array $data): void
{
    $lines = [];
    $lines[] = "# Diff de permisos: Código vs DB\n\n";

    if (!$data['db_ok']) {
        $lines[] = "⚠️ No se pudo consultar la base de datos.\n\n";
        $lines[] = "Error: `" . safeInline((string)$data['db_error']) . "`\n\n";
        $lines[] = "Se generó el listado de permisos del código, pero no se pudo hacer el diff contra DB.\n";
        file_put_contents($path, implode('', $lines));
        return;
    }

    $lines[] = "DB: **" . safeInline((string)$data['db_host']) . "** / **" . safeInline((string)$data['db_name']) . "**\n\n";
    $lines[] = "- Permisos distintos en código: **" . (int)$data['code_count'] . "**\n";
    $lines[] = "- Permisos distintos en DB (`us_permissions`) (todos): **" . (int)$data['db_count_all'] . "**\n";
    $lines[] = "- Permisos distintos en DB (`us_permissions`) (activos `status=1`): **" . (int)$data['db_count_active'] . "**\n";
    $lines[] = "- Faltantes en DB (no existen, referenciados en código): **" . count($data['missing']) . "**\n";
    $lines[] = "- Faltantes activos (no están activos `status=1`, referenciados en código): **" . count($data['missing_active']) . "**\n";
    $lines[] = "- Activos sin uso en código (solo en DB con `status=1`): **" . count($data['unused_active']) . "**\n";
    $lines[] = "- Inactivos pero usados (existen en DB pero `status=0`): **" . count($data['inactive_but_used']) . "**\n\n";

    $lines[] = "## Faltantes en DB\n\n";
    if (empty($data['missing'])) {
        $lines[] = "(Ninguno)\n\n";
    } else {
        foreach ($data['missing'] as $p) {
            $lines[] = "- `" . safeInline((string)$p) . "`\n";
        }
        $lines[] = "\n";
    }

    $lines[] = "## Sin uso en código\n\n";
    if (empty($data['unused_active'])) {
        $lines[] = "(Ninguno)\n";
    } else {
        foreach ($data['unused_active'] as $p) {
            $lines[] = "- `" . safeInline((string)$p) . "`\n";
        }
    }

    $lines[] = "\n## Inactivos pero usados (DB status=0)\n\n";
    if (empty($data['inactive_but_used'])) {
        $lines[] = "(Ninguno)\n";
    } else {
        foreach ($data['inactive_but_used'] as $p) {
            $lines[] = "- `" . safeInline((string)$p) . "`\n";
        }
    }

    file_put_contents($path, implode('', $lines));
}

function writeSql(string $path, array $missingPerms): void
{
    $lines = [];
    $lines[] = "-- Permisos faltantes en us_permissions (generado automáticamente)\n";
    $lines[] = "-- Revísalo antes de ejecutar en PROD.\n\n";

    if (empty($missingPerms)) {
        $lines[] = "-- No hay permisos faltantes.\n";
        file_put_contents($path, implode('', $lines));
        return;
    }

    foreach ($missingPerms as $p) {
        // Description queda NULL para que se complete manualmente después.
        // Forma idempotente (no inserta si ya existe el permission_name).
        $permEsc = addslashes((string)$p);
        $lines[] = "INSERT INTO us_permissions (permission_name, description, created_at, updated_at)\n";
        $lines[] = "SELECT '{$permEsc}', NULL, NOW(), NOW()\n";
        $lines[] = "FROM DUAL\n";
        $lines[] = "WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = '{$permEsc}');\n\n";
    }

    file_put_contents($path, implode('', $lines));
}

function writeDeactivateSql(string $path, array $unusedPerms): void
{
    $lines = [];
    $lines[] = "-- Desactiva permisos que están en DB pero no se usan en código (generado automáticamente)\n";
    $lines[] = "-- Recomendación: agrega primero la columna `status` con default 1.\n";
    $lines[] = "--\n";
    $lines[] = "-- ALTER TABLE us_permissions ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1;\n";
    $lines[] = "--\n";
    $lines[] = "-- Nota: revisa la lista antes de ejecutar en PROD; puede haber permisos usados dinámicamente.\n\n";

    if (empty($unusedPerms)) {
        $lines[] = "-- No hay permisos para desactivar.\n";
        file_put_contents($path, implode('', $lines));
        return;
    }

    // Chunk para evitar IN() gigantes.
    $chunks = array_chunk($unusedPerms, 50);
    foreach ($chunks as $chunk) {
        $escaped = [];
        foreach ($chunk as $p) {
            $p = (string)$p;
            $escaped[] = "'" . addslashes($p) . "'";
        }

        $lines[] = "UPDATE us_permissions\n";
        $lines[] = "SET status = 0\n";
        $lines[] = "WHERE permission_name IN (" . implode(', ', $escaped) . ")\n";
        $lines[] = "  AND (status IS NULL OR status <> 0);\n\n";
    }

    file_put_contents($path, implode('', $lines));
}

function writeActivateSql(string $path, array $usedPerms): void
{
    $lines = [];
    $lines[] = "-- Activa permisos que se usan en código (generado automáticamente)\n";
    $lines[] = "-- Recomendación: agrega primero la columna `status` con default 1.\n";
    $lines[] = "--\n";
    $lines[] = "-- ALTER TABLE us_permissions ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1;\n";
    $lines[] = "--\n\n";

    if (empty($usedPerms)) {
        $lines[] = "-- No hay permisos detectados en código.\n";
        file_put_contents($path, implode('', $lines));
        return;
    }

    $chunks = array_chunk($usedPerms, 50);
    foreach ($chunks as $chunk) {
        $escaped = [];
        foreach ($chunk as $p) {
            $p = (string)$p;
            $escaped[] = "'" . addslashes($p) . "'";
        }

        $lines[] = "UPDATE us_permissions\n";
        $lines[] = "SET status = 1\n";
        $lines[] = "WHERE permission_name IN (" . implode(', ', $escaped) . ")\n";
        $lines[] = "  AND (status IS NULL OR status <> 1);\n\n";
    }

    file_put_contents($path, implode('', $lines));
}

function safeInline(string $s): string
{
    // avoid breaking markdown inline code
    return str_replace('`', "'", $s);
}
