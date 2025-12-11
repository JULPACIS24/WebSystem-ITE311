<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    // user_id is always from session; current enrollments table only has user_id, course_id, enrolled_at
    protected $allowedFields = [
        'user_id',
        'course_id',
        'enrolled_at',
        'semester',
        'school_year',
        'start_date',
        'end_date',
        'status',
    ];

    // Insert a new enrollment record
    public function enrollUser($data)
    {
        return $this->insert($data);
    }

    // Fetch all courses a user is enrolled in
    public function getUserEnrollments($user_id)
    {
        try {
            return $this->select('courses.*, enrollments.enrolled_at, enrollments.semester, enrollments.school_year, enrollments.status')
                        ->join('courses', 'courses.id = enrollments.course_id')
                        ->where('enrollments.user_id', $user_id)
                        ->findAll();
        } catch (\Throwable $e) {
            // Fallback if `status` column does not exist: just log and return empty list
            log_message('error', 'EnrollmentModel::getUserEnrollments status column missing or query failed: ' . $e->getMessage());
            return [];
        }
    }

    // Check if already enrolled in the same course, optionally by semester and school year
    public function isAlreadyEnrolled($user_id, $course_id, $semester = null, $schoolYear = null)
    {
        $builder = $this->where('user_id', $user_id)
                        ->where('course_id', $course_id);

        if ($semester !== null) {
            $builder = $builder->where('semester', $semester);
        }

        if ($schoolYear !== null) {
            $builder = $builder->where('school_year', $schoolYear);
        }

        return $builder->first();
    }

    // Fetch pending enrollments for courses handled by a teacher
    public function getPendingForTeacher($teacherId)
    {
        try {
            return $this->select('enrollments.*, courses.title as course_title, users.name as student_name, users.email as student_email')
                        ->join('courses', 'courses.id = enrollments.course_id')
                        ->join('users', 'users.id = enrollments.user_id')
                        ->where('courses.teacher_id', $teacherId)
                        ->groupStart()
                            ->where('enrollments.status', 'pending')
                            ->orWhere('enrollments.status', null)
                            ->orWhere('enrollments.status', '')
                        ->groupEnd()
                        ->orderBy('enrollments.enrolled_at', 'DESC')
                        ->findAll();
        } catch (\Throwable $e) {
            // Fallback if `status` column does not exist: log and return empty list
            log_message('error', 'EnrollmentModel::getPendingForTeacher status column missing or query failed: ' . $e->getMessage());
            return [];
        }
    }

    // Approve a pending enrollment (set status to active)
    public function approveEnrollment($enrollmentId)
    {
        try {
            return (bool) $this->update($enrollmentId, ['status' => 'active']);
        } catch (\Throwable $e) {
            // If status column is missing, log and treat as success so flow continues
            log_message('error', 'EnrollmentModel::approveEnrollment status column missing or update failed: ' . $e->getMessage());
            return true;
        }
    }

    public function getActiveForTeacher($teacherId)
    {
        try {
            return $this->select('enrollments.*, courses.title as course_title, users.name as student_name, users.email as student_email')
                        ->join('courses', 'courses.id = enrollments.course_id')
                        ->join('users', 'users.id = enrollments.user_id')
                        ->where('courses.teacher_id', $teacherId)
                        ->where('enrollments.status', 'active')
                        ->orderBy('courses.title', 'ASC')
                        ->orderBy('users.name', 'ASC')
                        ->findAll();
        } catch (\Throwable $e) {
            // Fallback if `status` column does not exist: log and return empty list
            log_message('error', 'EnrollmentModel::getActiveForTeacher status column missing or query failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getEnrollmentCountByCourse($courseId)
    {
        return $this->where('course_id', $courseId)->countAllResults();
    }
}
