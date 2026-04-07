<?php

/**
 * Helpers de guard/deny para ACL.
 *
 * Objetivo: centralizar respuestas de acceso denegado (JSON vs redirect)
 * y checks comunes (login, permiso, multi-tenancy) para reducir duplicación.
 */

if (! function_exists('acl_wants_json')) {
    function acl_wants_json($request = null): bool
    {
        $request = $request ?? service('request');
        if (! $request) {
            return false;
        }

        if (method_exists($request, 'isAJAX') && $request->isAJAX()) {
            return true;
        }

        $accept = strtolower((string) $request->getHeaderLine('Accept'));
        if (strpos($accept, 'application/json') !== false || strpos($accept, '+json') !== false) {
            return true;
        }

        $xrw = strtolower((string) $request->getHeaderLine('X-Requested-With'));
        if ($xrw === 'xmlhttprequest') {
            return true;
        }

        return false;
    }
}

if (! function_exists('acl_deny')) {
    /**
     * Respuesta consistente para denegación.
     * - JSON: {success:false,status:"error",message,csrfHash}
     * - Web: redirect con flash 'error'
     */
    function acl_deny(string $message = 'Acceso denegado.', int $statusCode = 403, ?string $redirectTo = null, ?bool $forceJson = null)
    {
        $request = service('request');
        $wantsJson = $forceJson ?? acl_wants_json($request);

        if ($wantsJson) {
            $response = service('response');
            return $response->setStatusCode($statusCode)->setJSON([
                'success' => false,
                'status' => 'error',
                'message' => $message,
                'csrfHash' => csrf_hash(),
            ]);
        }

        if ($redirectTo === null || $redirectTo === '') {
            $redirectTo = '/deskapp/dashboard';
        }

        return redirect()->to($redirectTo)->with('error', $message);
    }
}

if (! function_exists('acl_deny_text')) {
    /**
     * Respuesta de texto plano para contratos que esperan body string.
     */
    function acl_deny_text(string $message = 'Acceso denegado', int $statusCode = 403)
    {
        $response = service('response');
        return $response->setStatusCode($statusCode)->setBody($message);
    }
}

if (! function_exists('acl_json_empty')) {
    /**
     * Respuesta JSON vacía, conservando contrato de endpoints que esperan `[]`.
     */
    function acl_json_empty(int $statusCode = 400)
    {
        $response = service('response');
        return $response->setStatusCode($statusCode)->setJSON([]);
    }
}

if (! function_exists('acl_require_login')) {
    function acl_require_login(?string $redirectTo = '/', string $message = 'Sesión expirada.', ?bool $forceJson = null)
    {
        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        if ($userId > 0) {
            return null;
        }

        return acl_deny($message, 401, $redirectTo, $forceJson);
    }
}

if (! function_exists('acl_require_permission')) {
    function acl_require_permission(string $permission, $roles, $perms, ?string $message = null, ?string $redirectTo = '/deskapp/dashboard', int $statusCode = 403, ?bool $forceJson = null)
    {
        helper(['permissions']);

        $message = $message ?? 'Acceso denegado.';
        if (has_permission($permission, $perms, $roles)) {
            return null;
        }

        return acl_deny($message, $statusCode, $redirectTo, $forceJson);
    }
}

