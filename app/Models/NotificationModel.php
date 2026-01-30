<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'tramite_id', 'type', 'title', 'message', 
        'icon', 'color', 'url', 'is_read', 'read_at', 'created_by'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    /**
     * Crear notificación de trámite creado
     */
    public function notificarTramiteCreado($tramiteId, $folioTramite, $createdBy, $userIds = [])
    {
        if (empty($userIds)) {
            $userIds = $this->getAdminUsers();
        }

        $notifications = [];
        foreach ($userIds as $userId) {
            if ($userId != $createdBy) { // No notificar al creador
                $notifications[] = [
                    'user_id' => $userId,
                    'tramite_id' => $tramiteId,
                    'type' => 'tramite_creado',
                    'title' => 'Nuevo Trámite Creado',
                    'message' => "Se creó el trámite {$folioTramite}",
                    'icon' => 'fa-file-alt',
                    'color' => 'info',
                    'url' => base_url("deskapp/tramites/view/{$tramiteId}"),
                    'created_by' => $createdBy
                ];
            }
        }

        return $this->insertBatch($notifications);
    }

    /**
     * Crear notificación de trámite actualizado
     */
    public function notificarTramiteActualizado($tramiteId, $folioTramite, $cambios, $createdBy, $userIds = [])
    {
        if (empty($userIds)) {
            $userIds = $this->getInterestedUsers($tramiteId);
        }

        $notifications = [];
        foreach ($userIds as $userId) {
            if ($userId != $createdBy) {
                $notifications[] = [
                    'user_id' => $userId,
                    'tramite_id' => $tramiteId,
                    'type' => 'tramite_actualizado',
                    'title' => 'Trámite Actualizado',
                    'message' => "El trámite {$folioTramite} fue actualizado: {$cambios}",
                    'icon' => 'fa-edit',
                    'color' => 'warning',
                    'url' => base_url("deskapp/tramites/view/{$tramiteId}"),
                    'created_by' => $createdBy
                ];
            }
        }

        return $this->insertBatch($notifications);
    }

    /**
     * Crear notificación de gestor asignado
     */
    public function notificarGestorAsignado($tramiteId, $folioTramite, $gestorNombre, $createdBy)
    {
        $userIds = $this->getInterestedUsers($tramiteId);

        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'tramite_id' => $tramiteId,
                'type' => 'gestor_asignado',
                'title' => 'Gestor Asignado',
                'message' => "Se asignó a {$gestorNombre} para el trámite {$folioTramite}",
                'icon' => 'fa-user-tie',
                'color' => 'primary',
                'url' => base_url("deskapp/tramites/view/{$tramiteId}"),
                'created_by' => $createdBy
            ];
        }

        return $this->insertBatch($notifications);
    }

    /**
     * Crear notificación de pago a gestor
     */
    public function notificarPagoGestor($tramiteId, $folioTramite, $monto, $createdBy)
    {
        $userIds = array_merge(
            $this->getAdminUsers(),
            $this->getFinanceUsers()
        );

        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'tramite_id' => $tramiteId,
                'type' => 'pago_gestor',
                'title' => 'Pago a Gestor Registrado',
                'message' => "Se registró pago de $" . number_format($monto, 2) . " para el trámite {$folioTramite}",
                'icon' => 'fa-money-bill-wave',
                'color' => 'success',
                'url' => base_url("deskapp/tramites/view/{$tramiteId}"),
                'created_by' => $createdBy
            ];
        }

        return $this->insertBatch($notifications);
    }

    /**
     * Crear notificación de factura generada
     */
    public function notificarFacturaGenerada($tramiteId, $folioTramite, $numeroFactura, $createdBy)
    {
        $userIds = array_merge(
            $this->getAdminUsers(),
            $this->getFinanceUsers()
        );

        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'tramite_id' => $tramiteId,
                'type' => 'factura_generada',
                'title' => 'Factura Generada',
                'message' => "Se generó la factura {$numeroFactura} para el trámite {$folioTramite}",
                'icon' => 'fa-file-invoice',
                'color' => 'info',
                'url' => base_url("deskapp/tramites/view/{$tramiteId}"),
                'created_by' => $createdBy
            ];
        }

        return $this->insertBatch($notifications);
    }

    /**
     * Crear notificación de factura cobrada
     */
    public function notificarFacturaCobrada($tramiteId, $folioTramite, $monto, $createdBy)
    {
        $userIds = array_merge(
            $this->getAdminUsers(),
            $this->getFinanceUsers()
        );

        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'tramite_id' => $tramiteId,
                'type' => 'factura_cobrada',
                'title' => 'Factura Cobrada',
                'message' => "Se cobró $" . number_format($monto, 2) . " del trámite {$folioTramite}",
                'icon' => 'fa-check-circle',
                'color' => 'success',
                'url' => base_url("deskapp/tramites/view/{$tramiteId}"),
                'created_by' => $createdBy
            ];
        }

        return $this->insertBatch($notifications);
    }

    /**
     * Obtener notificaciones no leídas de un usuario
     */
    public function getUnreadNotifications($userId, $limit = 10)
    {
        return $this->select('notifications.*, CONCAT(users.firstname, " ", users.lastname) as created_by_name')
            ->join('users', 'users.id = notifications.created_by', 'left')
            ->where('notifications.user_id', $userId)
            ->where('notifications.is_read', 0)
            ->orderBy('notifications.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Obtener todas las notificaciones de un usuario (leídas y no leídas)
     */
    public function getUserNotifications($userId, $limit = 50, $offset = 0)
    {
        return $this->select('notifications.*, CONCAT(users.firstname, " ", users.lastname) as created_by_name')
            ->join('users', 'users.id = notifications.created_by', 'left')
            ->where('notifications.user_id', $userId)
            ->orderBy('notifications.is_read', 'ASC')
            ->orderBy('notifications.created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Contar notificaciones no leídas
     */
    public function countUnread($userId)
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    /**
     * Marcar notificación como leída
     */
    public function markAsRead($notificationId, $userId = null)
    {
        $data = [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ];

        $builder = $this->where('id', $notificationId);
        if ($userId) {
            $builder->where('user_id', $userId);
        }

        return $builder->update(null, $data);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead($userId)
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->update(null, [
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Eliminar notificaciones antiguas (más de X días)
     */
    public function deleteOldNotifications($days = 90)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->where('created_at <', $date)
            ->where('is_read', 1)
            ->delete();
    }

    /**
     * Obtener usuarios administradores
     */
    private function getAdminUsers()
    {
        $db = \Config\Database::connect();
        return $db->table('users as u')
            ->select('u.id')
            ->join('us_user_roles as ur', 'u.id = ur.user_id')
            ->join('us_roles as r', 'ur.role_id = r.id')
            ->whereIn('r.role_name', ['Admin', 'Super Admin'])
            ->where('u.status', 'active')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener usuarios del área financiera
     */
    private function getFinanceUsers()
    {
        $db = \Config\Database::connect();
        return $db->table('users as u')
            ->select('u.id')
            ->join('us_user_roles as ur', 'u.id = ur.user_id')
            ->join('us_roles as r', 'ur.role_id = r.id')
            ->whereIn('r.role_name', ['Finance', 'Admin', 'Super Admin'])
            ->where('u.status', 'active')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener usuarios interesados en un trámite (creador, asignados, admins)
     */
    private function getInterestedUsers($tramiteId)
    {
        $db = \Config\Database::connect();
        
        // Obtener el creador y usuario asignado del trámite
        $tramite = $db->table('tramite')
            ->select('user_id, assigned_to')
            ->where('id', $tramiteId)
            ->get()
            ->getRowArray();

        $userIds = [];
        
        if ($tramite) {
            if (!empty($tramite['user_id'])) {
                $userIds[] = $tramite['user_id'];
            }
            if (!empty($tramite['assigned_to'])) {
                $userIds[] = $tramite['assigned_to'];
            }
        }

        // Agregar administradores
        $adminUsers = $this->getAdminUsers();
        foreach ($adminUsers as $admin) {
            $userIds[] = $admin['id'];
        }

        return array_unique($userIds);
    }
}
