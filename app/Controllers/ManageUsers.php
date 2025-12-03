<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class ManageUsers extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();
        return view('admin/manage_users', ['users' => $users]);
    }

    public function addUser()
    {
        helper(['form', 'url']);
        $data = [];
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'     => 'required|min_length[2]|max_length[100]',
                'email'    => 'required|valid_email|max_length[100]|is_unique[users.email]',
                'password' => 'required|min_length[3]'
            ];
            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $userModel = new UserModel();
                $newUser = [
                    'name' => $this->request->getPost('name'),
                    'email' => $this->request->getPost('email'),
                    'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role' => $this->request->getPost('role') ?? 'student',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $userModel->insert($newUser);
                session()->setFlashdata('success', 'User added successfully!');
                return redirect()->to('/manage-users');
            }
        }
        return view('admin/add_user', $data);
    }
}
