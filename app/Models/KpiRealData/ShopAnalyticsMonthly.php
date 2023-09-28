<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopAnalyticsMonthly extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'shop_analytics_monthly';
}
