<?php

namespace App\Repositories\Contracts;

interface ChatworkRepository extends Repository
{
    /**
     * Handle sending messages to the room for all members or a few specified members.
     */
    public function sendMessage($roomId, string $message, array $sendTo = []): ?bool;
}
