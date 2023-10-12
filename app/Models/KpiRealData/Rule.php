<?php

namespace App\Models\KpiRealData;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $connection = 'kpi_real_data';
    protected $primaryKey = 'rule_id';
    public $incrementing = false;

    public function ruleValue()
    {
        return $this->belongsTo(RuleValue::class, 'rule_values_id', 'rule_values_id');
    }
}
