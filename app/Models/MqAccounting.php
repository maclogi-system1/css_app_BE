<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MqAccounting extends Model
{
    use HasFactory;

    protected $table = 'mq_accounting';
    protected $fillable = [
        'store_id',
        'year',
        'month',
        'mq_kpi_id',
        'mq_access_num_id',
        'mq_ad_sales_amnt_id',
        'mq_user_trends_id',
        'mq_cost_id',
        'mq_sheet_id',
        'ltv_2y_amnt',
        'lim_cpa',
        'cpo_via_ad',
        'create_at',
        'updated_at',
        'csv_usage_fee',
        'store_opening_fee',
        'fixed_cost',
    ];

    public function mqKpi(): BelongsTo
    {
        return $this->belongsTo(MqKpi::class);
    }

    public function mqAccessNum(): BelongsTo
    {
        return $this->belongsTo(MqAccessNum::class);
    }

    public function mqAdSalesAmnt(): BelongsTo
    {
        return $this->belongsTo(MqAdSalesAmnt::class);
    }

    public function mqUserTrends(): BelongsTo
    {
        return $this->belongsTo(MqUserTrend::class, 'mq_user_trends_id');
    }

    public function mqCost(): BelongsTo
    {
        return $this->belongsTo(MqCost::class);
    }

    public function mqSheet(): BelongsTo
    {
        return $this->belongsTo(MqSheet::class);
    }

    public function scopeDateRange(Builder $builder, Carbon $fromDate, Carbon $toDate)
    {
        if ($fromDate <= $toDate) {
            $builder->whereRaw("CONCAT(`year`, LPAD(`month`, 2, '0')) >= ? and CONCAT(`year`, LPAD(`month`, 2, '0')) <= ?", [
                $fromDate->format('Ym'),
                $toDate->format('Ym'),
            ]);
        }
    }

    protected static function booted()
    {
        static::deleted(function (MqAccounting $mqAccounting) {
            $mqAccounting->mqKpi()->delete();
            $mqAccounting->mqAccessNum()->delete();
            $mqAccounting->mqAdSalesAmnt()->delete();
            $mqAccounting->mqUserTrends()->delete();
            $mqAccounting->mqCost()->delete();
        });
    }
}
