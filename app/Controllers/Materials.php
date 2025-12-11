<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\MaterialModel;
use App\Models\NotificationModel;

class Materials extends BaseController
{
    public function uploadForm($course_id)
    {
        $session = session();

        // Allow both teacher and admin to open the upload form
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['teacher', 'admin'], true)) {
            return redirect()->to('/login');
        }

        $data = [
            'action' => site_url('materials/upload/' . (int) $course_id),
        ];

        return view('materials/upload', $data);
    }

    public function upload($course_id)
    {
        $session = session();

        // Allow both teacher and admin to upload materials
        if (!$session->get('isLoggedIn') || !in_array($session->get('role'), ['teacher', 'admin'], true)) {
            return redirect()->to('/login');
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/dashboard');
        }

        $validationRules = [
            'material_file' => 'uploaded[material_file]|max_size[material_file,10240]|ext_in[material_file,pdf,ppt]'
        ];

        $validationMessages = [
            'material_file' => [
                'uploaded' => 'Please select a file to upload.',
                'max_size' => 'The file is too large. Maximum size is 10MB.',
                'ext_in'   => 'Only PDF and PPT files are allowed.',
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            $errors = $this->validator ? strip_tags($this->validator->listErrors()) : 'Only PDF and PPT files are allowed.';
            session()->setFlashdata('error', $errors);
            return redirect()->to('/dashboard');
        }

        $file = $this->request->getFile('material_file');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            session()->setFlashdata('error', 'Invalid file upload.');
            return redirect()->to('/dashboard');
        }

        $uploadPath = WRITEPATH . 'uploads/materials';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $newName = $file->getRandomName();

        try {
            $file->move($uploadPath, $newName);
        } catch (\Throwable $e) {
            session()->setFlashdata('error', 'Failed to save uploaded file.');
            return redirect()->to('/dashboard');
        }

        $materialModel    = new MaterialModel();
        $enrollmentModel  = new EnrollmentModel();
        $notificationModel = new NotificationModel();
        $courseModel      = new CourseModel();

        $now = date('Y-m-d H:i:s');

        $data = [
            'course_id'  => (int) $course_id,
            'file_name'  => $file->getClientName(),
            'file_path'  => 'materials/' . $newName,
            'created_at' => $now,
        ];

        if (!$materialModel->insertMaterial($data)) {
            @unlink($uploadPath . DIRECTORY_SEPARATOR . $newName);
            session()->setFlashdata('error', 'Failed to save material record.');
            return redirect()->to('/dashboard');
        }

        // Create notifications for all active enrolled students in this course
        try {
            $course = $courseModel->find((int) $course_id);
            $title  = $course['title'] ?? 'a course';

            $enrollments = $enrollmentModel
                ->where('course_id', (int) $course_id)
                ->where('status', 'active')
                ->findAll();

            foreach ($enrollments as $enroll) {
                $userId = $enroll['user_id'] ?? null;
                if (!$userId) {
                    continue;
                }

                $notificationModel->insert([
                    'user_id'    => (int) $userId,
                    'message'    => 'New material uploaded for ' . $title . ': ' . $file->getClientName(),
                    'is_read'    => 0,
                    'created_at' => $now,
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create material upload notifications: ' . $e->getMessage());
        }

        session()->setFlashdata('success', 'Material uploaded successfully.');
        return redirect()->to('/dashboard');
    }

    public function delete($material_id)
    {
        $session = session();

        if (!$session->get('isLoggedIn') || $session->get('role') !== 'teacher') {
            return redirect()->to('/login');
        }

        $materialModel = new MaterialModel();
        $courseModel   = new CourseModel();

        $material = $materialModel->find($material_id);

        if (!$material) {
            session()->setFlashdata('error', 'Material not found.');
            return redirect()->to('/dashboard');
        }

        $course = $courseModel->find($material['course_id']);
        $teacherId = $course['teacher_id'] ?? null;

        if (!$course || (int) $teacherId !== (int) $session->get('id')) {
            session()->setFlashdata('error', 'You are not allowed to delete this material.');
            return redirect()->to('/dashboard');
        }

        // Soft delete: mark as deleted but keep record and file, so it can be restored
        if (!$materialModel->update($material_id, ['is_deleted' => 1])) {
            session()->setFlashdata('error', 'Failed to delete material.');
        } else {
            session()->setFlashdata('success', 'Material deleted successfully. You can restore it later.');
        }

        return redirect()->to('/dashboard');
    }

    public function restore($material_id)
    {
        $session = session();

        if (!$session->get('isLoggedIn') || $session->get('role') !== 'teacher') {
            return redirect()->to('/login');
        }

        $materialModel = new MaterialModel();
        $courseModel   = new CourseModel();

        $material = $materialModel->find($material_id);

        if (!$material) {
            session()->setFlashdata('error', 'Material not found.');
            return redirect()->to('/dashboard');
        }

        $course    = $courseModel->find($material['course_id']);
        $teacherId = $course['teacher_id'] ?? null;

        if (!$course || (int) $teacherId !== (int) $session->get('id')) {
            session()->setFlashdata('error', 'You are not allowed to restore this material.');
            return redirect()->to('/dashboard');
        }

        if (!$materialModel->update($material_id, ['is_deleted' => 0])) {
            session()->setFlashdata('error', 'Failed to restore material.');
        } else {
            session()->setFlashdata('success', 'Material restored successfully.');
        }

        return redirect()->to('/dashboard');
    }

    public function download($material_id)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = $session->get('id');

        $materialModel   = new MaterialModel();
        $enrollmentModel = new EnrollmentModel();

        $material = $materialModel->find($material_id);

        if (!$material) {
            session()->setFlashdata('error', 'Material not found.');
            return redirect()->to('/dashboard');
        }

        $enrollment = $enrollmentModel->where('user_id', $userId)
            ->where('course_id', $material['course_id'])
            ->where('status', 'active')
            ->first();

        if (!$enrollment && $session->get('role') !== 'teacher' && $session->get('role') !== 'admin') {
            session()->setFlashdata('error', 'You are not allowed to download this material.');
            return redirect()->to('/dashboard');
        }

        $filePath = WRITEPATH . 'uploads/' . $material['file_path'];

        if (!is_file($filePath)) {
            session()->setFlashdata('error', 'File not found on server.');
            return redirect()->to('/dashboard');
        }

        return $this->response->download($filePath, null)->setFileName($material['file_name']);
    }
}
