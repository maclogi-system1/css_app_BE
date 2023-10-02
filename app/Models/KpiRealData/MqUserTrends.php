<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MqUserTrends extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'mq_user_trends';
}
