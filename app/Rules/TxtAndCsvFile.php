<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TxtAndCsvFile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            $value->getMimeType() == 'text/plain'
            && ! in_array($value->getClientOriginalExtension(), ['csv', 'txt'])
        ) {
            $fail(__('validation.mimes', ['attribute' => $attribute, 'values' => 'csv, txt']));
        }
    }
}
