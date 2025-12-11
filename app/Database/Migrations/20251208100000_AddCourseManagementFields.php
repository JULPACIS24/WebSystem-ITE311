<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCourseManagementFields extends Migration
{
    public function up()
    {
        $fields = [
            'default_semester' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'default_school_year' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'default_start_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'default_end_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_open' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'units' => [
                'type'       => 'INT',
                'constraint' => 2,
                'null'       => true,
            ],
            'course_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
        ];

        $this->forge->addColumn('courses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['default_semester', 'default_school_year', 'default_start_date', 'default_end_date', 'is_open', 'units', 'course_code']);
    }
}