if (! function_exists('acl_require_tramite_tenant_access')) {
    function acl_has_tramite_tenant_access(int $tramiteId, int $userId, $roles, $perms = null): bool
    {
        helper(['cliente_filter', 'permissions']);

        $tramiteId = (int) $tramiteId;
        $userId = (int) $userId;
        if ($tramiteId <= 0 || $userId <= 0) {
            return false;
        }

        // Bypass controlado por permiso (Super Admin pasa vía has_permission()).
        if ($perms === null) {
            try {
                $perms = session()->get('user_permissions');
            } catch (\Throwable $e) {
                $perms = [];
            }
        }

        if (has_permission('bypass_tramite_tenant_access', $perms, $roles)) {
            return true;
        }

        return (bool) validate_tramite_access($tramiteId, $userId);
    }

    function acl_require_tramite_tenant_access(int $tramiteId, int $userId, $roles, ?string $message = 'Acceso denegado.', ?string $redirectTo = '/deskapp/dashboard', int $statusCode = 403, ?bool $forceJson = null, $perms = null)
    {
        helper(['cliente_filter', 'permissions']);

        $tramiteId = (int) $tramiteId;
        $userId = (int) $userId;
        if ($tramiteId <= 0 || $userId <= 0) {
            return acl_deny('Datos insuficientes.', 400, $redirectTo, $forceJson);
        }

        if (acl_has_tramite_tenant_access($tramiteId, $userId, $roles, $perms)) {
            return null;
        }

        return acl_deny($message, $statusCode, $redirectTo, $forceJson);
    }
}

if (! function_exists('acl_throw_if_no_tramite_tenant_access')) {
    /**
     * Guard para flujos que usan excepciones (por ejemplo callbacks de GroceryCRUD).
     * Lanza \Exception si el usuario no tiene acceso al trámite.
     */
    function acl_throw_if_no_tramite_tenant_access(int $tramiteId, int $userId, $roles, string $message = 'Acceso denegado.', string $exceptionClass = '\\Exception', $perms = null)
    {
        if (!acl_has_tramite_tenant_access((int) $tramiteId, (int) $userId, $roles, $perms)) {
            $exceptionClass = $exceptionClass ?: '\\Exception';
            throw new $exceptionClass($message);
        }
    }
}

if (! function_exists('acl_throw_if_no_permission')) {
    /**
     * Guard para flujos que usan excepciones.
     * Lanza excepción si no se cuenta con un permiso específico.
     */
    function acl_throw_if_no_permission(string $permission, $roles, $perms, string $message = 'Acceso denegado.', string $exceptionClass = '\\Exception')
    {
        helper(['permissions']);
        if (has_permission($permission, $perms, $roles)) {
            return;
        }

        $exceptionClass = $exceptionClass ?: '\\Exception';
        throw new $exceptionClass($message);
    }
}

if (! function_exists('acl_throw_if_no_any_permission')) {
    /**
     * Guard para flujos que usan excepciones.
     * Lanza excepción si no se cuenta con al menos uno de los permisos indicados.
     *
     * @param string[] $permissions
     */
    function acl_throw_if_no_any_permission(array $permissions, $roles, $perms, string $message = 'Acceso denegado.', string $exceptionClass = '\\Exception')
    {
        helper(['permissions']);

        foreach ($permissions as $permission) {
            if (is_string($permission) && $permission !== '' && has_permission($permission, $perms, $roles)) {
                return;
            }
        }

        $exceptionClass = $exceptionClass ?: '\\Exception';
        throw new $exceptionClass($message);
    }
}

if (! function_exists('acl_throw_if_tramite_id_mismatch')) {
    /**
     * Guard para flujos que usan excepciones.
     * Lanza excepción cuando el tramite_id de un registro no coincide con el trámite esperado.
     */
    function acl_throw_if_tramite_id_mismatch(int $rowTramiteId, int $expectedTramiteId, string $message = 'Acceso denegado.', string $exceptionClass = '\\Exception')
    {
        if ((int) $rowTramiteId === (int) $expectedTramiteId) {
            return;
        }

        $exceptionClass = $exceptionClass ?: '\\Exception';
        throw new $exceptionClass($message);
    }
}

if (! function_exists('acl_throw_if_not_logged_in')) {
    /**
     * Guard para flujos que usan excepciones.
     * Lanza \Exception si no existe sesión activa.
     */
    function acl_throw_if_not_logged_in($session = null, string $message = 'Sesión expirada.', string $exceptionClass = '\\Exception')
    {
        $session = $session ?? session();
        $userId = (int) ($session->get('id') ?? 0);
        if ($userId <= 0) {
            $exceptionClass = $exceptionClass ?: '\\Exception';
            throw new $exceptionClass($message);
        }

        return $userId;
    }
}
