<?php

namespace App\Controllers\DashClient;

use App\Controllers\BaseController;
use App\Models\User\NotificationModel;

class NotificationController extends BaseController
{
    public function unreadCount()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['count' => 0]);
        }

        $model = new NotificationModel();
        $count = $model->getUnreadCount($userId);

        return $this->response->setJSON(['count' => $count]);
    }

    public function markAsRead()
    {
        $userId = session()->get('user_id');
        if (!$userId) return $this->response->setStatusCode(401);

        // Optional: mark specific IDs or all
        // For now, let's say accessing the notification list marks them as read?
        // Or specific action.
        // User didn't ask for list yet, just badge.
        // But to clear the badge, we need a way to mark read.
        
        // Let's assume opening the app or a specific view clears it? 
        // Or maybe just clicking the notification icon (if we had one).
        
        // For now, I'll just provide the endpoint to clear.
        $model = new NotificationModel();
        $model->where('user_id', $userId)->set(['is_read' => 1])->update();
        
        return $this->response->setJSON(['success' => true]);
    }
}
