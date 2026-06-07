<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\ExternalTramiteService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class DispatchExternalWebhooks extends BaseCommand
{
    private ?ExternalTramiteService $service = null;

    protected $group       = 'SGL';
    protected $name        = 'external-api:dispatch-webhooks';
    protected $description = 'Despacha webhooks pendientes de la API externa de trámites.';

    protected $usage = 'external-api:dispatch-webhooks [--limit=20]';

    protected $options = [
        '--limit' => 'Número máximo de eventos pendientes a procesar.',
    ];

    public function run(array $params)
    {
        $limit = (int) ($params['limit'] ?? CLI::getOption('limit') ?? 20);
        $service = $this->createService();
        $result = $service->dispatchPendingWebhookEvents($limit);

        if (!($result['success'] ?? false)) {
            CLI::error((string) ($result['message'] ?? 'No fue posible despachar los webhooks.'));
            return;
        }

        CLI::write((string) ($result['message'] ?? 'Proceso completado.'));
        CLI::write('Procesados: ' . (int) ($result['processed'] ?? 0));
        CLI::write('Entregados: ' . (int) ($result['delivered'] ?? 0));
        CLI::write('Con error: ' . (int) ($result['failed'] ?? 0));
    }

    public function setService(ExternalTramiteService $service): self
    {
        $this->service = $service;

        return $this;
    }

    protected function createService(): ExternalTramiteService
    {
        return $this->service ?? new ExternalTramiteService();
    }
}