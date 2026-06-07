<?php

namespace Tests\App\Commands;

use App\Commands\DispatchExternalWebhooks;
use App\Services\ExternalTramiteService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Services;

class DispatchExternalWebhooksCommandTest extends CIUnitTestCase
{
    /** @var resource */
    private $streamFilter;

    protected function setUp(): void
    {
        parent::setUp();

        FakeDispatchExternalWebhooksService::$result = [
            'success' => true,
            'message' => 'No hay webhooks pendientes.',
            'processed' => 0,
            'delivered' => 0,
            'failed' => 0,
        ];
        FakeDispatchExternalWebhooksService::$lastLimit = null;

        CITestStreamFilter::$buffer = '';
        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);
        parent::tearDown();
    }

    public function testRunPrintsSummaryFromService(): void
    {
        FakeDispatchExternalWebhooksService::$result = [
            'success' => true,
            'message' => 'Se procesaron 3 webhooks pendientes (2 entregados, 1 con error).',
            'processed' => 3,
            'delivered' => 2,
            'failed' => 1,
        ];

        $command = (new DispatchExternalWebhooks(Services::logger(), service('commands')))
            ->setService(new FakeDispatchExternalWebhooksService());
        $command->run(['limit' => 7]);

        $buffer = CITestStreamFilter::$buffer;

        $this->assertSame(7, FakeDispatchExternalWebhooksService::$lastLimit);
        $this->assertStringContainsString('Se procesaron 3 webhooks pendientes (2 entregados, 1 con error).', $buffer);
        $this->assertStringContainsString('Procesados: 3', $buffer);
        $this->assertStringContainsString('Entregados: 2', $buffer);
        $this->assertStringContainsString('Con error: 1', $buffer);
    }

    public function testRunPrintsErrorWhenServiceFails(): void
    {
        FakeDispatchExternalWebhooksService::$result = [
            'success' => false,
            'message' => 'No fue posible validar la infraestructura de webhooks.',
            'processed' => 0,
            'delivered' => 0,
            'failed' => 0,
        ];

        $command = (new DispatchExternalWebhooks(Services::logger(), service('commands')))
            ->setService(new FakeDispatchExternalWebhooksService());
        $command->run(['limit' => 2]);

        $buffer = CITestStreamFilter::$buffer;

        $this->assertSame(2, FakeDispatchExternalWebhooksService::$lastLimit);
        $this->assertStringContainsString('No fue posible validar la infraestructura de webhooks.', $buffer);
        $this->assertStringNotContainsString('Procesados:', $buffer);
    }
}

class FakeDispatchExternalWebhooksService extends ExternalTramiteService
{
    public static $result = [];
    public static $lastLimit;

    public function __construct()
    {
    }

    public function dispatchPendingWebhookEvents(int $limit = 20): array
    {
        static::$lastLimit = $limit;

        return static::$result;
    }
}