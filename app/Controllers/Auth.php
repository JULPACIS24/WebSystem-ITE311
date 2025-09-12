<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function register()
    {
        helper(['form', 'url']);
        $data = [];

        if ($this->request->getMethod() == 'POST') {
            $rules = [
                'name'     => 'required|min_length[2]|max_length[100]',
                'email'    => 'required|valid_email|max_length[100]|is_unique[users.email]',
                'password' => 'required|min_length[3]',
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $db      = \Config\Database::connect();
                $builder = $db->table('users');

                $newData = [
                    'name'     => trim($this->request->getPost('name')),
                    'email'    => trim($this->request->getPost('email')),
                    'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role'     => 'student',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                try {
                    $result = $builder->insert($newData);

                    if ($result) {
                        session()->setFlashdata('success', 'Registration successful!');
                        return redirect()->to('/login');
                    } else {
                        session()->setFlashdata('error', 'Registration failed.');
                    }
                } catch (\Exception $e) {
                    session()->setFlashdata('error', 'Database error: ' . $e->getMessage());
                }
            }
        }

        return view('auth/register', $data);
    }

    public function login()
    {
        helper(['form', 'url']);
        $data = [];

        if ($this->request->getMethod() == 'POST') {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[3]',
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $db      = \Config\Database::connect();
                $builder = $db->table('users');
                $user    = $builder->where('email', $this->request->getPost('email'))->get()->getRowArray();

                if ($user) {
                    if (password_verify($this->request->getPost('password'), $user['password'])) {
                        $sessionData = [
                            'id'        => $user['id'],
                            'name'      => $user['name'],
                            'email'     => $user['email'],
                            'role'      => $user['role'],
                            'isLoggedIn'=> true,
                        ];
                        session()->set($sessionData);

                        return redirect()->to('/dashboard');
                    } else {
                        session()->setFlashdata('error', 'Wrong password.');
                        return redirect()->to('/login');
                    }
                } else {
                    session()->setFlashdata('error', 'Email not found.');
                    return redirect()->to('/login');
                }
            }
        }

        return view('auth/login', $data);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }

    public function dashboard()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'name' => session()->get('name'),
            'role' => session()->get('role'),
        ];

        return view('dashboard', $data);
    }
}
