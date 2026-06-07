<?php

namespace Tests\App\Models;

use App\Models\DashboardModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;

class TestableDashboardModel extends DashboardModel
{
    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
    }
}

class DashboardModelTest extends CIUnitTestCase
{
    private const ACTIVE_STATUS_ID = 7;
    private const CONCLUIDO_STATUS_ID = 20;
    private const CANCELADO_STATUS_ID = 21;
    private const COBRO_PENDIENTE_STATUS_ID = 22;

    private BaseConnection $db;

    private Forge $forge;

    private string $databasePath;

    protected function setUp(): void
    {
        parent::setUp();

        $dbConfig = new \Config\Database();
        $config = $dbConfig->tests;

        $this->databasePath = WRITEPATH . 'dashboard-model-test.sqlite';
        if (is_file($this->databasePath)) {
            unlink($this->databasePath);
        }

        $config['database'] = $this->databasePath;
        $config['DBPrefix'] = '';

        $this->db = \Config\Database::connect($config, false);
        $this->forge = \Config\Database::forge($this->db);

        if ($this->db->connID instanceof \SQLite3) {
            $this->db->connID->createFunction('CURDATE', static function (): string {
                return date('Y-m-d');
            }, 0);
            $this->db->connID->createFunction('DATEDIFF', static function ($left, $right): ?int {
                if ($left === null || $right === null) {
                    return null;
                }

                try {
                    $leftDate = new \DateTimeImmutable((string) $left);
                    $rightDate = new \DateTimeImmutable((string) $right);
                } catch (\Exception $exception) {
                    return null;
                }

                return (int) $rightDate->diff($leftDate)->format('%r%a');
            }, 2);
        }

        $this->recreateTables();
        $this->seedCatalogs();
    }

    protected function tearDown(): void
    {
        if (isset($this->db)) {
            $this->db->close();
        }

        if (isset($this->databasePath) && is_file($this->databasePath)) {
            unlink($this->databasePath);
        }

        parent::tearDown();
    }

    public function testGetTramitesRetrasadosExcludesLockedStatuses(): void
    {
        $this->seedTramite([
            'id' => 7001,
            'folio' => 'TR-7001',
            'tra_status_id' => self::ACTIVE_STATUS_ID,
            'started_at' => date('Y-m-d H:i:s', strtotime('-45 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-46 days')),
        ]);
        $this->seedTramite([
            'id' => 7002,
            'folio' => 'TR-7002',
            'tra_status_id' => self::CONCLUIDO_STATUS_ID,
            'started_at' => date('Y-m-d H:i:s', strtotime('-45 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-46 days')),
        ]);
        $this->seedTramite([
            'id' => 7003,
            'folio' => 'TR-7003',
            'tra_status_id' => self::CANCELADO_STATUS_ID,
            'started_at' => date('Y-m-d H:i:s', strtotime('-45 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-46 days')),
        ]);

        $rows = (new TestableDashboardModel($this->db))->getTramitesRetrasados(30);

        $this->assertSame([7001], array_map('intval', array_column($rows, 'id')));
    }

    public function testCriticalAlertCountsExcludeLockedStatuses(): void
    {
        $this->seedTramite([
            'id' => 8001,
            'folio' => 'TR-8001',
            'tra_status_id' => self::ACTIVE_STATUS_ID,
            'started_at' => date('Y-m-d H:i:s', strtotime('-45 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-46 days')),
        ]);
        $this->seedTramite([
            'id' => 8002,
            'folio' => 'TR-8002',
            'tra_status_id' => self::CONCLUIDO_STATUS_ID,
            'started_at' => date('Y-m-d H:i:s', strtotime('-45 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-46 days')),
        ]);
        $this->seedTramite([
            'id' => 8003,
            'folio' => 'TR-8003',
            'tra_status_id' => self::ACTIVE_STATUS_ID,
            'numero_factura' => 'FAC-8003',
            'cobro_status_id' => self::COBRO_PENDIENTE_STATUS_ID,
            'finished_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
            'started_at' => date('Y-m-d H:i:s', strtotime('-25 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-26 days')),
        ]);
        $this->seedTramite([
            'id' => 8004,
            'folio' => 'TR-8004',
            'tra_status_id' => self::CANCELADO_STATUS_ID,
            'numero_factura' => 'FAC-8004',
            'cobro_status_id' => self::COBRO_PENDIENTE_STATUS_ID,
            'finished_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
            'started_at' => date('Y-m-d H:i:s', strtotime('-25 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-26 days')),
        ]);
        $this->seedTramite([
            'id' => 8005,
            'folio' => 'TR-8005',
            'tra_status_id' => self::ACTIVE_STATUS_ID,
            'started_at' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
        ]);
        $this->seedTramite([
            'id' => 8006,
            'folio' => 'TR-8006',
            'tra_status_id' => self::CONCLUIDO_STATUS_ID,
            'started_at' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
        ]);

        $model = new TestableDashboardModel($this->db);

        $this->assertSame(1, $model->countTramitesRetrasados(30));
        $this->assertSame(1, $model->countTramitesPendientesCobro(15));
        $this->assertSame(1, $model->countTramitesEstancados(7));
    }

