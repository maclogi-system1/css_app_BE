<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyRule extends Model
{
    use HasFactory;

    public const PURCHASE_AMOUNT_CONDITION = 1;
    public const NUMBER_OF_PURCHASES_CONDITION = 2;
    public const LIMITED_NUMBER_CONDITION = 3;
    public const NONE_CONDITION = 4;
    public const NOT_CLEAR_CONDITION = 5;
    public const SHIPPING_CONDITION = 6;
    public const CONDITIONS = [
        self::PURCHASE_AMOUNT_CONDITION => '購入金額',
        self::NUMBER_OF_PURCHASES_CONDITION => '購入点数',
        self::LIMITED_NUMBER_CONDITION => '個数限定',
        self::NONE_CONDITION => 'None',
        self::NOT_CLEAR_CONDITION => '不明',
        self::SHIPPING_CONDITION => '対象商品',
    ];
}
