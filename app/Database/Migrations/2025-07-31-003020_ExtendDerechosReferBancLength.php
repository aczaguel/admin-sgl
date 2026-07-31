<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Extiende la columna derechos_refer_banc de la tabla tramite
 * para permitir referencias bancarias de hasta 100 caracteres.
 *
 * Problema: la columna original (VARCHAR corto) truncaba la referencia
 * bancaria completa que el usuario necesita capturar.
 */
class ExtendDerechosReferBancLength extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('tramite', [
            'derechos_refer_banc' => [
                'name'       => 'derechos_refer_banc',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('tramite', [
            'derechos_refer_banc' => [
                'name'       => 'derechos_refer_banc',
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
        ]);
    }
}
