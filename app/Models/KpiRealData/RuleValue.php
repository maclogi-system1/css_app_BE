<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Model;

class RuleValue extends Model
{
    protected $connection = 'kpi_real_data';
    protected $primaryKey = 'rule_values_id';
    public $incrementing = false;
}
