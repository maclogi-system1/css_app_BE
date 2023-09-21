<?php

namespace App\Constants;

class KpiConstant
{
    public const ADS_TYPE_RPP = 'rpp';
    public const ADS_TYPE_COUPON_ADVANCE = 'coupon_advance';
    public const ADS_TYPE_RAKUTEN_GROUP_ADS = 'rakuten_group_advertisement';

    public const ADS_TYPES = [
        self::ADS_TYPE_RPP => 'RPP',
        self::ADS_TYPE_COUPON_ADVANCE => 'クーポンアドバンス',
        self::ADS_TYPE_RAKUTEN_GROUP_ADS => '楽天グループ広告',
    ];
}
