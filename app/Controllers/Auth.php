<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function register()
    {
        helper(['form']);

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'username'         => 'required|min_length[3]|max_length[20]',
                'email'            => 'required|valid_email|is_unique[users.email]',
                'password'         => 'required|min_length[6]',
                'password_confirm' => 'matches[password]'
            ];

            if ($this->validate($rules)) {
                $model = new UserModel();

                $data = [
                    'name'     => $this->request->getPost('username'),
                    'email'    => $this->request->getPost('email'),
                    'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role'     => 'user',
                ];

                $model->save($data);

                return redirect()->to('/login')->with('success', 'Registration successful. You can now log in.');
            } else {
                return view('auth/register', [
                    'validation' => $this->validator
                ]);
            }
        }

        return view('auth/register');
    }

    public function login()
    {
        helper(['form']);

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]',
            ];

            if ($this->validate($rules)) {
                $model = new UserModel();
                $user = $model->where('email', $this->request->getPost('email'))->first();

                if ($user && password_verify($this->request->getPost('password'), $user['password'])) {
                    $this->setUserSession($user);
                    return redirect()->to('/dashboard');
                } else {
                    return redirect()->back()->with('error', 'Invalid login credentials');
                }
            }
        }

        return view('auth/login');
    }

    private function setUserSession($user)
    {
        $data = [
            'id'       => $user['id'],
            'username' => $user['username'], // gumamit ng "name" kasi yun nasa db
            'email'    => $user['email'],
            'role'     => $user['role'],
            'isLoggedIn' => true,
        ];
        session()->set($data);
        return true;
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    public function dashboard()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        return view('auth/dashboard');
    }
}
