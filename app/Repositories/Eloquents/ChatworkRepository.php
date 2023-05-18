<?php

namespace App\Repositories\Eloquents;

use App\Models\Chatwork;
use App\Models\Notification;
use App\Repositories\Contracts\ChatworkRepository as ChatworkRepositoryContract;
use App\Repositories\Contracts\NotificationRepository;
use App\Repositories\Repository;
use App\Services\ChatworkService;
use Illuminate\Support\Collection;

class ChatworkRepository extends Repository implements ChatworkRepositoryContract
{
    public function __construct(
        private ChatworkService $chatworkService,
        private NotificationRepository $notificationRepository
    ) {}

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return Chatwork::class;
    }

    /**
     * Get a list of the member in a specified room.
     */
    public function membersList($roomId): Collection
    {
        return $this->chatworkService->getMembersInRoom($roomId);
    }

    /**
     * Handle sending messages to the room for all members or a few specified members.
     */
    public function sendMessage($roomId, string $message, array $sendTo = []): ?bool
    {
        return $this->handleSafely(function () use ($roomId, $message, $sendTo) {
            $notificationData = [
                'message' => $message,
                'room_id' => $roomId,
                'type' => 'info',
            ];
            $chatworks = $this->model()->whereIn('chatwork_id', $sendTo)->get();

            if (empty($sendTo)) {
                $notificationData['send_to'] = $this->handleSendMessage($message, $roomId);
            } elseif (array_search('all', $sendTo) === false) {
                $notificationData['send_to'] = $this->handleSendMessageToMembers($message, $roomId, $chatworks);
            } else {
                $notificationData['send_to'] = $this->handleSendMessageToAll($message, $roomId);
            }

            $this->notificationRepository->create($notificationData + [
                'chatwork_ids' => $chatworks->pluck('id')->toArray()
            ]);

            return true;
        }, 'Send message');
    }

    /**
     * Handle sending a message and return notification type is "Notification::SEND_TO_NORMAL".
     */
    private function handleSendMessage($message, $roomId): string
    {
        $this->chatworkService->sendMessage($message, $roomId);

        return Notification::SEND_TO_NORMAL;
    }

    /**
     * Handle sending a message and return notification type is "Notification::SEND_TO_SPECIFIED_MEMBER".
     */
    private function handleSendMessageToMembers($message, $roomId, $chatworks): string
    {
        if ($chatworks->isNotEmpty()) {
            $this->chatworkService->sendMessageToListByChatworkId(
                $chatworks->pluck('chatwork_id')->toArray(),
                $message,
                $roomId
            );
        }

        return Notification::SEND_TO_SPECIFIED_MEMBER;
    }

    /**
     * Handle sending a message and return notification type is "Notification::SEND_TO_ALL".
     */
    private function handleSendMessageToAll($message, $roomId): string
    {
        $this->chatworkService->sendMessageToAll($message, $roomId);

        return Notification::SEND_TO_ALL;
    }

    /**
     * Handle sending a message log to chatwork.
     */
    public function sendMessageLog(string $message, string $level = 'info'): void
    {
        $logTime = now()->format('Y-m-d H:i:s P');
        $levelLog = str($level)->upper()->prepend(env('APP_ENV', 'local').'.')->toString();

        $this->chatworkService->sendMessage("[{$logTime}] {$levelLog}: {$message}");

        $this->notificationRepository->create([
            'message' => $message,
            'room_id' => config('chatwork.room_id'),
            'type' => "log_{$level}",
        ]);
    }
}
