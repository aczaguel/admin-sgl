<?php

namespace Tests\App\Services;

use App\Services\CobranzaExpedienteService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;

class CobranzaExpedienteServiceTest extends CIUnitTestCase
{
    private BaseConnection $db;

    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();

        $this->recreateTables();
        $this->seedCatalogs();
        $this->seedSession();
    }

    public function testOpenOrReactivateForTramiteCreatesExpedienteAndAperturaGestion(): void
    {
        $this->seedTramite([
            'id' => 4001,
            'folio' => 'TR-4001',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 30,
            'user_id' => 88,
            'tra_status_id' => SGL_TRA_STATUS_PAGO_GESTOR,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $service = new CobranzaExpedienteService($this->db);
        $result = $service->openOrReactivateForTramite(4001, 55);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['created']);
        $this->assertSame(1, $this->db->table('cobranza_expediente')->countAllResults());
        $this->assertSame(1, $this->db->table('cobranza_gestion')->countAllResults());

        $expediente = $this->db->table('cobranza_expediente')->where('tramite_id', 4001)->get()->getRowArray();
        $this->assertSame(55, (int) $expediente['owner_user_id']);
        $this->assertSame(1, (int) $expediente['is_active']);

        $secondCall = $service->openOrReactivateForTramite(4001, 55);
        $this->assertTrue($secondCall['success']);
        $this->assertTrue($secondCall['already_active']);
        $this->assertSame(1, $this->db->table('cobranza_expediente')->countAllResults());
    }

    public function testRegisterGestionUpdatesExpedienteTimeline(): void
    {
        $this->seedTramite([
            'id' => 4002,
            'folio' => 'TR-4002',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 30,
            'user_id' => 88,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $service = new CobranzaExpedienteService($this->db);
        $opened = $service->openOrReactivateForTramite(4002, 55);
        $expedienteId = (int) $opened['expediente_id'];

        $gestion = $service->registerGestion($expedienteId, 55, [
            'tipo' => 'seguimiento',
            'canal' => 'whatsapp',
            'resultado' => 'seguimiento_registrado',
            'comentarios' => 'Se contacto al cliente y solicita seguimiento manana.',
            'siguiente_accion' => 'Confirmar fecha de pago',
            'fecha_proximo_seguimiento' => '2026-05-18 10:00:00',
        ]);

        $this->assertTrue($gestion['success']);
        $this->assertSame(2, $this->db->table('cobranza_gestion')->countAllResults());

        $expediente = $this->db->table('cobranza_expediente')->where('id', $expedienteId)->get()->getRowArray();
        $this->assertSame('2026-05-18 10:00:00', $expediente['fecha_proximo_seguimiento']);
        $this->assertNotEmpty($expediente['fecha_ultimo_contacto']);
    }

    public function testOpenOrReactivateForTramiteReactivatesClosedExpediente(): void
    {
        $this->seedTramite([
            'id' => 4003,
            'folio' => 'TR-4003',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 30,
            'user_id' => 88,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $this->db->table('cobranza_expediente')->insert([
            'tramite_id' => 4003,
            'cliente_id' => 1,
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 30,
            'owner_user_id' => 77,
            'supervisor_user_id' => null,
            'status_id' => 6,
            'prioridad_id' => 2,
            'origen_apertura' => 'legacy',
            'monto_objetivo' => 0,
            'saldo_actual' => 0,
            'moneda' => 'MXN',
            'fecha_apertura' => '2026-05-10 09:00:00',
            'fecha_ultimo_contacto' => '2026-05-11 09:00:00',
            'fecha_proximo_seguimiento' => null,
            'fecha_promesa_actual' => null,
            'fecha_cierre' => '2026-05-12 09:00:00',
            'motivo_cierre_id' => null,
            'sla_at_first_contact_at' => null,
            'sla_resolve_at' => null,
            'is_disputa' => 0,
            'is_requiere_revision' => 0,
            'external_reference' => null,
            'is_active' => 0,
            'created_at' => '2026-05-10 09:00:00',
            'updated_at' => '2026-05-12 09:00:00',
            'created_by' => 77,
            'updated_by' => 77,
        ]);

        $service = new CobranzaExpedienteService($this->db);
        $result = $service->openOrReactivateForTramite(4003, 55);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['reactivated']);

        $expediente = $this->db->table('cobranza_expediente')->where('tramite_id', 4003)->get()->getRowArray();
        $this->assertSame(55, (int) $expediente['owner_user_id']);
        $this->assertSame(1, (int) $expediente['is_active']);
        $this->assertNull($expediente['fecha_cierre']);
    }

    public function testRegisterPromesaPagoCreatesOwnRecordAndUpdatesExpediente(): void
    {
        $this->seedTramite([
            'id' => 4004,
            'folio' => 'TR-4004',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 30,
            'user_id' => 88,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $service = new CobranzaExpedienteService($this->db);
        $opened = $service->openOrReactivateForTramite(4004, 55, ['monto_objetivo' => 1200, 'saldo_actual' => 1200]);
        $expedienteId = (int) $opened['expediente_id'];

        $result = $service->registerPromesaPago($expedienteId, 55, [
            'monto_prometido' => 600,
            'fecha_promesa' => '2026-05-19 12:00:00',
            'medio_pago' => 'transferencia',
            'observaciones' => 'Cliente se compromete a enviar SPEI.',
            'canal' => 'whatsapp',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $this->db->table('cobranza_promesa_pago')->countAllResults());

        $promesa = $this->db->table('cobranza_promesa_pago')->where('expediente_id', $expedienteId)->get()->getRowArray();
        $this->assertSame('activa', $promesa['status_code']);
        $this->assertSame('2026-05-19 12:00:00', $promesa['fecha_promesa']);

        $expediente = $this->db->table('cobranza_expediente')->where('id', $expedienteId)->get()->getRowArray();
        $this->assertSame(3, (int) $expediente['status_id']);
        $this->assertSame('2026-05-19 12:00:00', $expediente['fecha_promesa_actual']);
        $this->assertSame('2026-05-19 12:00:00', $expediente['fecha_proximo_seguimiento']);
    }

    public function testRegisterPagoCreatesOwnRecordAndSupportsPartialPayments(): void
    {
        $this->seedTramite([
            'id' => 4005,
            'folio' => 'TR-4005',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 30,
            'user_id' => 88,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $service = new CobranzaExpedienteService($this->db);
        $opened = $service->openOrReactivateForTramite(4005, 55, ['monto_objetivo' => 1000, 'saldo_actual' => 1000]);
        $expedienteId = (int) $opened['expediente_id'];

        $result = $service->registerPago($expedienteId, 55, [
            'monto' => 250,
            'tipo_pago' => 'parcial',
            'fecha_pago_reportada' => '2026-05-17 14:00:00',
            'medio_pago' => 'deposito',
            'referencia_pago' => 'DEP-7788',
            'observaciones' => 'Cliente envia comprobante parcial.',
            'canal' => 'interno',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $this->db->table('cobranza_pago')->countAllResults());

        $pago = $this->db->table('cobranza_pago')->where('expediente_id', $expedienteId)->get()->getRowArray();
        $this->assertSame('parcial', $pago['tipo_pago']);
        $this->assertSame('reportado', $pago['status_code']);

        $expediente = $this->db->table('cobranza_expediente')->where('id', $expedienteId)->get()->getRowArray();
        $this->assertSame(4, (int) $expediente['status_id']);
        $this->assertSame(750.0, (float) $expediente['saldo_actual']);
        $this->assertSame(1, (int) $expediente['is_requiere_revision']);
    }

    public function testConfirmPagoReturnsExpedienteToGestionWhenSaldoRemains(): void
    {
        $this->seedTramite([
            'id' => 4006,
            'folio' => 'TR-4006',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 30,
            'user_id' => 88,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $service = new CobranzaExpedienteService($this->db);
        $opened = $service->openOrReactivateForTramite(4006, 55, ['monto_objetivo' => 1000, 'saldo_actual' => 1000]);
        $expedienteId = (int) $opened['expediente_id'];

        $reported = $service->registerPago($expedienteId, 55, [
            'monto' => 250,
            'tipo_pago' => 'parcial',
            'fecha_pago_reportada' => '2026-05-17 14:00:00',
            'medio_pago' => 'deposito',
            'referencia_pago' => 'DEP-9900',
            'observaciones' => 'Pago parcial recibido.',
            'canal' => 'interno',
        ]);

        $confirmed = $service->confirmPago($expedienteId, (int) $reported['pago_id'], 55, [
            'fecha_pago_confirmada' => '2026-05-17 16:00:00',
            'observaciones' => 'Tesoreria confirma ingreso parcial.',
            'canal' => 'interno',
            'fecha_proximo_seguimiento' => '2026-05-19 09:00:00',
        ]);

        $this->assertTrue($confirmed['success']);
        $this->assertFalse($confirmed['liquidated']);

        $pago = $this->db->table('cobranza_pago')->where('id', (int) $reported['pago_id'])->get()->getRowArray();
        $this->assertSame('confirmado', $pago['status_code']);
        $this->assertSame('2026-05-17 16:00:00', $pago['fecha_pago_confirmada']);

        $expediente = $this->db->table('cobranza_expediente')->where('id', $expedienteId)->get()->getRowArray();
        $this->assertSame(2, (int) $expediente['status_id']);
        $this->assertSame(0, (int) $expediente['is_requiere_revision']);
        $this->assertSame(750.0, (float) $expediente['saldo_actual']);
        $this->assertSame('2026-05-19 09:00:00', $expediente['fecha_proximo_seguimiento']);
        $this->assertNull($expediente['fecha_cierre']);
    }

    public function testConfirmPagoMarksExpedienteAsCobradoWhenSaldoIsSettled(): void
    {
        $this->seedTramite([
            'id' => 4007,
            'folio' => 'TR-4007',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 30,
            'user_id' => 88,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $service = new CobranzaExpedienteService($this->db);
        $opened = $service->openOrReactivateForTramite(4007, 55, ['monto_objetivo' => 1000, 'saldo_actual' => 1000]);
        $expedienteId = (int) $opened['expediente_id'];

        $reported = $service->registerPago($expedienteId, 55, [
            'monto' => 1000,
            'tipo_pago' => 'total',
            'fecha_pago_reportada' => '2026-05-17 14:30:00',
            'medio_pago' => 'transferencia',
            'referencia_pago' => 'SPEI-1000',
            'observaciones' => 'Pago total reportado.',
            'canal' => 'interno',
        ]);

        $confirmed = $service->confirmPago($expedienteId, (int) $reported['pago_id'], 55, [
            'fecha_pago_confirmada' => '2026-05-17 18:00:00',
            'observaciones' => 'Tesoreria confirma pago total.',
            'canal' => 'interno',
        ]);

        $this->assertTrue($confirmed['success']);
        $this->assertTrue($confirmed['liquidated']);

        $pago = $this->db->table('cobranza_pago')->where('id', (int) $reported['pago_id'])->get()->getRowArray();
        $this->assertSame('confirmado', $pago['status_code']);
        $this->assertSame('2026-05-17 18:00:00', $pago['fecha_pago_confirmada']);

        $expediente = $this->db->table('cobranza_expediente')->where('id', $expedienteId)->get()->getRowArray();
        $this->assertSame(5, (int) $expediente['status_id']);
        $this->assertSame(0, (int) $expediente['is_requiere_revision']);
        $this->assertSame(0.0, (float) $expediente['saldo_actual']);
        $this->assertSame('2026-05-17 18:00:00', $expediente['fecha_cierre']);
    }

    public function testFullCobranzaFlowOpensPromesaReportsAndConfirmsPagoWithoutLeavingActivePromesa(): void
    {
        $this->seedTramite([
            'id' => 4008,
            'folio' => 'TR-4008',
            'cli_directo_id' => 10,
            'cli_directo_ejecutivo_id' => 30,
            'user_id' => 88,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'cobrar_cliente' => 1,
            'pago_gestor_st_id' => 2,
        ]);

        $service = new CobranzaExpedienteService($this->db);

        $opened = $service->openOrReactivateForTramite(4008, 55, ['monto_objetivo' => 1000, 'saldo_actual' => 1000]);
        $expedienteId = (int) $opened['expediente_id'];

        $promesa = $service->registerPromesaPago($expedienteId, 55, [
            'monto_prometido' => 1000,
            'fecha_promesa' => '2026-05-18 09:00:00',
            'medio_pago' => 'transferencia',
            'observaciones' => 'Cliente promete liquidar saldo completo.',
            'canal' => 'whatsapp',
        ]);

        $reported = $service->registerPago($expedienteId, 55, [
            'monto' => 1000,
            'tipo_pago' => 'total',
            'fecha_pago_reportada' => '2026-05-18 11:00:00',
            'medio_pago' => 'transferencia',
            'referencia_pago' => 'SPEI-4008',
            'observaciones' => 'Cliente comparte comprobante final.',
            'canal' => 'interno',
        ]);

        $confirmed = $service->confirmPago($expedienteId, (int) $reported['pago_id'], 55, [
            'fecha_pago_confirmada' => '2026-05-18 12:00:00',
            'observaciones' => 'Tesoreria confirma entrada total.',
            'canal' => 'interno',
        ]);

        $this->assertTrue($opened['success']);
        $this->assertTrue($promesa['success']);
        $this->assertTrue($reported['success']);
        $this->assertTrue($confirmed['success']);
        $this->assertTrue($confirmed['liquidated']);

        $expediente = $this->db->table('cobranza_expediente')->where('id', $expedienteId)->get()->getRowArray();
        $this->assertSame(5, (int) $expediente['status_id']);
        $this->assertSame(0.0, (float) $expediente['saldo_actual']);
        $this->assertSame(0, (int) $expediente['is_requiere_revision']);
        $this->assertNull($expediente['fecha_promesa_actual']);
        $this->assertSame('2026-05-18 12:00:00', $expediente['fecha_cierre']);

        $promesaRow = $this->db->table('cobranza_promesa_pago')->where('id', (int) $promesa['promesa_id'])->get()->getRowArray();
        $this->assertSame('cumplida', $promesaRow['status_code']);

        $pagoRow = $this->db->table('cobranza_pago')->where('id', (int) $reported['pago_id'])->get()->getRowArray();
        $this->assertSame('confirmado', $pagoRow['status_code']);
        $this->assertSame('2026-05-18 12:00:00', $pagoRow['fecha_pago_confirmada']);

        $this->assertSame(4, $this->db->table('cobranza_gestion')->where('expediente_id', $expedienteId)->countAllResults());
        $this->assertSame(0, $this->db->table('cobranza_promesa_pago')->where('expediente_id', $expedienteId)->where('status_code', 'activa')->countAllResults());
    }

    private function seedSession(): void
    {
        session()->set([
            'id' => 55,
            'firstname' => 'Cobranza',
            'midname' => 'QA',
            'lastname' => 'User',
            'email' => 'cobranza@example.com',
        ]);
    }

    private function seedCatalogs(): void
    {
        $this->db->table('pago_gestor_status')->insertBatch([
            ['id' => 1, 'pago_status' => 'Pendiente'],
            ['id' => 2, 'pago_status' => 'Pagado'],
        ]);
        $this->db->table('cli_directo')->insert([
            'id' => 10,
            'razon_social' => 'Cliente Demo',
            'cliente_id' => 1,
        ]);

        foreach ([
            'cobranza_status' => [
                ['id' => 1, 'code' => 'abierto', 'name' => 'Abierto'],
                ['id' => 2, 'code' => 'en_gestion', 'name' => 'En gestion'],
                ['id' => 3, 'code' => 'promesa_activa', 'name' => 'Promesa activa'],
                ['id' => 4, 'code' => 'pago_en_revision', 'name' => 'Pago en revision'],
                ['id' => 5, 'code' => 'cobrado', 'name' => 'Cobrado'],
                ['id' => 6, 'code' => 'cerrado', 'name' => 'Cerrado'],
            ],
            'cobranza_prioridad' => [
                ['id' => 2, 'code' => 'media', 'name' => 'Media'],
            ],
            'cobranza_tipo_gestion' => [
                ['id' => 1, 'code' => 'apertura', 'name' => 'Apertura'],
                ['id' => 2, 'code' => 'seguimiento', 'name' => 'Seguimiento'],
                ['id' => 3, 'code' => 'promesa', 'name' => 'Promesa'],
                ['id' => 4, 'code' => 'pago', 'name' => 'Pago'],
            ],
            'cobranza_canal' => [
                ['id' => 1, 'code' => 'sistema', 'name' => 'Sistema'],
                ['id' => 2, 'code' => 'interno', 'name' => 'Interno'],
                ['id' => 4, 'code' => 'whatsapp', 'name' => 'WhatsApp'],
            ],
            'cobranza_resultado_gestion' => [
                ['id' => 1, 'code' => 'expediente_abierto', 'name' => 'Expediente abierto'],
                ['id' => 2, 'code' => 'seguimiento_registrado', 'name' => 'Seguimiento registrado'],
                ['id' => 5, 'code' => 'promesa_registrada', 'name' => 'Promesa registrada'],
                ['id' => 6, 'code' => 'pago_reportado', 'name' => 'Pago reportado'],
                ['id' => 7, 'code' => 'pago_confirmado', 'name' => 'Pago confirmado'],
            ],
            'cobranza_medio_pago' => [
                ['id' => 1, 'code' => 'transferencia', 'name' => 'Transferencia'],
                ['id' => 2, 'code' => 'deposito', 'name' => 'Deposito'],
                ['id' => 3, 'code' => 'efectivo', 'name' => 'Efectivo'],
            ],
        ] as $table => $rows) {
            $this->db->table($table)->insertBatch($rows);
        }
    }

    private function seedTramite(array $row): void
    {
        $defaults = [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('tramite')->insert(array_merge($defaults, $row));
    }

    private function recreateTables(): void
    {
        foreach ([
            'tramite_audit_log',
            'cobranza_pago',
            'cobranza_promesa_pago',
            'cobranza_medio_pago',
            'cobranza_gestion',
            'cobranza_expediente',
            'cobranza_resultado_gestion',
            'cobranza_canal',
            'cobranza_tipo_gestion',
            'cobranza_prioridad',
            'cobranza_status',
            'pago_gestor_status',
            'cli_directo',
            'tramite',
        ] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        $this->createTramiteTable();
        $this->createCliDirectoTable();
        $this->createPagoGestorStatusTable();
        $this->createCobranzaCatalogTable('cobranza_status');
        $this->createCobranzaCatalogTable('cobranza_prioridad');
        $this->createCobranzaCatalogTable('cobranza_tipo_gestion');
        $this->createCobranzaCatalogTable('cobranza_canal');
        $this->createCobranzaCatalogTable('cobranza_resultado_gestion');
        $this->createCobranzaCatalogTable('cobranza_medio_pago');
        $this->createCobranzaExpedienteTable();
        $this->createCobranzaGestionTable();
        $this->createCobranzaPromesaPagoTable();
        $this->createCobranzaPagoTable();
        $this->createTramiteAuditTable();
    }

    private function createTramiteTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'folio' => ['type' => 'TEXT', 'null' => true],
            'cli_directo_id' => ['type' => 'INTEGER', 'null' => true],
            'cli_directo_ejecutivo_id' => ['type' => 'INTEGER', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'cobrar_cliente' => ['type' => 'INTEGER', 'default' => 0],
            'pago_gestor_st_id' => ['type' => 'INTEGER', 'null' => true],
            'tra_status_id' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TEXT', 'null' => true],
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

    private function createPagoGestorStatusTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'pago_status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pago_gestor_status');
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
            'supervisor_user_id' => ['type' => 'INTEGER', 'null' => true],
            'status_id' => ['type' => 'INTEGER'],
            'prioridad_id' => ['type' => 'INTEGER'],
            'origen_apertura' => ['type' => 'TEXT', 'null' => true],
            'monto_objetivo' => ['type' => 'REAL', 'default' => 0],
            'saldo_actual' => ['type' => 'REAL', 'default' => 0],
            'moneda' => ['type' => 'TEXT', 'null' => true],
            'fecha_apertura' => ['type' => 'TEXT', 'null' => true],
            'fecha_ultimo_contacto' => ['type' => 'TEXT', 'null' => true],
            'fecha_proximo_seguimiento' => ['type' => 'TEXT', 'null' => true],
            'fecha_promesa_actual' => ['type' => 'TEXT', 'null' => true],
            'fecha_cierre' => ['type' => 'TEXT', 'null' => true],
            'motivo_cierre_id' => ['type' => 'INTEGER', 'null' => true],
            'sla_at_first_contact_at' => ['type' => 'TEXT', 'null' => true],
            'sla_resolve_at' => ['type' => 'TEXT', 'null' => true],
            'is_disputa' => ['type' => 'INTEGER', 'default' => 0],
            'is_requiere_revision' => ['type' => 'INTEGER', 'default' => 0],
            'external_reference' => ['type' => 'TEXT', 'null' => true],
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

    private function createTramiteAuditTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER'],
            'folio' => ['type' => 'TEXT', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'username' => ['type' => 'TEXT', 'null' => true],
            'user_email' => ['type' => 'TEXT', 'null' => true],
            'action' => ['type' => 'TEXT', 'null' => true],
            'entity_type' => ['type' => 'TEXT', 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'field_name' => ['type' => 'TEXT', 'null' => true],
            'old_value' => ['type' => 'TEXT', 'null' => true],
            'new_value' => ['type' => 'TEXT', 'null' => true],
            'metadata' => ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'TEXT', 'null' => true],
            'user_agent' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tramite_audit_log');
    }
}