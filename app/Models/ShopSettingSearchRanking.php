<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopSettingSearchRanking extends Model
{
    use HasFactory;

    protected $table = 'shop_setting_search_rankings';

    protected $fillable = [
        'store_id',
        'store_competitive_id',
        'merchandise_control_number',
        'keyword_1',
        'keyword_2',
        'keyword_3',
    ];
}
