<?php

namespace App\Http\Requests;

use App\Models\ShopSettingAwardPoint;
use App\Support\ShopSettingAwardPointCsv;
use App\Support\Traits\ShopSettingUpdateRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateShopSettingAwardPointRequest extends FormRequest
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
        return $this->getProperties(ShopSettingAwardPointCsv::HEADING, 'validation', ShopSettingAwardPoint::getModel()->getTable());
    }

    public function attributes()
    {
        return $this->getProperties(ShopSettingAwardPointCsv::HEADING, 'title', ShopSettingAwardPoint::getModel()->getTable());
    }
}
