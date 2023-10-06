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
}
