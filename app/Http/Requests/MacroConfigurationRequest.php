<?php

namespace App\Http\Requests;

use App\Constants\MacroConstant;
use App\Models\MacroConfiguration;
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
        if ($this->id) {
            return [
                'conditions' => ['nullable'],
                'time_conditions' => ['nullable'],
                'macro_type' => ['nullable', Rule::in(MacroConstant::MACRO_ARRAY)],
                'id' => [
                    'required',
                    'exists:macro_configurations,id', // Check if 'id' exists in the 'macroConfiguration' table
                ],
            ];
        }else{
            return [
                'conditions' => ['required'],
                'time_conditions' => ['required'],
                'macro_type' => ['required', Rule::in(MacroConstant::MACRO_ARRAY)],
            ];
        }
    }
}
