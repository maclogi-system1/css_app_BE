<?php

namespace App\Http\Requests;

class GetMqInferredAndExpectedMqSalesRequest extends GetMqAnalysisRequest
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
        $rules = parent::rules();
        unset($rules['compared_from_date'], $rules['compared_to_date']);
        $rules['mq_sheet_id'] = ['nullable', 'string'];

        return $rules;
    }
}
