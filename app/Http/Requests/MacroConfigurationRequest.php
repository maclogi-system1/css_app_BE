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
        $rules = [
            'store_ids' => ['required', 'string'],
            'conditions' => ['required', 'array'],
            'conditions.table' => ['required', 'string'],
            'conditions.operator' => ['required', 'string'],
            'conditions.conditions' => ['required', 'array'],
            'time_conditions' => ['required'],
            'time_conditions.applicable_date' => ['required', 'after_or_equal:'.now()->format('Y-m-d')],
            'time_conditions.schedule' => [Rule::requiredIf(fn () => empty($this->input('time_conditions.designation')))],
            'graph' => ['nullable'],
            'users_teams' => ['nullable', 'string'],
        ];

        $this->setRuleForSimulation($rules);
        $this->setRuleForPolicies($rules);
        $this->setRuleForTasks($rules);

        if ($id = $this->route('macroConfiguration')) {
            $rules['name'] = [
                'required',
                'string',
                Rule::unique('macro_configurations')->ignore($id)->whereNull('deleted_at'),
            ];
        } else {
            $rules['name'] = [
                'required',
                'string',
                Rule::unique('macro_configurations')->whereNull('deleted_at'),
            ];
            $rules['macro_type'] = [
                'required',
                Rule::in(array_keys(MacroConstant::MACRO_TYPES)),
            ];
        }

        return $rules;
    }

    protected function setRuleForSimulation(array &$rules)
    {
        $rules['simulation'] = ['nullable'];
    }

    protected function setRuleForPolicies(array &$rules)
    {
        $rules['policies'] = ['nullable', 'array'];
    }

    protected function setRuleForTasks(array &$rules)
    {
        $rules['tasks'] = ['nullable', 'array'];
    }
}
