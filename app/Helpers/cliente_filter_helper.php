<?php

/**
 * ============================================================================
 * HELPER DE FILTRADO DE CLIENTES - ARQUITECTURA MULTI-TENANCY
 * ============================================================================
 * 
 * Este helper implementa la lógica de segregación de datos basada en la
 * relación usuario-cliente (tabla cliente_user).
 * 
 * PROPÓSITO:
 * - Asegurar que cada usuario solo vea información de sus clientes asignados
 * - Implementar filtrado automático en consultas de trámites
 * - Prevenir acceso no autorizado a información de otros clientes
 * 
 * ARQUITECTURA:
 * - Tabla pivote: cliente_user (user_id, cliente_id)
 * - Cada usuario puede estar asignado a uno o varios clientes
 * - Los trámites se relacionan a clientes a través de cli_directo
 * 
 * USO EMPRESARIAL:
 * - Permite que la dueña otorgue acceso al sistema a sus clientes
 * - Cada cliente tiene sus propios ejecutivos operando exclusivamente sus trámites
 * - Se protege la confidencialidad entre clientes competidores
 * 
 * MODO ADMINISTRADOR:
 * - Usuarios con roles 'admin' o 'superadmin' tienen acceso COMPLETO
 * - No se aplica filtro de clientes a estos usuarios
 * - Pueden ver TODOS los trámites de TODOS los clientes
 * 
 * ============================================================================
 */

if (!function_exists('user_is_admin')) {
    /**
     * Verifica si el usuario tiene rol de administrador
     * 
     * BYPASS DE FILTROS:
     * Los usuarios con estos roles tienen acceso completo al sistema
     * sin restricciones de cliente.
     *
     * @param int|null $userId ID del usuario (null = usuario en sesión)
     * @return bool True si es administrador, false en caso contrario
     */
    function user_is_admin($userId = null)
    {
        $session = session();
        
        // Si no se proporciona userId, usar el de la sesión
        if ($userId === null) {
            $userId = $session->get('id');
            
            // Verificar roles en sesión para el usuario actual
            $userRoles = $session->get('user_roles');
            if ($userRoles !== null && is_array($userRoles)) {
                return in_array('admin', $userRoles) || 
                       in_array('superadmin', $userRoles) ||
                       in_array('Admin', $userRoles) ||
                       in_array('SuperAdmin', $userRoles);
            }
        }
        
        // Si no está en sesión, consultar la base de datos (sin depender de modelos)
        if (!is_numeric($userId) || (int) $userId <= 0) {
            return false;
        }

        $db = \Config\Database::connect();
        $builder = $db->table('us_user_roles as ur');
        $builder->select('r.role_name');
        $builder->join('us_roles as r', 'ur.role_id = r.id', 'inner');
        $builder->where('ur.user_id', (int) $userId);
        $rows = $builder->get()->getResultArray();

        $roles = [];
        foreach ($rows as $row) {
            if (!empty($row['role_name'])) {
                $roles[] = strtolower((string) $row['role_name']);
            }
        }

        return in_array('admin', $roles, true) || in_array('superadmin', $roles, true);
    }
}

if (!function_exists('user_has_global_cliente_access')) {
    /**
     * Determina si un admin tiene acceso global a todos los clientes.
     * - Admin sin clientes asignados => acceso global
     * - Admin con clientes asignados => acceso limitado
     */
    function user_has_global_cliente_access($userId = null)
    {
        $session = session();

        if ($userId === null) {
            $userId = $session->get('id');
        }

        if (!user_is_admin($userId)) {
            return false;
        }

        if (!is_numeric($userId) || (int) $userId <= 0) {
            return false;
        }

        if ($session->get('id') == $userId) {
            $cached = $session->get('admin_global_client_access');
            if ($cached !== null) {
                return (bool) $cached;
            }
        }

        $db = \Config\Database::connect();
        $row = $db->table('cliente_user')
            ->select('cliente_id')
            ->where('user_id', (int) $userId)
            ->limit(1)
            ->get()
            ->getRowArray();

        $isGlobal = empty($row);

        if ($session->get('id') == $userId) {
            $session->set('admin_global_client_access', $isGlobal);
        }

        return $isGlobal;
    }
}

