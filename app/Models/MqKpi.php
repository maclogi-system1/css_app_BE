<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MqKpi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mq_kpi';

    protected $fillable = [
        'sales_amnt', 'sales_num', 'access_num', 'conversion_rate', 'sales_amnt_per_user',
    ];

    public $timestamps = false;
}
