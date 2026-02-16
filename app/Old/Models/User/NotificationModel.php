<?php

namespace App\Models\User;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications_tb';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'from_user_id',
        'type',
        'title',
        'message',
        'link',
        'is_read',
        'created_at'
    ];

    protected $useTimestamps = false; // We manually set created_at

    /**
     * Get unread count for a user
     */
    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    /**
     * Add notification
     */
    public function addNotification($userId, $type, $title, $message, $link, $fromUserId = null)
    {
        return $this->insert([
            'user_id'      => $userId,
            'from_user_id' => $fromUserId,
            'type'         => $type,
            'title'        => $title,
            'message'      => $message,
            'link'         => $link,
            'created_at'   => date('Y-m-d H:i:s')
        ]);
    }
}