if (!function_exists('get_user_cliente_ids')) {
    /**
     * Obtiene los IDs de clientes asignados al usuario actual
     * 
     * IMPORTANTE - MODO ADMINISTRADOR:
     * Si el usuario tiene rol 'admin' o 'superadmin', retorna NULL
     * indicando que tiene acceso completo sin restricciones.
     *
     * @param int|null $userId ID del usuario (null = usuario en sesión)
     * @return array|null Array de IDs de clientes, o NULL si es admin (acceso total)
     */
    function get_user_cliente_ids($userId = null)
    {
        $session = session();
        
        // Si no se proporciona userId, usar el de la sesión
        if ($userId === null) {
            $userId = $session->get('id');
        }
        
        // Admin con acceso global ve todos los clientes
        if (user_has_global_cliente_access($userId)) {
            return null; // NULL = acceso a TODOS los clientes
        }
        
        // Verificar si ya está en sesión (optimización)
        $clientsInSession = $session->get('clients_by_user');
        if ($clientsInSession !== null && $session->get('id') == $userId) {
            return $clientsInSession;
        }
        
        // Si no está en sesión, consultar la base de datos (sin depender de modelos)
        if (!is_numeric($userId) || (int) $userId <= 0) {
            return [];
        }

        $db = \Config\Database::connect();
        $rows = $db->table('cliente_user')
            ->select('cliente_id')
            ->where('user_id', (int) $userId)
            ->get()
            ->getResultArray();

        $clienteIds = [];
        foreach ($rows as $row) {
            if (isset($row['cliente_id']) && is_numeric($row['cliente_id'])) {
                $clienteIds[] = (int) $row['cliente_id'];
            }
        }
        $clienteIds = array_values(array_unique($clienteIds));

        if ($session->get('id') == $userId) {
            $session->set('clients_by_user', $clienteIds);
        }

        return $clienteIds;
    }
}

if (!function_exists('has_access_to_cliente')) {
    /**
     * Verifica si el usuario tiene acceso a un cliente específico
     * 
     * MODO ADMINISTRADOR:
     * Los administradores siempre retornan true (acceso a todos)
     *
     * @param int $clienteId ID del cliente a verificar
     * @param int|null $userId ID del usuario (null = usuario en sesión)
     * @return bool True si tiene acceso, false en caso contrario
     */
    function has_access_to_cliente($clienteId, $userId = null)
    {
        if (user_has_global_cliente_access($userId)) {
            return true;
        }

        $clienteIds = get_user_cliente_ids($userId);
        
        if (!is_array($clienteIds)) {
            return false;
        }

        return in_array((int) $clienteId, array_map('intval', $clienteIds), true);
    }
}

if (!function_exists('get_cliente_filter_sql')) {
    /**
     * Genera la cláusula SQL WHERE para filtrar por clientes del usuario
     * 
     * IMPORTANTE: Esta función genera SQL dinámico. Los IDs de cliente
     * se validan como enteros para prevenir inyección SQL.
     * 
     * MODO ADMINISTRADOR:
     * Si el usuario es administrador, retorna "1 = 1" (sin filtro, ve todo)
     *
     * @param int|null $userId ID del usuario (null = usuario en sesión)
     * @param string $tramiteTable Alias o nombre de la tabla tramite
     * @return string Cláusula SQL WHERE (sin la palabra WHERE)
     */
    function get_cliente_filter_sql($userId = null, $tramiteTable = 'tramite')
    {
        $clienteIds = get_user_cliente_ids($userId);

        // Si es NULL (admin con acceso global), no aplicar filtro
        if ($clienteIds === null || user_has_global_cliente_access($userId)) {
            return "1 = 1"; // Condición que siempre es verdadera (sin filtro)
        }
        
        // Si no tiene clientes asignados, retornar condición que no coincida
        if (empty($clienteIds)) {
            return "1 = 0"; // No puede ver ningún trámite
        }
        
        // Validar y sanitizar los IDs (solo enteros)
        $clienteIds = array_filter($clienteIds, 'is_numeric');
        $clienteIds = array_map('intval', $clienteIds);
        
        if (empty($clienteIds)) {
            return "1 = 0";
        }
        
        $clienteIdsStr = implode(',', $clienteIds);
        
        // Generar la subconsulta SQL
        return "{$tramiteTable}.id IN (
            SELECT 
                t.id
            FROM 
                cliente_user cu
            INNER JOIN 
                cliente c ON cu.cliente_id = c.id
            INNER JOIN
                cli_directo cd ON cd.cliente_id = c.id
            INNER JOIN 
                tramite t ON cd.id = t.cli_directo_id
            WHERE 
                cu.cliente_id IN ({$clienteIdsStr})
        )";
    }
}

