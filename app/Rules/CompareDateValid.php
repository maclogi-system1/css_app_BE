<?php

namespace App\Rules;

use Carbon\CarbonInterface;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompareDateValid implements ValidationRule
{
    public function __construct(
        protected CarbonInterface $compare,
        protected string $compareType = 'gt',
        protected CarbonInterface $compareWith,
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $failed = false;
        $message = '';

        switch ($this->compareType) {
            case 'gt':
                $failed = $this->compare->lte($this->compareWith)
                    || $this->compare->diffInHours($this->compareWith) <= 24;
                $message = __('validation.after', [
                    'attribute' => $attribute,
                    'date' => $this->compareWith->addHour(24)->format('Y/m/d H:i'),
                ]);

                break;
            case 'gte':
                $failed = $this->compare->lt($this->compareWith)
                    || $this->compare->diffInHours($this->compareWith) < 24;
                $message = __('validation.after_or_equal', [
                    'attribute' => $attribute,
                    'date' => $this->compareWith->addHour(24)->format('Y/m/d H:i'),
                ]);

                break;
            case 'lt':
                $failed = $this->compare->gte($this->compareWith)
                    || $this->compare->diffInHours($this->compareWith) >= 24;
                $message = __('validation.before', [
                    'attribute' => $attribute,
                    'date' => $this->compareWith->addHour(24)->format('Y/m/d H:i'),
                ]);

                break;
            case 'lte':
                $failed = $this->compare->gt($this->compareWith)
                    || $this->compare->diffInHours($this->compareWith) > 24;
                $message = __('validation.before_or_equal', [
                    'attribute' => $attribute,
                    'date' => $this->compareWith->addHour(24)->format('Y/m/d H:i'),
                ]);

                break;
        }

        if ($failed) {
            $fail($message);
        }
    }
}
