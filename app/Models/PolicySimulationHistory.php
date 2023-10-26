<?php

namespace App\Models;

use App\Support\Traits\ModelDateTimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicySimulationHistory extends Model
{
    use HasFactory, ModelDateTimeFormatter;

    protected $fillable = [
        'policy_id',
        'manager',
        'title',
        'job_title',
        'execution_time',
        'undo_time',
        'creation_date',
        'sale_effect',
        'store_pred_2m',
        'items_pred_2m',
        'created_at',
        'updated_at',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
