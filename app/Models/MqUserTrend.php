<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MqUserTrend extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'new_sales_amnt',
        'new_sales_num',
        'new_price_per_user',
        're_sales_amnt',
        're_sales_num',
        're_price_per_user',
    ];

    public $timestamps = false;
}
