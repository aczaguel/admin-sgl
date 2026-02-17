<?php

/**
 * Helper para manejar el "cliente activo" (filtro global) vía sesión.
 *
 * Reglas:
 * - Si viene cliente_id por GET:
 *   - '' => limpiar (Todos los clientes)
 *   - numérico > 0 => validar acceso y guardar en sesión
 * - Si no viene por GET, usar sesión.
 * - Si el usuario solo tiene 1 cliente asignado (no admin), se considera contexto fijo.
 */

if (!function_exists('get_clientes_lista_for_user')) {
    /**
     * Retorna lista de clientes (tabla cliente) visibles para el usuario.
     * Formato: [['id' => 1, 'nombre' => '...'], ...]
     */
    function get_clientes_lista_for_user($userId = null): array
    {
        helper('cliente_filter');

        $session = session();
        if ($userId === null) {
            $userId = $session->get('id');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('cliente');
        $builder->select("id, COALESCE(NULLIF(razon_social,''), NULLIF(nombre,''), CONCAT('Cliente #', id)) as nombre", false);

        $clienteIds = get_user_cliente_ids($userId);
        if (is_array($clienteIds)) {
            if (empty($clienteIds)) {
                return [];
            }
            $builder->whereIn('id', array_map('intval', $clienteIds));
        }

        $builder->orderBy('nombre', 'ASC');
        return $builder->get()->getResultArray();
    }
}

if (!function_exists('resolve_active_cliente_id')) {
    /**
     * Resuelve el cliente activo (tabla cliente.id) y lo persiste en sesión.
     */
    function resolve_active_cliente_id($userId = null, $requestedClienteId = null): ?int
    {
        helper('cliente_filter');

        $session = session();
        if ($userId === null) {
            $userId = $session->get('id');
        }

        // Si el usuario solo tiene 1 cliente asignado, fijar contexto (sin selector)
        $clienteIds = get_user_cliente_ids($userId);
        if (is_array($clienteIds) && count($clienteIds) === 1) {
            $session->remove('active_cliente_id');
            return (int) $clienteIds[0];
        }

        // Si viene por GET y es string vacía => limpiar
        if ($requestedClienteId === '') {
            $session->remove('active_cliente_id');
            return null;
        }

        // Si viene por GET y es numérico => validar + guardar
        if ($requestedClienteId !== null && $requestedClienteId !== '') {
            if (!is_numeric($requestedClienteId)) {
                return $session->get('active_cliente_id') ? (int) $session->get('active_cliente_id') : null;
            }

            $clienteId = (int) $requestedClienteId;
            if ($clienteId <= 0) {
                $session->remove('active_cliente_id');
                return null;
            }

            // Admin con acceso global puede escoger; usuarios normales solo si tienen acceso
            if (user_has_global_cliente_access($userId) || has_access_to_cliente($clienteId, $userId)) {
                $session->set('active_cliente_id', $clienteId);
                return $clienteId;
            }

            // No autorizado: no cambiar contexto
            return $session->get('active_cliente_id') ? (int) $session->get('active_cliente_id') : null;
        }

        // Si no viene por GET: usar sesión (si existe)
        $active = $session->get('active_cliente_id');
        if ($active === null || $active === '') {
            return null;
        }

        $active = (int) $active;
        if ($active <= 0) {
            $session->remove('active_cliente_id');
            return null;
        }

        if (!user_has_global_cliente_access($userId) && !has_access_to_cliente($active, $userId)) {
            $session->remove('active_cliente_id');
            return null;
        }

        return $active;
    }
}
