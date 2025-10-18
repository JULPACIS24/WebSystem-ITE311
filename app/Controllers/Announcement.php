<?php

namespace App\Controllers;

use App\Models\AnnouncementModel;
use CodeIgniter\Controller;

class Announcement extends BaseController
{
    public function index()
    {
        $model = new AnnouncementModel();

        // Fetch all announcements (we’ll add the table in Task 2)
        $data['announcements'] = $model->orderBy('created_at', 'DESC')->findAll();

        return view('announcements', $data);
    }
}
