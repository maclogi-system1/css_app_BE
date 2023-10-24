<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Contracts\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [Company::class]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9-_\ \.]+$/', 'unique:companies'],
            'name' => ['required', 'string', 'max:150'],
            'team_names' => ['nullable', 'array'],
        ];
    }
}
