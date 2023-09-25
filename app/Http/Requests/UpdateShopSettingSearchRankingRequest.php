<?php

namespace App\Http\Requests;

use App\Support\ShopSettingSearchRankingCsv;
use App\Support\Traits\ShopSettingUpdateRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateShopSettingSearchRankingRequest extends FormRequest
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
        return $this->getProperties(ShopSettingSearchRankingCsv::HEADING, 'validation')
            + ['is_competitive_ranking' => ['nullable', Rule::in([0, 1])]];
    }

    public function attributes()
    {
        return $this->getProperties(ShopSettingSearchRankingCsv::HEADING, 'title');
    }
}