if (!function_exists('get_cliente_filter_sql_for_cliente_id')) {
    /**
     * Genera cláusula SQL WHERE para filtrar por un cliente específico (tabla cliente.id).
     * Respeta multi-tenancy: si el usuario no es admin y no tiene acceso, retorna "1 = 0".
     */
    function get_cliente_filter_sql_for_cliente_id($clienteId, $userId = null, $tramiteTable = 'tramite')
    {
        $clienteId = is_numeric($clienteId) ? (int) $clienteId : 0;
        if ($clienteId <= 0) {
            return '1 = 1';
        }

        if (!user_has_global_cliente_access($userId) && !has_access_to_cliente($clienteId, $userId)) {
            return '1 = 0';
        }

        // Filtrar trámites a través de cli_directo -> cliente
        return "{$tramiteTable}.id IN (
            SELECT t.id
            FROM tramite t
            INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
            WHERE cd.cliente_id = {$clienteId}
        )";
    }
}

if (!function_exists('get_tramite_filter_sql')) {
    /**
     * Devuelve el filtro SQL para trámites considerando el "cliente activo" en sesión.
     * - Si hay cliente activo: filtra SOLO ese cliente
     * - Si no: aplica el filtro multi-tenancy estándar (cliente_user)
     */
    function get_tramite_filter_sql($userId = null, $tramiteTable = 'tramite', $requestedClienteId = null)
    {
        helper('cliente_context');

        $activeClienteId = resolve_active_cliente_id($userId, $requestedClienteId);
        if (!empty($activeClienteId)) {
            return get_cliente_filter_sql_for_cliente_id($activeClienteId, $userId, $tramiteTable);
        }

        return get_cliente_filter_sql($userId, $tramiteTable);
    }
}

if (!function_exists('apply_cliente_filter')) {
    /**
     * Aplica el filtro de clientes a un Query Builder de CodeIgniter
     * 
     * MODO ADMINISTRADOR:
     * Si el usuario es administrador, NO se aplica ningún filtro
     * (puede ver todos los trámites)
     *
     * @param object $builder Query Builder de CodeIgniter
     * @param int|null $userId ID del usuario (null = usuario en sesión)
     * @param string $tramiteTable Alias o nombre de la tabla tramite
     * @return object Query Builder modificado
     */
    function apply_cliente_filter($builder, $userId = null, $tramiteTable = 'tramite')
    {
        // No aplicar filtro si tiene acceso global
        if (user_has_global_cliente_access($userId)) {
            return $builder;
        }
        
        $filterSql = get_cliente_filter_sql($userId, $tramiteTable);
        $builder->where($filterSql, null, false);
        return $builder;
    }
}

if (!function_exists('is_user_cliente')) {
    /**
     * Verifica si el usuario es un cliente (tiene relación en cliente_user)
     *
     * @param int|null $userId ID del usuario (null = usuario en sesión)
     * @return bool True si es cliente, false en caso contrario
     */
    function is_user_cliente($userId = null)
    {
        $session = session();
        
        // Si no se proporciona userId, usar el de la sesión
        if ($userId === null) {
            $userId = $session->get('id');
            
            // Verificar si ya está en sesión
            $userClient = $session->get('user_client');
            if ($userClient !== null) {
                return $userClient['is_client'] ?? false;
            }
        }
        
        if (!is_numeric($userId) || (int) $userId <= 0) {
            return false;
        }

        // Consultar si es cliente (tiene al menos 1 relación en cliente_user)
        $db = \Config\Database::connect();
        $row = $db->table('cliente_user')
            ->select('cliente_id')
            ->where('user_id', (int) $userId)
            ->limit(1)
            ->get()
            ->getRowArray();

        $isClient = !empty($row);
        if ($session->get('id') == $userId) {
            $session->set('user_client', ['is_client' => $isClient]);
        }

        return $isClient;
    }
}

