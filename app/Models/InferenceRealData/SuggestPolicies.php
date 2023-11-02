<?php

namespace App\Models\InferenceRealData;

use App\Constants\DatabaseConnectionConstant;
use Illuminate\Database\Eloquent\Model;

class SuggestPolicies extends Model
{
    public const COUPON_CLASS = 1;
    public const POINT_CLASS = 2;
    public const TIME_SALE_CLASS = 3;
    public const CLASSES = [
        self::COUPON_CLASS => 'クーポン',
        self::POINT_CLASS => 'ポイント',
        self::TIME_SALE_CLASS => 'タイムセール',
    ];

    public const FIXED_PRICE_DISCOUNT_SERVICE = 1;
    public const FIXED_RATE_DISCOUNT_SERVICE = 2;
    public const GIVE_DOUBLE_POINTS_SERVICE = 3;
    public const SERVICES = [
        self::FIXED_PRICE_DISCOUNT_SERVICE => '定額値引き',
        self::FIXED_RATE_DISCOUNT_SERVICE => '定率値引き',
        self::GIVE_DOUBLE_POINTS_SERVICE => 'ポイント倍付与',
    ];

    protected $connection = DatabaseConnectionConstant::INFERENCE_CONNECTION;
    protected $table = 'suggest_policies';
}
