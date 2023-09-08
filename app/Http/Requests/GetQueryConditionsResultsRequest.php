<?php

namespace App\Http\Requests;

class GetQueryConditionsResultsRequest extends FormRequest
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
            'store_ids' => ['required', 'string'],
            'conditions' => ['required', 'array'],
            'conditions.table' => ['required', 'string'],
            'conditions.operator' => ['required', 'string'],
            'conditions.conditions' => ['required', 'array'],
        ];
    }
}
