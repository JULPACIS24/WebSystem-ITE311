<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifications extends BaseController
{
    public function get()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $userId = (int) $session->get('id');

        $model = new NotificationModel();

        $count = $model->getUnreadCount($userId);
        $list  = $model->getNotificationsForUser($userId);

        return $this->response->setJSON([
            'status' => 'success',
            'count'  => $count,
            'items'  => $list,
        ]);
    }

    public function mark_as_read($id)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id    = (int) $id;
        $model = new NotificationModel();

        if ($model->markAsRead($id)) {
            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setStatusCode(500)
            ->setJSON(['status' => 'error', 'message' => 'Failed to update notification.']);
    }
}
