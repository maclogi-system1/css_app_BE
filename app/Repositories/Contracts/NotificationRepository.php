<?php

namespace App\Repositories\Contracts;

use App\Models\Notification;

interface NotificationRepository extends Repository
{
    /**
     * Handle create a new notification and link that to chatwork
     * if notification type is "Notification::SEND_TO_SPECIFIED_MEMBER".
     */
    public function create(array $data): ?Notification;
}
