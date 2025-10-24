<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use App\Models\CourseModel;
use App\Models\MaterialModel;
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

    // 🔹 DASHBOARD (Redirect by Role)
    public function dashboard()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $role = $session->get('role');
        $name = $session->get('name');
        $user_id = $session->get('id');

        // 🧩 ADMIN DASHBOARD
        if ($role === 'admin') {
            $courseModel = new CourseModel();
            $courses = $courseModel->findAll();

            return view('admin/dashboard', [
                'name' => $name,
                'role' => $role,
                'courses' => $courses
            ]);
        }

        // 🧩 TEACHER DASHBOARD
        if ($role === 'teacher') {
            $courseModel = new CourseModel();
            $courses = $courseModel->findAll(); // You can filter teacher-owned courses later

            return view('teacher/dashboard', [
                'name' => $name,
                'role' => $role,
                'courses' => $courses
            ]);
        }

        // 🧩 STUDENT DASHBOARD
        if ($role === 'student') {
            $enrollModel = new EnrollmentModel();
            $courseModel = new CourseModel();
            $materialModel = new MaterialModel();

            // Get enrolled courses
            $enrolledCourses = $enrollModel->getUserEnrollments($user_id);
            $enrolledIds = array_column($enrolledCourses, 'course_id');

            // Get available courses
            $availableCourses = count($enrolledIds) > 0
                ? $courseModel->whereNotIn('id', $enrolledIds)->findAll()
                : $courseModel->findAll();

            // Get materials per course
            $materials = [];
            foreach ($enrolledIds as $courseId) {
                $materials[$courseId] = $materialModel->getMaterialsByCourse($courseId);
            }

            return view('student/dashboard', [
                'name' => $name,
                'role' => $role,
                'enrolledCourses' => $enrolledCourses,
                'availableCourses' => $availableCourses,
                'materials' => $materials
            ]);
        }

        // Default fallback
        return redirect()->to('/login');
    }
}
