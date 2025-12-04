<?php
namespace App\Controllers;

use App\Models\UserModel;

class Admin extends BaseController
{
    public function index()
    {
        // Unified admin dashboard now shows Manage Users list,
        // so point /manage-users to the main dashboard.
        return redirect()->to('/dashboard');
    }

    public function addUser()
    {
        helper(['form', 'url']);
        $data = [];

        if (strtolower($this->request->getMethod()) === 'post') {
            $rules = [
                'name'     => 'required|min_length[2]|max_length[100]|alpha_space',
                'email'    => 'required|valid_email|max_length[100]|is_unique[users.email]',
                // At least 6 chars
                'password' => 'required|min_length[6]'
            ];

            $messages = [
                'name' => [
                    'required'    => 'Please enter a valid name (letters and spaces only).',
                    'min_length'  => 'Please enter a valid name (letters and spaces only).',
                    'max_length'  => 'Please enter a valid name (letters and spaces only).',
                    'alpha_space' => 'Name contains invalid characters.'
                ],
                'email' => [
                    'required'   => 'Please enter a valid email address.',
                    'valid_email'=> 'Invalid email format.',
                    'max_length' => 'Please enter a valid email address.',
                    'is_unique'  => 'This email address is already registered.'
                ],
                'password' => [
                    'required'   => 'Password must be at least 6 characters and contain letters and numbers.',
                    'min_length' => 'Password must be at least 6 characters and contain letters and numbers.',
                ],
            ];

            if (!$this->validate($rules, $messages)) {
                // Store validation errors in flashdata so dashboard can show them
                $errorsHtml = $this->validator->listErrors();
                session()->setFlashdata('error', $errorsHtml);
                return redirect()->to('/dashboard');
            } else {
                $userModel = new UserModel();
                $name = trim($this->request->getPost('name'));
                $newUser = [
                    'name'       => $name,
                    'email'      => $this->request->getPost('email'),
                    'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role'       => $this->request->getPost('role') ?? 'student',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($userModel->insert($newUser)) {
                    session()->setFlashdata('success', 'User added successfully!');
                } else {
                    session()->setFlashdata('error', 'Failed to add user.');
                }
                return redirect()->to('/dashboard');
            }
        }
        // For GET requests, the Add User form is shown on the admin dashboard
        return redirect()->to('/dashboard');
    }

    public function deleteUser($id)
    {
        $userModel = new UserModel();
        $currentUserId = session()->get('id');
        $user          = $userModel->find($id);

        // Prevent admin from deleting own account
        if ((int)$id === (int)$currentUserId) {
            session()->setFlashdata('error', 'You cannot delete your own admin account.');
            return redirect()->to('/dashboard');
        }

        // If user is admin, do not allow deletion at all
        if ($user && $user['role'] === 'admin') {
            session()->setFlashdata('error', 'Admin accounts cannot be deleted.');
            return redirect()->to('/dashboard');
        }

        // Soft delete instructors/students using is_deleted flag
        if ($userModel->update($id, ['is_deleted' => 1, 'updated_at' => date('Y-m-d H:i:s')])) {
            session()->setFlashdata('success', 'User marked as deleted.');
        } else {
            session()->setFlashdata('error', 'Failed to mark user as deleted.');
        }
        return redirect()->to('/dashboard');
    }

    public function updateUserRole($id)
    {
        $userModel = new UserModel();
        $currentUserId = session()->get('id');
        $currentUser   = $userModel->find($id);
        $newRole = $this->request->getPost('role');

        // Prevent admin from changing their own role
        if ($currentUser && (int)$id === (int)$currentUserId && $currentUser['role'] === 'admin') {
            session()->setFlashdata('error', 'You cannot change your own admin role.');
            return redirect()->to('/dashboard');
        }

        // Allow only valid roles
        $allowedRoles = ['admin', 'teacher', 'student'];
        if (!in_array($newRole, $allowedRoles, true)) {
            session()->setFlashdata('error', 'Invalid role selected.');
            return redirect()->to('/dashboard');
        }

        if ($userModel->update($id, ['role' => $newRole, 'updated_at' => date('Y-m-d H:i:s')])) {
            session()->setFlashdata('success', 'User role updated successfully!');
        } else {
            session()->setFlashdata('error', 'Failed to update user role.');
        }

        return redirect()->to('/dashboard');
    }
}
