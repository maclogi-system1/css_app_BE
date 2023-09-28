<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsData extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'items_data';
}
