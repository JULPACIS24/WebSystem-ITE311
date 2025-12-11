<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/manage-users', 'Admin::index');
$routes->get('/manage-courses', 'Admin::manageCourses');
$routes->get('/academic-management', 'Admin::academicManagement');
$routes->get('/course-schedule', 'Admin::courseSchedule');
$routes->get('/assign-teacher', 'Admin::assignTeacherDashboard');
$routes->get('/course-offerings', 'Admin::courseOfferingsDashboard');
$routes->match(['get', 'post'], '/add-user', 'Admin::addUser');
$routes->post('/delete-user/(:num)', 'Admin::deleteUser/$1');
$routes->post('/restore-user/(:num)', 'Admin::restoreUser/$1');
$routes->post('/update-user-role/(:num)', 'Admin::updateUserRole/$1');
$routes->post('/admin/course/add', 'Admin::addCourse');
$routes->get('/admin/course/edit/(:num)', 'Admin::editCourse/$1');
$routes->post('/admin/course/update/(:num)', 'Admin::updateCourse/$1');
$routes->post('/admin/course/delete/(:num)', 'Admin::deleteCourse/$1');
$routes->post('/admin/course/schedule/(:num)', 'Admin::updateCourseSchedule/$1');
$routes->post('/admin/assign-teacher', 'Admin::assignTeacherToCourse');
$routes->post('/admin/academic-year/save', 'Admin::saveAcademicYear');
$routes->post('/admin/semester/save', 'Admin::saveSemester');
$routes->post('/admin/year-level/save', 'Admin::saveDefaultYearLevel');
$routes->post('/admin/year-level/assign', 'Admin::assignYearLevelToCourse');
$routes->get('/', 'Home::index');
$routes->get('/index', 'Home::index');
$routes->get('/about', 'Home::about');
$routes->get('/contact', 'Home::contact');
$routes->get('/register', 'Auth::register');
$routes->post('/register', 'Auth::register');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::login');
$routes->get('/dashboard', 'Auth::dashboard');
$routes->get('/logout', 'Auth::logout');
$routes->post('/course/enroll', 'Course::enroll');
$routes->get('/mycourses', 'Course::index');

// Teacher routes
$routes->get('/upload-lessons', 'Teacher::uploadLessons');
$routes->post('/teacher/enroll-student', 'Teacher::enrollStudent');
$routes->post('/teacher/approve-enrollment/(:num)', 'Teacher::approveEnrollment/$1');
$routes->post('/teacher/reject-enrollment/(:num)', 'Teacher::rejectEnrollment/$1');



