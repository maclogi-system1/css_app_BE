<?php

namespace App\Rules;

use App\Repositories\Contracts\MqSheetRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LimitNumberMqSheets implements ValidationRule
{
    public function __construct(
        protected string $storeId,
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var \App\Repositories\Contracts\MqSheetRepository */
        $mqSheetRepository = app(MqSheetRepository::class);

        if ($mqSheetRepository->totalMqSheetInStore($this->storeId) >= 5) {
            $fail('A maximum of 5 sheets can only be created.');
        }
    }
}
