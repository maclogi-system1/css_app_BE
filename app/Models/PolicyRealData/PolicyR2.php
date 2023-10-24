<?php

namespace App\Models\PolicyRealData;

use App\Constants\DatabaseConnectionConstant;
use Illuminate\Database\Eloquent\Model;

class PolicyR2 extends Model
{
    protected $connection = DatabaseConnectionConstant::POLICY_CONNECTION;
    protected $table = 'policy_r2';
    protected $primaryKey = 'policy_id';
    public $incrementing = false;

    public function rule1()
    {
        return $this->belongsTo(Rule::class, 'rule_1_id', 'rule_id');
    }

    public function rule2()
    {
        return $this->belongsTo(Rule::class, 'rule_2_id', 'rule_id');
    }

    public function rule3()
    {
        return $this->belongsTo(Rule::class, 'rule_3_id', 'rule_id');
    }
}
