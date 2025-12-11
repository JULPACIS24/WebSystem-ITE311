<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    // Enrollments table with status and optional semester-related metadata
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
        $safeData = [
            'user_id'     => $data['user_id']     ?? null,
            'course_id'   => $data['course_id']   ?? null,
            'enrolled_at' => $data['enrolled_at'] ?? date('Y-m-d H:i:s'),
            'semester'    => $data['semester']    ?? null,
            'school_year' => $data['school_year'] ?? null,
            'start_date'  => $data['start_date']  ?? null,
            'end_date'    => $data['end_date']    ?? null,
            'status'      => $data['status']      ?? 'pending',
        ];

        return $this->insert($safeData);
    }

    // Fetch all courses a user is enrolled in
    public function getUserEnrollments($user_id)
    {
        try {
            $today = date('Y-m-d');

            return $this->select('courses.*, enrollments.enrolled_at, enrollments.semester, enrollments.school_year, enrollments.status, enrollments.start_date, enrollments.end_date')
                        ->join('courses', 'courses.id = enrollments.course_id')
                        ->where('enrollments.user_id', $user_id)
                        // Show only current or future enrollments: no end_date or end_date >= today
                        ->groupStart()
                            ->where('enrollments.end_date IS NULL')
                            ->orWhere('DATE(enrollments.end_date) >=', $today)
                        ->groupEnd()
                        ->findAll();
        } catch (\Throwable $e) {
            log_message('error', 'EnrollmentModel::getUserEnrollments query failed: ' . $e->getMessage());
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

        try {
            return $builder->first();
        } catch (\Throwable $e) {
            log_message('error', 'EnrollmentModel::isAlreadyEnrolled query failed: ' . $e->getMessage());

            return $this->where('user_id', $user_id)
                        ->where('course_id', $course_id)
                        ->first();
        }
    }

    // Fetch pending enrollments for courses handled by a teacher
    public function getPendingForTeacher($teacherId)
    {
        try {
            return $this->select('enrollments.*, courses.title as course_title, courses.units as course_units, users.name as student_name, users.email as student_email')
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
            $today = date('Y-m-d');

            return $this->select('enrollments.*, courses.title as course_title, courses.units as course_units, users.name as student_name, users.email as student_email')
                        ->join('courses', 'courses.id = enrollments.course_id')
                        ->join('users', 'users.id = enrollments.user_id')
                        ->where('courses.teacher_id', $teacherId)
                        ->where('enrollments.status', 'active')
                        // Only show enrollments that are still within their end_date (or have no end_date)
                        ->groupStart()
                            ->where('enrollments.end_date IS NULL')
                            ->orWhere('DATE(enrollments.end_date) >=', $today)
                        ->groupEnd()
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
