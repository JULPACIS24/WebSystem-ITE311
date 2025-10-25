<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\NotificationModel;

class Course extends BaseController
{
    public function index()
    {
        $session = session();
        $user_id = $session->get('id');
        $role = $session->get('role');

        $enrollModel = new EnrollmentModel();

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

        $enrollModel       = new EnrollmentModel();
        $courseModel       = new CourseModel();
        $notificationModel = new NotificationModel();

        // Prevent duplicate enrollment
        if ($enrollModel->isAlreadyEnrolled($user_id, $course_id)) {
            return redirect()->back()->with('error', 'Already enrolled in this course.');
        }

        $enrollModel->insert([
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrolled_at' => date('Y-m-d H:i:s')
        ]);

        $course     = $courseModel->find($course_id);
        $courseName = $course['title'] ?? 'a course';

        $notificationData = [
            'user_id'    => $user_id,
            'message'    => sprintf('You have been enrolled in %s', $courseName),
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if (!$notificationModel->insert($notificationData)) {
            log_message('error', 'Notification insert failed: ' . json_encode($notificationModel->errors()));
        } else {
            log_message('info', 'Notification created: ' . json_encode($notificationData));
        }

        return redirect()->to('/dashboard')->with('success', 'Enrolled successfully!');
    }
}
