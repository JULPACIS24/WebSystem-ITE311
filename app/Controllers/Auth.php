<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use App\Models\CourseModel;
use App\Models\AcademicSemesterModel;
use App\Models\AcademicSettingModel;
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
                $user    = $builder
                    ->where('email', $this->request->getPost('email'))
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

        // 🧩 Admin Dashboard Logic (load users list and admin modules for unified dashboard)
        if ($role === 'admin') {
            // Default empty data so the view can still render even if DB calls fail
            $data['users']     = [];
            $data['courses']   = [];
            $data['teachers']  = [];
            $data['semesters'] = [];
            $data['setting']   = null;

            try {
                $userModel     = new \App\Models\UserModel();
                $courseModel   = new CourseModel();
                $enrollModel   = new EnrollmentModel();
                $semesterModel = new AcademicSemesterModel();
                $settingModel  = new AcademicSettingModel();

                $data['users'] = $userModel->findAll();

                $courses = $courseModel->findAll();
                foreach ($courses as &$course) {
                    $course['enrollment_count'] = $enrollModel->getEnrollmentCountByCourse($course['id']);
                }
                unset($course);

                $teachers = $userModel
                    ->where('role', 'teacher')
                    ->findAll();

                // Some installations may not yet have the academic_semesters/settings tables.
                $semesters = [];
                $setting   = null;
                try {
                    $semesters = $semesterModel
                        ->orderBy('school_year', 'DESC')
                        ->orderBy('id', 'DESC')
                        ->findAll();
                    $setting   = $settingModel->first();
                } catch (\Throwable $e) {
                    log_message('error', 'Dashboard admin academic data failed: ' . $e->getMessage());
                }

                $data['courses']   = $courses;
                $data['teachers']  = $teachers;
                $data['semesters'] = $semesters;
                $data['setting']   = $setting;
            } catch (\Throwable $e) {
                log_message('error', 'Dashboard admin data failed: ' . $e->getMessage());
                // keep defaults so view still renders
            }
        }

        // 🧩 Teacher Dashboard Logic
        if ($role === 'teacher') {
            $courseModel   = new CourseModel();
            $userModel     = new \App\Models\UserModel();
            $enrollModel   = new EnrollmentModel();
            $settingModel  = new AcademicSettingModel();

            // Courses handled by this teacher
            $teacherCourses = $courseModel->where('teacher_id', $user_id)->findAll();
            $data['teacherCourses'] = $teacherCourses;

            // All students
            $data['students'] = $userModel
                ->where('role', 'student')
                ->findAll();

            // Pending enrollments awaiting this teacher's approval
            $data['pendingEnrollments'] = $enrollModel->getPendingForTeacher($user_id);

            $data['activeEnrollments'] = $enrollModel->getActiveForTeacher($user_id);

            // Current academic school year (for My Courses School Year column fallback)
            $setting                = $settingModel->first();
            $data['currentSchoolYear'] = $setting['current_school_year'] ?? null;
        }

        // 🧩 Student Dashboard Logic
        if ($role === 'student') {
            $enrollModel = new EnrollmentModel();
            $courseModel = new CourseModel();

            // Get enrolled courses
            $enrolledCourses = $enrollModel->getUserEnrollments($user_id);
            $enrolledIds     = array_column($enrolledCourses, 'course_id');

            // Get available courses
            if (count($enrolledIds) > 0) {
                $availableCourses = $courseModel->whereNotIn('id', $enrolledIds)->findAll();
            } else {
                $availableCourses = $courseModel->findAll();
            }

            $data['enrolledCourses']  = $enrolledCourses;
            $data['availableCourses'] = $availableCourses;
        }

        // 🧩 Admin, Teacher, or Student - unified dashboard view
        return view('auth/dashboard', $data);
    }
}
