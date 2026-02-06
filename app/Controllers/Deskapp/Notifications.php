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
        helper(['form', 'url']);
        $this->notificationModel = new NotificationModel();
        $this->session = session();
    }

    /**
     * Vista principal de notificaciones
     */
    public function index()
    {
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');

        // Obtener todas las notificaciones del usuario
        $data['notifications'] = $this->notificationModel->getUserNotifications($userId, 50);
        $data['unread_count'] = $this->notificationModel->countUnread($userId);

        return view('deskapp/notifications/index', $data);
    }

    /**
     * API: Obtener notificaciones no leídas (para el dropdown)
     */
    public function api_unread()
    {
        $userId = $this->session->get('id');
        
        // Obtener las últimas notificaciones (leídas y no leídas) para mostrar en el dropdown
        $notifications = $this->notificationModel->getRecentNotifications($userId, 10);
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
        $userId = $this->session->get('id');
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
        $userId = $this->session->get('id');

        if (!$notificationId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de notificación requerido'
            ]);
        }

        $result = $this->notificationModel->markAsRead($notificationId, $userId);

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
        $userId = $this->session->get('id');

        if (!$notificationId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de notificación requerido'
            ]);
        }

        // Verificar que la notificación pertenece al usuario
        $notification = $this->notificationModel
            ->where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ]);
        }

        $result = $this->notificationModel->delete($notificationId);

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
        $userId = $this->session->get('id');
        $offset = $this->request->getGet('offset') ?? 0;
        $limit = 20;

        $notifications = $this->notificationModel->getUserNotifications($userId, $limit, $offset);

        return $this->response->setJSON([
            'success' => true,
            'notifications' => $notifications,
            'has_more' => count($notifications) === $limit
        ]);
    }
}
