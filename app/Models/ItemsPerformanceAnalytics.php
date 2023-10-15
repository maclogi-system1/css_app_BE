<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsPerformanceAnalytics extends Model
{
    use HasFactory;

    protected $table = 'items_performance_analytics';

    protected $fillable = [
        'store_id', 'items_sales',
    ];
}
