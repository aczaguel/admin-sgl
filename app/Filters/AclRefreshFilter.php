<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class AclRefreshFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Solo si hay sesión activa.
        $userId = (int)($session->get('id') ?? 0);
        $username = (string)($session->get('username') ?? '');
        if ($userId <= 0 || $username === '') {
            return;
        }

        // Evitar refresco en endpoints de auth/logout.
        // Nota: NO excluir la ruta raíz; puede ser dashboard y necesitamos refrescar ahí.
        $path = $request->uri->getPath();
        if ($path === 'deskapp/login' || $path === 'deskapp/login/auth' || $path === 'deskapp/logout') {
            return;
        }

        helper('acl_version');
        $currentVersion = acl_get_version();
        if ($currentVersion === null) {
            return;
        }

        $sessionVersion = (int)($session->get('acl_version') ?? 0);
        if ($sessionVersion === $currentVersion) {
            return;
        }

        // Recalcular roles/permisos y actualizar sesión.
        try {
            $db = \Config\Database::connect();
            $model = new UserModel($db);
            $session->set('user_permissions', $model->getUserPermissions($userId));
            $session->set('user_roles', $model->getUserRoles($userId));

            // Clientes visibles (multi-tenancy)
            $session->set('clients_by_user', $model->obtenerClientesPorUsuario($userId));

            // Contexto de usuario-cliente
            $userClient = $model->isUserClient($userId);
            if (is_array($userClient) && !empty($userClient['is_client'])) {
                $session->set('user_client', $userClient);
            } else {
                $session->remove('user_client');
            }

            $session->set('acl_version', $currentVersion);
        } catch (\Throwable $e) {
            // Fail-open: no bloquear navegación si algo falla.
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
