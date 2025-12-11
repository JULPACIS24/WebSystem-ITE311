<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// --------------------------------------------------------------------
// Admin Management Routes
// --------------------------------------------------------------------
$routes->get('/manage-users', 'Admin::index');
$routes->get('/manage-courses', 'Admin::manageCourses');
$routes->get('/academic-management', 'Admin::academicManagement');
$routes->get('/course-schedule', 'Admin::courseSchedule');
$routes->get('/assign-teacher', 'Admin::assignTeacherDashboard');
$routes->get('/course-offerings', 'Admin::courseOfferingsDashboard');

// Admin - User Management Actions
$routes->match(['get', 'post'], '/add-user', 'Admin::addUser');
$routes->post('/delete-user/(:num)', 'Admin::deleteUser/$1');
$routes->post('/restore-user/(:num)', 'Admin::restoreUser/$1');
$routes->post('/update-user-role/(:num)', 'Admin::updateUserRole/$1');

// Admin - Course Management Actions
$routes->post('/admin/course/add', 'Admin::addCourse');
$routes->get('/admin/course/edit/(:num)', 'Admin::editCourse/$1');
$routes->post('/admin/course/update/(:num)', 'Admin::updateCourse/$1');
$routes->post('/admin/course/delete/(:num)', 'Admin::deleteCourse/$1');
$routes->post('/admin/course/schedule/(:num)', 'Admin::updateCourseSchedule/$1');

// Admin - Academic Settings
$routes->post('/admin/assign-teacher', 'Admin::assignTeacherToCourse');
$routes->post('/admin/academic-year/save', 'Admin::saveAcademicYear');
$routes->post('/admin/semester/save', 'Admin::saveSemester');
$routes->post('/admin/year-level/save', 'Admin::saveDefaultYearLevel');
$routes->post('/admin/year-level/assign', 'Admin::assignYearLevelToCourse');

// Public Pages
$routes->get('/', 'Home::index');
$routes->get('/index', 'Home::index');
$routes->get('/about', 'Home::about');
$routes->get('/contact', 'Home::contact');

// Authentication Routes
$routes->get('/register', 'Auth::register');
$routes->post('/register', 'Auth::register');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::login');
$routes->get('/dashboard', 'Auth::dashboard');
$routes->get('/logout', 'Auth::logout');

// Student / Course Enrollment Routes
$routes->post('/course/enroll', 'Course::enroll');
$routes->get('/mycourses', 'Course::index');
$routes->get('/courses', 'Course::index');

// Course search routes (GET and POST) - /courses/search
$routes->get('/courses/search', 'Course::search');
$routes->post('/courses/search', 'Course::search');

// Teacher Routes
$routes->get('/upload-lessons', 'Teacher::uploadLessons');
$routes->post('/teacher/enroll-student', 'Teacher::enrollStudent');
$routes->post('/teacher/approve-enrollment/(:num)', 'Teacher::approveEnrollment/$1');
$routes->post('/teacher/reject-enrollment/(:num)', 'Teacher::rejectEnrollment/$1');

// Materials Routes
$routes->get('/materials/upload/(:num)', 'Materials::uploadForm/$1');
$routes->post('/materials/upload/(:num)', 'Materials::upload/$1');
$routes->post('/materials/delete/(:num)', 'Materials::delete/$1');
$routes->post('/materials/restore/(:num)', 'Materials::restore/$1');
$routes->get('/materials/download/(:num)', 'Materials::download/$1');

// Notifications API
$routes->get('/notifications', 'Notifications::get');
$routes->post('/notifications/mark_read/(:num)', 'Notifications::mark_as_read/$1');

