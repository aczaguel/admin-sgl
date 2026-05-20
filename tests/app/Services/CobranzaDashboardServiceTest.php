<?php

namespace Tests\App\Services;

use App\Services\CobranzaDashboardService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;

class CobranzaDashboardServiceTest extends CIUnitTestCase
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
        $this->seedCatalogs();
    }

    public function testBuildDashboardAggregatesOnlyVisibleCobranzaExpedientes(): void
    {
        $this->seedTramite([
            'id' => 1001,
            'folio' => 'TR-1001',
            'contrato' => 'CTR-1001',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'user_id' => 55,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'started_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
        ]);
        $this->seedTramite([
            'id' => 1002,
            'folio' => 'TR-1002',
            'contrato' => 'CTR-1002',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'user_id' => 77,
            'tra_status_id' => SGL_TRA_STATUS_PAGO_GESTOR,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'started_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
        ]);
        $this->seedTramite([
            'id' => 1003,
            'folio' => 'TR-1003',
            'contrato' => 'CTR-1003',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'user_id' => 55,
            'tra_status_id' => SGL_TRA_STATUS_PAGO_GESTOR,
            'cobrar_cliente' => 0,
            'pago_gestor_st_id' => 2,
        ]);
        $this->seedTramite([
            'id' => 1004,
            'folio' => 'TR-1004',
            'contrato' => 'CTR-1004',
            'cli_directo_id' => 11,
            'cli_directo_ejecutivo_id' => 81,
            'user_id' => 55,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $this->db->table('tra_cobro_cliente')->insert([
            'tramite_id' => 1002,
            'file' => 'pago-parcial.pdf',
            'cobro_correcto' => 'parcial',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);
        $this->db->table('tramite_audit_log')->insert([
            'tramite_id' => 1002,
            'action' => 'Cambio de seguimiento',
            'description' => 'Se recibio evidencia de pago parcial.',
            'username' => 'qa.user',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);

        $service = new CobranzaDashboardService($this->db);
        $dashboard = $service->buildDashboard(55, 'cli_directo_id = 10', ['bucket' => 'all'], 1002);

        $this->assertCount(2, $dashboard['items']);
        $this->assertSame(2, $dashboard['summary']['active']);
        $this->assertSame(1, $dashboard['summary']['my_portfolio']);
        $this->assertSame(1, $dashboard['summary']['ready_to_open']);
        $this->assertSame(1, $dashboard['summary']['without_evidence']);
        $this->assertSame(1, $dashboard['summary']['partial_payment']);
        $this->assertSame(1, $dashboard['summary']['priority_overdue']);
        $this->assertSame(1002, $dashboard['selected_expediente']['id']);
        $this->assertSame('Pago parcial reportado', $dashboard['selected_expediente']['stage_label']);
        $this->assertNotEmpty($dashboard['selected_expediente']['timeline']);

        $withoutEvidence = $service->buildDashboard(55, 'cli_directo_id = 10', ['bucket' => 'sin-evidencia']);

        $this->assertCount(1, $withoutEvidence['items']);
        $this->assertSame(1001, $withoutEvidence['items'][0]['id']);
    }

    public function testBuildDashboardIncludesCobranzaExpedienteAndGestionTimelineWhenSchemaExists(): void
    {
        $this->createCobranzaCatalogTable('cobranza_status');
        $this->createCobranzaCatalogTable('cobranza_tipo_gestion');
        $this->createCobranzaCatalogTable('cobranza_canal');
        $this->createCobranzaCatalogTable('cobranza_resultado_gestion');
        $this->createCobranzaCatalogTable('cobranza_medio_pago');
        $this->createCobranzaExpedienteTable();
        $this->createCobranzaGestionTable();
        $this->createCobranzaPromesaPagoTable();
        $this->createCobranzaPagoTable();

        $this->db->table('cobranza_status')->insert(['id' => 1, 'code' => 'abierto', 'name' => 'Abierto']);
        $this->db->table('cobranza_tipo_gestion')->insert(['id' => 2, 'code' => 'seguimiento', 'name' => 'Seguimiento']);
        $this->db->table('cobranza_canal')->insert(['id' => 4, 'code' => 'whatsapp', 'name' => 'WhatsApp']);
        $this->db->table('cobranza_resultado_gestion')->insert(['id' => 2, 'code' => 'seguimiento_registrado', 'name' => 'Seguimiento registrado']);
        $this->db->table('cobranza_medio_pago')->insert(['id' => 1, 'code' => 'transferencia', 'name' => 'Transferencia']);

        $this->seedTramite([
            'id' => 3001,
            'folio' => 'TR-3001',
            'contrato' => 'CTR-3001',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'user_id' => 77,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);
        $this->db->table('cobranza_expediente')->insert([
            'tramite_id' => 3001,
            'cliente_id' => 1,
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'owner_user_id' => 55,
            'status_id' => 1,
            'prioridad_id' => 1,
            'origen_apertura' => 'modulo_cobranza',
            'monto_objetivo' => 0,
            'saldo_actual' => 0,
            'moneda' => 'MXN',
            'fecha_apertura' => '2026-05-17 08:00:00',
            'fecha_ultimo_contacto' => '2026-05-17 09:00:00',
            'fecha_proximo_seguimiento' => '2026-05-18 10:00:00',
            'is_disputa' => 0,
            'is_requiere_revision' => 0,
            'is_active' => 1,
            'created_at' => '2026-05-17 08:00:00',
            'updated_at' => '2026-05-17 09:00:00',
            'created_by' => 55,
            'updated_by' => 55,
        ]);
        $this->db->table('cobranza_gestion')->insert([
            'expediente_id' => 1,
            'tipo_gestion_id' => 2,
            'canal_id' => 4,
            'resultado_id' => 2,
            'fecha_gestion' => '2026-05-17 09:00:00',
            'siguiente_accion' => 'Confirmar pago',
            'fecha_proximo_seguimiento' => '2026-05-18 10:00:00',
            'comentarios' => 'Cliente confirma seguimiento por WhatsApp.',
            'metadata_json' => '{}',
            'created_at' => '2026-05-17 09:00:00',
            'created_by' => 55,
        ]);
        $this->db->table('cobranza_promesa_pago')->insert([
            'expediente_id' => 1,
            'monto_prometido' => 500,
            'fecha_promesa' => '2026-05-18 12:00:00',
            'medio_pago_id' => 1,
            'status_code' => 'activa',
            'observaciones' => 'Promesa vigente',
            'created_at' => '2026-05-17 09:10:00',
            'updated_at' => '2026-05-17 09:10:00',
            'created_by' => 55,
            'updated_by' => 55,
        ]);
        $this->db->table('cobranza_pago')->insert([
            'expediente_id' => 1,
            'monto' => 250,
            'tipo_pago' => 'parcial',
            'fecha_pago_reportada' => '2026-05-17 09:20:00',
            'fecha_pago_confirmada' => null,
            'medio_pago_id' => 1,
            'referencia_pago' => 'REF-250',
            'status_code' => 'reportado',
            'documento_id' => null,
            'observaciones' => 'Pago parcial',
            'created_at' => '2026-05-17 09:20:00',
            'updated_at' => '2026-05-17 09:20:00',
            'created_by' => 55,
            'updated_by' => 55,
        ]);

        $service = new CobranzaDashboardService($this->db);
        $dashboard = $service->buildDashboard(55, 'cli_directo_id = 10', ['bucket' => 'all'], 3001);

        $this->assertTrue($dashboard['cobranza_schema_ready']);
        $this->assertTrue($dashboard['selected_expediente']['has_active_expediente']);
        $this->assertFalse($dashboard['selected_expediente']['can_open_expediente']);
        $this->assertTrue($dashboard['selected_expediente']['can_register_gestion']);
        $this->assertSame('Abierto', $dashboard['selected_expediente']['expediente_status_nombre']);
        $this->assertSame(500.0, (float) ($dashboard['selected_expediente']['promesa_activa']['monto_prometido'] ?? 0));
        $this->assertSame(1, (int) ($dashboard['selected_expediente']['pago_summary']['count'] ?? 0));
        $this->assertSame(1, (int) ($dashboard['selected_expediente']['pago_summary']['pending_count'] ?? 0));
        $this->assertCount(1, $dashboard['selected_expediente']['pagos_pendientes'] ?? []);
        $this->assertNotEmpty($dashboard['selected_expediente']['timeline']);
        $this->assertSame('Promesa de pago', $dashboard['selected_expediente']['timeline'][0]['title']);
        $this->assertContains('Pago reportado', array_column($dashboard['selected_expediente']['timeline'], 'title'));
    }

    public function testBuildDashboardPaginatesVisibleItemsTwentyAtATime(): void
    {
        for ($index = 1; $index <= 25; $index++) {
            $tramiteId = 5000 + $index;
            $this->seedTramite([
                'id' => $tramiteId,
                'folio' => 'TR-' . $tramiteId,
                'contrato' => 'CTR-' . $tramiteId,
                'cli_directo_id' => 10,
                'cli_directo_ejecutivo_id' => 80,
                'user_id' => 55,
                'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
                'cobrar_cliente' => 1,
                'pago_gestor_st_id' => 2,
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . $index . ' days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-' . $index . ' hours')),
                'started_at' => date('Y-m-d H:i:s', strtotime('-' . $index . ' days')),
            ]);
        }

        $service = new CobranzaDashboardService($this->db);

        $firstPage = $service->buildDashboard(55, 'cli_directo_id = 10', ['bucket' => 'all', 'page' => 1]);
        $secondPage = $service->buildDashboard(55, 'cli_directo_id = 10', ['bucket' => 'all', 'page' => 2], 5001);

        $this->assertCount(20, $firstPage['items']);
        $this->assertSame(25, $firstPage['pagination']['total_items']);
        $this->assertSame(2, $firstPage['pagination']['total_pages']);
        $this->assertSame(1, $firstPage['pagination']['current_page']);
        $this->assertTrue($firstPage['pagination']['has_next']);

        $this->assertCount(5, $secondPage['items']);
        $this->assertSame(2, $secondPage['pagination']['current_page']);
        $this->assertTrue($secondPage['pagination']['has_prev']);
        $this->assertFalse($secondPage['pagination']['has_next']);
        $this->assertSame(5001, $secondPage['selected_expediente']['id']);
    }

    public function testBuildDashboardMovesVisiblePageToSelectedExpedienteWhenRedirectStartsOnAnotherPage(): void
    {
        for ($index = 1; $index <= 25; $index++) {
            $tramiteId = 7000 + $index;
            $this->seedTramite([
                'id' => $tramiteId,
                'folio' => 'TR-' . $tramiteId,
                'contrato' => 'CTR-' . $tramiteId,
                'cli_directo_id' => 10,
                'cli_directo_ejecutivo_id' => 80,
                'user_id' => 55,
                'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
                'cobrar_cliente' => 1,
                'pago_gestor_st_id' => 2,
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . $index . ' days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-' . $index . ' hours')),
                'started_at' => date('Y-m-d H:i:s', strtotime('-' . $index . ' days')),
            ]);
        }

        $service = new CobranzaDashboardService($this->db);
        $dashboard = $service->buildDashboard(55, 'cli_directo_id = 10', ['bucket' => 'all', 'page' => 1], 7001);

        $this->assertSame(2, $dashboard['pagination']['current_page']);
        $this->assertSame(7001, $dashboard['selected_expediente']['id']);
        $this->assertContains(7001, array_map(static fn (array $item): int => (int) $item['id'], $dashboard['items']));
    }

    public function testLoadSelectedExpedienteReturnsOnlyRequestedTramiteDetail(): void
    {
        $this->seedTramite([
            'id' => 6101,
            'folio' => 'TR-6101',
            'contrato' => 'CTR-6101',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'user_id' => 55,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);
        $this->seedTramite([
            'id' => 6102,
            'folio' => 'TR-6102',
            'contrato' => 'CTR-6102',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'user_id' => 55,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $this->db->table('tra_cobro_cliente')->insert([
            'tramite_id' => 6102,
            'file' => 'parcial-6102.pdf',
            'cobro_correcto' => 'parcial',
            'created_at' => '2026-05-17 10:00:00',
        ]);
        $this->db->table('tramite_audit_log')->insert([
            'tramite_id' => 6102,
            'action' => 'Seguimiento',
            'description' => 'Se cargo evidencia puntual para este tramite.',
            'username' => 'qa.user',
            'created_at' => '2026-05-17 10:05:00',
        ]);

        $service = new CobranzaDashboardService($this->db);
        $selected = $service->loadSelectedExpediente(55, 'cli_directo_id = 10', 6102, ['bucket' => 'all']);

        $this->assertNotNull($selected);
        $this->assertSame(6102, $selected['id']);
        $this->assertSame('Pago parcial reportado', $selected['stage_label']);
        $this->assertNotEmpty($selected['timeline']);
    }

    public function testBuildDashboardReflectsClosedCollectionFlowWithoutActivePromesaOrPendingPagos(): void
    {
        $this->createCobranzaCatalogTable('cobranza_status');
        $this->createCobranzaCatalogTable('cobranza_tipo_gestion');
        $this->createCobranzaCatalogTable('cobranza_canal');
        $this->createCobranzaCatalogTable('cobranza_resultado_gestion');
        $this->createCobranzaCatalogTable('cobranza_medio_pago');
        $this->createCobranzaExpedienteTable();
        $this->createCobranzaGestionTable();
        $this->createCobranzaPromesaPagoTable();
        $this->createCobranzaPagoTable();

        $this->db->table('cobranza_status')->insertBatch([
            ['id' => 1, 'code' => 'abierto', 'name' => 'Abierto'],
            ['id' => 5, 'code' => 'cobrado', 'name' => 'Cobrado'],
        ]);
        $this->db->table('cobranza_tipo_gestion')->insertBatch([
            ['id' => 1, 'code' => 'apertura', 'name' => 'Apertura'],
            ['id' => 3, 'code' => 'promesa', 'name' => 'Promesa'],
            ['id' => 4, 'code' => 'pago', 'name' => 'Pago'],
        ]);
        $this->db->table('cobranza_canal')->insertBatch([
            ['id' => 1, 'code' => 'sistema', 'name' => 'Sistema'],
            ['id' => 2, 'code' => 'interno', 'name' => 'Interno'],
            ['id' => 4, 'code' => 'whatsapp', 'name' => 'WhatsApp'],
        ]);
        $this->db->table('cobranza_resultado_gestion')->insertBatch([
            ['id' => 1, 'code' => 'expediente_abierto', 'name' => 'Expediente abierto'],
            ['id' => 5, 'code' => 'promesa_registrada', 'name' => 'Promesa registrada'],
            ['id' => 6, 'code' => 'pago_reportado', 'name' => 'Pago reportado'],
            ['id' => 7, 'code' => 'pago_confirmado', 'name' => 'Pago confirmado'],
        ]);
        $this->db->table('cobranza_medio_pago')->insert(['id' => 1, 'code' => 'transferencia', 'name' => 'Transferencia']);

        $this->seedTramite([
            'id' => 3002,
            'folio' => 'TR-3002',
            'contrato' => 'CTR-3002',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'user_id' => 55,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
            'cobro_status_id' => SGL_COBRO_STATUS_COBRADO,
        ]);
        $this->db->table('cobranza_expediente')->insert([
            'tramite_id' => 3002,
            'cliente_id' => 1,
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'owner_user_id' => 55,
            'status_id' => 5,
            'prioridad_id' => 1,
            'origen_apertura' => 'modulo_cobranza',
            'monto_objetivo' => 1000,
            'saldo_actual' => 0,
            'moneda' => 'MXN',
            'fecha_apertura' => '2026-05-18 08:00:00',
            'fecha_ultimo_contacto' => '2026-05-18 12:00:00',
            'fecha_proximo_seguimiento' => null,
            'is_disputa' => 0,
            'is_requiere_revision' => 0,
            'is_active' => 1,
            'created_at' => '2026-05-18 08:00:00',
            'updated_at' => '2026-05-18 12:00:00',
            'created_by' => 55,
            'updated_by' => 55,
        ]);
        $this->db->table('cobranza_promesa_pago')->insert([
            'expediente_id' => 1,
            'monto_prometido' => 1000,
            'fecha_promesa' => '2026-05-18 09:00:00',
            'medio_pago_id' => 1,
            'status_code' => 'cumplida',
            'observaciones' => 'Promesa atendida con pago confirmado.',
            'created_at' => '2026-05-18 08:30:00',
            'updated_at' => '2026-05-18 12:00:00',
            'created_by' => 55,
            'updated_by' => 55,
        ]);
        $this->db->table('cobranza_pago')->insert([
            'expediente_id' => 1,
            'monto' => 1000,
            'tipo_pago' => 'total',
            'fecha_pago_reportada' => '2026-05-18 11:00:00',
            'fecha_pago_confirmada' => '2026-05-18 12:00:00',
            'medio_pago_id' => 1,
            'referencia_pago' => 'SPEI-3002',
            'status_code' => 'confirmado',
            'documento_id' => null,
            'observaciones' => 'Pago total conciliado.',
            'created_at' => '2026-05-18 11:00:00',
            'updated_at' => '2026-05-18 12:00:00',
            'created_by' => 55,
            'updated_by' => 55,
        ]);

        $service = new CobranzaDashboardService($this->db);
        $dashboard = $service->buildDashboard(55, 'cli_directo_id = 10', ['bucket' => 'all'], 3002);

        $this->assertSame('Cobrado', $dashboard['selected_expediente']['expediente_status_nombre']);
        $this->assertNull($dashboard['selected_expediente']['promesa_activa']);
        $this->assertSame(1, (int) ($dashboard['selected_expediente']['pago_summary']['count'] ?? 0));
        $this->assertSame(1, (int) ($dashboard['selected_expediente']['pago_summary']['confirmed_count'] ?? 0));
        $this->assertSame(0, (int) ($dashboard['selected_expediente']['pago_summary']['pending_count'] ?? 0));
        $this->assertEmpty($dashboard['selected_expediente']['pagos_pendientes']);
        $this->assertNotEmpty($dashboard['selected_expediente']['timeline']);
        $this->assertSame('Pago confirmado', $dashboard['selected_expediente']['timeline'][0]['title']);
    }

    public function testBuildDashboardBlocksInaccessibleClienteFilter(): void
    {
        $this->seedTramite([
            'id' => 2001,
            'folio' => 'TR-2001',
            'contrato' => 'CTR-2001',
            'cli_directo_id' => 11,
            'cli_directo_ejecutivo_id' => 81,
            'user_id' => 55,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $service = new CobranzaDashboardService($this->db);
        $dashboard = $service->buildDashboard(55, '1 = 0', ['bucket' => 'all']);

        $this->assertSame(0, $dashboard['summary']['active']);
        $this->assertEmpty($dashboard['items']);
    }

    public function testBuildDashboardSearchMatchesTramiteId(): void
    {
        $this->seedTramite([
            'id' => 4030,
            'folio' => 'TR-4030',
            'contrato' => 'CTR-4030',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'user_id' => 55,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);
        $this->seedTramite([
            'id' => 4031,
            'folio' => 'TR-4031',
            'contrato' => 'CTR-4031',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 80,
            'user_id' => 55,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $service = new CobranzaDashboardService($this->db);
        $dashboard = $service->buildDashboard(55, 'cli_directo_id = 10', ['bucket' => 'all', 'q' => '4030']);

        $this->assertCount(1, $dashboard['items']);
        $this->assertSame(4030, $dashboard['items'][0]['id']);
    }

    private function seedSession(): void
    {
        session()->set([
            'id' => 55,
            'user_name' => 'Cobranza QA',
            'user_roles' => ['ejecutivo'],
        ]);
    }

    private function seedCatalogs(): void
    {
        $this->db->table('cli_directo')->insertBatch([
            ['id' => 10, 'razon_social' => 'Cliente Uno', 'cliente_id' => 1],
            ['id' => 11, 'razon_social' => 'Cliente Dos', 'cliente_id' => 2],
        ]);
        $this->db->table('cliente')->insertBatch([
            ['id' => 1, 'razon_social' => 'Cliente Uno'],
            ['id' => 2, 'razon_social' => 'Cliente Dos'],
        ]);
        $this->db->table('cli_directo_ejecutivo')->insertBatch([
            ['id' => 80, 'nombre' => 'Ejecutivo Uno'],
            ['id' => 81, 'nombre' => 'Ejecutivo Dos'],
        ]);
        $this->db->table('users')->insertBatch([
            ['id' => 55, 'firstname' => 'Ana', 'midname' => '', 'lastname' => 'Cobranza'],
            ['id' => 77, 'firstname' => 'Luis', 'midname' => '', 'lastname' => 'Seguimiento'],
        ]);
        $this->db->table('tra_status')->insertBatch([
            ['id' => SGL_TRA_STATUS_PAGO_GESTOR, 'tra_status' => 'Pago a Gestor'],
            ['id' => SGL_TRA_STATUS_COBRO_CLIENTE, 'tra_status' => 'Cobro a Cliente'],
        ]);
        $this->db->table('cobro_statuses')->insertBatch([
            ['id' => SGL_COBRO_STATUS_PENDIENTE, 'cobro_status' => 'Pendiente'],
            ['id' => SGL_COBRO_STATUS_COBRADO, 'cobro_status' => 'Cobrado'],
        ]);
        $this->db->table('pago_gestor_status')->insertBatch([
            ['id' => 1, 'pago_status' => 'Pendiente'],
            ['id' => 2, 'pago_status' => 'Pagado'],
        ]);
        $this->db->table('cliente_user')->insert([
            'user_id' => 55,
            'cliente_id' => 1,
        ]);
    }

    private function seedTramite(array $data): void
    {
        $defaults = [
            'unidad' => 'Unidad demo',
            'serie' => 'SERIE-DEMO',
            'placas' => 'ABC123',
            'cobro_status_id' => SGL_COBRO_STATUS_PENDIENTE,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'started_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('tramite')->insert(array_merge($defaults, $data));
    }

    private function recreateTables(): void
    {
        foreach ([
            'tramite_audit_log',
            'cobranza_promesa_pago',
            'cobranza_pago',
            'cobranza_medio_pago',
            'cobranza_gestion',
            'cobranza_expediente',
            'cobranza_resultado_gestion',
            'cobranza_canal',
            'cobranza_tipo_gestion',
            'cobranza_status',
            'tra_cobro_cliente',
            'tramite',
            'cliente',
            'cli_directo',
            'cli_directo_ejecutivo',
            'users',
            'tra_status',
            'cobro_statuses',
            'pago_gestor_status',
            'cliente_user',
            'us_user_roles',
            'us_roles',
        ] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        $this->createTramiteTable();
        $this->createClienteTable();
        $this->createCliDirectoTable();
        $this->createCliDirectoEjecutivoTable();
        $this->createUsersTable();
        $this->createTraStatusTable();
        $this->createCobroStatusesTable();
        $this->createPagoGestorStatusTable();
        $this->createTraCobroClienteTable();
        $this->createTramiteAuditLogTable();
        $this->createClienteUserTable();
        $this->createUsRolesTable();
        $this->createUsUserRolesTable();
    }

    private function createTramiteTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'folio' => ['type' => 'TEXT', 'null' => true],
            'contrato' => ['type' => 'TEXT', 'null' => true],
            'unidad' => ['type' => 'TEXT', 'null' => true],
            'serie' => ['type' => 'TEXT', 'null' => true],
            'placas' => ['type' => 'TEXT', 'null' => true],
            'id_give_cliente' => ['type' => 'TEXT', 'null' => true],
            'numero_factura' => ['type' => 'TEXT', 'null' => true],
            'numero_refactura' => ['type' => 'TEXT', 'null' => true],
            'evidencia_cobro_txt' => ['type' => 'TEXT', 'null' => true],
            'costo_gestoria' => ['type' => 'REAL', 'default' => 0],
            'costo_pago_cliente' => ['type' => 'REAL', 'default' => 0],
            'comision_derechos' => ['type' => 'REAL', 'default' => 0],
            'iva' => ['type' => 'REAL', 'default' => 0],
            'costo_total' => ['type' => 'REAL', 'default' => 0],
            'cli_directo_id' => ['type' => 'INTEGER', 'null' => true],
            'cli_directo_ejecutivo_id' => ['type' => 'INTEGER', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'cobrar_cliente' => ['type' => 'INTEGER', 'default' => 0],
            'pago_gestor_st_id' => ['type' => 'INTEGER', 'null' => true],
            'tra_status_id' => ['type' => 'INTEGER', 'null' => true],
            'cobro_status_id' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TEXT', 'null' => true],
            'started_at' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tramite');
    }

    private function createCliDirectoTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'razon_social' => ['type' => 'TEXT', 'null' => true],
            'cliente_id' => ['type' => 'INTEGER', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cli_directo');
    }

    private function createClienteTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'razon_social' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cliente');
    }

    private function createCliDirectoEjecutivoTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'nombre' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cli_directo_ejecutivo');
    }

    private function createUsersTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'firstname' => ['type' => 'TEXT', 'null' => true],
            'midname' => ['type' => 'TEXT', 'null' => true],
            'lastname' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
    }

    private function createTraStatusTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'tra_status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tra_status');
    }

    private function createCobroStatusesTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'cobro_status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cobro_statuses');
    }

    private function createCobranzaCatalogTable(string $table): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'code' => ['type' => 'TEXT', 'null' => true],
            'name' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable($table);
    }

    private function createCobranzaExpedienteTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER'],
            'cliente_id' => ['type' => 'INTEGER', 'null' => true],
            'cli_directo_id' => ['type' => 'INTEGER', 'null' => true],
            'cli_directo_ejecutivo_id' => ['type' => 'INTEGER', 'null' => true],
            'owner_user_id' => ['type' => 'INTEGER', 'null' => true],
            'status_id' => ['type' => 'INTEGER', 'null' => true],
            'prioridad_id' => ['type' => 'INTEGER', 'null' => true],
            'origen_apertura' => ['type' => 'TEXT', 'null' => true],
            'monto_objetivo' => ['type' => 'REAL', 'default' => 0],
            'saldo_actual' => ['type' => 'REAL', 'default' => 0],
            'moneda' => ['type' => 'TEXT', 'null' => true],
            'fecha_apertura' => ['type' => 'TEXT', 'null' => true],
            'fecha_ultimo_contacto' => ['type' => 'TEXT', 'null' => true],
            'fecha_proximo_seguimiento' => ['type' => 'TEXT', 'null' => true],
            'is_disputa' => ['type' => 'INTEGER', 'default' => 0],
            'is_requiere_revision' => ['type' => 'INTEGER', 'default' => 0],
            'is_active' => ['type' => 'INTEGER', 'default' => 1],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'updated_by' => ['type' => 'INTEGER', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cobranza_expediente');
    }

    private function createCobranzaGestionTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'expediente_id' => ['type' => 'INTEGER'],
            'tipo_gestion_id' => ['type' => 'INTEGER'],
            'canal_id' => ['type' => 'INTEGER'],
            'resultado_id' => ['type' => 'INTEGER'],
            'fecha_gestion' => ['type' => 'TEXT', 'null' => true],
            'siguiente_accion' => ['type' => 'TEXT', 'null' => true],
            'fecha_proximo_seguimiento' => ['type' => 'TEXT', 'null' => true],
            'comentarios' => ['type' => 'TEXT', 'null' => true],
            'metadata_json' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cobranza_gestion');
    }

    private function createCobranzaPromesaPagoTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'expediente_id' => ['type' => 'INTEGER'],
            'monto_prometido' => ['type' => 'REAL', 'default' => 0],
            'fecha_promesa' => ['type' => 'TEXT', 'null' => true],
            'medio_pago_id' => ['type' => 'INTEGER'],
            'status_code' => ['type' => 'TEXT', 'null' => true],
            'observaciones' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'updated_by' => ['type' => 'INTEGER', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cobranza_promesa_pago');
    }

    private function createCobranzaPagoTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'expediente_id' => ['type' => 'INTEGER'],
            'monto' => ['type' => 'REAL', 'default' => 0],
            'tipo_pago' => ['type' => 'TEXT', 'null' => true],
            'fecha_pago_reportada' => ['type' => 'TEXT', 'null' => true],
            'fecha_pago_confirmada' => ['type' => 'TEXT', 'null' => true],
            'medio_pago_id' => ['type' => 'INTEGER'],
            'referencia_pago' => ['type' => 'TEXT', 'null' => true],
            'status_code' => ['type' => 'TEXT', 'null' => true],
            'documento_id' => ['type' => 'INTEGER', 'null' => true],
            'observaciones' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'updated_by' => ['type' => 'INTEGER', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cobranza_pago');
    }

    private function createPagoGestorStatusTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'pago_status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pago_gestor_status');
    }

    private function createTraCobroClienteTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER'],
            'file' => ['type' => 'TEXT', 'null' => true],
            'cobro_correcto' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tra_cobro_cliente');
    }

    private function createTramiteAuditLogTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER'],
            'action' => ['type' => 'TEXT', 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'username' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tramite_audit_log');
    }

    private function createClienteUserTable(): void
    {
        $this->forge->addField([
            'user_id' => ['type' => 'INTEGER'],
            'cliente_id' => ['type' => 'INTEGER'],
        ]);
        $this->forge->createTable('cliente_user');
    }

    private function createUsRolesTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'role_name' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('us_roles');
    }

    private function createUsUserRolesTable(): void
    {
        $this->forge->addField([
            'user_id' => ['type' => 'INTEGER'],
            'role_id' => ['type' => 'INTEGER'],
        ]);
        $this->forge->createTable('us_user_roles');
    }
}