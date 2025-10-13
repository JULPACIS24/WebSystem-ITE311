<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use App\Models\CourseModel;
use CodeIgniter\Controller;

class Course extends BaseController
{
    public function enroll()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $user_id = $session->get('user_id');
        $course_id = $this->request->getPost('course_id');

        $enrollModel = new EnrollmentModel();
        $courseModel = new CourseModel();

        // Check if course exists
        if (!$courseModel->find($course_id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid course'])->setStatusCode(400);
        }

        // Check if already enrolled
        if ($enrollModel->isAlreadyEnrolled($user_id, $course_id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Already enrolled']);
        }

        $data = [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrollment_date' => date('Y-m-d H:i:s'),
        ];

        if ($enrollModel->enrollUser($data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Enrolled successfully!']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Enrollment failed']);
    }
}
