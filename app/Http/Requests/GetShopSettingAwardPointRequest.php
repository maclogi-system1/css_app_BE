<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class GetShopSettingAwardPointRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required'],
            'from_date' => ['nullable', 'date_format:Y-m'],
            'to_date' => ['nullable', 'required_with:from_date', 'date_format:Y-m'],
        ];
    }
}
