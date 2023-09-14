<?php

namespace App\Http\Requests;

use App\Models\MqSheet;
use Closure;
use Illuminate\Validation\Rule;

class StoreMqSheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'string', 'max:255'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('mq_sheets')
                    ->where(function ($query) {
                        $query->where('store_id', $this->input('store_id'));
                    }),
                function (string $attributes, mixed $value, Closure $fail) {
                    $mqSheetCount = MqSheet::where('store_id', $this->input('store_id'))->count();

                    if ($mqSheetCount >= 5) {
                        $fail('A maximum of 5 sheets can only be created.');
                    }
                },
            ],
        ];
    }
}
