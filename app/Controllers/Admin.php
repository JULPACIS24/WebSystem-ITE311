<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\CourseModel;
use App\Models\AcademicSemesterModel;
use App\Models\AcademicSettingModel;
use App\Models\NotificationModel;

class Admin extends BaseController
{
    public function index()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();

        $data = [
            'name'  => $session->get('name'),
            'role'  => $session->get('role'),
            'users' => $userModel->findAll(),
        ];

        return view('admin/manage_users', $data);
    }

    public function addUser()
    {
        helper(['form', 'url']);
        $data = [];

        if (strtolower($this->request->getMethod()) === 'post') {
            $rules = [
                'name'     => 'required|min_length[2]|max_length[100]|alpha_space',
                'email'    => 'required|valid_email|max_length[100]|is_unique[users.email]',
                'password' => 'required|min_length[6]'
            ];

            $messages = [
                'name' => [
                    'required'    => 'Please enter a valid name (letters and spaces only).',
                    'min_length'  => 'Please enter a valid name (letters and spaces only).',
                    'max_length'  => 'Please enter a valid name (letters and spaces only).',
                    'alpha_space' => 'Name contains invalid characters.'
                ],
                'email' => [
                    'required'   => 'Please enter a valid email address.',
                    'valid_email'=> 'Invalid email format.',
                    'max_length' => 'Please enter a valid email address.',
                    'is_unique'  => 'This email address is already registered.'
                ],
                'password' => [
                    'required'   => 'Password must be at least 6 characters and contain letters and numbers.',
                    'min_length' => 'Password must be at least 6 characters and contain letters and numbers.',
                ],
            ];

            if (!$this->validate($rules, $messages)) {
                // Store validation errors in flashdata so dashboard can show them
                $errorsHtml = $this->validator->listErrors();
                session()->setFlashdata('error', $errorsHtml);
                return redirect()->to('/dashboard');
            } else {
                $userModel = new UserModel();
                $name = trim($this->request->getPost('name'));
                $newUser = [
                    'name'       => $name,
                    'email'      => $this->request->getPost('email'),
                    'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role'       => $this->request->getPost('role') ?? 'student',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($userModel->insert($newUser)) {
                    session()->setFlashdata('success', 'User added successfully!');
                } else {
                    session()->setFlashdata('error', 'Failed to add user.');
                }
                return redirect()->to('/dashboard');
            }
        }
        // For GET requests, the Add User form is shown on the admin dashboard
        return redirect()->to('/dashboard');
    }

    public function deleteUser($id)
    {
        $userModel = new UserModel();
        $currentUserId = session()->get('id');
        $user          = $userModel->find($id);

        // Prevent admin from deleting own account
        if ((int)$id === (int)$currentUserId) {
            session()->setFlashdata('error', 'You cannot delete your own admin account.');
            return redirect()->to('/dashboard');
        }

        // If user is admin, do not allow deletion at all
        if ($user && $user['role'] === 'admin') {
            session()->setFlashdata('error', 'Admin accounts cannot be deleted.');
            return redirect()->to('/dashboard');
        }

        // Soft delete instructors/students using is_deleted flag
        if ($userModel->update($id, ['is_deleted' => 1, 'updated_at' => date('Y-m-d H:i:s')])) {
            session()->setFlashdata('success', 'User marked as deleted.');
        } else {
            session()->setFlashdata('error', 'Failed to mark user as deleted.');
        }
        return redirect()->to('/dashboard');
    }

    public function restoreUser($id)
    {
        $userModel = new UserModel();
        $user      = $userModel->find($id);

        if (!$user) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to('/dashboard');
        }

        // Do not allow changing admin deletion state via this endpoint
        if ($user['role'] === 'admin') {
            session()->setFlashdata('error', 'Admin accounts cannot be restored via this action.');
            return redirect()->to('/dashboard');
        }

        if ($userModel->update($id, ['is_deleted' => 0, 'updated_at' => date('Y-m-d H:i:s')])) {
            session()->setFlashdata('success', 'User has been reactivated.');
        } else {
            session()->setFlashdata('error', 'Failed to reactivate user.');
        }

        return redirect()->to('/dashboard');
    }

    public function updateUserRole($id)
    {
        $userModel = new UserModel();
        $currentUserId = session()->get('id');
        $currentUser   = $userModel->find($id);
        $newRole = $this->request->getPost('role');

        // Prevent admin from changing their own role
        if ($currentUser && (int)$id === (int)$currentUserId && $currentUser['role'] === 'admin') {
            session()->setFlashdata('error', 'You cannot change your own admin role.');
            return redirect()->to('/dashboard');
        }

        // Allow only valid roles
        $allowedRoles = ['admin', 'teacher', 'student'];
        if (!in_array($newRole, $allowedRoles, true)) {
            session()->setFlashdata('error', 'Invalid role selected.');
            return redirect()->to('/dashboard');
        }

        if ($userModel->update($id, ['role' => $newRole, 'updated_at' => date('Y-m-d H:i:s')])) {
            session()->setFlashdata('success', 'User role updated successfully!');
        } else {
            session()->setFlashdata('error', 'Failed to update user role.');
        }

        return redirect()->to('/dashboard');
    }

    public function addCourse()
    {
        helper(['form', 'url']);

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/manage-courses');
        }

        $courseModel = new CourseModel();

        $title          = trim((string) $this->request->getPost('title'));
        $unitsInput     = $this->request->getPost('units');
        $semester       = $this->request->getPost('default_semester');
        $term           = $this->request->getPost('term');
        $scheduleDay    = $this->request->getPost('schedule_day');
        $scheduleStart  = $this->request->getPost('schedule_start_time');
        $scheduleEnd    = $this->request->getPost('schedule_end_time');
        $scheduleRoom   = $this->request->getPost('schedule_room');

        if ($title === '') {
            session()->setFlashdata('error', 'Title is required for a course.');
            return redirect()->to('/dashboard');
        }

        // Normalize units to range 1-5 (default 5 when invalid)
        $units = null;
        if ($unitsInput !== null && $unitsInput !== '') {
            $units = (int) $unitsInput;
            if ($units < 1) {
                $units = 1;
            } elseif ($units > 5) {
                $units = 5;
            }
        } else {
            $units = 5;
        }

        // Auto-generate a unique Course CN in the format CN- + 4 digits (e.g. CN-0001).
        // We look for the current max CN-**** code and increment its numeric part.
        $lastCnCourse = $courseModel
            ->like('course_code', 'CN-', 'after')
            ->orderBy('course_code', 'DESC')
            ->first();

        $nextNumber = 1;
        if (!empty($lastCnCourse['course_code']) && strlen($lastCnCourse['course_code']) >= 7) {
            // characters after 'CN-'
            $numericPart = substr($lastCnCourse['course_code'], 3);
            if (ctype_digit($numericPart)) {
                $nextNumber = ((int) $numericPart) + 1;
            }
        }

        $courseCode = 'CN-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

        $data = [
            'title'               => $title,
            'course_code'         => $courseCode,
            'units'               => $units,
            'default_semester'    => $semester ?: null,
            'term'                => $term !== null && $term !== '' ? (int) $term : null,
            'schedule_day'        => $scheduleDay ?: null,
            'schedule_start_time' => $scheduleStart ?: null,
            'schedule_end_time'   => $scheduleEnd ?: null,
            'schedule_room'       => $scheduleRoom ?: null,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        if (!$courseModel->insert($data)) {
            session()->setFlashdata('error', 'Failed to create course.');
        } else {
            session()->setFlashdata('success', 'Course created successfully.');
        }

        return redirect()->to('/manage-courses');
    }

    public function updateCourse($id)
    {
        helper(['form', 'url']);

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/dashboard#assign-teacher');
        }

        $courseModel = new CourseModel();
        $course      = $courseModel->find($id);

        if (!$course) {
            session()->setFlashdata('assign_error', 'Course not found.');
            return redirect()->to('/assign-teacher');
        }

        $title      = trim((string) $this->request->getPost('title'));
        $courseCode = trim((string) $this->request->getPost('course_code'));
        $unitsInput = $this->request->getPost('units');
        $semester   = $this->request->getPost('default_semester');
        $term       = $this->request->getPost('term');

        if ($title === '') {
            session()->setFlashdata('error', 'Title is required for a course.');
            return redirect()->to('/dashboard');
        }

        // Normalize units to range 1-5 (default 5 when invalid)
        $units = null;
        if ($unitsInput !== null && $unitsInput !== '') {
            $units = (int) $unitsInput;
            if ($units < 1) {
                $units = 1;
            } elseif ($units > 5) {
                $units = 5;
            }
        } else {
            $units = 5;
        }

        // Prevent duplicate CN globally when updating (exclude this course id)
        if ($courseCode !== '') {
            $existing = $courseModel
                ->where('course_code', $courseCode)
                ->where('id !=', (int) $id)
                ->first();

            if ($existing) {
                session()->setFlashdata('error', 'CN is already used by another course. Please use a different CN.');
                return redirect()->to('/dashboard');
            }
        }

        $data = [
            'title'            => $title,
            'course_code'      => $courseCode !== '' ? $courseCode : null,
            'units'            => $units,
            'default_semester' => $semester ?: null,
            'term'             => $term !== null && $term !== '' ? (int) $term : null,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        if (!$courseModel->update($id, $data)) {
            session()->setFlashdata('error', 'Failed to update course.');
        } else {
            session()->setFlashdata('success', 'Course updated successfully.');
        }

        return redirect()->to('/dashboard');
    }

    public function editCourse($id)
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $courseModel = new CourseModel();
        $course      = $courseModel->find($id);

        if (!$course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/manage-courses');
        }

        $data = [
            'name'   => $session->get('name'),
            'role'   => $session->get('role'),
            'course' => $course,
        ];

        return view('admin/edit_course', $data);
    }

    public function deleteCourse($id)
    {
        $courseModel = new CourseModel();

        if (!$courseModel->find($id)) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/dashboard');
        }

        if (!$courseModel->delete($id)) {
            session()->setFlashdata('error', 'Failed to delete course.');
        } else {
            session()->setFlashdata('success', 'Course deleted successfully.');
        }

        return redirect()->to('/dashboard');
    }

    public function manageCourses()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $courseModel  = new CourseModel();
        $userModel    = new UserModel();
        $settingModel = new AcademicSettingModel();

        // Join courses with users to get instructor name from teacher_id
        $courses = $courseModel
            ->select('courses.*, users.name AS instructor_name')
            ->join('users', 'users.id = courses.teacher_id', 'left')
            ->findAll();

        // Get current school year from academic settings (if configured)
        $setting           = $settingModel->first();
        $currentSchoolYear = $setting['current_school_year'] ?? null;

        $data = [
            'name'               => $session->get('name'),
            'role'               => $session->get('role'),
            'courses'            => $courses,
            'currentSchoolYear'  => $currentSchoolYear,
        ];

        return view('admin/manage_courses', $data);
    }

    public function academicManagement()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $semesterModel = new AcademicSemesterModel();
        $settingModel  = new AcademicSettingModel();

        $data = [
            'name'      => $session->get('name'),
            'role'      => $session->get('role'),
            'semesters' => [],
            'setting'   => null,
        ];

        try {
            $semesters = $semesterModel
                ->orderBy('school_year', 'DESC')
                ->orderBy('id', 'DESC')
                ->findAll();
            $setting   = $settingModel->first();

            $data['semesters'] = $semesters;
            $data['setting']   = $setting;
        } catch (\Throwable $e) {
            log_message('error', 'AcademicManagement data failed: ' . $e->getMessage());
        }

        return view('admin/academic_year_semester', $data);
    }

    public function saveAcademicYear()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        helper(['form', 'url']);

        $year = trim((string) $this->request->getPost('active_school_year'));

        $settingModel  = new AcademicSettingModel();
        $semesterModel = new AcademicSemesterModel();
        $existing      = $settingModel->first();

        // Prevent setting a new academic year while another school year is still ongoing
        if ($year !== '') {
            $today   = date('Y-m-d');
            $ongoing = $semesterModel
                ->where('DATE(end_date) >=', $today)
                ->first();

            if ($ongoing && ($ongoing['school_year'] ?? '') !== $year) {
                session()->setFlashdata(
                    'error',
                    'You cannot set the academic year to ' . $year . ' while the school year ' . ($ongoing['school_year'] ?? '') . ' is still ongoing.'
                );
                return redirect()->to('/admin/academic-management');
            }
        }

        $data = [
            'current_school_year' => $year !== '' ? $year : null,
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            if (!$settingModel->update($existing['id'], $data)) {
                session()->setFlashdata('error', 'Failed to save academic year.');
            } else {
                session()->setFlashdata('success', 'Academic year updated.');
            }
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            if (!$settingModel->insert($data)) {
                session()->setFlashdata('error', 'Failed to save academic year.');
            } else {
                session()->setFlashdata('success', 'Academic year saved.');
            }
        }

        // Return to Academic Management page so admin sees notifications there
        return redirect()->to('/admin/academic-management');
    }

    public function saveSemester()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        helper(['form', 'url']);

        $semesterName = $this->request->getPost('active_semester');
        $term         = $this->request->getPost('term');
        $schoolYear   = $this->request->getPost('school_year');
        $startDate    = $this->request->getPost('semester_start_date');
        $endDate      = $this->request->getPost('semester_end_date');
        $status       = $this->request->getPost('enrollment_status');

        if (!$semesterName || !$schoolYear) {
            session()->setFlashdata('error', 'Semester name and school year are required.');
            return redirect()->to('/admin/academic-management');
        }

        $semesterModel = new AcademicSemesterModel();
        // Do not allow adding a semester for a new school year
        // while there is still an ongoing academic year (end_date today or in the future)
        $today   = date('Y-m-d');
        $ongoing = $semesterModel
            ->where('DATE(end_date) >=', $today)
            ->first();

        if ($ongoing && ($ongoing['school_year'] ?? '') !== $schoolYear) {
            session()->setFlashdata(
                'error',
                'You cannot add a semester for ' . $schoolYear . ' while the academic year ' . ($ongoing['school_year'] ?? '') . ' is still ongoing.'
            );
            return redirect()->to('/admin/academic-management');
        }

        $data = [
            'semester_name'     => $semesterName,
            'term'              => $term !== '' ? (int) $term : null,
            'school_year'       => $schoolYear,
            'start_date'        => $startDate ?: null,
            'end_date'          => $endDate ?: null,
            'enrollment_status' => $status ?: 'Open',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        if (!$semesterModel->insert($data)) {
            session()->setFlashdata('error', 'Failed to save semester.');
        } else {
            session()->setFlashdata('success', 'Semester saved successfully.');
        }

        return redirect()->to('/admin/academic-management');
    }

    public function saveDefaultYearLevel()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        helper(['form', 'url']);

        $yearLevel = $this->request->getPost('default_year_level');

        $settingModel = new AcademicSettingModel();
        $existing     = $settingModel->first();

        $data = [
            'default_year_level' => $yearLevel !== '' ? (int) $yearLevel : null,
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            if (!$settingModel->update($existing['id'], $data)) {
                session()->setFlashdata('error', 'Failed to save default year level.');
            } else {
                session()->setFlashdata('success', 'Default year level updated.');
            }
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            if (!$settingModel->insert($data)) {
                session()->setFlashdata('error', 'Failed to save default year level.');
            } else {
                session()->setFlashdata('success', 'Default year level saved.');
            }
        }

        return redirect()->to('/dashboard');
    }

    public function assignYearLevelToCourse()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        helper(['form', 'url']);

        $courseId  = $this->request->getPost('course_for_year_level');
        $yearLevel = $this->request->getPost('assigned_year_level');

        if (!$courseId || !$yearLevel) {
            session()->setFlashdata('error', 'Please select both course and year level.');
            return redirect()->to('/dashboard');
        }

        $courseModel = new CourseModel();
        $course      = $courseModel->find($courseId);

        if (!$course) {
            session()->setFlashdata('assign_error', 'Course not found.');
            return redirect()->to('/dashboard?section=teachers');
        }

        if (!$courseModel->update($courseId, [
            'year_level' => (int) $yearLevel,
            'updated_at' => date('Y-m-d H:i:s'),
        ])) {
            session()->setFlashdata('error', 'Failed to assign year level to course.');
        } else {
            session()->setFlashdata('success', 'Year level assigned to course.');
        }

        return redirect()->to('/dashboard');
    }

    public function courseSchedule()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }
        // Separate Course Schedule page is retired; use Manage Courses page instead.
        return redirect()->to('/manage-courses');
    }

    public function updateCourseSchedule($id)
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        helper(['form', 'url']);

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/dashboard');
        }

        $courseModel = new CourseModel();
        $course      = $courseModel->find($id);

        if (!$course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/dashboard');
        }

        $scheduleDay       = $this->request->getPost('schedule_day');
        $scheduleStartTime = $this->request->getPost('schedule_start_time');
        $scheduleEndTime   = $this->request->getPost('schedule_end_time');
        $scheduleRoom      = $this->request->getPost('schedule_room');

        // Basic required fields check for schedule
        if (!$scheduleDay || !$scheduleStartTime || !$scheduleEndTime) {
            session()->setFlashdata('error', 'Day, start time, and end time are required for a schedule.');
            return redirect()->to('/dashboard');
        }

        // Prevent schedule conflicts for the same teacher on the same day.
        // Titles may be the same, but the same teacher cannot be scheduled
        // for overlapping time ranges on the same day.
        $teacherId = $course['teacher_id'] ?? null;
        if (!empty($teacherId)) {
            $conflict = $courseModel
                ->where('teacher_id', (int) $teacherId)
                ->where('id !=', (int) $id)
                ->where('schedule_day', $scheduleDay)
                // Overlap condition: start < other_end AND end > other_start
                ->where('schedule_start_time <', $scheduleEndTime)
                ->where('schedule_end_time >', $scheduleStartTime)
                ->first();

            if ($conflict) {
                session()->setFlashdata('error', 'Schedule conflict: this teacher already has a class at that time on ' . $scheduleDay . '.');
                return redirect()->to('/dashboard');
            }
        }

        $data = [
            'schedule_day'        => $scheduleDay ?: null,
            'schedule_start_time' => $scheduleStartTime ?: null,
            'schedule_end_time'   => $scheduleEndTime ?: null,
            'schedule_room'       => $scheduleRoom ?: null,
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        if (!$courseModel->update($id, $data)) {
            session()->setFlashdata('error', 'Failed to update course schedule.');
        } else {
            session()->setFlashdata('success', 'Course schedule updated successfully.');
        }

        return redirect()->to('/dashboard');
    }

    public function assignTeacherDashboard()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $userModel      = new UserModel();
        $courseModel    = new CourseModel();
        $settingModel   = new AcademicSettingModel();

        $teachers = $userModel
            ->where('role', 'teacher')
            ->findAll();

        $courses = $courseModel->findAll();

        $setting           = $settingModel->first();
        $currentSchoolYear = $setting['current_school_year'] ?? null;

        $data = [
            'name'     => $session->get('name'),
            'role'     => $session->get('role'),
            'teachers' => $teachers,
            'courses'  => $courses,
            'currentSchoolYear' => $currentSchoolYear,
        ];

        return view('admin/assign_teacher', $data);
    }

    public function assignTeacherToCourse()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        helper(['form', 'url']);

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/dashboard');
        }

        $teacherId = $this->request->getPost('teacher_id');
        $courseId  = $this->request->getPost('course_id');

        if (!$teacherId || !$courseId) {
            session()->setFlashdata('assign_error', 'Please select both a teacher and a course.');
            return redirect()->to('/assign-teacher');
        }

        $courseModel = new CourseModel();
        $course      = $courseModel->find($courseId);

        if (!$course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/dashboard');
        }

        // If course already has a different teacher assigned, do not allow reassignment
        if (!empty($course['teacher_id']) && (int) $course['teacher_id'] !== (int) $teacherId) {
            session()->setFlashdata('assign_error', 'This course already has another teacher assigned.');
            return redirect()->to('/assign-teacher');
        }

        // Prevent assigning if this teacher is already assigned to the course
        if (!empty($course['teacher_id']) && (int) $course['teacher_id'] === (int) $teacherId) {
            session()->setFlashdata('assign_error', 'This teacher is already assigned to the selected course.');
            return redirect()->to('/assign-teacher');
        }

        // If the course already has a schedule, prevent assigning a teacher
        // who has another course that overlaps on the same day, semester, and term.
        $scheduleDay   = $course['schedule_day'] ?? null;
        $scheduleFrom  = $course['schedule_start_time'] ?? null;
        $scheduleTo    = $course['schedule_end_time'] ?? null;
        $courseSem     = $course['default_semester'] ?? null;
        $courseTermVal = $course['term'] ?? null;

        if ($scheduleDay && $scheduleFrom && $scheduleTo) {
            $builder = $courseModel
                ->where('teacher_id', (int) $teacherId)
                ->where('id !=', (int) $courseId)
                ->where('schedule_day', $scheduleDay);

            if ($courseSem !== null && $courseSem !== '') {
                $builder = $builder->where('default_semester', $courseSem);
            }

            if ($courseTermVal !== null && $courseTermVal !== '') {
                $builder = $builder->where('term', (int) $courseTermVal);
            }

            // Overlap condition: start < other_end AND end > other_start
            $conflict = $builder
                ->where('schedule_start_time <', $scheduleTo)
                ->where('schedule_end_time >', $scheduleFrom)
                ->first();

            if ($conflict) {
                // Format time to 12-hour with AM/PM for the message
                $fromFormatted = date('g:i A', strtotime($scheduleFrom));
                $toFormatted   = date('g:i A', strtotime($scheduleTo));
                $semLabel      = $course['default_semester'] ?? '';
                $termValue     = $course['term'] ?? null;
                $termLabel     = $termValue !== null && $termValue !== '' ? 'Term ' . (int) $termValue : 'Term';

                $message = 'Time conflict: The teacher already has a course scheduled at '
                    . $fromFormatted . ' - ' . $toFormatted
                    . ' for the same ' . ($semLabel !== '' ? $semLabel : 'semester')
                    . ' and ' . $termLabel . '.';

                session()->setFlashdata('assign_error', $message);
                return redirect()->to('/assign-teacher');
            }
        }

        if (!$courseModel->update($courseId, [
            'teacher_id' => (int) $teacherId,
            'updated_at' => date('Y-m-d H:i:s'),
        ])) {
            session()->setFlashdata('assign_error', 'Failed to assign teacher to course.');
        } else {
            session()->setFlashdata('assign_success', 'Teacher successfully assigned to course.');

            // Create a notification for the assigned teacher
            try {
                $notificationModel = new NotificationModel();
                $courseTitle       = $course['title'] ?? 'a course';
                $now               = date('Y-m-d H:i:s');

                $notificationModel->insert([
                    'user_id'    => (int) $teacherId,
                    'message'    => 'You have been assigned to teach ' . $courseTitle . '.',
                    'is_read'    => 0,
                    'created_at' => $now,
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Failed to create teacher assignment notification: ' . $e->getMessage());
            }
        }

        return redirect()->to('/assign-teacher');
    }

    public function courseOfferingsDashboard()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }
        // Course Offerings dashboard content is now shown on the unified dashboard.
        return redirect()->to('/dashboard');
    }
}
