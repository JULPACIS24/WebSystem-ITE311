<?php
namespace App\Models;

use CodeIgniter\Model;

class AcademicSemesterModel extends Model
{
    protected $table      = 'academic_semesters';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'semester_name',
        'term',
        'school_year',
        'start_date',
        'end_date',
        'enrollment_status',
        'created_at',
        'updated_at',
    ];
}
