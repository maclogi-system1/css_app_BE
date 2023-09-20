<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopSettingMqAccounting extends Model
{
    use HasFactory;

    protected $table = 'shop_setting_mq_accounting';

    protected $fillable = [
        'store_id',
        'date',
        'estimated_management_agency_expenses',
        'estimated_cost_rate',
        'estimated_shipping_fee',
        'estimated_commission_rate',
        'estimated_csv_usage_fee',
        'estimated_store_opening_fee',
        'actual_management_agency_expenses',
        'actual_cost_rate',
        'actual_shipping_fee',
        'actual_commission_rate',
        'actual_csv_usage_fee',
        'actual_store_opening_fee',
    ];
}
