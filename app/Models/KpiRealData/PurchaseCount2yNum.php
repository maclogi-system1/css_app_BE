<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseCount2yNum extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'purchase_count_2y_num';
}
