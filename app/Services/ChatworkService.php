<?php

namespace App\Services;

use Illuminate\Support\Collection;
use wataridori\ChatworkSDK\ChatworkApi;
use wataridori\ChatworkSDK\ChatworkRoom;
use wataridori\ChatworkSDK\ChatworkSDK;
use wataridori\ChatworkSDK\ChatworkUser;

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

    /**
     * Get account info.
     *
     * @return \Illuminate\Support\Collection
     */
    public function me(): Collection
    {
        return collect($this->chatworkApi->me());
    }

    /**
     * Get a list of the room.
     *
     * @param  array  $filter
     * @return \Illuminate\Support\Collection
     */
    public function getRooms(array $filter = []): Collection
    {
        $rooms = collect($this->chatworkApi->getRooms());

        if (! empty($filter)) {
            foreach ($filter as $key => $value) {
                $rooms = $rooms->where($key, $value);
            }
        }

        return $rooms;
    }

    /**
     * Get room detail by id.
     *
     * @param  string|int  $roomId
     * @return \Illuminate\Support\Collection
     */
    public function getRoomById(string|int $roomId): Collection
    {
        return collect($this->chatworkApi->getRoomById($roomId));
    }

    /**
     * Send a message to the room for all members.
     *
     * @param  string  $message
     * @param  string|int|null  $roomId
     * @return void
     */
    public function sendMessageToAll($message, ?string $roomId = null): void
    {
        $this->chatworkRoom($roomId)->sendMessageToAll($message);
    }

    /**
     * Send a message to the room for a few specified members by chatwork id list.
     *
     * @param  array  $chatworkIds
     * @param  string $message
     * @param  string|null  $roomId
     * @return void
     */
    public function sendMessageToListByChatworkId(array $chatworkIds, string $message, ?string $roomId = null): void
    {
        $members = $this->getMembersInRoom($roomId)->whereIn('chatwork_id', $chatworkIds)->toArray();
        $this->chatworkRoom($roomId)->sendMessageToList($members, $message);
    }

    /**
     * Send a message to the room.
     *
     * @param  string $message
     * @param  string|null  $roomId
     * @return void
     */
    public function sendMessage(string $message, ?string $roomId = null): void
    {
        $this->chatworkRoom($roomId)->sendMessage($message);
    }

    /**
     * Get a list of the member in a specified room.
     *
     * @param  string|null  $roomId
     * @return \Illuminate\Support\Collection
     */
    public function getMembersInRoom(?string $roomId = null): Collection
    {
        return collect($this->chatworkRoom($roomId)->getMembers());
    }

    /**
     * Find member by chatwork id.
     *
     * @param  string  $chatworkId
     * @param  string|null  $roomId
     * @return \wataridori\ChatworkSDK\ChatworkUser
     */
    public function findMemberByChatworkId($chatworkId, ?string $roomId = null): ChatworkUser
    {
        $members = $this->getMembersInRoom($roomId);

        return $members->where('chatwork_id', $chatworkId)->first();
    }

    public function chatworkRoom(?string $roomId = null)
    {
        return empty($roomId) ? $this->chatworkRoom : new ChatworkRoom($roomId);
    }
}
