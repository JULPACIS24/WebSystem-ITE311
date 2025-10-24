<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\EnrollmentModel;
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

    // ✅ Always pass course_id to the view (even if null)
    if ($this->request->getMethod() !== 'post') {
        return view('admin/upload', ['course_id' => $course_id ?? 0]);
    }

    // ✅ Validation rules
    $validationRule = [
        'material_file' => [
            'label' => 'File',
            'rules' => 'uploaded[material_file]|max_size[material_file,10240]|ext_in[material_file,pdf,ppt,pptx,doc,docx]',
        ],
    ];

    if (!$this->validate($validationRule)) {
        return redirect()->back()->with('error', 'Invalid file upload.');
    }

    $file = $this->request->getFile('material_file');

    if ($file && $file->isValid() && !$file->hasMoved()) {
        // ✅ Ensure upload directory exists
        $uploadPath = ROOTPATH . 'public/uploads/materials/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // ✅ Move uploaded file
        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        // ✅ Prepare database data
        $data = [
            'course_id'  => $course_id ?? 0, // fallback if null
            'file_name'  => $file->getClientName(),
            'file_path'  => 'uploads/materials/' . $newName,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // ✅ Insert record
        if ($this->materialModel->insert($data)) {
            return redirect()->back()->with('success', 'File uploaded successfully!');
        } else {
            return redirect()->back()->with('error', 'Database insert failed.');
        }
    } else {
        return redirect()->back()->with('error', 'File upload failed.');
    }
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
