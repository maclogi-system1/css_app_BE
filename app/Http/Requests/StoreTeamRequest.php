<?php

namespace App\Http\Requests;

class StoreTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create_team');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required'],
            'name' => ['required', 'string', 'max:255', 'unique:teams'],
            'users' => ['nullable', 'array'],
        ];
    }
}
