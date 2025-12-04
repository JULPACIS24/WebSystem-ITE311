<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;

class Course extends BaseController
{
    public function index()
    {
        // Redirect to unified dashboard where student courses are shown
        return redirect()->to('/dashboard');
    }

    public function enroll()
    {
        $session = session();

        // Must be logged in
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'You must be logged in to enroll.'
            ]);
        }

        $user_id   = $session->get('id');
        $course_id = $this->request->getPost('course_id');

        if (!$course_id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invalid course.'
            ]);
        }

        $enrollModel = new EnrollmentModel();

        // Prevent duplicate enrollment
        if ($enrollModel->isAlreadyEnrolled($user_id, $course_id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Already enrolled in this course.'
            ]);
        }

        $enrollData = [
            'user_id'         => $user_id,
            'course_id'       => $course_id,
            'enrollment_date' => date('Y-m-d H:i:s'),
        ];

        if (!$enrollModel->enrollUser($enrollData)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to enroll in this course.'
            ]);
        }

        // Fetch the course details to update UI
        $courseModel = new CourseModel();
        $course      = $courseModel->find($course_id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Enrolled successfully!',
            'course'  => $course,
        ]);
    }
}
