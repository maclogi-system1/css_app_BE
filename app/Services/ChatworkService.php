<?php

namespace App\Services;

use wataridori\ChatworkSDK\ChatworkApi;
use wataridori\ChatworkSDK\ChatworkRequest;
use wataridori\ChatworkSDK\ChatworkRoom;
use wataridori\ChatworkSDK\ChatworkSDK;

class ChatworkService extends Service
{
    private $chatworkApi;
    private $chatworkRoom;

    public function __construct(?string $roomId = null, ?string $apiKey = null)
    {
        ChatworkSDK::setApiKey($apiKey ?? config('chatwork.api_key'));
        $this->chatworkApi = new ChatworkApi();
        $this->chatworkRoom = new ChatworkRoom($roomId ?? config('chatwork.room_id'));
    }

    public function me()
    {
        return collect($this->chatworkApi->me());
    }

    public function getRooms(array $filter = [])
    {
        $rooms = collect($this->chatworkApi->getRooms());

        if (! empty($filter)) {
            foreach ($filter as $key => $value) {
                $rooms = $rooms->where($key, $value);
            }
        }

        return $rooms;
    }

    public function getRoomById($roomId)
    {
        return collect($this->chatworkApi->getRoomById($roomId));
    }

    public function sendMessageToAll($message, ?string $roomId = null)
    {
        $this->chatworkRoom($roomId)->sendMessageToAll($message);
    }

    public function sendMessageToListByAccountId(array $chatworkIds, $message, ?string $roomId = null)
    {
        $members = $this->getMembersInRoom($roomId)->whereIn('account_id', $chatworkIds)->toArray();
        $this->chatworkRoom($roomId)->sendMessageToList($members, $message);
    }

    public function sendMessage($message, ?string $roomId = null)
    {
        $this->chatworkRoom($roomId)->sendMessage($message);
    }

    public function getMembersInRoom(?string $roomId = null)
    {
        return collect($this->chatworkRoom($roomId)->getMembers());
    }

    public function chatworkRoom(?string $roomId = null)
    {
        return empty($roomId) ? $this->chatworkRoom : new ChatworkRoom($roomId);
    }
}
