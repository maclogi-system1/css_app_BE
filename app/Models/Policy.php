<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory, HasUuids;

    public const MEDIUM_TERM_CATEGORY = 1;
    public const LONG_TERM_CATEGORY = 2;
    public const AI_RECOMMENDATION_CATEGORY = 3;
    public const CATEGORIES = [
        self::MEDIUM_TERM_CATEGORY => '中期施策',
        self::LONG_TERM_CATEGORY => '長期施策',
        self::AI_RECOMMENDATION_CATEGORY => 'AIレコメンド施策',
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

    public const COUPON_TEMPLATE = 1;
    public const POINT_TEMPLATE = 2;
    public const TIME_SALE_TEMPLATE = 3;
    public const TEMPLATES = [
        self::COUPON_TEMPLATE => 'クーポン',
        self::POINT_TEMPLATE => 'ポイント',
        self::TIME_SALE_TEMPLATE => 'タイムセール',
    ];

    public const BEFORE_PROPOSAL_STATUS = 1;
    public const CONFIRMED_STATUS = 2;
    public const DURING_CORRESPONDENCE_STATUS = 3;
    public const DELAY_STATUS = 4;
    public const COMPLETED_STATUS = 5;
    public const NO_RESPONSE_REQUIRED_STATUS = 6;
    public const STATUSES = [
        self::BEFORE_PROPOSAL_STATUS => '提案前',
        self::CONFIRMED_STATUS => '施策確定',
        self::DURING_CORRESPONDENCE_STATUS => '対応中',
        self::DELAY_STATUS => '遅延',
        self::COMPLETED_STATUS => '完了',
        self::NO_RESPONSE_REQUIRED_STATUS => '対応不要',
    ];

    protected $fillable = [
        'store_id',
        'job_group_id',
        'name',
        'category',
        'kpi',
        'template',
        'status',
        'start_date',
        'end_date',
        'description',
        'point_rate',
        'point_application_period',
        'flat_rate_discount',
        'created_at',
        'updated_at',
    ];

    public function getCategoryForHumanAttribute(): string
    {
        return static::CATEGORIES[$this->category] ?? static::AI_RECOMMENDATION_CATEGORY;
    }

    public function getKpiForHumanAttribute(): string
    {
        return static::KPIS[$this->kpi] ?? static::INCREASED_SALES_KPI;
    }

    public function getTemplateForHumanAttribute(): string
    {
        return static::TEMPLATES[$this->template] ?? static::COUPON_TEMPLATE;
    }

    public function getStatusForHumanAttribute(): string
    {
        return static::STATUSES[$this->status] ?? static::BEFORE_PROPOSAL_STATUS;
    }
}
