<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;

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

        $teacherId = $session->get('id');
        $studentId = $this->request->getPost('student_id');
        $courseId  = $this->request->getPost('course_id');

        if (!$studentId || !$courseId) {
            session()->setFlashdata('error', 'Please select both a course and a student.');
            return redirect()->to('/dashboard');
        }

        $enrollModel = new EnrollmentModel();

        // Prevent duplicate enrollment
        if ($enrollModel->isAlreadyEnrolled($studentId, $courseId)) {
            session()->setFlashdata('error', 'This student is already enrolled in the selected course.');
            return redirect()->to('/dashboard');
        }

        $enrollData = [
            'user_id'         => $studentId,
            'course_id'       => $courseId,
            'enrollment_date' => date('Y-m-d H:i:s'),
        ];

        if (!$enrollModel->enrollUser($enrollData)) {
            session()->setFlashdata('error', 'Failed to enroll the student in this course.');
        } else {
            session()->setFlashdata('success', 'Student enrolled successfully!');
        }

        return redirect()->to('/dashboard');
    }
}
