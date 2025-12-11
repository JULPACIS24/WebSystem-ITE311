<?php
namespace App\Models;

use CodeIgniter\Model;

class AcademicSettingModel extends Model
{
    protected $table      = 'academic_settings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'current_school_year',
        'default_year_level',
        'created_at',
        'updated_at',
    ];
}
