<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;

class Course extends BaseController
{
    public function index()
    {
        $session = session();
        $user_id = $session->get('id');
        $role = $session->get('role');

        $enrollModel = new EnrollmentModel();
        $courseModel = new CourseModel();

        // Get enrolled courses for this user
        $enrolledCourses = $enrollModel->getUserEnrollments($user_id);

        return view('student/mycourses', [
            'role' => $role,
            'enrolledCourses' => $enrolledCourses
        ]);
    }

    public function enroll()
    {
        $session = session();
        $user_id = $session->get('id');
        $course_id = $this->request->getPost('course_id');

        $enrollModel = new EnrollmentModel();

        // Prevent duplicate enrollment
        if ($enrollModel->isAlreadyEnrolled($user_id, $course_id)) {
            return redirect()->back()->with('error', 'Already enrolled in this course.');
        }

        $enrollModel->insert([
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrolled_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/dashboard')->with('success', 'Enrolled successfully!');
    }
}
