<?php

namespace App\Models\KpiRealData;

use App\Constants\DatabaseConnectionConstant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeysearchRanking extends Model
{
    use HasFactory;

    protected $connection = DatabaseConnectionConstant::KPI_CONNECTION;
    protected $table = 'keysearch_ranking';
}
