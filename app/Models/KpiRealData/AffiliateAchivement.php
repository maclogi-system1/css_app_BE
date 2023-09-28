<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateAchivement extends Model
{
    use HasFactory;

    protected $connection = 'kpi_real_data';
    protected $table = 'affiliate_achivement';
}
