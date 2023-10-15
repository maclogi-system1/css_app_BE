<?php

namespace App\Models;

use App\Support\Traits\ModelDateTimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyRule extends Model
{
    use HasFactory, ModelDateTimeFormatter;

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
    public const YEN_SERVICE = 4;
    public const POINT_BACK_SERVICE = 5;
    public const LIMITED_SPECIAL_PRICE_SERVICE = 6;
    public const SERVICES = [
        self::FIXED_PRICE_DISCOUNT_SERVICE => '定額値引き',
        self::FIXED_RATE_DISCOUNT_SERVICE => '定率値引き',
        self::GIVE_DOUBLE_POINTS_SERVICE => 'ポイント倍付与',
        self::YEN_SERVICE => '〇円ぽっきり',
        self::POINT_BACK_SERVICE => 'ポイントバック',
        self::LIMITED_SPECIAL_PRICE_SERVICE => '限定特価',
    ];

    public const PURCHASE_AMOUNT_CONDITION = 1;
    public const NUMBER_OF_PURCHASES_CONDITION = 2;
    public const LIMITED_NUMBER_CONDITION = 3;
    public const LIMITED_NUMBER_OF_COUPONS_CONDITION = 4;
    public const NONE_CONDITION = 5;
    public const SHIPPING_CONDITION = 6;
    public const TEXT_INPUT_CONDITIONS = [
        self::PURCHASE_AMOUNT_CONDITION => '購入金額',
        self::NUMBER_OF_PURCHASES_CONDITION => '購入点数',
        self::LIMITED_NUMBER_CONDITION => '個数限定',
        self::LIMITED_NUMBER_OF_COUPONS_CONDITION => 'クーポン枚数限定',
        self::NONE_CONDITION => 'None',
    ];

    public const UPLOADABLE_CONDITIONS = [
        self::SHIPPING_CONDITION => '対象商品',
    ];

    protected $fillable = [
        'policy_id',
        'class',
        'service',
        'value',
        'condition_1',
        'condition_value_1',
        'condition_2',
        'condition_value_2',
        'condition_3',
        'condition_value_3',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
