<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSettingRanking extends Model
{
    protected $table = 'shop_setting_rankings';

    protected $fillable = [
        'store_id',
        'store_competitive_id',
        'merchandise_control_number',
        'directory_id',
        'is_competitive',
    ];
}
