<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'tramite_id', 'type', 'title', 'message',
        'icon', 'color', 'url', 'is_read', 'read_at', 'created_by',
        'client_id'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    /**
     * Crear notificación de trámite creado
     */
    public function notificarTramiteCreado($tramiteId, $folioTramite, $createdBy, $userIds = [])
    {
        // Obtener datos del trámite (cliente, tipo de trámite)
        $db = \Config\Database::connect();
        $tramite = $db->table('tramite as t')
            ->select('t.cli_directo_id, t.tra_tipos_id, c.razon_social as cliente, tt.tipo_tramite')
            ->join('cli_directo as c', 't.cli_directo_id = c.id', 'left')
            ->join('tra_tipos as tt', 't.tra_tipos_id = tt.id', 'left')
            ->where('t.id', $tramiteId)
            ->get()
            ->getRowArray();
        
        $clientId = $tramite['cli_directo_id'] ?? null;
        $clienteNombre = $tramite['cliente'] ?? 'Cliente no especificado';
        $tipoTramite = $tramite['tipo_tramite'] ?? 'Trámite';
        
        if (empty($userIds)) {
            $userIds = array_merge(
                [$createdBy],
                array_column($this->getAdminUsers(), 'id')
            );
        }

        $userIds = array_unique(array_filter($userIds));
        if (empty($userIds)) {
            return true;
        }

        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'tramite_id' => $tramiteId,
                'client_id' => $clientId,
                'type' => 'tramite_creado',
                'title' => 'Nuevo Trámite Creado',
                'message' => "Se creó el trámite {$folioTramite} - {$tipoTramite} para {$clienteNombre}",
                'icon' => 'fa-file-alt',
                'color' => 'info',
                'url' => base_url("deskapp/tramites/update/{$tramiteId}"),
                'created_by' => $createdBy
            ];
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

        if (empty($notifications)) {
            return true;
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

        if (empty($notifications)) {
            return true;
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

        if (empty($notifications)) {
            return true;
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

        if (empty($notifications)) {
            return true;
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

        if (empty($notifications)) {
            return true;
        }

        return $this->insertBatch($notifications);
    }

    /**
     * Obtener notificaciones no leídas de un usuario
     */
    public function getUnreadNotifications($userId, $limit = 10)
    {
        $db = \Config\Database::connect();
        
        // Verificar si el usuario es Admin o Super Admin (solo necesitamos saber si tiene al menos uno)
        $isAdmin = $db->table('us_user_roles as ur')
            ->join('us_roles as r', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->whereIn('r.role_name', ['Admin', 'Super Admin'])
            ->limit(1)
            ->countAllResults() > 0;
        
        $builder = $db->table('notifications')
            ->distinct()
            ->select('notifications.*, CONCAT(users.firstname, " ", users.lastname) as created_by_name, nr.id as is_read')
            ->join('users', 'users.id = notifications.created_by', 'left')
            ->join('notification_reads as nr', 'nr.notification_id = notifications.id AND nr.user_id = ' . $userId, 'left')
            ->where('nr.id IS NULL'); // Solo notificaciones NO leídas por este usuario
        
        if ($isAdmin) {
            // Admin ve notificaciones asignadas a él Y las globales (user_id = NULL)
            $builder->groupStart()
                ->where('notifications.user_id', $userId)
                ->orWhere('notifications.user_id IS NULL')
                ->groupEnd();
        } else {
            // Usuario normal solo ve sus notificaciones específicas
            $builder->where('notifications.user_id', $userId);
        }
        
        return $builder->orderBy('notifications.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener notificaciones recientes (leídas y no leídas) para el dropdown
     */
    public function getRecentNotifications($userId, $limit = 10)
    {
        $db = \Config\Database::connect();
        
        // Verificar si el usuario es Admin o Super Admin
        $isAdmin = $db->table('us_user_roles as ur')
            ->join('us_roles as r', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->whereIn('r.role_name', ['Admin', 'Super Admin'])
            ->limit(1)
            ->countAllResults() > 0;
        
        $builder = $db->table('notifications')
            ->distinct()
            ->select('notifications.*, CONCAT(users.firstname, " ", users.lastname) as created_by_name, CASE WHEN nr.id IS NULL THEN 0 ELSE 1 END as is_read', false)
            ->join('users', 'users.id = notifications.created_by', 'left')
            ->join('notification_reads as nr', 'nr.notification_id = notifications.id AND nr.user_id = ' . $userId, 'left');
        
        if ($isAdmin) {
            // Admin ve notificaciones asignadas a él Y las globales (user_id = NULL)
            $builder->groupStart()
                ->where('notifications.user_id', $userId)
                ->orWhere('notifications.user_id IS NULL')
                ->groupEnd();
        } else {
            // Usuario normal solo ve sus notificaciones específicas
            $builder->where('notifications.user_id', $userId);
        }
        
        return $builder->orderBy('notifications.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener todas las notificaciones de un usuario (leídas y no leídas)
     */
    public function getUserNotifications($userId, $limit = 50, $offset = 0)
    {
        $db = \Config\Database::connect();
        
        // Verificar si el usuario es Admin o Super Admin (solo necesitamos saber si tiene al menos uno)
        $isAdmin = $db->table('us_user_roles as ur')
            ->join('us_roles as r', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->whereIn('r.role_name', ['Admin', 'Super Admin'])
            ->limit(1)
            ->countAllResults() > 0;
        
        $builder = $db->table('notifications')
            ->distinct()
            ->select('notifications.*, CONCAT(users.firstname, " ", users.lastname) as created_by_name, CASE WHEN nr.id IS NULL THEN 0 ELSE 1 END as is_read', false)
            ->join('users', 'users.id = notifications.created_by', 'left')
            ->join('notification_reads as nr', 'nr.notification_id = notifications.id AND nr.user_id = ' . $userId, 'left');
        
        if ($isAdmin) {
            // Admin ve notificaciones asignadas a él Y las globales (user_id = NULL)
            $builder->groupStart()
                ->where('notifications.user_id', $userId)
                ->orWhere('notifications.user_id IS NULL')
                ->groupEnd();
        } else {
            // Usuario normal solo ve sus notificaciones específicas
            $builder->where('notifications.user_id', $userId);
        }
        
        return $builder->orderBy('is_read', 'ASC')
            ->orderBy('notifications.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Contar notificaciones no leídas
     */
    public function countUnread($userId)
    {
        $db = \Config\Database::connect();
        
        // Verificar si el usuario es Admin o Super Admin (solo necesitamos saber si tiene al menos uno)
        $isAdmin = $db->table('us_user_roles as ur')
            ->join('us_roles as r', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->whereIn('r.role_name', ['Admin', 'Super Admin'])
            ->limit(1)
            ->countAllResults() > 0;
        
        $builder = $db->table('notifications')
            ->select('COUNT(DISTINCT notifications.id) as total')
            ->join('notification_reads as nr', 'nr.notification_id = notifications.id AND nr.user_id = ' . $userId, 'left')
            ->where('nr.id IS NULL'); // Solo notificaciones NO leídas
        
        if ($isAdmin) {
            // Admin cuenta notificaciones asignadas a él Y las globales (user_id = NULL)
            $builder->groupStart()
                ->where('notifications.user_id', $userId)
                ->orWhere('notifications.user_id IS NULL')
                ->groupEnd();
        } else {
            // Usuario normal solo cuenta sus notificaciones específicas
            $builder->where('notifications.user_id', $userId);
        }
        
        $result = $builder->get()->getRowArray();
        return $result['total'] ?? 0;
    }

    /**
     * Marcar notificación como leída
     */
    public function markAsRead($notificationId, $userId = null)
    {
        if (!$userId) {
            return false;
        }

        $db = \Config\Database::connect();
        
        // Insertar registro en notification_reads (tabla de lecturas)
        // Si ya existe (UNIQUE constraint), no hace nada
        try {
            $db->table('notification_reads')->insert([
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'read_at' => date('Y-m-d H:i:s')
            ]);
            return true;
        } catch (\Exception $e) {
            // Si ya existe el registro, también es éxito
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return true;
            }
            return false;
        }
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead($userId)
    {
        $db = \Config\Database::connect();

        // Verificar si el usuario es Admin o Super Admin (solo necesitamos saber si tiene al menos uno)
        $isAdmin = $db->table('us_user_roles as ur')
            ->join('us_roles as r', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->whereIn('r.role_name', ['Admin', 'Super Admin'])
            ->limit(1)
            ->countAllResults() > 0;

        // Obtener IDs de notificaciones visibles para el usuario
        $notificationsBuilder = $db->table('notifications')
            ->select('notifications.id');

        if ($isAdmin) {
            $notificationsBuilder->groupStart()
                ->where('notifications.user_id', $userId)
                ->orWhere('notifications.user_id IS NULL')
                ->groupEnd();
        } else {
            $notificationsBuilder->where('notifications.user_id', $userId);
        }

        $notificationIds = array_column($notificationsBuilder->get()->getResultArray(), 'id');

        if (empty($notificationIds)) {
            return 0;
        }

        // Obtener lecturas existentes
        $readIds = $db->table('notification_reads')
            ->select('notification_id')
            ->where('user_id', $userId)
            ->whereIn('notification_id', $notificationIds)
            ->get()
            ->getResultArray();

        $readIdMap = array_flip(array_column($readIds, 'notification_id'));

        $toInsert = [];
        $now = date('Y-m-d H:i:s');

        foreach ($notificationIds as $notificationId) {
            if (!isset($readIdMap[$notificationId])) {
                $toInsert[] = [
                    'notification_id' => $notificationId,
                    'user_id' => $userId,
                    'read_at' => $now
                ];
            }
        }

        if (empty($toInsert)) {
            return 0;
        }

        try {
            $db->table('notification_reads')->insertBatch($toInsert);
            return count($toInsert);
        } catch (\Exception $e) {
            return false;
        }
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
            ->where('u.status', 1)
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
            ->where('u.status', 1)
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
        $selectFields = ['user_id'];
        if ($db->fieldExists('assigned_to', 'tramite')) {
            $selectFields[] = 'assigned_to';
        }

        $tramite = $db->table('tramite')
            ->select($selectFields)
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
