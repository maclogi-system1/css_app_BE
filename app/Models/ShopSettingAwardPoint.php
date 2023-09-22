<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopSettingAwardPoint extends Model
{
    use HasFactory;

    protected $table = 'shop_setting_award_points';

    protected $fillable = [
        'store_id',
        'purchase_date',
        'order_number',
        'points_awarded',
    ];
}
