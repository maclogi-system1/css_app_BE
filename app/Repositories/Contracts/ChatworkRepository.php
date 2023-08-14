<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ChatworkRepository extends Repository
{
    /**
     * Get room detail by id.
     */
    public function roomDetail($roomId): Collection;

    /**
     * Get a list of the member in a specified room.
     */
    public function listMembersInRoom($roomId): Collection;

    /**
     * Handle sending messages to the room for all members or a few specified members.
     */
    public function sendMessage($roomId, string $message, array $sendTo = []): ?bool;

    /**
     * Handle sending a message log to chatwork.
     */
    public function sendMessageLog(string $message, string $level = 'info'): void;
}
