<?php

namespace App\Http\Requests;

use App\Models\UserSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserSettingRequest extends FormRequest
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
            UserSetting::RECEIVATION_KEY => ['nullable', Rule::in(array_keys(UserSetting::RECEIVING_STATES))],
            UserSetting::ONESIGNAL_USER_ID_KEY => ['nullable'],
        ];
    }
}
