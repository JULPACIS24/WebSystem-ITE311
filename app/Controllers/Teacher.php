<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use App\Models\NotificationModel;

class Teacher extends BaseController
{
    public function uploadLessons()
    {
        $session = session();

        if (!$session->get('isLoggedIn') || $session->get('role') !== 'teacher') {
            return redirect()->to('/login');
        }

        // Use unified dashboard for teacher view
        return redirect()->to('/dashboard');
    }

    public function enrollStudent()
    {
        $session = session();

        if (!$session->get('isLoggedIn') || $session->get('role') !== 'teacher') {
            return redirect()->to('/login');
        }

        $studentId = $this->request->getPost('student_id');
        $courseId  = $this->request->getPost('course_id');
        // For now we treat semester and school year as current configured values.
        // These can later be made dynamic (e.g., from a form or config table).
        $semester   = $this->request->getPost('semester') ?? '1st Sem';
        $schoolYear = $this->request->getPost('school_year') ?? '2025-2026';

        if (!$studentId || !$courseId || !$semester || !$schoolYear) {
            session()->setFlashdata('error', 'Please select a course, a student, and provide semester and school year.');
            return redirect()->to('/dashboard');
        }

        $enrollModel = new EnrollmentModel();

        $db      = \Config\Database::connect();
        $builder = $db->table('courses');
        $course  = $builder->where('id', $courseId)->get()->getRowArray();

        if (!$course || empty($course['is_open'])) {
            session()->setFlashdata('error', 'This course is not open for enrollment.');
            return redirect()->to('/dashboard');
        }

        // Prevent duplicate enrollment in the same course, semester, and school year
        if ($enrollModel->isAlreadyEnrolled($studentId, $courseId, $semester, $schoolYear)) {
            session()->setFlashdata('error', 'This student is already enrolled in this course for the selected semester and school year.');
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
            'user_id'     => $studentId,
            'course_id'   => $courseId,
            'enrolled_at' => date('Y-m-d H:i:s'),
            'semester'    => $semester,
            'school_year' => $schoolYear,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'status'      => 'active',
        ];

        if (!$enrollModel->enrollUser($enrollData)) {
            session()->setFlashdata('error', 'Failed to enroll the student in this course.');
        } else {
            session()->setFlashdata('success', 'Student enrolled successfully!');

            // Notify the student that the teacher enrolled them
            try {
                $notifModel  = new NotificationModel();
                $courseTitle = $course['title'] ?? 'a course';
                $teacherName = $session->get('name') ?? 'Your teacher';
                $message     = $teacherName . ' enrolled you in ' . $courseTitle . '.';
                $notifModel->insert([
                    'user_id'    => (int) $studentId,
                    'message'    => $message,
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                // ignore notification failures
            }
        }

        return redirect()->to('/dashboard');
    }

    public function approveEnrollment($id)
    {
        $session = session();

        if (!$session->get('isLoggedIn') || $session->get('role') !== 'teacher') {
            return redirect()->to('/login');
        }

        $teacherId     = $session->get('id');
        $enrollModel   = new EnrollmentModel();
        $enrollment    = $enrollModel->find($id);

        if (!$enrollment) {
            session()->setFlashdata('error', 'Enrollment not found.');
            return redirect()->to('/dashboard');
        }

        // Make sure this enrollment belongs to one of this teacher's courses
        $db      = \Config\Database::connect();
        $builder = $db->table('courses');
        $course  = $builder->where('id', $enrollment['course_id'])
                            ->where('teacher_id', $teacherId)
                            ->get()
                            ->getRowArray();

        if (!$course) {
            session()->setFlashdata('error', 'You are not allowed to approve this enrollment.');
            return redirect()->to('/dashboard');
        }

        $status = $enrollment['status'] ?? null;
        // Treat null/empty status as pending (legacy data). Only block when explicitly active or rejected.
        if ($status === 'active' || $status === 'rejected') {
            session()->setFlashdata('error', 'This enrollment is not pending.');
            return redirect()->to('/dashboard');
        }

        if (!$enrollModel->approveEnrollment($id)) {
            session()->setFlashdata('error', 'Failed to approve enrollment.');
        } else {
            session()->setFlashdata('success', 'Enrollment approved successfully.');

            // Notify the student that their enrollment was approved
            try {
                $notifModel  = new NotificationModel();
                $courseTitle = $course['title'] ?? 'a course';
                $message     = 'Your enrollment in ' . $courseTitle . ' has been approved.';
                $notifModel->insert([
                    'user_id'    => (int) $enrollment['user_id'],
                    'message'    => $message,
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                // ignore notification failures
            }
        }

        return redirect()->to('/dashboard');
    }

    public function rejectEnrollment($id)
    {
        $session = session();

        if (!$session->get('isLoggedIn') || $session->get('role') !== 'teacher') {
            return redirect()->to('/login');
        }

        $teacherId   = $session->get('id');
        $enrollModel = new EnrollmentModel();
        $enrollment  = $enrollModel->find($id);

        if (!$enrollment) {
            session()->setFlashdata('error', 'Enrollment not found.');
            return redirect()->to('/dashboard');
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('courses');
        $course  = $builder->where('id', $enrollment['course_id'])
                            ->where('teacher_id', $teacherId)
                            ->get()
                            ->getRowArray();

        if (!$course) {
            session()->setFlashdata('error', 'You are not allowed to reject this enrollment.');
            return redirect()->to('/dashboard');
        }

        if ($enrollment['status'] !== 'pending') {
            session()->setFlashdata('error', 'This enrollment is not pending.');
            return redirect()->to('/dashboard');
        }

        if (!$enrollModel->update($id, ['status' => 'rejected'])) {
            session()->setFlashdata('error', 'Failed to reject enrollment.');
        } else {
            session()->setFlashdata('success', 'Enrollment rejected successfully.');

            // Notify the student that their enrollment was rejected
            try {
                $notifModel  = new NotificationModel();
                $courseTitle = $course['title'] ?? 'a course';
                $message     = 'Your enrollment in ' . $courseTitle . ' has been rejected.';
                $notifModel->insert([
                    'user_id'    => (int) $enrollment['user_id'],
                    'message'    => $message,
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                // ignore notification failures
            }
        }

        return redirect()->to('/dashboard');
    }
}
