<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Teacher extends BaseController
{
    public function dashboard()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'teacher') {
            return redirect()->to('/login');
        }

        $data = [
            'name' => $session->get('name'),
            'role' => $session->get('role'),
        ];

        return view('teacher/dashboard', $data);
    }
}