    public function testSemaforoAndAtoradosExcludeLockedStatuses(): void
    {
        $this->seedTramite([
            'id' => 9001,
            'folio' => 'TR-9001',
            'tra_status_id' => self::ACTIVE_STATUS_ID,
            'tra_tipos_id' => 2,
            'entidad_id' => 1,
            'started_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
        ]);
        $this->seedTramite([
            'id' => 9002,
            'folio' => 'TR-9002',
            'tra_status_id' => self::CONCLUIDO_STATUS_ID,
            'tra_tipos_id' => 2,
            'entidad_id' => 1,
            'started_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-21 days')),
        ]);
        $this->seedTramite([
            'id' => 9003,
            'folio' => 'TR-9003',
            'tra_status_id' => self::ACTIVE_STATUS_ID,
            'tra_tipos_id' => 1,
            'entidad_id' => 2,
            'started_at' => date('Y-m-d H:i:s', strtotime('-14 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
        ]);
        $this->seedTramite([
            'id' => 9004,
            'folio' => 'TR-9004',
            'tra_status_id' => self::CANCELADO_STATUS_ID,
            'tra_tipos_id' => 1,
            'entidad_id' => 2,
            'started_at' => date('Y-m-d H:i:s', strtotime('-14 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
        ]);

        $model = new TestableDashboardModel($this->db);
        $semaforo = $model->getSemaforoAtencion();
        $atorados = $model->getAtoradosPorTipoServicio(10);

        $this->assertSame(1, (int) ($semaforo['local_amarillo'] ?? 0));
        $this->assertSame(0, (int) ($semaforo['local_violeta'] ?? 0));
        $this->assertSame(1, (int) ($semaforo['foraneo_rojo'] ?? 0));
        $this->assertSame(0, (int) ($semaforo['foraneo_violeta'] ?? 0));

        $this->assertCount(1, $atorados);
        $this->assertSame('Cambio de propietario', $atorados[0]['tipo']);
        $this->assertSame(1, (int) $atorados[0]['total']);
    }

    private function seedCatalogs(): void
    {
        $this->db->table('cli_directo')->insert([
            'id' => 10,
            'razon_social' => 'Cliente Demo',
        ]);
        $this->db->table('users')->insert([
            'id' => 55,
            'firstname' => 'Ana',
            'lastname' => 'QA',
        ]);
        $this->db->table('tra_status')->insertBatch([
            ['id' => self::ACTIVE_STATUS_ID, 'tra_status' => 'En proceso'],
            ['id' => self::CONCLUIDO_STATUS_ID, 'tra_status' => 'Concluido'],
            ['id' => self::CANCELADO_STATUS_ID, 'tra_status' => 'Cancelado'],
        ]);
        $this->db->table('tra_tipos')->insertBatch([
            ['id' => 1, 'tipo_tramite' => 'Cambio de propietario'],
            ['id' => 2, 'tipo_tramite' => 'Alta de placas'],
        ]);
        $this->db->table('entidad')->insertBatch([
            ['id' => 1, 'entidad' => 'CIUDAD DE MEXICO'],
            ['id' => 2, 'entidad' => 'JALISCO'],
        ]);
    }

    private function seedTramite(array $data): void
    {
        $defaults = [
            'contrato' => 'CTR-DEMO',
            'unidad' => 'Unidad demo',
            'tra_status_id' => self::ACTIVE_STATUS_ID,
            'tra_tipos_id' => 1,
            'entidad_id' => 2,
            'user_id' => 55,
            'cli_directo_id' => 10,
            'cobro_status_id' => 0,
            'numero_factura' => null,
            'numero_refactura' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'started_at' => date('Y-m-d H:i:s'),
            'finished_at' => null,
        ];

        $this->db->table('tramite')->insert(array_merge($defaults, $data));
    }

    private function recreateTables(): void
    {
        foreach (['tramite', 'entidad', 'tra_tipos', 'tra_status', 'users', 'cli_directo'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        $this->createTramiteTable();
        $this->createEntidadTable();
        $this->createTraTiposTable();
        $this->createTraStatusTable();
        $this->createUsersTable();
        $this->createCliDirectoTable();
    }

    private function createTramiteTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'folio' => ['type' => 'TEXT', 'null' => true],
            'contrato' => ['type' => 'TEXT', 'null' => true],
            'unidad' => ['type' => 'TEXT', 'null' => true],
            'numero_factura' => ['type' => 'TEXT', 'null' => true],
            'numero_refactura' => ['type' => 'TEXT', 'null' => true],
            'tra_status_id' => ['type' => 'INTEGER', 'null' => true],
            'tra_tipos_id' => ['type' => 'INTEGER', 'null' => true],
            'entidad_id' => ['type' => 'INTEGER', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'cli_directo_id' => ['type' => 'INTEGER', 'null' => true],
            'cobro_status_id' => ['type' => 'INTEGER', 'null' => true],
            'costo_total' => ['type' => 'REAL', 'default' => 0],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'started_at' => ['type' => 'TEXT', 'null' => true],
            'finished_at' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tramite');
    }

    private function createEntidadTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'entidad' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('entidad');
    }

    private function createTraTiposTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'tipo_tramite' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tra_tipos');
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

    private function createUsersTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'firstname' => ['type' => 'TEXT', 'null' => true],
            'lastname' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
    }

    private function createCliDirectoTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'razon_social' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cli_directo');
    }
}