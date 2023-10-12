<?php

namespace App\Http\Requests;

use App\Models\ShopSettingRanking;
use App\Support\ShopSettingRankingCsv;
use App\Support\Traits\ShopSettingUpdateRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateShopSettingRankingRequest extends FormRequest
{
    use ShopSettingUpdateRequest;

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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return $this->getProperties(ShopSettingRankingCsv::HEADING, 'validation', ShopSettingRanking::getModel()->getTable())
            + ['is_competitive_ranking' => ['nullable', Rule::in([0, 1])]];
    }

    public function attributes()
    {
        return $this->getProperties(ShopSettingRankingCsv::HEADING, 'title', ShopSettingRanking::getModel()->getTable());
    }
}
