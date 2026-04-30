<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;

/**
 * DEBUG CONTROLLER - Verificar sesión y permisos en tiempo real
 * 
 * Rutas:
 * - GET /deskapp/debug/session → Ver sesión actual
 * - GET /deskapp/debug/permissions → Ver permisos cargados
 * - GET /deskapp/debug/check-dashboard → Verificar acceso a dashboard cliente
 * 
 * IMPORTANTE: Borrar este archivo en producción
 */
class DebugPermisos extends BaseController
{
    protected $session;

    public function __construct()
    {
        helper(['permissions']);
        $this->session = session();
    }

    /**
     * Ver información de sesión actual
     */
    public function session()
    {
        if (!$this->isDebugAllowed()) {
            return $this->response->setStatusCode(403)->setBody('Debug deshabilitado');
        }

        $userId = $this->session->get('id');
        $username = $this->session->get('username');

        $data = [
            'user_id' => $userId,
            'username' => $username,
            'user_roles' => $this->session->get('user_roles'),
            'user_permissions_count' => count($this->session->get('user_permissions') ?? []),
            'acl_version' => $this->session->get('acl_version'),
            'user_client' => $this->session->get('user_client'),
            'clients_by_user' => $this->session->get('clients_by_user'),
        ];

        return $this->response
            ->setContentType('application/json')
            ->setJSON($data);
    }

    /**
     * Ver todos los permisos cargados
     */
    public function permissions()
    {
        if (!$this->isDebugAllowed()) {
            return $this->response->setStatusCode(403)->setBody('Debug deshabilitado');
        }

        $perms = $this->session->get('user_permissions') ?? [];
        sort($perms);

        $data = [
            'total' => count($perms),
            'permissions' => $perms,
            'has_menu_dashboard_cliente' => in_array('menu_dashboard_cliente', $perms),
            'has_menu_tramites_cliente' => in_array('menu_tramites_cliente', $perms),
            'has_ui_sidebar_cliente' => in_array('ui_sidebar_cliente', $perms),
        ];

        return $this->response
            ->setContentType('application/json')
            ->setJSON($data);
    }

    /**
     * Verificar acceso a dashboard cliente
     */
    public function checkDashboard()
    {
        if (!$this->isDebugAllowed()) {
            return $this->response->setStatusCode(403)->setBody('Debug deshabilitado');
        }

        [$roles, $perms] = session_roles_perms($this->session);

        $has_perm = has_permission('menu_dashboard_cliente', $perms, $roles);
        $is_super_admin = in_array('Super Admin', $roles, true);

        $data = [
            'user_id' => $this->session->get('id'),
            'username' => $this->session->get('username'),
            'roles' => $roles,
            'is_super_admin' => $is_super_admin,
            'has_permission_menu_dashboard_cliente' => $has_perm,
            'would_allow_access' => ($is_super_admin || $has_perm),
            'debug_info' => [
                'perms_count' => count($perms),
                'roles_count' => count($roles),
                'menu_dashboard_cliente_in_perms' => in_array('menu_dashboard_cliente', $perms),
            ],
        ];

        return $this->response
            ->setContentType('application/json')
            ->setJSON($data);
    }

    /**
     * Verificar si debug está permitido
     */
    private function isDebugAllowed(): bool
    {
        // Solo Super Admin puede usar debug
        [$roles, $perms] = session_roles_perms($this->session);
        return in_array('Super Admin', $roles, true) || in_array('Admin', $roles, true);
    }
}
