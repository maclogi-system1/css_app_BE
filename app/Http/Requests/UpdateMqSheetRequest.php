<?php

namespace App\Http\Requests;

use App\Models\MqSheet;
use Closure;
use Illuminate\Validation\Rule;

class UpdateMqSheetRequest extends FormRequest
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
            'store_id' => ['required', 'string', 'max:255'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('mq_sheets')
                    ->where(function ($query) {
                        $query->where('store_id', $this->input('store_id'))
                            ->where('id', '=', $this->route('mqSheet'));
                    }),
                function (string $attribute, mixed $value, Closure $fail) {
                    $mqSheet = is_string($this->route('mqSheet'))
                        ? MqSheet::findOrFail($this->route('mqSheet'))
                        : $this->route('mqSheet');

                    if ($mqSheet->isDefault() && $value != $mqSheet->name) {
                        $fail('You cannot update a default sheet.');
                    }
                },
            ],
        ];
    }
}
