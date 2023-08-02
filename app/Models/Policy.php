<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Policy extends Model
{
    use HasFactory, HasUuids;

    public const EDIT_ACTION = 1;
    public const CREATE_ACTION = 2;
    public const REMOVE_ACTION = 3;
    public const CONTROL_ACTIONS = [
        self::EDIT_ACTION => '更新',
        self::CREATE_ACTION => '新規',
        self::REMOVE_ACTION => '削除',
    ];

    public const MEASURES_CATEGORY = 1;
    public const PROJECT_CATEGORY = 2;
    public const SIMULATION_CATEGORY = 3;
    public const CATEGORIES = [
        self::MEASURES_CATEGORY => '施策一覧',
        self::PROJECT_CATEGORY => 'プロジェクト一覧',
        self::SIMULATION_CATEGORY => '施策シミュレーション',
    ];

    public const NONE_BANNER = 1;
    public const CANBE_BANNER = 2;
    public const UNNECESSARY_BANNER = 3;
    public const BANNERS = [
        self::NONE_BANNER => 'なし',
        self::CANBE_BANNER => 'あり',
        self::UNNECESSARY_BANNER => '不要',
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
        'created_at',
        'updated_at',
    ];

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
}
