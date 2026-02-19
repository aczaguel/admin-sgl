<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use App\Models\NotificationModel;

class Notifications extends BaseController
{
    protected $notificationModel;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'permissions']);
        $this->notificationModel = new NotificationModel();
        $this->session = session();
    }

    private function guardAccess(bool $json = true)
    {
        $userId = $this->session->get('id');
        if (!$userId) {
            if ($json) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Sesión expirada'
                ]);
            }
            return redirect()->to('/deskapp/auth/login');
        }

        $perms = $this->session->get('user_permissions');
        $roles = $this->session->get('user_roles');

        // Clientes pueden ver sus propias notificaciones.
        if (is_client($roles)) {
            return null;
        }

        // Permiso base: permitir a cualquier usuario que pueda ver trámites (en proceso o finalizados)
        $canRead = has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles);
        if (!(is_super_admin($roles) || is_admin($roles)) && !$canRead) {
            if ($json) {
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => 'Acceso denegado'
                ]);
            }
            return redirect()->to('/deskapp/dashboard')->with('error', 'No tienes permisos para ver notificaciones.');
        }

        return null;
    }

    private function adjustUrlsForUser(array $notifications): array
    {
        $roles = $this->session->get('user_roles');
        if (!is_client($roles)) {
            return $notifications;
        }

        foreach ($notifications as $i => $row) {
            if (!empty($row['tramite_id'])) {
                $notifications[$i]['url'] = base_url('deskapp/clientes/ver/' . $row['tramite_id']);
            }
        }

        return $notifications;
    }

    /**
     * Vista principal de notificaciones
     */
    public function index()
    {
        if ($resp = $this->guardAccess(false)) {
            return $resp;
        }

        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');

        if (is_client($this->session->get('user_roles'))) {
            $this->notificationModel->syncTramiteCreadoForClienteUser((int) $userId);
        }

        // Obtener todas las notificaciones del usuario
        $data['notifications'] = $this->adjustUrlsForUser($this->notificationModel->getUserNotifications($userId, 50));
        $data['unread_count'] = $this->notificationModel->countUnread($userId);

        return view('deskapp/notifications/index', $data);
    }

    /**
     * API: Obtener notificaciones no leídas (para el dropdown)
     */
    public function api_unread()
    {
        if ($resp = $this->guardAccess(true)) {
            return $resp;
        }

        $userId = $this->session->get('id');

        if (is_client($this->session->get('user_roles'))) {
            $this->notificationModel->syncTramiteCreadoForClienteUser((int) $userId);
        }
        
        // Obtener las últimas notificaciones (leídas y no leídas) para mostrar en el dropdown
        $notifications = $this->adjustUrlsForUser($this->notificationModel->getRecentNotifications($userId, 10));
        $unreadCount = $this->notificationModel->countUnread($userId);

        return $this->response->setJSON([
            'success' => true,
            'count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    /**
     * API: Obtener contador de no leídas
     */
    public function api_count()
    {
        if ($resp = $this->guardAccess(true)) {
            return $resp;
        }

        $userId = $this->session->get('id');

        if (is_client($this->session->get('user_roles'))) {
            $this->notificationModel->syncTramiteCreadoForClienteUser((int) $userId);
        }
        $count = $this->notificationModel->countUnread($userId);

        return $this->response->setJSON([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * API: Marcar notificación como leída
     */
    public function api_mark_read($notificationId = null)
    {
        if ($resp = $this->guardAccess(true)) {
            return $resp;
        }

        $userId = $this->session->get('id');

        if (!$notificationId || !is_numeric($notificationId)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID de notificación requerido'
            ]);
        }

        $result = $this->notificationModel->markAsRead((int) $notificationId, (int) $userId);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Notificación marcada como leída' : 'Error al marcar notificación'
        ]);
    }

    /**
     * API: Marcar todas las notificaciones como leídas
     */
    public function api_mark_all_read()
    {
        if ($resp = $this->guardAccess(true)) {
            return $resp;
        }

        $userId = $this->session->get('id');
        $result = $this->notificationModel->markAllAsRead($userId);

        return $this->response->setJSON([
            'success' => $result !== false,
            'message' => $result !== false ? 'Todas las notificaciones marcadas como leídas' : 'Error al marcar notificaciones'
        ]);
    }

    /**
     * API: Eliminar notificación
     */
    public function api_delete($notificationId = null)
    {
        if ($resp = $this->guardAccess(true)) {
            return $resp;
        }

        $userId = $this->session->get('id');

        if (!$notificationId || !is_numeric($notificationId)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID de notificación requerido'
            ]);
        }

        // Verificar que la notificación pertenece al usuario
        $notification = $this->notificationModel
            ->where('id', (int) $notificationId)
            ->where('user_id', (int) $userId)
            ->first();

        if (!$notification) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ]);
        }

        $result = $this->notificationModel->delete((int) $notificationId);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Notificación eliminada' : 'Error al eliminar notificación'
        ]);
    }

    /**
     * Cargar más notificaciones (infinite scroll)
     */
    public function api_load_more()
    {
        if ($resp = $this->guardAccess(true)) {
            return $resp;
        }

        $userId = $this->session->get('id');

        if (is_client($this->session->get('user_roles'))) {
            $this->notificationModel->syncTramiteCreadoForClienteUser((int) $userId);
        }
        $offset = $this->request->getGet('offset') ?? 0;
        if (!is_numeric($offset) || (int) $offset < 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Offset inválido'
            ]);
        }
        $limit = 20;

        $notifications = $this->adjustUrlsForUser($this->notificationModel->getUserNotifications((int) $userId, $limit, (int) $offset));

        return $this->response->setJSON([
            'success' => true,
            'notifications' => $notifications,
            'has_more' => count($notifications) === $limit
        ]);
    }
}
