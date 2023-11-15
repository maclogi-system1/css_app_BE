<?php

namespace App\Http\Requests;

use App\Support\Traits\FilterYearMonthValidationRules;

class GetListValueChainRequest extends FormRequest
{
    use FilterYearMonthValidationRules;

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
        return $this->yearMonthFromToRules();
    }
}
