<?php

namespace App\Http\Requests;

class GetQueryResultsRequest extends FormRequest
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
            'table' => ['required', 'string'],
            'operator' => ['required', 'string'],
            'conditions' => ['required', 'array'],
            'conditions.*.value' => ['required'],
            'conditions.*.field' => ['required'],
            'conditions.*.operator' => ['required'],
        ];
    }
}
