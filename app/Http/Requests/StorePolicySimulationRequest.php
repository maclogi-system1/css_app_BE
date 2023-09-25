<?php

namespace App\Http\Requests;

use App\Models\PolicyRule;
use App\Rules\CompareDateValid;
use App\Rules\DateValid;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StorePolicySimulationRequest extends FormRequest
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
        $simulationStartDate = Carbon::create($this->simulation_start_date.' '.$this->simulation_start_time)
            ->toImmutable();
        $simulationEndDate = Carbon::create($this->simulation_end_date.' '.$this->simulation_end_time)
            ->toImmutable();

        return [
            'name' => ['required', 'max:100', 'string'],
            'store_id' => [Rule::requiredIf(fn () => ! $this->route('storeId')), 'max:100', 'string'],
            'simulation_start_date' => ['required', 'date_format:Y-m-d', new DateValid()],
            'simulation_start_time' => ['required', 'date_format:H:i'],
            'simulation_end_date' => [
                'required',
                'date_format:Y-m-d',
                new DateValid(),
                new CompareDateValid($simulationEndDate, 'gt', $simulationStartDate),
            ],
            'simulation_end_time' => ['required', 'date_format:H:i'],
            'simulation_promotional_expenses' => ['required', 'integer', 'between:-2000000000,2000000000'],
            'simulation_store_priority' => ['required', 'decimal:0,6', 'between:-999999,999999'],
            'simulation_product_priority' => ['required', 'decimal:0,6', 'between:-999999,999999'],
            'policy_rules' => ['nullable', 'array'],
            'policy_rules.*.class' => ['required', Rule::in(array_keys(PolicyRule::CLASSES))],
            'policy_rules.*.service' => ['required', Rule::in(array_keys(PolicyRule::SERVICES))],
            'policy_rules.*.value' => ['required', 'integer', 'between:-2000000000,2000000000'],
            'policy_rules.*.condition_1' => ['required', Rule::in(array_keys(PolicyRule::TEXT_INPUT_CONDITIONS))],
            'policy_rules.*.condition_value_1' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
            'policy_rules.*.condition_2' => ['required', Rule::in(array_keys(PolicyRule::UPLOADABLE_CONDITIONS))],
            'policy_rules.*.condition_value_2' => ['nullable'],
            'policy_rules.*.condition_3' => ['required', Rule::in(array_keys(PolicyRule::TEXT_INPUT_CONDITIONS))],
            'policy_rules.*.condition_value_3' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
            'policy_rules.*.attachment_key_1' => ['nullable', 'string', 'size:16'],
            'policy_rules.*.attachment_key_2' => ['nullable', 'string', 'size:16'],
            'policy_rules.*.attachment_key_3' => ['nullable', 'string', 'size:16'],
        ];
    }
}
