<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username' => 'admin',
                'email'    => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role'     => 'admin',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'student1',
                'email'    => 'student1@example.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role'     => 'student',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'instructor',
                'email'    => 'instructor@example.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role'     => 'instructor',
                'created_at' => date('Y-m-d H:i:s')
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
