<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdPurchaseHistory extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'ad_purchase_history';
}
