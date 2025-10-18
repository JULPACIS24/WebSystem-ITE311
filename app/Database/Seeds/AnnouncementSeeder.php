<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title'      => 'Welcome to the Portal',
                'content'    => 'This is your first announcement.',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'title'      => 'System Maintenance',
                'content'    => 'The system will be under maintenance tonight at 10 PM.',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('announcements')->insertBatch($data);
    }
}
