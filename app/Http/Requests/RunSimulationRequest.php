<?php

namespace App\Http\Requests;

use App\Models\Policy;
use Illuminate\Validation\Rule;

class RunSimulationRequest extends FormRequest
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
            'policy_id' => ['required', 'string', 'size:36', Rule::exists('policies', 'id')->where(function ($query) {
                $query->where('category', Policy::SIMULATION_CATEGORY);
            })],
        ];
    }
}
