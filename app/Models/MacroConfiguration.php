<?php

namespace App\Models;

use App\Constants\MacroConstant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class MacroConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'conditions', 'time_conditions', 'macro_type', 'created_by', 'updated_by', 'deleted_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'created_by');
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
}
