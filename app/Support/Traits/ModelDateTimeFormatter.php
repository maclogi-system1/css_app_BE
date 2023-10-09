<?php

namespace App\Support\Traits;

use Illuminate\Support\Carbon;

trait ModelDateTimeFormatter
{
    public function getAttributeValue($key)
    {
        $castsDateTime = array_filter(parent::getCasts(), function ($value) {
            return str_contains($value, 'datetime');
        });

        $value = parent::getAttributeValue($key);

        if (in_array($key, array_keys($castsDateTime))) {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }
}
