<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Policy extends Model
{
    use HasFactory, HasUuids;

    public const MEASURES_CATEGORY = 1;
    public const PROJECT_CATEGORY = 2;
    public const SIMULATION_CATEGORY = 3;
    public const CATEGORIES = [
        self::MEASURES_CATEGORY => '施策一覧',
        self::PROJECT_CATEGORY => 'プロジェクト一覧',
        self::SIMULATION_CATEGORY => '施策シミュレーション',
    ];

    public const INCREASED_SALES_KPI = 1;
    public const IMPROVED_ACCESS_KPI = 2;
    public const IMPROVE_CONVERSION_RATE_KPI = 3;
    public const INCREASE_IN_AVERAGE_SPEND_PER_CUSTOMER_KPI = 4;
    public const OTHERS_KPI = 5;
    public const KPIS = [
        self::INCREASED_SALES_KPI => '売上向上',
        self::IMPROVED_ACCESS_KPI => 'アクセス向上',
        self::IMPROVE_CONVERSION_RATE_KPI => '転換率向上',
        self::INCREASE_IN_AVERAGE_SPEND_PER_CUSTOMER_KPI => '客単価向上',
        self::OTHERS_KPI => 'その他',
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

    public function getCategoryForHumanAttribute(): string
    {
        return static::CATEGORIES[$this->category] ?? static::MEASURES_CATEGORY;
    }

    public function getKpiForHumanAttribute(): string
    {
        return static::KPIS[$this->kpi] ?? static::INCREASED_SALES_KPI;
    }
}
