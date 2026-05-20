<?php

namespace Tests\Support\Services;

use App\Services\CobranzaExpedienteService;

class TestableCobranzaExpedienteService extends CobranzaExpedienteService
{
    public static array $registerPagoCall = [];

    public static array $confirmPagoCall = [];

    public static array $registerPagoResult = [
        'success' => true,
        'message' => 'Pago registrado en cobranza.',
        'pago_id' => 9001,
    ];

    public static array $confirmPagoResult = [
        'success' => true,
        'message' => 'Pago confirmado y expediente marcado como cobrado.',
        'pago_id' => 9001,
        'liquidated' => true,
    ];

    public function __construct()
    {
    }

    public static function resetState(): void
    {
        static::$registerPagoCall = [];
        static::$confirmPagoCall = [];
        static::$registerPagoResult = [
            'success' => true,
            'message' => 'Pago registrado en cobranza.',
            'pago_id' => 9001,
        ];
        static::$confirmPagoResult = [
            'success' => true,
            'message' => 'Pago confirmado y expediente marcado como cobrado.',
            'pago_id' => 9001,
            'liquidated' => true,
        ];
    }

    public function registerPago(int $expedienteId, int $actingUserId, array $payload): array
    {
        static::$registerPagoCall = [
            'expediente_id' => $expedienteId,
            'acting_user_id' => $actingUserId,
            'payload' => $payload,
        ];

        return static::$registerPagoResult;
    }

    public function confirmPago(int $expedienteId, int $pagoId, int $actingUserId, array $payload = []): array
    {
        static::$confirmPagoCall = [
            'expediente_id' => $expedienteId,
            'pago_id' => $pagoId,
            'acting_user_id' => $actingUserId,
            'payload' => $payload,
        ];

        return static::$confirmPagoResult;
    }
}