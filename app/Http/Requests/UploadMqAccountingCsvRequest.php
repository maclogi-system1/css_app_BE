<?php

namespace App\Http\Requests;

class UploadMqAccountingCsvRequest extends FormRequest
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
            'mq_accounting' => ['required', 'file', 'max:2048', 'mimes:csv,txt'],
        ];
    }
}
