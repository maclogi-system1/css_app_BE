<?php

namespace App\Support\Traits;

use Illuminate\Validation\Rule;

trait FilterYearMonthValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array
     */
    protected function yearMonthFromToRules(bool $withComparation = false): array
    {
        $rules = [
            'from_date' => ['required', 'date:Y-m', 'after_or_equal:2021-01'],
            'to_date' => ['required', 'date:Y-m', 'after_or_equal:from_date'],
        ];

        if ($withComparation) {
            return $rules + $this->compareYearMonthFromToRules();
        }

        return $rules;
    }

    protected function compareYearMonthFromToRules(): array
    {
        return [
            'compared_from_date' => ['nullable', 'date:Y-m', 'after_or_equal:2021-01'],
            'compared_to_date' => [
                Rule::requiredIf($this->has('compared_from_date')),
                'date:Y-m',
                'after_or_equal:compared_from_date',
            ],
        ];
    }
}
