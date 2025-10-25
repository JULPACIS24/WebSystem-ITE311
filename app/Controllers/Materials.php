<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\EnrollmentModel;
use App\Models\CourseModel;
use CodeIgniter\Controller;

class Materials extends Controller
{
    protected $materialModel;
    protected $session;

    public function __construct()
    {
        $this->materialModel = new MaterialModel();
        $this->session = session();
    }

    // 📤 Upload material
    public function upload($course_id = null)
    {
        helper(['form', 'url', 'filesystem']);

        $methodOriginal = $this->request->getMethod();
        $method = strtolower($methodOriginal);
        log_message('debug', 'Materials::upload accessed | method=' . $methodOriginal . ' | route_course_id=' . ($course_id ?? 'null'));

        $courseModel = new CourseModel();
        $courses = $courseModel->findAll();

        if ($method !== 'post') {
            return view('admin/upload', [
                'course_id' => $course_id,
                'courses'   => $courses,
            ]);
        }

        $postedCourseId = $this->request->getPost('course_id');
        log_message('debug', 'Materials::upload handling POST | method=' . $methodOriginal . ' | route_course_id=' . ($course_id ?? 'null') . ' | posted_course_id=' . ($postedCourseId ?? 'null'));

        $resolvedCourseId = $course_id ?? (int) $postedCourseId;
        if (!$resolvedCourseId) {
            log_message('debug', 'Materials::upload missing course_id');
            return redirect()->back()->withInput()->with('error', 'Please choose a course before uploading.');
        }

        $validationRule = [
            'material_file' => [
                'label' => 'File',
                'rules' => 'uploaded[material_file]|max_size[material_file,10240]|ext_in[material_file,pdf,ppt,pptx,doc,docx]',
            ],
        ];

        if (!$this->validate($validationRule)) {
            log_message('debug', 'Materials::upload validation failed | errors=' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('error', 'Invalid file upload.');
        }

        $file = $this->request->getFile('material_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            log_message('debug', 'Materials::upload file received | client_name=' . $file->getClientName() . ' | size=' . $file->getSize() . ' | mime=' . $file->getClientMimeType());

            $uploadPath = ROOTPATH . 'public/uploads/materials/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $newName = $file->getRandomName();
            if (!$file->move($uploadPath, $newName)) {
                log_message('error', 'Material upload: failed moving file. Error: ' . $file->getErrorString());
                return redirect()->back()->withInput()->with('error', 'Failed to save file on server: ' . $file->getErrorString());
            }

            log_message('debug', 'Materials::upload file moved | stored_name=' . $newName . ' | path=' . $uploadPath . $newName);

            $data = [
                'course_id'  => $resolvedCourseId,
                'file_name'  => $file->getClientName(),
                'file_path'  => 'uploads/materials/' . $newName,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->materialModel->insert($data)) {
                log_message('debug', 'Materials::upload database insert success | data=' . json_encode($data));
                return redirect()->back()->with('success', 'File uploaded successfully!');
            }

            $modelErrors = $this->materialModel->errors();
            $dbError = $this->materialModel->db->error();
            $errorMessage = !empty($modelErrors)
                ? implode(', ', $modelErrors)
                : ($dbError['message'] ?? 'Unknown database error');

            log_message('error', 'Material upload: database insert failed. Data: ' . json_encode($data) . ' | Errors: ' . $errorMessage);

            return redirect()->back()->withInput()->with('error', 'Database insert failed: ' . $errorMessage);
        }

        log_message('debug', 'Materials::upload request missing valid file');
        return redirect()->back()->with('error', 'File upload failed.');
    }

    // 🗑 Delete material
    public function delete($material_id)
    {
        $material = $this->materialModel->find($material_id);
        if ($material) {
            $filePath = ROOTPATH . 'public/' . $material['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->materialModel->delete($material_id);
            $this->session->setFlashdata('success', 'Material deleted successfully.');
        } else {
            $this->session->setFlashdata('error', 'Material not found.');
        }

        return redirect()->back();
    }

    // 📥 Download material (secure version)
    public function download($material_id)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $user_id = $session->get('id');
        $role = $session->get('role');

        $enrollmentModel = new EnrollmentModel();
        $material = $this->materialModel->find($material_id);

        if (!$material) {
            $this->session->setFlashdata('error', 'Material not found.');
            return redirect()->back();
        }

        $course_id = $material['course_id'];

        // ✅ Students must be enrolled before download
        if ($role === 'student') {
            $isEnrolled = $enrollmentModel
                ->where('user_id', $user_id)
                ->where('course_id', $course_id)
                ->first();

            if (!$isEnrolled) {
                $this->session->setFlashdata('error', 'Access denied. You are not enrolled in this course.');
                return redirect()->back();
            }
        }

        $filePath = ROOTPATH . 'public/' . $material['file_path'];

        if (!file_exists($filePath)) {
            $this->session->setFlashdata('error', 'File not found on server.');
            return redirect()->back();
        }

        return $this->response->download($filePath, null);
    }
}
