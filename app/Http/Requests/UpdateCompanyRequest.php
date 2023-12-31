<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', [$this->route('company')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => [
                'required',
                'string',
                'max:32',
                'regex:/^[a-zA-Z0-9-_\ \.]+$/',
                Rule::unique('companies')->ignore($this->route('company')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'team_names' => ['nullable', 'array'],
        ];
    }
}
