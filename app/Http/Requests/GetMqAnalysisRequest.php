<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class GetMqAnalysisRequest extends FormRequest
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
            'store_id' => [Rule::requiredIf(! $this->route('storeId')), 'string'],
            'from_date' => ['required', 'date:Y-m', 'after_or_equal:2021-01'],
            'to_date' => ['required', 'date:Y-m', 'after_or_equal:from_date'],
            'compared_from_date' => ['nullable', 'date:Y-m', 'after_or_equal:2021-01'],
            'compared_to_date' => [
                Rule::requiredIf($this->has('compared_from_date')),
                'date:Y-m',
                'after_or_equal:compared_from_date',
            ],
        ];
    }
}
