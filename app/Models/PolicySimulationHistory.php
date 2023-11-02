<?php

namespace App\Models;

use App\Models\InferenceRealData\SuggestPolicies;
use App\Support\Traits\ModelDateTimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicySimulationHistory extends Model
{
    use HasFactory, ModelDateTimeFormatter;

    protected $fillable = [
        'policy_id', 'user_id', 'title', 'job_title', 'execution_time', 'undo_time', 'creation_date', 'sale_effect',
        'store_pred_2m', 'items_pred_2m', 'class', 'service', 'value', 'condition_1', 'condition_value_1',
        'condition_2', 'condition_value_2', 'condition_3', 'condition_value_3', 'created_at', 'updated_at',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function templatePolicy(): int
    {
        return match ($this->class) {
            SuggestPolicies::POINT_CLASS => 1, // ポイント変倍（商品名込） template OSS
            SuggestPolicies::COUPON_CLASS => 3, // クーポン（商品名込） template OSS
            SuggestPolicies::TIME_SALE_CLASS => 5, // 直値引き（商品名込） template OSS
        };
    }
}
