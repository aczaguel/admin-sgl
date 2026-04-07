<?php

/**
 * Escanea el código (app/) en busca de gates por rol (helpers is_*) que normalmente
 * impiden que el control sea 100% por permisos activables/desactivables.
 *
 * Uso:
 *   php utils/scan_role_based_gates.php
 *
 * Salida:
 *   ROLE_BASED_GATES.md (en la raíz del repo)
 */

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Cannot resolve repo root\n");
    exit(1);
}

$appDir = $root . '/app';
if (!is_dir($appDir)) {
    fwrite(STDERR, "Missing app/ dir\n");
    exit(1);
}

$outPath = $root . '/ROLE_BASED_GATES.md';

$roleHelpers = [
    'is_super_admin',
    'is_admin',
    'is_starter',
    'is_executer',
    'is_closer',
    'is_viewer',
    'is_client',
    'is_read_only',
    'is_authorizer_editor',
    'is_authorizer_simple',
];

$helperAlt = implode('|', array_map(static fn($s) => preg_quote($s, '/'), $roleHelpers));

// Detecta llamadas a helpers is_* rol.
$reRoleCall = '/\b(' . $helperAlt . ')\s*\(/';
// Detecta variables tipo $isCloserRole / $isAuthorizerRole, etc.
$reRoleVar = '/\$is[A-Za-z0-9_]*Role\b/';

$matches = [];
$countsByHelper = array_fill_keys($roleHelpers, 0);

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

    $absPath = $file->getPathname();
    $relPath = ltrim(str_replace($root, '', $absPath), '/');

    $lines = @file($absPath, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        continue;
    }

    foreach ($lines as $idx => $line) {
        $lineNo = $idx + 1;
        $snippet = trim((string)$line);
        if ($snippet === '') {
            continue;
        }

        // Evitar ruido de definiciones de funciones en el helper.
        if (strpos($relPath, 'app/Helpers/permissions_helper.php') !== false && strpos($snippet, 'function is_') !== false) {
            continue;
        }

        $hit = false;
        if (preg_match($reRoleCall, $snippet, $m)) {
            $hit = true;
            $helper = (string)($m[1] ?? '');
            if ($helper !== '' && array_key_exists($helper, $countsByHelper)) {
                $countsByHelper[$helper]++;
            }
        }

        if (preg_match($reRoleVar, $snippet)) {
            $hit = true;
        }

        if (!$hit) {
            continue;
        }

        $matches[] = [
            'file' => $relPath,
            'line' => $lineNo,
            'snippet' => $snippet,
        ];
    }
}

usort($matches, static function (array $a, array $b): int {
    $cmp = strcmp((string)$a['file'], (string)$b['file']);
    if ($cmp !== 0) {
        return $cmp;
    }
    return ((int)$a['line']) <=> ((int)$b['line']);
});

$md = [];
$md[] = '# ROLE_BASED_GATES';
$md[] = '';
$md[] = 'Listado de líneas donde el acceso/flujo depende de helpers de rol (`is_*`).';
$md[] = 'Mientras existan estos gates, activar/desactivar permisos no dará control total (porque hay bypass o bloqueo por rol).';
$md[] = '';
$md[] = '## Resumen (conteo por helper)';
$md[] = '';
$md[] = '| Helper | Ocurrencias |';
$md[] = '|---|---:|';
foreach ($countsByHelper as $helper => $count) {
    $md[] = '| `' . $helper . '` | ' . (int)$count . ' |';
}
$md[] = '';
$md[] = '## Detalle (archivo / línea / snippet)';
$md[] = '';

if (empty($matches)) {
    $md[] = '_No se encontraron gates por rol en app/._';
} else {
    foreach ($matches as $row) {
        $md[] = '- ' . $row['file'] . '#L' . $row['line'] . ' — `' . str_replace('`', "'", (string)$row['snippet']) . '`';
    }
}

$md[] = '';
$md[] = '---';
$md[] = 'Generado por `php utils/scan_role_based_gates.php`.';

file_put_contents($outPath, implode("\n", $md));
echo 'Wrote ' . basename($outPath) . "\n";
