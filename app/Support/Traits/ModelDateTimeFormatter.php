<?php

namespace App\Support\Traits;

use Illuminate\Support\Carbon;

trait ModelDateTimeFormatter
{
    /**
     * Set default datetime format.
     */
    protected function asDateTime($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    /**
     * Set default serializeDate format.
     */
    protected function serializeDate($date): ?string
    {
        if ($date instanceof Carbon) {
            return $date->format('Y-m-d H:i:s');
        }

        return ($date != null) ? Carbon::parse($date)->format('Y-m-d H:i:s') : null;
    }
}
