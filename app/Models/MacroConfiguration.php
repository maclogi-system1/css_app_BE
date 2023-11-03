<?php

namespace App\Models;

use App\Constants\MacroConstant;
use App\Support\CronExpression;
use App\Support\Traits\ColumnTypeHandler;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class MacroConfiguration extends Model
{
    use HasFactory, SoftDeletes, ColumnTypeHandler;

    protected $fillable = [
        'store_ids', 'name', 'conditions', 'time_conditions', 'macro_type', 'created_by', 'updated_by', 'deleted_by',
        'status',
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
        $condition = json_decode($this->conditions, true);
        $conditions = collect(Arr::get($condition, 'conditions', []))
            ->filter(fn ($item) => $item['field'] != 'store_id')
            ->map(function ($item) {
                $item['type'] = $this->getColumnDataType(Arr::get($item, 'field'));
                $item['label'] = trans('macro-labels.'.Arr::get($item, 'field'));

                return $item;
            })
            ->values()
            ->toArray();
        $condition['conditions'] = $conditions;

        return $condition;
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

    public function templates(): HasMany
    {
        return $this->hasMany(MacroTemplate::class);
    }

    public function simulationTemplates(): HasMany
    {
        return $this->hasMany(MacroTemplate::class)->where('type', MacroConstant::MACRO_TYPE_AI_POLICY_RECOMMENDATION);
    }

    public function policyTemplates(): HasMany
    {
        return $this->hasMany(MacroTemplate::class)->where('type', MacroConstant::MACRO_TYPE_POLICY_REGISTRATION);
    }

    public function taskTemplates(): HasMany
    {
        return $this->hasMany(MacroTemplate::class)->where('type', MacroConstant::MACRO_TYPE_TASK_ISSUE);
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'macroable');
    }

    public function teams(): MorphToMany
    {
        return $this->morphedByMany(Team::class, 'macroable');
    }
}
