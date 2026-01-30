<?php

use App\Models\NotificationModel;

if (!function_exists('notify_tramite_creado')) {
    /**
     * Notificar que se creó un nuevo trámite
     *
     * @param int $tramiteId ID del trámite
     * @param string $folioTramite Folio del trámite
     * @param int $createdBy ID del usuario que lo creó
     * @param array $userIds IDs de usuarios a notificar (opcional)
     * @return bool
     */
    function notify_tramite_creado($tramiteId, $folioTramite, $createdBy, $userIds = [])
    {
        try {
            $notificationModel = new NotificationModel();
            return $notificationModel->notificarTramiteCreado($tramiteId, $folioTramite, $createdBy, $userIds);
        } catch (\Exception $e) {
            log_message('error', 'Error en notify_tramite_creado: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('notify_tramite_actualizado')) {
    /**
     * Notificar que se actualizó un trámite
     *
     * @param int $tramiteId ID del trámite
     * @param string $folioTramite Folio del trámite
     * @param string $cambios Descripción de los cambios
     * @param int $createdBy ID del usuario que hizo el cambio
     * @param array $userIds IDs de usuarios a notificar (opcional)
     * @return bool
     */
    function notify_tramite_actualizado($tramiteId, $folioTramite, $cambios, $createdBy, $userIds = [])
    {
        try {
            $notificationModel = new NotificationModel();
            return $notificationModel->notificarTramiteActualizado($tramiteId, $folioTramite, $cambios, $createdBy, $userIds);
        } catch (\Exception $e) {
            log_message('error', 'Error en notify_tramite_actualizado: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('notify_gestor_asignado')) {
    /**
     * Notificar que se asignó un gestor
     *
     * @param int $tramiteId ID del trámite
     * @param string $folioTramite Folio del trámite
     * @param string $gestorNombre Nombre del gestor asignado
     * @param int $createdBy ID del usuario que asignó
     * @return bool
     */
    function notify_gestor_asignado($tramiteId, $folioTramite, $gestorNombre, $createdBy)
    {
        try {
            $notificationModel = new NotificationModel();
            return $notificationModel->notificarGestorAsignado($tramiteId, $folioTramite, $gestorNombre, $createdBy);
        } catch (\Exception $e) {
            log_message('error', 'Error en notify_gestor_asignado: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('notify_pago_gestor')) {
    /**
     * Notificar que se registró un pago a gestor
     *
     * @param int $tramiteId ID del trámite
     * @param string $folioTramite Folio del trámite
     * @param float $monto Monto del pago
     * @param int $createdBy ID del usuario que registró el pago
     * @return bool
     */
    function notify_pago_gestor($tramiteId, $folioTramite, $monto, $createdBy)
    {
        try {
            $notificationModel = new NotificationModel();
            return $notificationModel->notificarPagoGestor($tramiteId, $folioTramite, $monto, $createdBy);
        } catch (\Exception $e) {
            log_message('error', 'Error en notify_pago_gestor: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('notify_factura_generada')) {
    /**
     * Notificar que se generó una factura
     *
     * @param int $tramiteId ID del trámite
     * @param string $folioTramite Folio del trámite
     * @param string $numeroFactura Número de la factura generada
     * @param int $createdBy ID del usuario que generó la factura
     * @return bool
     */
    function notify_factura_generada($tramiteId, $folioTramite, $numeroFactura, $createdBy)
    {
        try {
            $notificationModel = new NotificationModel();
            return $notificationModel->notificarFacturaGenerada($tramiteId, $folioTramite, $numeroFactura, $createdBy);
        } catch (\Exception $e) {
            log_message('error', 'Error en notify_factura_generada: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('notify_factura_cobrada')) {
    /**
     * Notificar que se cobró una factura
     *
     * @param int $tramiteId ID del trámite
     * @param string $folioTramite Folio del trámite
     * @param float $monto Monto cobrado
     * @param int $createdBy ID del usuario que registró el cobro
     * @return bool
     */
    function notify_factura_cobrada($tramiteId, $folioTramite, $monto, $createdBy)
    {
        try {
            $notificationModel = new NotificationModel();
            return $notificationModel->notificarFacturaCobrada($tramiteId, $folioTramite, $monto, $createdBy);
        } catch (\Exception $e) {
            log_message('error', 'Error en notify_factura_cobrada: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('notify_custom')) {
    /**
     * Crear notificación personalizada
     *
     * @param array $userIds IDs de usuarios a notificar
     * @param string $type Tipo de notificación
     * @param string $title Título
     * @param string $message Mensaje
     * @param array $options Opciones adicionales (icon, color, url, tramiteId, createdBy)
     * @return bool
     */
    function notify_custom($userIds, $type, $title, $message, $options = [])
    {
        try {
            $notificationModel = new NotificationModel();
            
            $notifications = [];
            foreach ($userIds as $userId) {
                $notifications[] = array_merge([
                    'user_id' => $userId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'icon' => 'fa-bell',
                    'color' => 'primary',
                    'url' => null,
                    'tramite_id' => null,
                    'created_by' => null
                ], $options);
            }
            
            return $notificationModel->insertBatch($notifications);
        } catch (\Exception $e) {
            log_message('error', 'Error en notify_custom: ' . $e->getMessage());
            return false;
        }
    }
}
