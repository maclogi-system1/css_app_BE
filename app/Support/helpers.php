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
        if (! app()->environment('testing')) {
            app(ChatworkRepository::class)->sendMessageLog($message, $level);
        }
    }
}

if (! function_exists('convert_fields_to_sjis')) {
    /**
     * Convert fields to sjis encoding.
     */
    function convert_fields_to_sjis(array $fields)
    {
        $result = [];

        foreach ($fields as $field) {
            $result[] = mb_convert_encoding($field, 'SJIS', 'UTF-8');
        }

        return $result;
    }
}

if (! function_exists('convert_sjis_to_utf8')) {
    /**
     * Convert sjis to utf8 encoding.
     */
    function convert_sjis_to_utf8(array $fields)
    {
        $result = [];
        foreach ($fields as $field) {
            $result[] = mb_detect_encoding($field) == 'UTF-8' ? $field : mb_convert_encoding($field, 'UTF-8', 'SJIS');
        }

        return $result;
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
