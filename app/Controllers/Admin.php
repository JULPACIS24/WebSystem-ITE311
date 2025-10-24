<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Admin extends BaseController
{
    public function dashboard()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $data = [
            'name' => $session->get('name'),
            'role' => $session->get('role'),
        ];

        return view('admin/dashboard', $data);
    }
}
