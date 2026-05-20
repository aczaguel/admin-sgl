<?php

namespace Tests\App\Controllers\Deskapp;

use App\Controllers\Deskapp\Cobranza;
use App\Services\CobranzaDashboardService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;
use Psr\Log\NullLogger;
use ReflectionClass;
use Tests\Support\Services\TestableCobranzaExpedienteService;

class CobranzaControllerTest extends CIUnitTestCase
{
    private BaseConnection $db;

    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();

        $this->recreateTables();
        $this->seedSession();
        TestableCobranzaExpedienteService::resetState();
    }

    protected function tearDown(): void
    {
        foreach (['cliente_user', 'tramite', 'cli_directo', 'cliente', 'cobranza_expediente'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        parent::tearDown();
    }

    public function testRegistrarPagoDelegatesToServiceAndRedirectsBackToExpediente(): void
    {
        $this->seedExpediente(7001, 4005);

        $controller = $this->makeController('/deskapp/cobranza/expediente/4005/pagos', 'POST', [
            'monto' => '250.00',
            'tipo_pago' => 'parcial',
            'fecha_pago_reportada' => '2026-05-17T14:30',
            'medio_pago' => 'deposito',
            'referencia_pago' => 'DEP-CTRL-01',
            'observaciones' => 'Pago parcial validado en controller test.',
            'canal' => 'interno',
        ]);

        $result = $controller->registrarPago(4005);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/deskapp/cobranza/expediente/4005', $result->getHeaderLine('Location'));
        $this->assertSame('Pago registrado en cobranza.', session()->getFlashdata('success'));
        $this->assertSame(7001, TestableCobranzaExpedienteService::$registerPagoCall['expediente_id']);
        $this->assertSame(99, TestableCobranzaExpedienteService::$registerPagoCall['acting_user_id']);
        $this->assertSame('2026-05-17 14:30:00', TestableCobranzaExpedienteService::$registerPagoCall['payload']['fecha_pago_reportada']);
        $this->assertSame('DEP-CTRL-01', TestableCobranzaExpedienteService::$registerPagoCall['payload']['referencia_pago']);
    }

    public function testConfirmarPagoDelegatesToServiceAndNormalizesDates(): void
    {
        $this->seedExpediente(7002, 4006);

        $controller = $this->makeController('/deskapp/cobranza/expediente/4006/pagos/9001/confirmar', 'POST', [
            'fecha_pago_confirmada' => '2026-05-17T18:00',
            'observaciones' => 'Tesoreria confirma ingreso.',
            'canal' => 'interno',
            'fecha_proximo_seguimiento' => '2026-05-19T09:00',
        ]);

        $result = $controller->confirmarPago(4006, 9001);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/deskapp/cobranza/expediente/4006', $result->getHeaderLine('Location'));
        $this->assertSame('Pago confirmado y expediente marcado como cobrado.', session()->getFlashdata('success'));
        $this->assertSame(7002, TestableCobranzaExpedienteService::$confirmPagoCall['expediente_id']);
        $this->assertSame(9001, TestableCobranzaExpedienteService::$confirmPagoCall['pago_id']);
        $this->assertSame(99, TestableCobranzaExpedienteService::$confirmPagoCall['acting_user_id']);
        $this->assertSame('2026-05-17 18:00:00', TestableCobranzaExpedienteService::$confirmPagoCall['payload']['fecha_pago_confirmada']);
        $this->assertSame('2026-05-19 09:00:00', TestableCobranzaExpedienteService::$confirmPagoCall['payload']['fecha_proximo_seguimiento']);
    }

    public function testExpedienteReturnsPartialHtmlForAjaxRequests(): void
    {
        $dashboardService = new class extends CobranzaDashboardService {
            public function __construct()
            {
            }

            public function loadSelectedExpediente(int $userId, string $tenantFilterSql = '1 = 1', int $tramiteId = 0, array $filters = []): ?array
            {
                return [
                    'id' => $tramiteId,
                    'folio' => 'TR-' . $tramiteId,
                    'cliente_nombre' => 'Cliente AJAX',
                    'contrato' => 'CTR-' . $tramiteId,
                    'stage_tone' => 'info',
                    'stage_label' => 'En seguimiento',
                    'tramite_url' => '/deskapp/tramitesn/update/' . $tramiteId,
                    'can_open_expediente' => false,
                    'has_active_expediente' => false,
                    'owner_name' => 'Ana Cobranza',
                    'cliente_ejecutivo_nombre' => 'Ejecutivo Cliente',
                    'tramite_status_nombre' => 'Cobro cliente',
                    'cobro_status_nombre' => 'Pendiente',
                    'expediente_status_nombre' => '',
                    'latest_evidence_at' => null,
                    'aging_days' => 4,
                    'fecha_ultimo_contacto' => null,
                    'fecha_proximo_seguimiento' => null,
                    'evidence_total' => 0,
                    'evidence_partial_count' => 0,
                    'evidence_complete_count' => 0,
                    'promesa_activa' => null,
                    'pago_summary' => ['count' => 0, 'pending_count' => 0, 'partial_count' => 0, 'confirmed_count' => 0, 'amount_total' => 0, 'latest_pago_reportado' => null],
                    'pagos_pendientes' => [],
                    'can_register_gestion' => false,
                    'timeline' => [],
                ];
            }

            public function isCobranzaSchemaReady(): bool
            {
                return true;
            }
        };

        $controller = $this->makeController('/deskapp/cobranza/expediente/4010', 'GET', [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'], $dashboardService);

        $result = $controller->expediente(4010);

        $this->assertIsString($result);
        $this->assertStringContainsString('Expediente seleccionado', $result);
        $this->assertStringContainsString('TR-4010', $result);
        $this->assertStringNotContainsString('<html', $result);
    }

    public function testExpedienteShowsCobroFormsWithModulePermissionAndFinePermissions(): void
    {
        $this->seedSession(['list_cobro_cliente', 'editar_final', 'can_upload_dropzone_cobro_cliente', 'quick_action_documentos', 'quick_action_bitacora', 'quick_action_pagos_derecho', 'quick_action_pago_gestor', 'quick_action_evidencias_finales', 'quick_action_cobros_cliente', 'section_pago_gestor', 'section_final_costos', 'tramite_detalle_quick_actions_historial_actividad_ver']);

        $dashboardService = new class extends CobranzaDashboardService {
            public function __construct()
            {
            }

            public function loadSelectedExpediente(int $userId, string $tenantFilterSql = '1 = 1', int $tramiteId = 0, array $filters = []): ?array
            {
                return [
                    'id' => $tramiteId,
                    'folio' => 'TR-' . $tramiteId,
                    'cliente_nombre' => 'Cliente Cobranza',
                    'contrato' => 'CTR-' . $tramiteId,
                    'stage_tone' => 'success',
                    'stage_label' => 'Cobranza activa',
                    'tramite_url' => '/deskapp/tramitesn/update/' . $tramiteId,
                    'can_open_expediente' => false,
                    'has_active_expediente' => true,
                    'owner_name' => 'Ana Cobranza',
                    'cliente_ejecutivo_nombre' => 'Ejecutivo Cliente',
                    'tramite_status_nombre' => 'Cobro cliente',
                    'cobro_status_nombre' => 'Pendiente',
                    'cobro_status_id' => 22,
                    'expediente_status_nombre' => 'Abierto',
                    'latest_evidence_at' => null,
                    'aging_days' => 4,
                    'fecha_ultimo_contacto' => null,
                    'fecha_proximo_seguimiento' => null,
                    'evidence_total' => 0,
                    'evidence_partial_count' => 0,
                    'evidence_complete_count' => 0,
                    'promesa_activa' => null,
                    'pago_summary' => ['count' => 0, 'pending_count' => 0, 'partial_count' => 0, 'confirmed_count' => 0, 'amount_total' => 0, 'latest_pago_reportado' => null],
                    'pagos_pendientes' => [],
                    'can_register_gestion' => false,
                    'timeline' => [],
                    'cobro_cliente_files' => [],
                    'id_give_cliente' => 'GIVE-4011',
                    'numero_factura' => 'FAC-4011',
                    'numero_refactura' => '',
                    'evidencia_cobro_txt' => '',
                    'costo_gestoria' => 100.00,
                    'costo_pago_cliente' => 200.00,
                    'comision_derechos' => 50.00,
                    'iva' => 16.00,
                    'servicios_asociados' => [['label' => 'Alta vehicular']],
                    'readonly_step1' => [['label' => 'Contrato', 'value' => 'CTR-' . $tramiteId]],
                    'readonly_step2' => [['label' => 'Gestor', 'value' => 'Ana Gestor']],
                    'readonly_step3' => [['label' => 'Monto pago de derechos', 'value' => '999.00']],
                    'pago_gestor_resumen' => [['label' => 'Estatus del Pago', 'value' => 'Pagado']],
                    'pago_derechos_db' => [],
                    'pago_gestor_evidencias_db' => [],
                    'pago_gestor_pago_db' => [],
                    'step1_complete' => true,
                    'step2_complete' => true,
                    'step3_complete' => true,
                    'has_comprobante_tramite_recibido' => true,
                    'has_comprobante_acuse_recibo' => true,
                    'has_factura_gestor' => true,
                    'has_comprobante_pago' => true,
                ];
            }

            public function isCobranzaSchemaReady(): bool
            {
                return true;
            }
        };

        $controller = $this->makeController('/deskapp/cobranza/expediente/4011', 'GET', [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'], $dashboardService);

        $result = $controller->expediente(4011);

        $this->assertIsString($result);
        $this->assertStringContainsString('Detalle rapido', $result);
        $this->assertStringContainsString('Paso 1: Datos del tramite', $result);
        $this->assertStringContainsString('data-target="#modal-documentos-cobranza"', $result);
        $this->assertStringContainsString('id="modal-cobro-cliente-cobranza"', $result);
        $this->assertStringContainsString('Guardar ajustes', $result);
        $this->assertStringContainsString('Subir evidencia', $result);
    }

    public function testExpedienteShowsConcluirActionWhenUserCanConcludeAndTramiteIsCobroCliente(): void
    {
        $this->seedSession(['list_cobro_cliente', 'important_concluir_tramite']);

        $dashboardService = new class extends CobranzaDashboardService {
            public function __construct()
            {
            }

            public function loadSelectedExpediente(int $userId, string $tenantFilterSql = '1 = 1', int $tramiteId = 0, array $filters = []): ?array
            {
                return [
                    'id' => $tramiteId,
                    'folio' => 'TR-' . $tramiteId,
                    'cliente_nombre' => 'Cliente Cobranza',
                    'contrato' => 'CTR-' . $tramiteId,
                    'stage_tone' => 'success',
                    'stage_label' => 'Cobranza activa',
                    'tramite_url' => '/deskapp/tramitesn/update/' . $tramiteId,
                    'tramite_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
                    'can_open_expediente' => false,
                    'has_active_expediente' => true,
                    'owner_name' => 'Ana Cobranza',
                    'cliente_ejecutivo_nombre' => 'Ejecutivo Cliente',
                    'tramite_status_nombre' => 'Cobro cliente',
                    'cobro_status_nombre' => 'Pendiente',
                    'cobro_status_id' => 22,
                    'expediente_status_nombre' => 'Abierto',
                    'latest_evidence_at' => null,
                    'aging_days' => 4,
                    'fecha_ultimo_contacto' => null,
                    'fecha_proximo_seguimiento' => null,
                    'evidence_total' => 0,
                    'evidence_partial_count' => 0,
                    'evidence_complete_count' => 0,
                    'promesa_activa' => null,
                    'pago_summary' => ['count' => 0, 'pending_count' => 0, 'partial_count' => 0, 'confirmed_count' => 0, 'amount_total' => 0, 'latest_pago_reportado' => null],
                    'pagos_pendientes' => [],
                    'can_register_gestion' => true,
                    'timeline' => [],
                ];
            }

            public function isCobranzaSchemaReady(): bool
            {
                return true;
            }
        };

        $controller = $this->makeController('/deskapp/cobranza/expediente/4012', 'GET', [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'], $dashboardService);

        $result = $controller->expediente(4012);

        $this->assertIsString($result);
        $this->assertStringContainsString('Concluir tramite', $result);
        $this->assertStringContainsString('/deskapp/tramites/change_status', $result);
    }

    public function testExpedienteHidesConcluirActionWhenThereArePendingPagosToConciliate(): void
    {
        $this->seedSession(['list_cobro_cliente', 'important_concluir_tramite']);

        $dashboardService = new class extends CobranzaDashboardService {
            public function __construct()
            {
            }

            public function loadSelectedExpediente(int $userId, string $tenantFilterSql = '1 = 1', int $tramiteId = 0, array $filters = []): ?array
            {
                return [
                    'id' => $tramiteId,
                    'folio' => 'TR-' . $tramiteId,
                    'cliente_nombre' => 'Cliente Cobranza',
                    'contrato' => 'CTR-' . $tramiteId,
                    'stage_tone' => 'warning',
                    'stage_label' => 'Cobranza activa',
                    'tramite_url' => '/deskapp/tramitesn/update/' . $tramiteId,
                    'tramite_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
                    'can_open_expediente' => false,
                    'has_active_expediente' => true,
                    'owner_name' => 'Ana Cobranza',
                    'cliente_ejecutivo_nombre' => 'Ejecutivo Cliente',
                    'tramite_status_nombre' => 'Cobro cliente',
                    'cobro_status_nombre' => 'Pendiente',
                    'cobro_status_id' => 22,
                    'expediente_status_nombre' => 'Abierto',
                    'latest_evidence_at' => null,
                    'aging_days' => 4,
                    'fecha_ultimo_contacto' => null,
                    'fecha_proximo_seguimiento' => null,
                    'evidence_total' => 0,
                    'evidence_partial_count' => 0,
                    'evidence_complete_count' => 0,
                    'promesa_activa' => null,
                    'pago_summary' => ['count' => 1, 'pending_count' => 1, 'partial_count' => 0, 'confirmed_count' => 0, 'amount_total' => 250, 'latest_pago_reportado' => null],
                    'pagos_pendientes' => [['id' => 9001]],
                    'can_register_gestion' => true,
                    'timeline' => [],
                ];
            }

            public function isCobranzaSchemaReady(): bool
            {
                return true;
            }
        };

        $controller = $this->makeController('/deskapp/cobranza/expediente/4013', 'GET', [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'], $dashboardService);

        $result = $controller->expediente(4013);

        $this->assertIsString($result);
        $this->assertStringNotContainsString('Concluir tramite', $result);
        $this->assertStringContainsString('pagos pendientes de conciliacion', $result);
    }

    public function testIndexRedirectsToDashboardWhenUserLacksCobranzaPermission(): void
    {
        $this->seedSession([]);

        $controller = $this->makeController('/deskapp/cobranza', 'GET', []);

        $result = $controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/deskapp/dashboard', $result->getHeaderLine('Location'));
        $this->assertSame('No tienes permisos para acceder al centro de cobranza.', session()->getFlashdata('error'));
    }

    public function testIndexRedirectsToLoginWhenSessionExpired(): void
    {
        session()->remove(['id', 'user_name', 'user_roles', 'user_permissions']);

        $controller = $this->makeController('/deskapp/cobranza', 'GET', []);

        $result = $controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/deskapp/auth/login', $result->getHeaderLine('Location'));
        $this->assertSame('Sesión expirada.', session()->getFlashdata('error'));
    }

    public function testExpedienteRedirectsToLoginWhenSessionExpired(): void
    {
        session()->remove(['id', 'user_name', 'user_roles', 'user_permissions']);

        $controller = $this->makeController('/deskapp/cobranza/expediente/4010', 'GET', []);

        $result = $controller->expediente(4010);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/deskapp/auth/login', $result->getHeaderLine('Location'));
        $this->assertSame('Sesión expirada.', session()->getFlashdata('error'));
    }

    private function makeController(string $path, string $method, array $post, array $get = [], array $server = [], ?CobranzaDashboardService $dashboardService = null): Cobranza
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['HTTP_X_REQUESTED_WITH'] = $server['HTTP_X_REQUESTED_WITH'] ?? '';

        $request = new IncomingRequest($config, new URI('http://example.com' . $path), null, new UserAgent());
        $request->setMethod(strtolower($method));
        $request->setGlobal('post', $post);
        $request->setGlobal('get', $get);

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new Cobranza();
        $controller->initController($request, $response, $logger);

        $reflection = new ReflectionClass($controller);
        $property = $reflection->getProperty('expedienteService');
        $property->setAccessible(true);
        $property->setValue($controller, new TestableCobranzaExpedienteService());

        if ($dashboardService !== null) {
            $dashboardProperty = $reflection->getProperty('dashboardService');
            $dashboardProperty->setAccessible(true);
            $dashboardProperty->setValue($controller, $dashboardService);
        }

        return $controller;
    }

    private function seedSession(array $permissions = ['list_cobro_cliente']): void
    {
        session()->set([
            'id' => 99,
            'user_name' => 'tester.cobranza',
            'user_roles' => ['Admin'],
            'user_permissions' => $permissions,
        ]);
    }

    private function recreateTables(): void
    {
        foreach (['cliente_user', 'tramite', 'cli_directo', 'cliente', 'cobranza_expediente'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cliente');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'cliente_id' => ['type' => 'INTEGER'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cli_directo');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'cli_directo_id' => ['type' => 'INTEGER'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tramite');

        $this->forge->addField([
            'user_id' => ['type' => 'INTEGER'],
            'cliente_id' => ['type' => 'INTEGER'],
        ]);
        $this->forge->createTable('cliente_user');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER'],
            'is_active' => ['type' => 'INTEGER', 'default' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cobranza_expediente');
    }

    private function seedExpediente(int $expedienteId, int $tramiteId): void
    {
        $clienteId = $tramiteId + 1000;
        $cliDirectoId = $tramiteId + 2000;

        $this->db->table('cliente')->insert(['id' => $clienteId]);
        $this->db->table('cli_directo')->insert([
            'id' => $cliDirectoId,
            'cliente_id' => $clienteId,
        ]);
        $this->db->table('tramite')->insert([
            'id' => $tramiteId,
            'cli_directo_id' => $cliDirectoId,
        ]);
        $this->db->table('cliente_user')->insert([
            'user_id' => 99,
            'cliente_id' => $clienteId,
        ]);
        $this->db->table('cobranza_expediente')->insert([
            'id' => $expedienteId,
            'tramite_id' => $tramiteId,
            'is_active' => 1,
        ]);
    }
}