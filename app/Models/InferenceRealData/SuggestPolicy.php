<?php

namespace App\Models\InferenceRealData;

use App\Constants\DatabaseConnectionConstant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuggestPolicy extends Model
{
    public const RUNNING_STATUS = 1;
    public const FAIL_STATUS = 2;
    public const SUCCESS_STATUS = 3;
    public const NO_DATA_STATUS = 4;
    public const OVER_LIMIT_STATUS = 5;

    protected $connection = DatabaseConnectionConstant::INFERENCE_CONNECTION;
    protected $table = 'suggest_policy';

    public function suggestedPolicies(): HasMany
    {
        return $this->hasMany(SuggestPolicies::class, 'policy_id', 'policy_id');
    }
}
