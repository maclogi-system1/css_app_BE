<?php

namespace App\Models\PolicyRealData;

use App\Constants\DatabaseConnectionConstant;
use Illuminate\Database\Eloquent\Model;

class RuleValue extends Model
{
    protected $connection = DatabaseConnectionConstant::POLICY_CONNECTION;
    protected $primaryKey = 'rule_values_id';
    public $incrementing = false;
}
