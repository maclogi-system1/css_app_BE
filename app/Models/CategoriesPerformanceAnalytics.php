<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriesPerformanceAnalytics extends Model
{
    use HasFactory;
    protected $table = 'categories_performance_analytics';

    protected $fillable = [
        'store_id', 'categories_sales',
    ];
}
