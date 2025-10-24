<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ====================================================
// 🔹 PUBLIC ROUTES
// ====================================================
$routes->get('/', 'Home::index');
$routes->get('index', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// ====================================================
// 🔹 AUTHENTICATION
// ====================================================
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::register');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

// ====================================================
// 🔹 DASHBOARD (redirect per role)
// ====================================================
$routes->get('dashboard', 'Auth::dashboard');

// ====================================================
// 🧩 ADMIN ROUTES
// ====================================================
$routes->group('admin', ['filter' => 'role:admin'], function ($routes) {

    // Admin dashboard
    $routes->get('dashboard', 'Admin::dashboard');

    // ✅ Direct upload page for admin (no course ID)
    $routes->get('upload', 'Materials::upload');
    $routes->post('upload', 'Materials::upload');

    // ✅ Upload tied to a course
    $routes->get('course/(:num)/upload', 'Materials::upload/$1');
    $routes->post('course/(:num)/upload', 'Materials::upload/$1');
});

// ====================================================
// 🧩 TEACHER ROUTES
// ====================================================
$routes->group('teacher', ['filter' => 'role:teacher'], function ($routes) {

    // Teacher dashboard
    $routes->get('dashboard', 'Teacher::dashboard');

    // ✅ Direct upload page for teacher (no course ID)
    $routes->get('upload', 'Materials::upload');
    $routes->post('upload', 'Materials::upload');

    // ✅ Upload tied to a course
    $routes->get('course/(:num)/upload', 'Materials::upload/$1');
    $routes->post('course/(:num)/upload', 'Materials::upload/$1');
});

// ====================================================
// 🧩 STUDENT ROUTES
// ====================================================
$routes->group('student', ['filter' => 'role:student'], function ($routes) {
    $routes->get('dashboard', 'Student::dashboard');
    $routes->post('course/enroll', 'Course::enroll');
    $routes->get('mycourses', 'Course::index');
});

// ====================================================
// 🧩 MATERIALS ROUTES (shared by all roles)
// ====================================================
$routes->get('materials/delete/(:num)', 'Materials::delete/$1');
$routes->get('materials/download/(:num)', 'Materials::download/$1');
