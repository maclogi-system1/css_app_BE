<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'display_name' => [
                'required',
                'string',
                'max:125',
                Rule::unique('permissions')->ignore($this->route('permission')),
            ],
        ];
    }
}
