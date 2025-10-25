<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['user_id', 'message', 'is_read', 'created_at'];
    protected $useTimestamps    = false;

    public function getUnreadCount(int $userId): int
    {
        return $this->builder()
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    public function getNotificationsForUser(int $userId, int $limit = 5): array
    {
        $builder = $this->builder();

        return $builder
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function markAsRead(int $notificationId): bool
    {
        return (bool) $this->builder()
            ->where('id', $notificationId)
            ->update(['is_read' => 1]);
    }
}
