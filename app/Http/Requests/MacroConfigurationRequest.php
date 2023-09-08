<?php

namespace App\Http\Requests;

use App\Constants\MacroConstant;
use App\Models\PolicyRule;
use App\Rules\CompareDateValid;
use App\Rules\DateValid;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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
            'time_conditions.applicable_date' => ['required'],
            'time_conditions.schedule' => [Rule::requiredIf(fn () => empty($this->input('time_conditions.designation')))],
            'graph' => ['nullable'],
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
        $simulationStartDate = Arr::get($this->simulation, 'simulation_start_date');
        $simulationStartTime = Arr::get($this->simulation, 'simulation_start_time');
        $simulationStartDateTime = Carbon::create($simulationStartDate.' '.$simulationStartTime)
            ->toImmutable();

        $simulationEndDate = Arr::get($this->simulation, 'simulation_end_date');
        $simulationEndTime = Arr::get($this->simulation, 'simulation_end_time');
        $simulationEndDateTime = Carbon::create($simulationEndDate.' '.$simulationEndTime)
            ->toImmutable();

        $simulationRequireIf = Rule::requiredIf(
            fn () => $this->macro_type == MacroConstant::MACRO_TYPE_AI_POLICY_RECOMMENDATION && $this->has('simulation')
        );

        $rules['simulation'] = ['nullable', 'array'];
        $rules['simulation.name'] = [$simulationRequireIf, 'max:100', 'string'];
        $rules['simulation.simulation_start_date'] = [$simulationRequireIf, 'date_format:Y-m-d', new DateValid()];
        $rules['simulation.simulation_start_time'] = [$simulationRequireIf, 'date_format:H:i'];
        $rules['simulation.simulation_end_date'] = [
            $simulationRequireIf,
            'date_format:Y-m-d',
            new DateValid(),
            new CompareDateValid($simulationEndDateTime, 'gt', $simulationStartDateTime),
        ];
        $rules['simulation.simulation_end_time'] = [$simulationRequireIf, 'date_format:H:i'];
        $rules['simulation.simulation_promotional_expenses'] = [
            $simulationRequireIf,
            'integer',
            'between:-2000000000,2000000000',
        ];
        $rules['simulation.simulation_store_priority'] = [$simulationRequireIf, 'decimal:0,6', 'between:-999999,999999'];
        $rules['simulation.simulation_product_priority'] = [$simulationRequireIf, 'decimal:0,6', 'between:-999999,999999'];
        $rules['simulation.policy_rules'] = ['nullable', 'array'];
        $rules['simulation.policy_rules.*.class'] = [$simulationRequireIf, Rule::in(array_keys(PolicyRule::CLASSES))];
        $rules['simulation.policy_rules.*.service'] = [$simulationRequireIf, Rule::in(array_keys(PolicyRule::SERVICES))];
        $rules['simulation.policy_rules.*.value'] = [$simulationRequireIf, 'integer', 'between:-2000000000,2000000000'];
        $rules['simulation.policy_rules.*.condition_1'] = [
            $simulationRequireIf,
            Rule::in(array_keys(PolicyRule::TEXT_INPUT_CONDITIONS)),
        ];
        $rules['simulation.policy_rules.*.condition_value_1'] = ['nullable', 'integer', 'between:-2000000000,2000000000'];
        $rules['simulation.policy_rules.*.condition_2'] = [
            $simulationRequireIf,
            Rule::in(array_keys(PolicyRule::UPLOADABLE_CONDITIONS)),
        ];
        $rules['simulation.policy_rules.*.condition_value_2'] = ['nullable'];
        $rules['simulation.policy_rules.*.condition_3'] = [
            $simulationRequireIf,
            Rule::in(array_keys(PolicyRule::TEXT_INPUT_CONDITIONS)),
        ];
        $rules['simulation.policy_rules.*.condition_value_3'] = ['nullable', 'integer', 'between:-2000000000,2000000000'];
        $rules['simulation.policy_rules.*.attachment_key_1'] = ['nullable', 'string', 'size:16'];
        $rules['simulation.policy_rules.*.attachment_key_2'] = ['nullable', 'string', 'size:16'];
        $rules['simulation.policy_rules.*.attachment_key_3'] = ['nullable', 'string', 'size:16'];
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
