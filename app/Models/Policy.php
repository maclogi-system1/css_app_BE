<?php

namespace App\Models;

use App\Support\Traits\ModelDateTimeFormatter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Policy extends Model
{
    use HasFactory, HasUuids, ModelDateTimeFormatter;

    public const MEASURES_CATEGORY = 1;
    public const PROJECT_CATEGORY = 2;
    public const SIMULATION_CATEGORY = 3;
    public const CATEGORIES = [
        self::MEASURES_CATEGORY => '施策一覧',
        self::PROJECT_CATEGORY => 'プロジェクト一覧',
        self::SIMULATION_CATEGORY => '施策シミュレーション',
    ];

    public const NEW_PROCESSING_STATUS = 0;
    public const RUNNING_PROCESSING_STATUS = 1;
    public const DONE_PROCESSING_STATUS = 2;
    public const ERROR_PROCESSING_STATUS = 3;
    public const PROCESSING_STATES = [
        self::NEW_PROCESSING_STATUS => 'New',
        self::RUNNING_PROCESSING_STATUS => 'Running',
        self::DONE_PROCESSING_STATUS => 'Done',
        self::ERROR_PROCESSING_STATUS => 'Error',
    ];

    protected $fillable = [
        'store_id',
        'job_group_id',
        'single_job_id',
        'name',
        'category',
        'kpi',
        'simulation_start_date',
        'simulation_end_date',
        'simulation_promotional_expenses',
        'simulation_store_priority',
        'simulation_product_priority',
        'description',
        'immediate_reflection',
        'processing_status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'simulation_start_date' => 'datetime',
        'simulation_end_date' => 'datetime',
    ];

    public function isProcessDone(): bool
    {
        return $this->processing_status == static::DONE_PROCESSING_STATUS;
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PolicyAttachment::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(PolicyRule::class);
    }

    public function getCategoryForHumanAttribute(): string
    {
        return static::CATEGORIES[$this->category] ?? static::MEASURES_CATEGORY;
    }

    public function withAllRels(): static
    {
        return $this->where('id', $this->getKey())->with(['rules'])->first();
    }

    public function getProcessingStatusForHumanAttribute(): string
    {
        return static::PROCESSING_STATES[$this->processing_status];
    }
}
