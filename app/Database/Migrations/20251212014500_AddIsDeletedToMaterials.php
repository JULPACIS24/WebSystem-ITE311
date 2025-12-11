<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsDeletedToMaterials extends Migration
{
    public function up()
    {
        $fields = [
            'is_deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
        ];

        $this->forge->addColumn('materials', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('materials', 'is_deleted');
    }
}
