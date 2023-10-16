<?php

namespace App\Models\PolicyRealData;

use App\Constants\DatabaseConnectionConstant;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $connection = DatabaseConnectionConstant::POLICY_CONNECTION;
    protected $primaryKey = 'rule_id';
    public $incrementing = false;

    public function ruleValue()
    {
        return $this->belongsTo(RuleValue::class, 'rule_values_id', 'rule_values_id');
    }
}
