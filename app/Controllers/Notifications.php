<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use CodeIgniter\HTTP\ResponseInterface;

class Notifications extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    public function get(): ResponseInterface
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized',
                ]);
        }

        $userId = (int) $this->session->get('id');

        $notifications = $this->notificationModel->getNotificationsForUser($userId);
        $unreadCount   = $this->notificationModel->getUnreadCount($userId);

        return $this->response->setJSON([
            'success'        => true,
            'unread_count'   => $unreadCount,
            'notifications'  => $notifications,
        ]);
    }

    public function mark_as_read(int $id): ResponseInterface
    {
        if (!$this->session->get('isLoggedIn')) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized',
                ]);
        }

        $userId = (int) $this->session->get('id');
        $notification = $this->notificationModel->find($id);

        if (!$notification || (int) $notification['user_id'] !== $userId) {
            log_message('warning', 'Notification mark request not allowed', [
                'notificationId' => $id,
                'userId'         => $userId,
                'notification'   => $notification,
            ]);
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'success' => false,
                    'message' => 'Notification not found',
                ]);
        }

        $result = $this->notificationModel->markAsRead($id);

        if (!$result) {
            log_message('error', 'Notification mark update failed', [
                'notificationId' => $id,
                'userId'         => $userId,
                'errors'         => $this->notificationModel->errors(),
            ]);
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON([
                    'success' => false,
                    'message' => 'Failed to update notification',
                ]);
        }

        log_message('info', 'Notification marked as read', [
            'notificationId' => $id,
            'userId'         => $userId,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }
}
