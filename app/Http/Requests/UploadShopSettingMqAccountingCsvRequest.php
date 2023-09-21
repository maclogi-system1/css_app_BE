<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class UploadShopSettingMqAccountingCsvRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:2048', 'mimes:csv,txt'],
        ];
    }
}
