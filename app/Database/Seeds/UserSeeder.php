<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'     => 'Admin User',
                'email'    => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role'     => 'admin'
            ],
            [
                'name'     => 'Instructor One',
                'email'    => 'instructor@example.com',
                'password' => password_hash('teach123', PASSWORD_DEFAULT),
                'role'     => 'instructor'
            ],
            [
                'name'     => 'Student One',
                'email'    => 'student@example.com',
                'password' => password_hash('learn123', PASSWORD_DEFAULT),
                'role'     => 'student'
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
