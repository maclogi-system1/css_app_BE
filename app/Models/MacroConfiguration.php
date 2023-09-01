<?php

namespace App\Models;

use App\Constants\MacroConstant;
use App\Support\CronExpression;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class MacroConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_ids', 'name', 'conditions', 'time_conditions', 'macro_type', 'created_by', 'updated_by', 'deleted_by',
    ];

    public function isOneTime(): bool
    {
        $timeCondition = $this->getTimeConditionsDecodeAttribute();

        return Arr::has($timeCondition, 'designation');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getConditionsDecodeAttribute(): array
    {
        return json_decode($this->conditions, true);
    }

    public function getTimeConditionsDecodeAttribute(): array
    {
        return json_decode($this->time_conditions, true);
    }

    public function getMacroTypeForHumanAttribute(): string
    {
        return Arr::get(MacroConstant::MACRO_TYPES, $this->macro_type, MacroConstant::MACRO_TYPE_AI_SALES_FORECAST);
    }

    public function getTimeConditionDesignationAttribute(): string
    {
        $timeCondition = $this->getTimeConditionsDecodeAttribute();

        return Arr::get($timeCondition, 'designation', '');
    }

    public function getTimeConditionScheduleAttribute(): array
    {
        $timeCondition = $this->getTimeConditionsDecodeAttribute();

        return Arr::get($timeCondition, 'schedule', []);
    }

    public function getCronExpressionAttribute(): CronExpression
    {
        $cronExpression = CronExpression::make($this->getTimeConditionScheduleAttribute());

        return $cronExpression;
    }

    public function graph(): HasOne
    {
        return $this->hasOne(MacroGraph::class);
    }
}
