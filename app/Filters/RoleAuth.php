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

        // 🔹 Check login status
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // 🔹 If role restriction exists (like role:admin)
        if ($arguments && count($arguments) > 0) {
            $allowedRole = $arguments[0];
            if ($session->get('role') !== $allowedRole) {
                return redirect()->to('/dashboard')->with('error', 'Access denied.');
            }
        }

        // Continue request if allowed
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action after
    }
}
