<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class UpdateMqAccountingRequest extends FormRequest
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
            'mq_sheet_id' => [
                'required',
                'string',
                'max:36',
                Rule::exists('mq_sheets', 'id')->where(function (Builder $query) {
                    return $query->where('store_id', $this->route('storeId'));
                }),
            ],
        ];
    }
}