if (!function_exists('validate_tramite_access')) {
    /**
     * Valida si el usuario tiene acceso a un trámite específico
     * 
     * Esta función verifica que el trámite pertenezca a uno de los
     * clientes asignados al usuario.
     * 
     * MODO ADMINISTRADOR:
     * Los administradores siempre tienen acceso (retorna true)
     *
     * @param int $tramiteId ID del trámite
     * @param int|null $userId ID del usuario (null = usuario en sesión)
     * @return bool True si tiene acceso, false en caso contrario
     */
    function validate_tramite_access($tramiteId, $userId = null)
    {
        if (!is_numeric($tramiteId)) {
            return false;
        }

        // Si tiene acceso global, permite todo
        if (user_has_global_cliente_access($userId)) {
            return true;
        }
        
        $db = \Config\Database::connect();
        $builder = $db->table('tramite as t');
        
        $clienteIds = get_user_cliente_ids($userId);
        
        if (empty($clienteIds)) {
            return false;
        }
        
        // Validar IDs
        $clienteIds = array_filter($clienteIds, 'is_numeric');
        $clienteIds = array_map('intval', $clienteIds);
        
        if (empty($clienteIds)) {
            return false;
        }
        
        $builder->select('t.id');
        $builder->join('cli_directo cd', 'cd.id = t.cli_directo_id', 'inner');
        $builder->join('cliente c', 'c.id = cd.cliente_id', 'inner');
        $builder->where('t.id', $tramiteId);
        $builder->whereIn('c.id', $clienteIds);
        
        $result = $builder->get()->getRowArray();
        
        return !empty($result);
    }
}

if (!function_exists('log_unauthorized_access_attempt')) {
    /**
     * Registra un intento de acceso no autorizado
     *
     * @param string $resource Recurso al que se intentó acceder
     * @param int $resourceId ID del recurso
     * @param int|null $userId ID del usuario (null = usuario en sesión)
     * @return void
     */
    function log_unauthorized_access_attempt($resource, $resourceId, $userId = null)
    {
        $session = session();
        
        if ($userId === null) {
            $userId = $session->get('id');
        }
        
        $username = $session->get('username') ?? 'unknown';
        
        log_message('warning', sprintf(
            'Intento de acceso no autorizado: Usuario %d (%s) intentó acceder a %s ID: %d',
            $userId,
            $username,
            $resource,
            $resourceId
        ));
    }
}

if (!function_exists('get_cliente_relation_filter')) {
    /**
     * Obtiene las condiciones de filtro para setRelation de GroceryCrud
     * 
     * PROTECCIÓN DE CONFIDENCIALIDAD:
     * Esta función retorna las condiciones WHERE para filtrar cli_directo
     * y mostrar solo los clientes directos relacionados con los clientes
     * asignados al usuario, ocultando los nombres de otros clientes.
     * 
     * USO en GroceryCrud:
     * $conditions = get_cliente_relation_filter($userId);
     * if ($conditions !== null) {
     *     $crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $conditions);
     * } else {
     *     $crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
     * }
     *
     * @param int|null $userId ID del usuario (null = usuario en sesión)
     * @return array|null Array con condiciones WHERE, o NULL si es admin (sin filtro)
     */
    function get_cliente_relation_filter($userId = null)
    {
        // Si tiene acceso global, no aplicar filtro
        if (user_has_global_cliente_access($userId)) {
            return null;
        }
        
        // Obtener clientes del usuario
        $clienteIds = get_user_cliente_ids($userId);
        
        // Si no tiene clientes, retornar condición imposible
        if (empty($clienteIds)) {
            return ['id' => 0]; // Ningún cli_directo con id = 0
        }
        
        // Retornar condición para filtrar por cliente_id
        return ['cliente_id' => $clienteIds];
    }
}
