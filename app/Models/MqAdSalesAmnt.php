<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MqAdSalesAmnt extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mq_ad_sales_amnt';

    protected $fillable = [
        'sales_amnt_via_ad',
        'sales_amnt_seasonal',
        'sales_amnt_event',
        'tda_access_num',
        'tda_v_sales_amnt',
        'tda_v_roas',
    ];

    public $timestamps = false;
}
