<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\MaterialModel;

class Materials extends BaseController
{
    public function uploadForm($course_id)
    {
        $session = session();

        if (!$session->get('isLoggedIn') || $session->get('role') !== 'teacher') {
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

        if (!$session->get('isLoggedIn') || $session->get('role') !== 'teacher') {
            return redirect()->to('/login');
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/dashboard');
        }

        $validationRules = [
            'material_file' => 'uploaded[material_file]|max_size[material_file,10240]|ext_in[material_file,pdf,ppt]'
        ];

        if (!$this->validate($validationRules)) {
            $errors = $this->validator ? strip_tags($this->validator->listErrors()) : 'Invalid file upload.';
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

        $materialModel = new MaterialModel();

        $data = [
            'course_id'  => (int) $course_id,
            'file_name'  => $file->getClientName(),
            'file_path'  => 'materials/' . $newName,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if (!$materialModel->insertMaterial($data)) {
            @unlink($uploadPath . DIRECTORY_SEPARATOR . $newName);
            session()->setFlashdata('error', 'Failed to save material record.');
            return redirect()->to('/dashboard');
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

        $filePath = WRITEPATH . 'uploads/' . $material['file_path'];
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        if (!$materialModel->delete($material_id)) {
            session()->setFlashdata('error', 'Failed to delete material.');
        } else {
            session()->setFlashdata('success', 'Material deleted successfully.');
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
