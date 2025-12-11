<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use App\Models\CourseModel;
use App\Models\NotificationModel;

class Course extends BaseController
{
    public function index()
    {
        $courseModel = new CourseModel();
        $courses     = $courseModel->findAll();

        return view('courses/index', [
            'courses' => $courses,
        ]);
    }

    public function enroll()
    {
        $session = session();

        // Must be logged in
        if (!$session->get('isLoggedIn')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'You must be logged in to enroll.'
                ]);
            }
            session()->setFlashdata('error', 'You must be logged in to enroll.');
            return redirect()->to('/login');
        }

        $user_id   = $session->get('id');
        $course_id = $this->request->getPost('course_id');
        // Current semester configuration; these can be made dynamic later
        $semester   = $this->request->getPost('semester') ?? '1st Sem';
        $schoolYear = $this->request->getPost('school_year') ?? '2025-2026';

        if (!$course_id || !$semester || !$schoolYear) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Invalid course, semester, or school year.'
                ]);
            }
            session()->setFlashdata('error', 'Invalid course, semester, or school year.');
            return redirect()->to('/dashboard');
        }

        $enrollModel = new EnrollmentModel();

        $courseModel = new CourseModel();
        $course      = $courseModel->find($course_id);
        if (!$course || empty($course['is_open'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'This course is not open for enrollment.',
                ]);
            }
            session()->setFlashdata('error', 'This course is not open for enrollment.');
            return redirect()->to('/dashboard');
        }

        // Prevent duplicate enrollment in the same course, semester, and school year
        if ($enrollModel->isAlreadyEnrolled($user_id, $course_id, $semester, $schoolYear)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Already enrolled in this course for the selected semester and school year.'
                ]);
            }
            session()->setFlashdata('error', 'Already enrolled in this course for the selected semester and school year.');
            return redirect()->to('/dashboard');
        }

        // Derive semester start and end dates from semester + school year
        $startDate = null;
        $endDate   = null;
        try {
            if (strpos($schoolYear, '-') !== false) {
                [$startYearStr, $endYearStr] = explode('-', $schoolYear, 2);
                $startYear = (int) trim($startYearStr);
                $endYear   = (int) trim($endYearStr);

                if ($semester === '1st Sem') {
                    $startDate = sprintf('%04d-08-01 00:00:00', $startYear);
                    $endDate   = sprintf('%04d-12-31 23:59:59', $startYear);
                } elseif ($semester === '2nd Sem') {
                    $startDate = sprintf('%04d-01-01 00:00:00', $endYear);
                    $endDate   = sprintf('%04d-05-31 23:59:59', $endYear);
                } elseif ($semester === 'Summer') {
                    $startDate = sprintf('%04d-06-01 00:00:00', $endYear);
                    $endDate   = sprintf('%04d-07-31 23:59:59', $endYear);
                }
            }
        } catch (\Throwable $e) {
            $startDate = null;
            $endDate   = null;
        }

        $enrollData = [
            'user_id'     => $user_id,
            'course_id'   => $course_id,
            'enrolled_at' => date('Y-m-d H:i:s'),
            'semester'    => $semester,
            'school_year' => $schoolYear,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            // Student self-enrollments start as pending and need teacher approval
            'status'      => 'pending',
        ];

        if (!$enrollModel->enrollUser($enrollData)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Failed to enroll in this course.'
                ]);
            }
            session()->setFlashdata('error', 'Failed to enroll in this course.');
            return redirect()->to('/dashboard');
        }

        // Create notifications about the enrollment request
        try {
            $notifModel  = new NotificationModel();
            $courseTitle = $course['title'] ?? 'a course';

            // For the student
            $studentMsg = 'Your enrollment request for ' . $courseTitle . ' has been submitted.';
            $notifModel->insert([
                'user_id'    => $user_id,
                'message'    => $studentMsg,
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // For the teacher handling this course (if any)
            $teacherId = $course['teacher_id'] ?? null;
            if (!empty($teacherId)) {
                $studentName = $session->get('name') ?? 'A student';
                $teacherMsg  = $studentName . ' requested enrollment in ' . $courseTitle . '.';
                $notifModel->insert([
                    'user_id'    => (int) $teacherId,
                    'message'    => $teacherMsg,
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            // ignore notification failures
        }

        if ($this->request->isAJAX()) {
            // Fetch the course details to update UI
            $courseModel = new CourseModel();
            $course      = $courseModel->find($course_id);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Enrolled successfully!',
                'course'  => $course,
            ]);
        }

        session()->setFlashdata('success', 'Enrolled successfully!');
        return redirect()->to('/dashboard');
    }

    public function search()
    {
        // Accept search_term from GET or POST
        $searchTerm = trim((string) $this->request->getVar('search_term'));

        $courseModel = new CourseModel();

        // Apply LIKE filters only when there is a term
        if ($searchTerm !== '') {
            $courseModel
                ->groupStart()
                    ->like('title', $searchTerm)
                    ->orLike('course_code', $searchTerm)
                ->groupEnd();
        }

        $courses = $courseModel->findAll();

        // Return JSON for AJAX requests
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($courses);
        }

        // Render the main courses index view for regular (non-AJAX) requests
        return view('courses/index', [
            'courses'    => $courses,
            'searchTerm' => $searchTerm,
        ]);
    }
}
