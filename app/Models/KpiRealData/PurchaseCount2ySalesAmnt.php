<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseCount2ySalesAmnt extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'purchase_count_2y_sales_amnt';
}
