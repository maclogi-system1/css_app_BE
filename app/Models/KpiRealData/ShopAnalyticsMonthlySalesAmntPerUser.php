<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopAnalyticsMonthlySalesAmntPerUser extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'shop_analytics_monthly_sales_amnt_per_user';
}
