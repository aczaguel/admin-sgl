<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class SglSmokeDemo extends BaseCommand
{
    protected $group       = 'SGL';
    protected $name        = 'sgl:smoke-demo';
    protected $description = 'Smoke checks rápidos para validar multi-tenancy y datos demo.';

    protected $usage = 'sgl:smoke-demo [--email=luisa.flores@demo.local]';

    protected $options = [
        '--email' => 'Email del usuario dummy (default: luisa.flores@demo.local).',
    ];

    public function run(array $params)
    {
        $email = (string) ($params['email'] ?? CLI::getOption('email') ?? 'luisa.flores@demo.local');
        $db = \Config\Database::connect();

        $user = $db->table('users')->select('id, firstname, midname, lastname, email')->where('email', $email)->get(1)->getRowArray();
        if (!$user) {
            CLI::error('No existe el usuario demo con email: ' . $email);
            CLI::write('Ejecuta primero: php spark sgl:demo-data');
            return;
        }

        $userId = (int) $user['id'];

        $clientes = $db->table('cliente_user cu')
            ->select('c.id, c.nombre')
            ->join('cliente c', 'c.id = cu.cliente_id', 'inner')
            ->where('cu.user_id', $userId)
            ->orderBy('c.id', 'asc')
            ->get()
            ->getResultArray();

        $clienteIds = array_map(static fn ($r) => (int) $r['id'], $clientes);

        CLI::write('✅ Usuario: ' . trim(($user['firstname'] ?? '') . ' ' . ($user['midname'] ?? '') . ' ' . ($user['lastname'] ?? '')) . ' (#' . $userId . ')');
        CLI::write('✅ Clientes asignados: ' . count($clienteIds));
        foreach ($clientes as $c) {
            CLI::write('  - #' . $c['id'] . ' ' . $c['nombre']);
        }

        // Conteos de trámites por cliente (via cli_directo)
        $tramitesTotal = (int) $db->table('tramite t')
            ->select('COUNT(*) as c', false)
            ->join('cli_directo cd', 'cd.id = t.cli_directo_id', 'inner')
            ->whereIn('cd.cliente_id', $clienteIds ?: [0])
            ->get(1)
            ->getRowArray()['c'];

        CLI::write('✅ Trámites visibles (por asignación cliente_user): ' . $tramitesTotal);

        // Validar que existan los 2 clientes demo
        $demoNames = ['Servicios Yolanda', 'Servicios Sofia'];
        foreach ($demoNames as $name) {
            $exists = $db->table('cliente')->select('id')->where('nombre', $name)->get(1)->getRowArray();
            CLI::write(($exists ? '✅' : '⚠️') . ' Cliente demo: ' . $name);
        }

        CLI::write('');
        CLI::write('Siguiente paso (manual rápido):');
        CLI::write('- Logueate como ' . $email . ' y prueba selector de cliente (Todos/Yolanda/Sofia).');
        CLI::write('- Abre 2 trámites (uno YOL y uno SOF) y valida que el wizard/update carga y respeta acceso.');
    }
}
