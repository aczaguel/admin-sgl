<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class SglDemoData extends BaseCommand
{
    protected $group       = 'SGL';
    protected $name        = 'sgl:demo-data';
    protected $description = 'Crea datos dummy (clientes, cli_directo, ejecutivos, usuario y trámites) para smoke testing.';

    /** @var array<string, array<string, true>> */
    private array $tableColumnsCache = [];

    protected $usage = 'sgl:demo-data [--count=10] [--email=luisa.flores@demo.local] [--password=Demo1234!]';

    protected $options = [
        '--count'    => 'Cantidad de trámites dummy a crear (default: 10).',
        '--email'    => 'Email del usuario dummy (default: luisa.flores@demo.local).',
        '--password' => 'Password del usuario dummy (default: Demo1234!).',
    ];

    public function run(array $params)
    {
        $count = (int) ($params['count'] ?? CLI::getOption('count') ?? 10);
        if ($count <= 0) {
            $count = 10;
        }

        $email = (string) ($params['email'] ?? CLI::getOption('email') ?? 'luisa.flores@demo.local');
        $passwordPlain = (string) ($params['password'] ?? CLI::getOption('password') ?? 'Demo1234!');

        $db = \Config\Database::connect();

        $db->transStart();

        // 1) Clientes
        $clienteYolandaId = $this->ensureCliente($db, [
            'nombre'       => 'Servicios Yolanda',
            'razon_social' => 'Servicios Yolanda',
            'rfc'          => 'SYL010101AA1',
            'correo_electronico' => 'yolanda@demo.local',
            'telefono'     => '5550000001',
            'status'       => 1,
            'prefijo'      => 'YOL',
        ]);

        $clienteSofiaId = $this->ensureCliente($db, [
            'nombre'       => 'Servicios Sofia',
            'razon_social' => 'Servicios Sofia',
            'rfc'          => 'SSO010101AA1',
            'correo_electronico' => 'sofia@demo.local',
            'telefono'     => '5550000002',
            'status'       => 1,
            'prefijo'      => 'SOF',
        ]);

        // 2) Clientes directos + ejecutivos
        $cliDirectoYolandaId = $this->ensureCliDirecto($db, [
            'cliente_id'   => $clienteYolandaId,
            'nombre'       => 'Cliente Directo Yolanda',
            'razon_social' => 'Cliente Directo Yolanda',
            'rfc'          => 'CDY010101AA1',
            'correo_electronico' => 'cd.yolanda@demo.local',
            'telefono'     => '5550000101',
            'status'       => 1,
        ]);

        $cliDirectoSofiaId = $this->ensureCliDirecto($db, [
            'cliente_id'   => $clienteSofiaId,
            'nombre'       => 'Cliente Directo Sofia',
            'razon_social' => 'Cliente Directo Sofia',
            'rfc'          => 'CDS010101AA1',
            'correo_electronico' => 'cd.sofia@demo.local',
            'telefono'     => '5550000102',
            'status'       => 1,
        ]);

        $ejecutivoYolandaId = $this->ensureCliDirectoEjecutivo($db, [
            'cli_directo_id'     => $cliDirectoYolandaId,
            'nombre'             => 'Ejecutivo Yolanda',
            'correo_electronico' => 'ej.yolanda@demo.local',
            'telefono'           => '5550000201',
            'status'             => 1,
        ]);

        $ejecutivoSofiaId = $this->ensureCliDirectoEjecutivo($db, [
            'cli_directo_id'     => $cliDirectoSofiaId,
            'nombre'             => 'Ejecutivo Sofia',
            'correo_electronico' => 'ej.sofia@demo.local',
            'telefono'           => '5550000202',
            'status'             => 1,
        ]);

        // 3) Usuario dummy
        $userId = $this->ensureUser($db, [
            'username'  => 'luisa.flores',
            'firstname' => 'Luisa',
            'midname'   => 'Flores',
            'lastname'  => 'Flores',
            'email'     => $email,
            'phone'     => '5550000301',
            'status'    => 1,
            'password'  => password_hash($passwordPlain, PASSWORD_DEFAULT),
        ]);

        // 4) Roles requeridos
        foreach ([2, 11, 12, 3] as $roleId) {
            $this->ensureUserRole($db, $userId, (int) $roleId);
        }

        // 5) Asignación a clientes (pivot cliente_user)
        $this->ensureClienteUser($db, $userId, $clienteYolandaId);
        $this->ensureClienteUser($db, $userId, $clienteSofiaId);

        // 6) Catálogos/IDs necesarios
        $traTiposIds = $this->getIds($db, 'tra_tipos', 30);
        $entidadId = $this->firstId($db, 'entidad') ?? $this->firstId($db, 'entidades') ?? 1;
        $entMunicipioId = $this->firstId($db, 'ent_municipio') ?? 266;

        $empresaGestoraId = $this->firstId($db, 'empresa_gestora');
        $gestorId = $this->firstId($db, 'ges_gestor');

        $reembolsoStatusId = $this->firstId($db, 'reembolso_status');
        $cobroStatusId = $this->firstId($db, 'cobro_status');

        // Tra status: seleccionar un id por step (1..5) si existen
        $traStatusByStep = [];
        for ($step = 1; $step <= 5; $step++) {
            $row = $db->table('tra_status')->select('id')->where('step', $step)->get(1)->getRowArray();
            if ($row && isset($row['id'])) {
                $traStatusByStep[$step] = (int) $row['id'];
            }
        }
        $defaultTraStatusId = $this->firstId($db, 'tra_status') ?? 1;

        if (empty($traTiposIds)) {
            $traTiposIds = [1];
        }

        // 7) Trámites dummy (repartidos 50/50 entre clientes)
        $createdTramites = 0;
        for ($i = 1; $i <= $count; $i++) {
            $useYolanda = ($i % 2) === 1;
            $cliDirectoId = $useYolanda ? $cliDirectoYolandaId : $cliDirectoSofiaId;
            $ejecutivoId = $useYolanda ? $ejecutivoYolandaId : $ejecutivoSofiaId;

            $step = (($i - 1) % 5) + 1;
            $traStatusId = $traStatusByStep[$step] ?? $defaultTraStatusId;

            $traTiposId = (int) $traTiposIds[($i - 1) % count($traTiposIds)];

            $folioPrefix = $useYolanda ? 'YOL' : 'SOF';
            $folio = sprintf('%s-DEMO-%s-%02d', $folioPrefix, date('ymd'), $i);

            $tramiteId = $this->ensureTramite($db, [
                'folio'                  => $folio,
                'contrato'               => 'CONTRATO-DEMO-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'unidad'                 => 'UNIDAD-DEMO-' . $i,
                'serie'                  => 'SERIE-DEMO-' . $i,
                'placas'                 => 'DEM' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'tra_tipos_id'           => $traTiposId,
                'entidad_id'             => $entidadId,
                'ent_municipio_id'       => $entMunicipioId,
                'cli_directo_id'         => $cliDirectoId,
                'cli_directo_ejecutivo_id' => $ejecutivoId,
                'empresa_gestora_id'     => $empresaGestoraId,
                'gestor_id'              => $gestorId,
                'derechos_tramite'       => 1500,
                'derechos_revol_cliente' => 'revolvente',
                'derechos_refer_banc'    => 'REF-DEMO-' . $i,
                'deposito_gestor'        => 500,
                'col_a_favor'            => 0,
                'col_a_favor_gestor'     => 0,
                'impuesto_gestoria'      => 250,
                'id_give_cliente'        => 'ID-CLIENTE-DEMO-' . $i,
                'numero_factura'         => 'FAC-DEMO-' . $i,
                'numero_refactura'       => 'REFAC-DEMO-' . $i,
                'costo_pago_cliente'      => 300,
                'comision_derechos'      => 100,
                'iva'                    => 0,
                'costo_total'            => 1900,
                'reembolso_status_id'    => $reembolsoStatusId,
                'cobro_status_id'        => $cobroStatusId,
                'tra_status_id'          => $traStatusId,
                'user_id'                => $userId,
                'observaciones'          => 'Trámite demo generado automáticamente',
            ]);

            if ($tramiteId) {
                $createdTramites++;

                // Asegurar al menos 1 servicio asociado (tra_tramite_asociado)
                $this->ensureTramiteAsociado($db, $tramiteId, $traTiposId);

                // Agregar 1-2 servicios adicionales (si hay catálogo)
                if (count($traTiposIds) > 3) {
                    $extra1 = (int) $traTiposIds[($i + 3) % count($traTiposIds)];
                    $extra2 = (int) $traTiposIds[($i + 7) % count($traTiposIds)];
                    $this->ensureTramiteAsociado($db, $tramiteId, $extra1);
                    $this->ensureTramiteAsociado($db, $tramiteId, $extra2);
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            CLI::error('No se pudieron crear los datos demo (rollback). Revisa constraints/llaves foráneas.');
            return;
        }

        CLI::write('✅ Demo listo', 'green');
        CLI::write('- Usuario: ' . $email . ' / ' . $passwordPlain);
        CLI::write('- Clientes: Servicios Yolanda, Servicios Sofia');
        CLI::write('- Trámites creados/reusados: ' . $createdTramites);
        CLI::write('- Tip: entra y prueba selector "Todos los clientes" vs cada cliente.');
    }

    private function ensureCliente($db, array $data): int
    {
        $row = $db->table('cliente')->select('id')->where('nombre', $data['nombre'])->get(1)->getRowArray();
        if ($row && isset($row['id'])) {
            return (int) $row['id'];
        }

        $insert = $data;
        $insert['created_at'] = $insert['created_at'] ?? date('Y-m-d H:i:s');
        $insert['updated_at'] = $insert['updated_at'] ?? date('Y-m-d H:i:s');

        $insert = $this->filterToTableColumns($db, 'cliente', $insert);

        $db->table('cliente')->insert($insert);
        return (int) $db->insertID();
    }

    private function ensureCliDirecto($db, array $data): int
    {
        $row = $db->table('cli_directo')
            ->select('id')
            ->where('cliente_id', $data['cliente_id'])
            ->where('razon_social', $data['razon_social'])
            ->get(1)
            ->getRowArray();

        if ($row && isset($row['id'])) {
            return (int) $row['id'];
        }

        $insert = $data;
        $insert['created_at'] = $insert['created_at'] ?? date('Y-m-d H:i:s');
        $insert['updated_at'] = $insert['updated_at'] ?? date('Y-m-d H:i:s');

        $insert = $this->filterToTableColumns($db, 'cli_directo', $insert);

        $db->table('cli_directo')->insert($insert);
        return (int) $db->insertID();
    }

    private function ensureCliDirectoEjecutivo($db, array $data): int
    {
        $row = $db->table('cli_directo_ejecutivo')
            ->select('id')
            ->where('cli_directo_id', $data['cli_directo_id'])
            ->where('nombre', $data['nombre'])
            ->get(1)
            ->getRowArray();

        if ($row && isset($row['id'])) {
            return (int) $row['id'];
        }

        $insert = $data;
        $insert['created_at'] = $insert['created_at'] ?? date('Y-m-d H:i:s');
        $insert['updated_at'] = $insert['updated_at'] ?? date('Y-m-d H:i:s');

        $insert = $this->filterToTableColumns($db, 'cli_directo_ejecutivo', $insert);

        $db->table('cli_directo_ejecutivo')->insert($insert);
        return (int) $db->insertID();
    }

    private function ensureUser($db, array $data): int
    {
        $row = $db->table('users')->select('id')->where('email', $data['email'])->get(1)->getRowArray();
        if ($row && isset($row['id'])) {
            return (int) $row['id'];
        }

        $insert = $data;
        $insert['created_at'] = $insert['created_at'] ?? date('Y-m-d H:i:s');
        $insert['updated_at'] = $insert['updated_at'] ?? date('Y-m-d H:i:s');

        $insert = $this->filterToTableColumns($db, 'users', $insert);

        $db->table('users')->insert($insert);
        return (int) $db->insertID();
    }

    private function filterToTableColumns($db, string $table, array $data): array
    {
        if (!isset($this->tableColumnsCache[$table])) {
            try {
                $columns = $db->getFieldNames($table);
                if (!is_array($columns) || $columns === []) {
                    $this->tableColumnsCache[$table] = [];
                } else {
                    $this->tableColumnsCache[$table] = array_fill_keys($columns, true);
                }
            } catch (\Throwable $e) {
                $this->tableColumnsCache[$table] = [];
            }
        }

        // Si no pudimos resolver columnas, no filtramos (mejor intentar y fallar con error real).
        if ($this->tableColumnsCache[$table] === []) {
            return $data;
        }

        return array_intersect_key($data, $this->tableColumnsCache[$table]);
    }

    private function ensureUserRole($db, int $userId, int $roleId): void
    {
        $exists = $db->table('us_user_roles')
            ->select('id')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->get(1)
            ->getRowArray();

        if ($exists) {
            return;
        }

        $db->table('us_user_roles')->insert([
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function ensureClienteUser($db, int $userId, int $clienteId): void
    {
        $exists = $db->table('cliente_user')
            ->select('user_id')
            ->where('user_id', $userId)
            ->where('cliente_id', $clienteId)
            ->get(1)
            ->getRowArray();

        if ($exists) {
            return;
        }

        $db->table('cliente_user')->insert([
            'user_id'    => $userId,
            'cliente_id' => $clienteId,
        ]);
    }

    private function firstId($db, string $table): ?int
    {
        try {
            $row = $db->table($table)->select('id')->get(1)->getRowArray();
            if ($row && isset($row['id'])) {
                return (int) $row['id'];
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /** @return int[] */
    private function getIds($db, string $table, int $limit = 50): array
    {
        try {
            $rows = $db->table($table)->select('id')->limit($limit)->get()->getResultArray();
            return array_values(array_map(static fn ($r) => (int) $r['id'], $rows));
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function ensureTramite($db, array $data): ?int
    {
        $row = $db->table('tramite')->select('id')->where('folio', $data['folio'])->get(1)->getRowArray();
        if ($row && isset($row['id'])) {
            return (int) $row['id'];
        }

        $insert = $data;
        $insert['created_at'] = $insert['created_at'] ?? date('Y-m-d H:i:s');
        $insert['updated_at'] = $insert['updated_at'] ?? date('Y-m-d H:i:s');
        $insert['started_at'] = $insert['started_at'] ?? date('Y-m-d H:i:s');

        $insert = $this->filterToTableColumns($db, 'tramite', $insert);

        $db->table('tramite')->insert($insert);
        return (int) $db->insertID();
    }

    private function ensureTramiteAsociado($db, int $tramiteId, int $traTiposId): void
    {
        $exists = $db->table('tra_tramite_asociado')
            ->select('id')
            ->where('tramite_id', $tramiteId)
            ->where('tra_tipos_id', $traTiposId)
            ->get(1)
            ->getRowArray();

        if ($exists) {
            return;
        }

        $insert = [
            'tramite_id' => $tramiteId,
            'tra_tipos_id' => $traTiposId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $insert = $this->filterToTableColumns($db, 'tra_tramite_asociado', $insert);

        $db->table('tra_tramite_asociado')->insert($insert);
    }
}
