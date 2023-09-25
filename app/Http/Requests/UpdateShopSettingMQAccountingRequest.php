<?php

namespace App\Http\Requests;

use App\Support\ShopSettingMqAccountingCsv;
use App\Support\Traits\ShopSettingUpdateRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateShopSettingMQAccountingRequest extends FormRequest
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
        return $this->getProperties(ShopSettingMqAccountingCsv::HEADING, 'validation');
    }

    public function attributes()
    {
        return $this->getProperties(ShopSettingMqAccountingCsv::HEADING, 'title');
    }
}
