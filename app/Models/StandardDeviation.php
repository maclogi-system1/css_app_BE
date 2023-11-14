<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandardDeviation extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'date', 'number_of_categories', 'number_of_items', 'product_utilization_rate',
        'product_page_conversion_rate', 'access_number', 'event_sales_ratio', 'sales_ratio_day_endings_0_5',
        'coupon_effect', 'rpp_ad', 'coupon_advance', 'rgroup_ad', 'tda_ad', 'sns_ad',
        'google_access', 'instagram_access', 'shipping_fee', 'shipping_ratio', 'bundling_ratio', 'email_newsletter',
        're_sales_num_rate', 'line_official', 'ltv', 'created_at', 'updated_at',
    ];
}
