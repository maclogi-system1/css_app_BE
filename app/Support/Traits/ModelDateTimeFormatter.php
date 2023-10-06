<?php

namespace App\Support\Traits;

trait ModelDateTimeFormatter
{
    /**
     * Set default datetime format.
     */
    protected function asDateTime($value)
    {
        return parent::asDateTime($value)->format('Y-m-d H:i:s');
    }
}
