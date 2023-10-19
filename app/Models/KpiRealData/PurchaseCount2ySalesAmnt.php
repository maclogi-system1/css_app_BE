<?php

namespace App\Models\KpiRealData;

use App\Constants\DatabaseConnectionConstant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseCount2ySalesAmnt extends Model
{
    use HasFactory;

    protected $connection = DatabaseConnectionConstant::KPI_CONNECTION;
    protected $table = 'purchase_count_2y_sales_amnt';
}
