<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use App\Models\CourseModel;
use CodeIgniter\Controller;

class Auth extends BaseController
{
    // 🔹 REGISTER
    public function register()
    {
        helper(['form', 'url']);
        $data = [];

        if ($this->request->getMethod() == 'POST') {
            $rules = [
                'name'     => 'required|min_length[2]|max_length[100]',
                'email'    => 'required|valid_email|max_length[100]|is_unique[users.email]',
                'password' => 'required|min_length[3]',
                'password_confirm' => 'required|matches[password]',
            ];

            if (!$this->validate($rules)) {
                $data['validation'] = $this->validator;
            } else {
                $db      = \Config\Database::connect();
                $builder = $db->table('users');

                $newData = [
                    'name'        => trim($this->request->getPost('name')),
                    'email'       => trim($this->request->getPost('email')),
                    'password'    => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role'        => 'student', // default role
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];

                try {
                    $builder->insert($newData);
                    session()->setFlashdata('success', 'Registration successful!');
                    return redirect()->to('/login');
                } catch (\Exception $e) {
                    session()->setFlashdata('error', 'Database error: ' . $e->getMessage());
                }
            }
        }

        return view('auth/register', $data);
    }

    // 🔹 LOGIN
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
                            'id'         => $user['id'],
                            'name'       => $user['name'],
                            'email'      => $user['email'],
                            'role'       => $user['role'],
                            'isLoggedIn' => true,
                        ];
                        session()->set($sessionData);
                        return redirect()->to('/dashboard');
                    } else {
                        session()->setFlashdata('error', 'Wrong password.');
                    }
                } else {
                    session()->setFlashdata('error', 'Email not found.');
                }

                return redirect()->to('/login');
            }
        }

        return view('auth/login', $data);
    }

    // 🔹 LOGOUT
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    // 🔹 DASHBOARD (redirects per role)
    public function dashboard()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $role = $session->get('role');
        $name = $session->get('name');
        $user_id = $session->get('id');

        // 🧩 Student Dashboard Logic
        if ($role === 'student') {
            $enrollModel = new EnrollmentModel();
            $courseModel = new CourseModel();

            // Get enrolled courses
            $enrolledCourses = $enrollModel->getUserEnrollments($user_id);
            $enrolledIds = array_column($enrolledCourses, 'course_id');

            // Get available courses
            if (count($enrolledIds) > 0) {
                $availableCourses = $courseModel->whereNotIn('id', $enrolledIds)->findAll();
            } else {
                $availableCourses = $courseModel->findAll();
            }

            return view('student/dashboard', [
                'name' => $name,
                'role' => $role,
                'enrolledCourses' => $enrolledCourses,
                'availableCourses' => $availableCourses
            ]);
        }

        // 🧩 Admin or Teacher
        return view('auth/dashboard', [
            'name' => $name,
            'role' => $role
        ]);
    }
}
