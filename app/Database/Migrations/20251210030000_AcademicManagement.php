<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AcademicManagement extends Migration
{
    public function up()
    {
        // Table for academic semesters
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'semester_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'term' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'null'       => true,
            ],
            'school_year' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'enrollment_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'Open',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('academic_semesters', true);

        // Simple settings table for current academic year and default year level
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'current_school_year' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'default_year_level' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('academic_settings', true);

        // Add year_level column to courses table for assigning year level to course
        $this->forge->addColumn('courses', [
            'year_level' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'null'       => true,
                'after'      => 'units',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('academic_semesters', true);
        $this->forge->dropTable('academic_settings', true);
        $this->forge->dropColumn('courses', ['year_level']);
    }
}
