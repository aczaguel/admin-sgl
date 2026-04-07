<?php

if (!function_exists('acl_get_version')) {
    /**
     * Obtiene la versión global del ACL desde BD.
     *
     * Retorna null si la tabla no existe o no es accesible (fail-open).
     */
    function acl_get_version(): ?int
    {
        try {
            $db = \Config\Database::connect();
            $row = $db->table('us_acl_version')
                ->select('version')
                ->where('id', 1)
                ->get()
                ->getRowArray();
            if (!$row || !isset($row['version'])) {
                return null;
            }
            return (int) $row['version'];
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('acl_bump_version')) {
    /**
     * Incrementa la versión global del ACL.
     *
     * Retorna la nueva versión si se pudo actualizar; null si no (fail-open).
     */
    function acl_bump_version(): ?int
    {
        try {
            $db = \Config\Database::connect();

            // Asegurar la fila id=1.
            $exists = $db->table('us_acl_version')
                ->select('id')
                ->where('id', 1)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!$exists) {
                $db->table('us_acl_version')->insert([
                    'id' => 1,
                    'version' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return 1;
            }

            $db->table('us_acl_version')
                ->set('version', 'version + 1', false)
                ->set('updated_at', date('Y-m-d H:i:s'))
                ->where('id', 1)
                ->update();

            return acl_get_version();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
