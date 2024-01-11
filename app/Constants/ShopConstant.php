<?php

namespace App\Constants;

class ShopConstant
{
    public const SHOP_ALL_OPTION = '__all__';

    public const SHOP_OWNER_OPTION = '__shop_owner__';

    public const SHOP_OPTIONS = [
        self::SHOP_ALL_OPTION => '全店舗',
        self::SHOP_OWNER_OPTION => '担当店舗',
    ];
}
