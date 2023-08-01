<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class DateValid implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $dateTime = new Carbon($value);

            if ($dateTime->year < 1900) {
                $fail(__('validation.custom.min_year', ['year' => 1900, 'attribute' => $attribute]));
            } elseif ($dateTime->year > 3000) {
                $fail(__('validation.custom.max_year', ['year' => 2999, 'attribute' => $attribute]));
            }
        } catch (\Throwable $e) {
            $fail(__('validation.date'));
        }
    }
}
