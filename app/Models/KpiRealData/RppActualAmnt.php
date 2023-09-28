<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RppActualAmnt extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'rpp_actual_amnt';
}
