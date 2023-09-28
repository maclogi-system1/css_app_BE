<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopAnalyticsDailyAccessNum extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'shop_analytics_daily_access_num';
}
