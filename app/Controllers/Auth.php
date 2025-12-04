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
                // Only allow non-deleted users to log in
                $user    = $builder
                    ->where('email', $this->request->getPost('email'))
                    ->where('is_deleted', 0)
                    ->get()
                    ->getRowArray();

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

        $role    = $session->get('role');
        $name    = $session->get('name');
        $user_id = $session->get('id');

        // Base data for all roles
        $data = [
            'name' => $name,
            'role' => $role,
        ];

        // 🧩 Admin Dashboard Logic (load users list for unified dashboard)
        if ($role === 'admin') {
            $userModel = new \App\Models\UserModel();
            $data['users'] = $userModel->findAll();
        }

        // 🧩 Teacher Dashboard Logic
        if ($role === 'teacher') {
            $courseModel = new CourseModel();
            $userModel   = new \App\Models\UserModel();

            // Courses handled by this teacher
            $data['teacherCourses'] = $courseModel->where('teacher_id', $user_id)->findAll();

            // All active students (not deleted)
            $data['students'] = $userModel
                ->where('role', 'student')
                ->groupStart()
                    ->where('is_deleted', 0)
                    ->orWhere('is_deleted', null)
                ->groupEnd()
                ->findAll();
        }

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

            $data['enrolledCourses']  = $enrolledCourses;
            $data['availableCourses'] = $availableCourses;
        }

        // 🧩 Admin, Teacher, or Student
        return view('auth/dashboard', $data);
    }
}
