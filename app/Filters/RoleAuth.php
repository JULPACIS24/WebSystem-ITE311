<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('isLoggedIn')) {
            // Redirect to login page if not logged in
            if (!str_starts_with($request->getUri()->getPath(), 'login')) {
                return redirect()->to('/login');
            }
            return;
        }

        //Clean URI - remove index.php if present
        $uri = ltrim(str_replace('index.php/', '', $request->getUri()->getPath()), '/');
        $role = $session->get('role');


        //  Role-based access control
        if ($role === 'admin') {
            // Admin should only access /admin routes
            if (!preg_match('#^admin#', $uri)) {
                return redirect()->to('/admin/dashboard');
            }
        } elseif ($role === 'teacher') {
            // Teacher can only access /teacher routes
            if (!preg_match('#^teacher#', $uri)) {
                return redirect()->to('/teacher/dashboard');
            }
        } elseif ($role === 'student') {
            // Student can only access announcements
            if (!preg_match('#^announcements#', $uri)) {
                return redirect()->to('/announcements');
            }
        }

        // Allow access if matched
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        
    }
}
