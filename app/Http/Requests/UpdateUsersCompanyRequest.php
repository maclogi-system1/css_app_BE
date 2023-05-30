<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateUsersCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit_company');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => [
                'required',
                'string',
                'max:150',
                Rule::unique('companies')->ignore($this->user()->company_id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'team_names' => ['nullable', 'array'],
        ];
    }
}
