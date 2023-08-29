<?php

namespace App\Http\Requests;

use App\Constants\MacroConstant;
use Illuminate\Validation\Rule;

class MacroConfigurationRequest extends FormRequest
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
        if ($id = $this->route('macroConfiguration')) {
            return [
                'name' => [
                    'nullable',
                    'string',
                    Rule::unique('macro_configurations')->ignore($id)->whereNull('deleted_at'),
                ],
                'conditions' => ['nullable'],
                'time_conditions' => ['nullable'],
                'macro_type' => ['nullable', Rule::in(array_keys(MacroConstant::MACRO_TYPES))],
                'graph' => ['nullable'],
            ];
        }

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('macro_configurations')->whereNull('deleted_at'),
            ],
            'conditions' => ['required'],
            'time_conditions' => ['required'],
            'macro_type' => ['required', Rule::in(array_keys(MacroConstant::MACRO_TYPES))],
            'graph' => ['nullable'],
        ];
    }
}
