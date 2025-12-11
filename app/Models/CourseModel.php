<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title',
        'course_code',
        'description',
        'teacher_id',
        'units',
        'default_semester',
        'default_school_year',
        'default_start_date',
        'default_end_date',
        'term',
        'year_level',
        'schedule_day',
        'schedule_start_time',
        'schedule_end_time',
        'schedule_room',
        'created_at',
        'updated_at',
    ];
}
