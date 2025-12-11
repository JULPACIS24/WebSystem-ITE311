<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table            = 'materials';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'course_id',
        'file_name',
        'file_path',
        'created_at',
        'is_deleted',
    ];

    public function insertMaterial(array $data)
    {
        return $this->insert($data);
    }

    public function getMaterialsByCourse(int $course_id)
    {
        return $this->where('course_id', $course_id)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
