<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', [User::class, $this->route('user')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'company_id' => ['required', 'integer'],
            'roles' => ['required', 'array'],
            'chatwork_account_id' => ['nullable', 'max:10'],
            'teams' => ['required', 'array'],
            'profile_photo_path' => [
                'nullable',
                'image',
                'max:'.config('filesystems.profile_photo_max', 2 * pow(2, 10)), // default 2MB
            ],
        ];
    }
}
