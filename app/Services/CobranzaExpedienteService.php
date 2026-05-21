<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class CobranzaExpedienteService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        helper(['audit']);
        $this->db = $db ?? \Config\Database::connect();
    }

    public function openOrReactivateForTramite(int $tramiteId, int $actingUserId, array $options = []): array
    {
        if (!$this->hasRequiredTables()) {
            return $this->errorResult(500, 'El modulo de cobranza no esta configurado en la base de datos.');
        }

        $tramite = $this->loadTramite($tramiteId);
        if (empty($tramite)) {
            return $this->errorResult(404, 'Tramite no encontrado.');
        }

        if (!$this->isEligibleForCobranza($tramite)) {
            return $this->errorResult(409, 'El tramite aun no cumple las condiciones para abrir cobranza.');
        }

        $statusOpenId = $this->catalogId('cobranza_status', 'abierto');
        $priorityId = $this->catalogId('cobranza_prioridad', 'media');
        $tipoAperturaId = $this->catalogId('cobranza_tipo_gestion', 'apertura');
        $canalSistemaId = $this->catalogId('cobranza_canal', 'sistema');
        $resultadoAperturaId = $this->catalogId('cobranza_resultado_gestion', 'expediente_abierto');

        if (in_array(null, [$statusOpenId, $priorityId, $tipoAperturaId, $canalSistemaId, $resultadoAperturaId], true)) {
            return $this->errorResult(500, 'Faltan catalogos base del modulo de cobranza.');
        }

        $ownerUserId = (int) ($options['owner_user_id'] ?? $actingUserId ?: ($tramite['user_id'] ?? 0));
        $now = date('Y-m-d H:i:s');

        $activeExpediente = $this->db->table('cobranza_expediente')
            ->where('tramite_id', $tramiteId)
            ->where('is_active', 1)
            ->get(1)
            ->getRowArray();

        if (!empty($activeExpediente)) {
            return [
                'success' => true,
                'statusCode' => 200,
                'message' => 'El expediente de cobranza ya se encuentra abierto.',
                'expediente_id' => (int) $activeExpediente['id'],
                'created' => false,
                'reactivated' => false,
                'already_active' => true,
            ];
        }

        $this->db->transStart();

        $latestExpediente = $this->db->table('cobranza_expediente')
            ->where('tramite_id', $tramiteId)
            ->orderBy('id', 'DESC')
            ->get(1)
            ->getRowArray();

        $expedienteId = 0;
        $created = false;
        $reactivated = false;

        if (!empty($latestExpediente)) {
            $expedienteId = (int) $latestExpediente['id'];
            $reactivated = true;
            $this->db->table('cobranza_expediente')
                ->where('id', $expedienteId)
                ->update([
                    'owner_user_id' => $ownerUserId,
                    'status_id' => $statusOpenId,
                    'prioridad_id' => $priorityId,
                    'fecha_apertura' => $now,
                    'fecha_cierre' => null,
                    'is_disputa' => 0,
                    'is_requiere_revision' => 0,
                    'is_active' => 1,
                    'updated_at' => $now,
                    'updated_by' => $actingUserId,
                ]);
        } else {
            $created = true;
            $this->db->table('cobranza_expediente')->insert([
                'tramite_id' => $tramiteId,
                'cliente_id' => (int) ($tramite['cliente_id'] ?? 0),
                'cli_directo_id' => (int) ($tramite['cli_directo_id'] ?? 0),
                'cli_directo_ejecutivo_id' => (int) ($tramite['cli_directo_ejecutivo_id'] ?? 0),
                'owner_user_id' => $ownerUserId,
                'supervisor_user_id' => null,
                'status_id' => $statusOpenId,
                'prioridad_id' => $priorityId,
                'origen_apertura' => (string) ($options['origen_apertura'] ?? 'modulo_cobranza'),
                'monto_objetivo' => (float) ($options['monto_objetivo'] ?? 0),
                'saldo_actual' => (float) ($options['saldo_actual'] ?? 0),
                'moneda' => (string) ($options['moneda'] ?? 'MXN'),
                'fecha_apertura' => $now,
                'fecha_ultimo_contacto' => null,
                'fecha_proximo_seguimiento' => null,
                'fecha_promesa_actual' => null,
                'fecha_cierre' => null,
                'motivo_cierre_id' => null,
                'sla_at_first_contact_at' => null,
                'sla_resolve_at' => null,
                'is_disputa' => 0,
                'is_requiere_revision' => 0,
                'external_reference' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $actingUserId,
                'updated_by' => $actingUserId,
            ]);
            $expedienteId = (int) $this->db->insertID();
        }

        $this->db->table('cobranza_gestion')->insert([
            'expediente_id' => $expedienteId,
            'tipo_gestion_id' => $tipoAperturaId,
            'canal_id' => $canalSistemaId,
            'resultado_id' => $resultadoAperturaId,
            'fecha_gestion' => $now,
            'siguiente_accion' => 'Iniciar primer contacto de cobranza',
            'fecha_proximo_seguimiento' => null,
            'comentarios' => $reactivated
                ? 'Expediente reactivado desde el centro de cobranza.'
                : 'Expediente abierto desde el centro de cobranza.',
            'metadata_json' => json_encode([
                'tramite_id' => $tramiteId,
                'folio' => $tramite['folio'] ?? null,
                'source' => 'cobranza_dashboard',
            ]),
            'created_at' => $now,
            'created_by' => $actingUserId,
        ]);

        $this->auditTramite($tramiteId, $created ? 'Apertura de expediente de cobranza.' : 'Reapertura de expediente de cobranza.', $actingUserId);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->errorResult(500, 'No se pudo abrir el expediente de cobranza.');
        }

        return [
            'success' => true,
            'statusCode' => $created ? 201 : 200,
            'message' => $created ? 'Expediente de cobranza abierto.' : 'Expediente de cobranza reactivado.',
            'expediente_id' => $expedienteId,
            'created' => $created,
            'reactivated' => $reactivated,
            'already_active' => false,
        ];
    }

    public function registerGestion(int $expedienteId, int $actingUserId, array $payload): array
    {
        if (!$this->hasRequiredTables()) {
            return $this->errorResult(500, 'El modulo de cobranza no esta configurado en la base de datos.');
        }

        $expediente = $this->db->table('cobranza_expediente')
            ->where('id', $expedienteId)
            ->where('is_active', 1)
            ->get(1)
            ->getRowArray();

        if (empty($expediente)) {
            return $this->errorResult(404, 'Expediente de cobranza no encontrado o inactivo.');
        }

        $tipoCode = trim((string) ($payload['tipo'] ?? 'seguimiento'));
        $canalCode = trim((string) ($payload['canal'] ?? 'interno'));
        $resultadoCode = trim((string) ($payload['resultado'] ?? 'seguimiento_registrado'));
        $comentarios = trim((string) ($payload['comentarios'] ?? ''));
        if ($comentarios === '') {
            return $this->errorResult(400, 'Los comentarios de la gestion son obligatorios.');
        }

        $tipoId = $this->catalogId('cobranza_tipo_gestion', $tipoCode);
        $canalId = $this->catalogId('cobranza_canal', $canalCode);
        $resultadoId = $this->catalogId('cobranza_resultado_gestion', $resultadoCode);
        $statusGestionId = $this->catalogId('cobranza_status', 'en_gestion');
        $statusPromesaId = $this->catalogId('cobranza_status', 'promesa_activa');

        if (in_array(null, [$tipoId, $canalId, $resultadoId, $statusGestionId], true)) {
            return $this->errorResult(500, 'Faltan catalogos base para registrar gestiones de cobranza.');
        }

        $now = date('Y-m-d H:i:s');
        $fechaGestion = trim((string) ($payload['fecha_gestion'] ?? $now));
        $fechaSeguimiento = trim((string) ($payload['fecha_proximo_seguimiento'] ?? ''));
        $siguienteAccion = trim((string) ($payload['siguiente_accion'] ?? ''));
        $nextStatusId = $resultadoCode === 'promesa_registrada' && $statusPromesaId !== null ? $statusPromesaId : $statusGestionId;

        $this->db->transStart();

        $this->db->table('cobranza_gestion')->insert([
            'expediente_id' => $expedienteId,
            'tipo_gestion_id' => $tipoId,
            'canal_id' => $canalId,
            'resultado_id' => $resultadoId,
            'fecha_gestion' => $fechaGestion,
            'siguiente_accion' => $siguienteAccion !== '' ? $siguienteAccion : null,
            'fecha_proximo_seguimiento' => $fechaSeguimiento !== '' ? $fechaSeguimiento : null,
            'comentarios' => $comentarios,
            'metadata_json' => json_encode([
                'source' => 'cobranza_dashboard',
            ]),
            'created_at' => $now,
            'created_by' => $actingUserId,
        ]);

        $this->db->table('cobranza_expediente')
            ->where('id', $expedienteId)
            ->update([
                'status_id' => $nextStatusId,
                'fecha_ultimo_contacto' => $fechaGestion,
                'fecha_proximo_seguimiento' => $fechaSeguimiento !== '' ? $fechaSeguimiento : null,
                'updated_at' => $now,
                'updated_by' => $actingUserId,
            ]);

        $this->auditTramite((int) $expediente['tramite_id'], 'Registro de gestion de cobranza.', $actingUserId);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->errorResult(500, 'No se pudo registrar la gestion de cobranza.');
        }

        return [
            'success' => true,
            'statusCode' => 201,
            'message' => 'Gestion de cobranza registrada.',
            'expediente_id' => $expedienteId,
        ];
    }

    public function registerPromesaPago(int $expedienteId, int $actingUserId, array $payload): array
    {
        if (!$this->hasPromiseAndPaymentTables()) {
            return $this->errorResult(500, 'El modulo de promesas de pago no esta configurado en la base de datos.');
        }

        $expediente = $this->loadActiveExpediente($expedienteId);
        if (empty($expediente)) {
            return $this->errorResult(404, 'Expediente de cobranza no encontrado o inactivo.');
        }

        $montoPrometido = (float) ($payload['monto_prometido'] ?? 0);
        $fechaPromesa = trim((string) ($payload['fecha_promesa'] ?? ''));
        $medioPagoCode = trim((string) ($payload['medio_pago'] ?? 'transferencia'));
        $observaciones = trim((string) ($payload['observaciones'] ?? ''));
        $canalCode = trim((string) ($payload['canal'] ?? 'interno'));

        if ($montoPrometido <= 0) {
            return $this->errorResult(400, 'El monto prometido debe ser mayor a cero.');
        }

        if ($fechaPromesa === '') {
            return $this->errorResult(400, 'La fecha promesa es obligatoria.');
        }

        $medioPagoId = $this->catalogId('cobranza_medio_pago', $medioPagoCode);
        $tipoPromesaId = $this->catalogId('cobranza_tipo_gestion', 'promesa');
        $resultadoPromesaId = $this->catalogId('cobranza_resultado_gestion', 'promesa_registrada');
        $canalId = $this->catalogId('cobranza_canal', $canalCode);
        $statusPromesaId = $this->catalogId('cobranza_status', 'promesa_activa');

        if (in_array(null, [$medioPagoId, $tipoPromesaId, $resultadoPromesaId, $canalId, $statusPromesaId], true)) {
            return $this->errorResult(500, 'Faltan catalogos base para registrar promesas de pago.');
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('cobranza_promesa_pago')
            ->where('expediente_id', $expedienteId)
            ->where('status_code', 'activa')
            ->update([
                'status_code' => 'cancelada',
                'updated_at' => $now,
                'updated_by' => $actingUserId,
            ]);

        $this->db->table('cobranza_promesa_pago')->insert([
            'expediente_id' => $expedienteId,
            'monto_prometido' => $montoPrometido,
            'fecha_promesa' => $fechaPromesa,
            'medio_pago_id' => $medioPagoId,
            'status_code' => 'activa',
            'observaciones' => $observaciones !== '' ? $observaciones : null,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $actingUserId,
            'updated_by' => $actingUserId,
        ]);
        $promesaId = (int) $this->db->insertID();

        $this->db->table('cobranza_gestion')->insert([
            'expediente_id' => $expedienteId,
            'tipo_gestion_id' => $tipoPromesaId,
            'canal_id' => $canalId,
            'resultado_id' => $resultadoPromesaId,
            'fecha_gestion' => $now,
            'siguiente_accion' => 'Dar seguimiento a promesa de pago',
            'fecha_proximo_seguimiento' => $fechaPromesa,
            'comentarios' => $observaciones !== '' ? $observaciones : 'Promesa de pago registrada desde el centro de cobranza.',
            'metadata_json' => json_encode([
                'promesa_id' => $promesaId,
                'monto_prometido' => $montoPrometido,
                'medio_pago' => $medioPagoCode,
                'source' => 'cobranza_dashboard',
            ]),
            'created_at' => $now,
            'created_by' => $actingUserId,
        ]);

        $this->db->table('cobranza_expediente')
            ->where('id', $expedienteId)
            ->update([
                'status_id' => $statusPromesaId,
                'fecha_promesa_actual' => $fechaPromesa,
                'fecha_proximo_seguimiento' => $fechaPromesa,
                'updated_at' => $now,
                'updated_by' => $actingUserId,
            ]);

        $this->auditTramite((int) $expediente['tramite_id'], 'Registro de promesa de pago en cobranza.', $actingUserId);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->errorResult(500, 'No se pudo registrar la promesa de pago.');
        }

        return [
            'success' => true,
            'statusCode' => 201,
            'message' => 'Promesa de pago registrada.',
            'expediente_id' => $expedienteId,
            'promesa_id' => $promesaId,
        ];
    }

    public function registerPago(int $expedienteId, int $actingUserId, array $payload): array
    {
        if (!$this->hasPromiseAndPaymentTables()) {
            return $this->errorResult(500, 'El modulo de pagos de cobranza no esta configurado en la base de datos.');
        }

        $expediente = $this->loadActiveExpediente($expedienteId);
        if (empty($expediente)) {
            return $this->errorResult(404, 'Expediente de cobranza no encontrado o inactivo.');
        }

        $monto = (float) ($payload['monto'] ?? 0);
        $tipoPago = trim((string) ($payload['tipo_pago'] ?? 'parcial'));
        $fechaPagoReportada = trim((string) ($payload['fecha_pago_reportada'] ?? date('Y-m-d H:i:s')));
        $medioPagoCode = trim((string) ($payload['medio_pago'] ?? 'transferencia'));
        $referenciaPago = trim((string) ($payload['referencia_pago'] ?? ''));
        $observaciones = trim((string) ($payload['observaciones'] ?? ''));
        $canalCode = trim((string) ($payload['canal'] ?? 'interno'));

        if ($monto <= 0) {
            return $this->errorResult(400, 'El monto del pago debe ser mayor a cero.');
        }

        if (!in_array($tipoPago, ['parcial', 'total'], true)) {
            return $this->errorResult(400, 'El tipo de pago es invalido.');
        }

        $medioPagoId = $this->catalogId('cobranza_medio_pago', $medioPagoCode);
        $tipoGestionPagoId = $this->catalogId('cobranza_tipo_gestion', 'pago');
        $resultadoPagoId = $this->catalogId('cobranza_resultado_gestion', 'pago_reportado');
        $canalId = $this->catalogId('cobranza_canal', $canalCode);
        $statusPagoRevisionId = $this->catalogId('cobranza_status', 'pago_en_revision');

        if (in_array(null, [$medioPagoId, $tipoGestionPagoId, $resultadoPagoId, $canalId, $statusPagoRevisionId], true)) {
            return $this->errorResult(500, 'Faltan catalogos base para registrar pagos de cobranza.');
        }

        $saldoActual = (float) ($expediente['saldo_actual'] ?? 0);
        $saldoNuevo = $saldoActual > 0 ? max($saldoActual - $monto, 0) : $saldoActual;
        if ($tipoPago === 'total') {
            $saldoNuevo = 0.0;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('cobranza_pago')->insert([
            'expediente_id' => $expedienteId,
            'monto' => $monto,
            'tipo_pago' => $tipoPago,
            'fecha_pago_reportada' => $fechaPagoReportada,
            'fecha_pago_confirmada' => null,
            'medio_pago_id' => $medioPagoId,
            'referencia_pago' => $referenciaPago !== '' ? $referenciaPago : null,
            'status_code' => 'reportado',
            'documento_id' => null,
            'observaciones' => $observaciones !== '' ? $observaciones : null,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $actingUserId,
            'updated_by' => $actingUserId,
        ]);
        $pagoId = (int) $this->db->insertID();

        $this->db->table('cobranza_gestion')->insert([
            'expediente_id' => $expedienteId,
            'tipo_gestion_id' => $tipoGestionPagoId,
            'canal_id' => $canalId,
            'resultado_id' => $resultadoPagoId,
            'fecha_gestion' => $fechaPagoReportada,
            'siguiente_accion' => 'Validar y conciliar pago reportado',
            'fecha_proximo_seguimiento' => null,
            'comentarios' => $observaciones !== '' ? $observaciones : 'Pago reportado desde el centro de cobranza.',
            'metadata_json' => json_encode([
                'pago_id' => $pagoId,
                'monto' => $monto,
                'tipo_pago' => $tipoPago,
                'medio_pago' => $medioPagoCode,
                'source' => 'cobranza_dashboard',
            ]),
            'created_at' => $now,
            'created_by' => $actingUserId,
        ]);

        $this->db->table('cobranza_expediente')
            ->where('id', $expedienteId)
            ->update([
                'status_id' => $statusPagoRevisionId,
                'saldo_actual' => $saldoNuevo,
                'is_requiere_revision' => 1,
                'fecha_ultimo_contacto' => $fechaPagoReportada,
                'updated_at' => $now,
                'updated_by' => $actingUserId,
            ]);

        $this->auditTramite((int) $expediente['tramite_id'], 'Registro de pago reportado en cobranza.', $actingUserId);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->errorResult(500, 'No se pudo registrar el pago de cobranza.');
        }

        return [
            'success' => true,
            'statusCode' => 201,
            'message' => 'Pago registrado en cobranza.',
            'expediente_id' => $expedienteId,
            'pago_id' => $pagoId,
        ];
    }

    public function confirmPago(int $expedienteId, int $pagoId, int $actingUserId, array $payload = []): array
    {
        if (!$this->hasPromiseAndPaymentTables()) {
            return $this->errorResult(500, 'El modulo de pagos de cobranza no esta configurado en la base de datos.');
        }

        $expediente = $this->loadActiveExpediente($expedienteId);
        if (empty($expediente)) {
            return $this->errorResult(404, 'Expediente de cobranza no encontrado o inactivo.');
        }

        $pago = $this->db->table('cobranza_pago')
            ->where('id', $pagoId)
            ->where('expediente_id', $expedienteId)
            ->get(1)
            ->getRowArray();

        if (empty($pago)) {
            return $this->errorResult(404, 'Pago de cobranza no encontrado.');
        }

        if (($pago['status_code'] ?? '') !== 'reportado') {
            return $this->errorResult(409, 'El pago ya fue conciliado previamente o no puede confirmarse.');
        }

        $fechaConfirmacion = trim((string) ($payload['fecha_pago_confirmada'] ?? date('Y-m-d H:i:s')));
        $comentarios = trim((string) ($payload['observaciones'] ?? ''));
        $canalCode = trim((string) ($payload['canal'] ?? 'interno'));
        $fechaSeguimiento = trim((string) ($payload['fecha_proximo_seguimiento'] ?? ''));

        $tipoGestionPagoId = $this->catalogId('cobranza_tipo_gestion', 'pago');
        $resultadoPagoConfirmadoId = $this->catalogId('cobranza_resultado_gestion', 'pago_confirmado');
        $canalId = $this->catalogId('cobranza_canal', $canalCode);
        $statusCobradoId = $this->catalogId('cobranza_status', 'cobrado');
        $statusGestionId = $this->catalogId('cobranza_status', 'en_gestion');

        if (in_array(null, [$tipoGestionPagoId, $resultadoPagoConfirmadoId, $canalId, $statusCobradoId, $statusGestionId], true)) {
            return $this->errorResult(500, 'Faltan catalogos base para confirmar pagos de cobranza.');
        }

        $saldoActual = (float) ($expediente['saldo_actual'] ?? 0);
        $liquidated = $saldoActual <= 0.00001 || ($pago['tipo_pago'] ?? '') === 'total';
        $nextStatusId = $liquidated ? $statusCobradoId : $statusGestionId;
        $promesaStatus = $liquidated ? 'cumplida' : 'atendida';
        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('cobranza_pago')
            ->where('id', $pagoId)
            ->update([
                'status_code' => 'confirmado',
                'fecha_pago_confirmada' => $fechaConfirmacion,
                'updated_at' => $now,
                'updated_by' => $actingUserId,
            ]);

        $this->db->table('cobranza_promesa_pago')
            ->where('expediente_id', $expedienteId)
            ->where('status_code', 'activa')
            ->update([
                'status_code' => $promesaStatus,
                'updated_at' => $now,
                'updated_by' => $actingUserId,
            ]);

        $this->db->table('cobranza_gestion')->insert([
            'expediente_id' => $expedienteId,
            'tipo_gestion_id' => $tipoGestionPagoId,
            'canal_id' => $canalId,
            'resultado_id' => $resultadoPagoConfirmadoId,
            'fecha_gestion' => $fechaConfirmacion,
            'siguiente_accion' => $liquidated ? 'Pago conciliado sin saldo remanente.' : 'Continuar seguimiento del saldo remanente.',
            'fecha_proximo_seguimiento' => !$liquidated && $fechaSeguimiento !== '' ? $fechaSeguimiento : null,
            'comentarios' => $comentarios !== '' ? $comentarios : ($liquidated ? 'Pago confirmado y conciliado en cobranza.' : 'Pago confirmado con saldo remanente en cobranza.'),
            'metadata_json' => json_encode([
                'pago_id' => $pagoId,
                'saldo_actual' => $saldoActual,
                'liquidated' => $liquidated,
                'source' => 'cobranza_dashboard',
            ]),
            'created_at' => $now,
            'created_by' => $actingUserId,
        ]);

        $this->db->table('cobranza_expediente')
            ->where('id', $expedienteId)
            ->update([
                'status_id' => $nextStatusId,
                'is_requiere_revision' => 0,
                'fecha_ultimo_contacto' => $fechaConfirmacion,
                'fecha_promesa_actual' => null,
                'fecha_proximo_seguimiento' => !$liquidated && $fechaSeguimiento !== '' ? $fechaSeguimiento : null,
                'fecha_cierre' => $liquidated ? $fechaConfirmacion : null,
                'updated_at' => $now,
                'updated_by' => $actingUserId,
            ]);

        $this->auditTramite((int) $expediente['tramite_id'], 'Confirmacion de pago en cobranza.', $actingUserId);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->errorResult(500, 'No se pudo confirmar el pago de cobranza.');
        }

        return [
            'success' => true,
            'statusCode' => 200,
            'message' => $liquidated ? 'Pago confirmado y expediente marcado como cobrado.' : 'Pago confirmado y expediente regresado a gestion.',
            'expediente_id' => $expedienteId,
            'pago_id' => $pagoId,
            'liquidated' => $liquidated,
        ];
    }

    private function hasRequiredTables(): bool
    {
        return $this->db->tableExists('cobranza_expediente')
            && $this->db->tableExists('cobranza_gestion')
            && $this->db->tableExists('cobranza_status')
            && $this->db->tableExists('cobranza_prioridad')
            && $this->db->tableExists('cobranza_tipo_gestion')
            && $this->db->tableExists('cobranza_canal')
            && $this->db->tableExists('cobranza_resultado_gestion');
    }

    private function hasPromiseAndPaymentTables(): bool
    {
        return $this->hasRequiredTables()
            && $this->db->tableExists('cobranza_medio_pago')
            && $this->db->tableExists('cobranza_promesa_pago')
            && $this->db->tableExists('cobranza_pago');
    }

    private function loadTramite(int $tramiteId): array
    {
        return $this->db->table('tramite')
            ->select('tramite.id, tramite.folio, tramite.cli_directo_id, tramite.cli_directo_ejecutivo_id, tramite.user_id, tramite.cobrar_cliente, tramite.pago_gestor_st_id, tramite.tra_status_id, cli_directo.cliente_id')
            ->join('cli_directo', 'cli_directo.id = tramite.cli_directo_id', 'left')
            ->where('tramite.id', $tramiteId)
            ->get(1)
            ->getRowArray() ?? [];
    }

    private function loadActiveExpediente(int $expedienteId): array
    {
        return $this->db->table('cobranza_expediente')
            ->where('id', $expedienteId)
            ->where('is_active', 1)
            ->get(1)
            ->getRowArray() ?? [];
    }

    private function isEligibleForCobranza(array $tramite): bool
    {
        $statusId = (int) ($tramite['tra_status_id'] ?? 0);
        if ($statusId === SGL_TRA_STATUS_COBRO_CLIENTE) {
            return true;
        }

        if ($statusId !== SGL_TRA_STATUS_PAGO_GESTOR) {
            return false;
        }

        return (int) ($tramite['cobrar_cliente'] ?? 0) === 1
            && in_array((int) ($tramite['pago_gestor_st_id'] ?? 0), $this->getPaidPagoGestorStatusIds(), true);
    }

    private function getPaidPagoGestorStatusIds(): array
    {
        if (!$this->db->tableExists('pago_gestor_status')) {
            return [];
        }

        $rows = $this->db->table('pago_gestor_status')->select('id, pago_status')->get()->getResultArray();
        $paidIds = [];
        foreach ($rows as $row) {
            $label = trim((string) ($row['pago_status'] ?? ''));
            $normalized = function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
            if ($normalized !== '' && strpos($normalized, 'pagado') !== false) {
                $paidIds[] = (int) ($row['id'] ?? 0);
            }
        }

        return array_values(array_unique(array_filter($paidIds)));
    }

    private function catalogId(string $table, string $code): ?int
    {
        if (!$this->db->tableExists($table)) {
            return null;
        }

        $row = $this->db->table($table)
            ->select('id')
            ->where('code', $code)
            ->get(1)
            ->getRowArray();

        return isset($row['id']) ? (int) $row['id'] : null;
    }

    private function auditTramite(int $tramiteId, string $description, int $actingUserId): void
    {
        if ($tramiteId <= 0 || !function_exists('log_tramite_change') || !$this->db->tableExists('tramite_audit_log')) {
            return;
        }

        if ($actingUserId > 0) {
            session()->set('id', $actingUserId);
        }

        log_tramite_change($tramiteId, 'update', 'cobranza', $description);
    }

    private function errorResult(int $statusCode, string $message): array
    {
        return [
            'success' => false,
            'statusCode' => $statusCode,
            'message' => $message,
        ];
    }
}