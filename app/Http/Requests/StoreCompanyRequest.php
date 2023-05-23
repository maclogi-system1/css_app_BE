<?php

namespace App\Http\Requests;

use App\Support\Traits\PasswordValidationRules;

class StoreCompanyRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create_company');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'string', 'max:150', 'unique:companies'],
            'name' => ['required', 'string', 'max:150'],
            'password' => $this->passwordRules(),
            'team_names' => ['nullable', 'array'],
        ];
    }
}
