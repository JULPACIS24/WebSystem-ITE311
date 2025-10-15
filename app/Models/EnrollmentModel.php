<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'course_id', 'enrolled_at'];

    // Insert a new enrollment record
    public function enrollUser($data)
    {
        return $this->insert($data);
    }

    // ✅ Fetch all courses a user is enrolled in
    public function getUserEnrollments($user_id)
    {
        return $this->select('courses.*')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('enrollments.user_id', $user_id)
                    ->findAll();
    }

    // ✅ Check if already enrolled
    public function isAlreadyEnrolled($user_id, $course_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('course_id', $course_id)
                    ->first();
    }
}
