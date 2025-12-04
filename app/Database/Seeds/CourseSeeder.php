<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title'       => 'Introduction to Programming (C++)',
                'description' => 'Learn basic programming concepts using C++: variables, conditions, loops, and functions.',
                'teacher_id'  => 3,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'title'       => 'Web Development with HTML & CSS',
                'description' => 'Build static web pages using HTML5 and CSS3, including layout and responsive design.',
                'teacher_id'  => 3,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'title'       => 'JavaScript Basics',
                'description' => 'Introduction to JavaScript syntax, DOM manipulation, and events for interactive web pages.',
                'teacher_id'  => 3,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'title'       => 'PHP for Beginners',
                'description' => 'Learn server-side programming with PHP: forms, sessions, and MySQL integration.',
                'teacher_id'  => 3,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'title'       => 'Object-Oriented Programming in Java',
                'description' => 'Understand classes, objects, inheritance, and polymorphism using Java.',
                'teacher_id'  => 3,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'title'       => 'Database Fundamentals with MySQL',
                'description' => 'Design relational databases and write SQL queries including joins.',
                'teacher_id'  => 3,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert multiple records
        $this->db->table('courses')->insertBatch($data);
    }
}
