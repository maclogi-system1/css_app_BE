<?php

namespace App\Repositories\Eloquents;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepository as NotificationRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Support\Arr;

class NotificationRepository extends Repository implements NotificationRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return Notification::class;
    }

    /**
     * Handle create a new notification and link that to chatwork
     * if notification type is "Notification::SEND_TO_SPECIFIED_MEMBER".
     */
    public function create(array $data): ?Notification
    {
        return $this->handleSafely(function () use ($data) {
            $notification = $this->model()->fill(Arr::only($data, ['message', 'room_id', 'type', 'send_to']));
            $notification->save();
            $chatworkIds = Arr::get($data, 'chatwork_ids', []);

            if ($notification->send_to == Notification::SEND_TO_SPECIFIED_MEMBER && ! empty($chatworkIds)) {
                $notification->chatworks()->sync($chatworkIds);
            }

            return $notification;
        }, 'Create notification');
    }
}
