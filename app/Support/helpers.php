<?php

use App\Repositories\Contracts\ChatworkRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

if (! function_exists('chatwork_log')) {
    /**
     * Handle sending a message log to chatwork.
     */
    function chatwork_log(string $message, $level = 'info'): void
    {
        app(ChatworkRepository::class)->sendMessageLog($message, $level);
    }
}

if (! function_exists('to_array')) {
    /**
     * Results array of items from Collection or Arrayable.
     */
    function to_array($items): array
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return (array) $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }
}
