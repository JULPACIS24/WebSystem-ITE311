<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function register()
    {
        $session = session();
        helper(['form']);

        if ($this->request->getMethod() == 'post') {
            $rules = [
                'name' => 'required|min_length[3]|max_length[100]',
                'email' => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]|max_length[255]',
                'password_confirm' => 'matches[password]'
            ];

            if ($this->validate($rules)) {
                $userModel = new UserModel();
                $data = [
                    'name' => $this->request->getVar('name'),
                    'email' => $this->request->getVar('email'),
                    'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                    'role' => 'user',
                ];
                $userModel->save($data);
                $session->setFlashdata('success', 'Registration successful!');
                return redirect()->to('/login');
            } else {
                $data['validation'] = $this->validator;
            }
        }

        return view('auth/register', isset($data) ? $data : []);
    }

    public function login()
    {
        $session = session();
        helper(['form']);

        if ($this->request->getMethod() == 'post') {
            $rules = [
                'email' => 'required|valid_email',
                'password' => 'required|min_length[6]|max_length[255]'
            ];

            if ($this->validate($rules)) {
                $userModel = new UserModel();
                $user = $userModel->where('email', $this->request->getVar('email'))->first();

                if ($user && password_verify($this->request->getVar('password'), $user['password'])) {
                    $session->set([
                        'user_id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'isLoggedIn' => true
                    ]);
                    return redirect()->to('/dashboard');
                } else {
                    $session->setFlashdata('error', 'Invalid credentials');
                    return redirect()->to('/login');
                }
            } else {
                $data['validation'] = $this->validator;
            }
        }

        return view('auth/login', isset($data) ? $data : []);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    public function dashboard()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        return view('auth/dashboard');
    }
}
