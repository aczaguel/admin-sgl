<?php

/**
 * Catálogo de estatus de trámite (referencia del negocio)
 *
 * Tabla proporcionada (2026-02-23):
 * # id, tra_status, descripcion, step, status, created_at, updated_at, user_id
 * '11', 'RECOLECCION DE DCTOS', 'En Proceso', '1', '1', '2024-01-12 20:23:49', '2024-11-01 18:46:12', '4'
 * '20', 'CONCLUIDO', 'Concluido', '1', '1', '2024-01-12 20:23:49', '2024-11-01 18:47:26', '4'
 * '21', 'CANCELADO', 'Cancelado', '1', '1', '2024-03-03 17:50:56', '2024-11-01 18:47:25', '4'
 * '22', 'DCTOS COMPLETOS', 'Un tramite que ya fue tomado por alguién de perfil 2', '2', '1', '2024-09-04 19:45:03', '2024-11-01 18:46:12', '4'
 * '23', 'Pago a Gestor', 'Proceso de Pago a gestor', '4', '1', '2024-09-04 19:45:25', '2025-03-12 19:44:54', '4'
 * '24', 'SOLICITUD', 'Tramite recien cargado', '1', '1', '2024-09-11 15:03:37', '2024-11-01 18:46:12', '4'
 * '25', 'Pago de Derechos', 'Se hace la cotizacion de pago de derechos', '3', '1', '2024-11-01 18:46:12', '2024-11-01 18:46:12', NULL
 * '26', 'Linea de Captura', 'Se sube linea de captura ', '3', '1', '2024-11-01 18:46:12', '2025-03-12 19:44:54', NULL
 * '27', 'Pago de Derechos', 'Listado de documentos de pagos', '3', '1', '2024-11-01 18:46:12', '2025-03-12 19:44:54', NULL
 * '28', 'Cobro a Cliente', 'Paso final cobro a cliente', '5', '1', '2025-01-13 19:51:30', '2025-03-12 19:44:54', NULL
 * '29', 'Cotizacion', 'Se guarda solo como cotizacion', NULL, '1', '2025-02-16 10:50:11', '2025-02-16 10:50:11', NULL
 */

if (!function_exists('tramite_status_catalog')) {
    function tramite_status_catalog(): array
    {
        return [
            11 => ['tra_status' => 'RECOLECCION DE DCTOS', 'descripcion' => 'En Proceso', 'step' => 1],
            20 => ['tra_status' => 'CONCLUIDO', 'descripcion' => 'Concluido', 'step' => 1],
            21 => ['tra_status' => 'CANCELADO', 'descripcion' => 'Cancelado', 'step' => 1],
            22 => ['tra_status' => 'DCTOS COMPLETOS', 'descripcion' => 'Un tramite que ya fue tomado por alguién de perfil 2', 'step' => 2],
            23 => ['tra_status' => 'Pago a Gestor', 'descripcion' => 'Proceso de Pago a gestor', 'step' => 4],
            24 => ['tra_status' => 'SOLICITUD', 'descripcion' => 'Tramite recien cargado', 'step' => 1],
            25 => ['tra_status' => 'Pago de Derechos', 'descripcion' => 'Se hace la cotizacion de pago de derechos', 'step' => 3],
            26 => ['tra_status' => 'Linea de Captura', 'descripcion' => 'Se sube linea de captura', 'step' => 3],
            27 => ['tra_status' => 'Pago de Derechos', 'descripcion' => 'Listado de documentos de pagos', 'step' => 3],
            28 => ['tra_status' => 'Cobro a Cliente', 'descripcion' => 'Paso final cobro a cliente', 'step' => 5],
            29 => ['tra_status' => 'Cotizacion', 'descripcion' => 'Se guarda solo como cotizacion', 'step' => null],
        ];
    }
}

if (!function_exists('tramite_status_step')) {
    function tramite_status_step(int $traStatusId): ?int
    {
        $catalog = tramite_status_catalog();
        if (!array_key_exists($traStatusId, $catalog)) {
            return null;
        }
        $step = $catalog[$traStatusId]['step'] ?? null;
        return is_int($step) ? $step : null;
    }
}

if (!function_exists('tramite_is_aprobado_por_status')) {
    /**
     * Define si un trámite se considera "aprobado" a nivel negocio.
     *
     * Regla actual (basada en tu tabla):
     * - A partir de Paso 4 (estatus 23+) los pasos 1–3 deben verse como solo lectura.
     * - Concluido/Cancelado (20/21) también se considera aprobado para este efecto.
     */
    function tramite_is_aprobado_por_status(int $traStatusId): bool
    {
        if (in_array($traStatusId, [23, 28, 20, 21], true)) {
            return true;
        }

        $step = tramite_status_step($traStatusId);
        return $step !== null && $step >= 4;
    }
}
