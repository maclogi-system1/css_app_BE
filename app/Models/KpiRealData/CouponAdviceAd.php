<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponAdviceAd extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'coupon_advice_ad';
}
