<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MqCost extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mq_cost';

    protected $fillable = [
        'coupon_points_cost',
        'coupon_points_cost_rate',
        'ad_cost',
        'ad_cpc_cost',
        'ad_season_cost',
        'ad_event_cost',
        'ad_tda_cost',
        'ad_cost_rate',
        'cost_price',
        'cost_price_rate',
        'postage',
        'postage_rate',
        'commision',
        'commision_rate',
        'variable_cost_sum',
        'gross_profit',
        'gross_profit_rate',
        'management_agency_fee',
        'reserve1',
        'reserve2',
        'management_agency_fee_rate',
        'cost_sum',
        'profit',
        'sum_profit',
    ];

    public $timestamps = false;
}
