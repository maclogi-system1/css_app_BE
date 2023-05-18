<?php

use App\Repositories\Contracts\ChatworkRepository;

if (! function_exists('chatwork_log')) {
    /**
     * Handle sending a message log to chatwork.
     */
    function chatwork_log(string $message, $level = 'info'): void
    {
        app(ChatworkRepository::class)->sendMessageLog($message, $level);
    }
}
