<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'user_id',
        'message',
        'is_read',
        'created_at',
    ];

    public function getUnreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    public function getNotificationsForUser(int $userId, int $limit = 5): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit, 0);
    }

    public function markAsRead(int $notificationId): bool
    {
        return (bool) $this->update($notificationId, ['is_read' => 1]);
    }
}
