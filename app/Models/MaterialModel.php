<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table = 'materials';
    protected $primaryKey = 'id';
    protected $allowedFields = ['course_id', 'file_name', 'file_path', 'created_at'];
    protected $useTimestamps = false;

    public function getMaterialsByCourse(int $courseId): array
    {
        return $this->where('course_id', $courseId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
