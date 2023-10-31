<?php

namespace App\Models\KpiRealData;

use App\Constants\DatabaseConnectionConstant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MqAccounting extends Model
{
    use HasFactory;

    protected $connection = DatabaseConnectionConstant::KPI_CONNECTION;
    protected $table = 'mq_accounting';

    public function mqKpi(): BelongsTo
    {
        return $this->belongsTo(MqKpi::class, 'mq_kpi_id', 'mq_kpi_id');
    }

    public function mqAccessNum(): BelongsTo
    {
        return $this->belongsTo(MqAccessNum::class, 'mq_access_num_id', 'mq_access_num_id');
    }

    public function mqAdSalesAmnt(): BelongsTo
    {
        return $this->belongsTo(MqAdSalesAmnt::class, 'mq_ad_sales_amnt_id', 'mq_ad_sales_amnt_id');
    }

    public function mqUserTrends(): BelongsTo
    {
        return $this->belongsTo(MqUserTrends::class, 'mq_user_trends_id', 'mq_user_trends_id');
    }

    public function mqCost(): BelongsTo
    {
        return $this->belongsTo(MqCost::class, 'mq_cost_id', 'mq_cost_id');
    }

    public function scopeDateRange(Builder $query, Carbon $fromDate, Carbon $toDate)
    {
        if ($fromDate <= $toDate) {
            $query->where(function ($query) use ($fromDate, $toDate) {
                $query->whereRaw("
                    STR_TO_DATE(CONCAT(mq_accounting.year, '-', LPAD(mq_accounting.month, 2, '0'), '-".$fromDate->lastOfMonth()->day."'), '%Y-%m-%d') >= DATE('".$fromDate->format('Y-m-d')."')
                ")
                ->whereRaw("
                    STR_TO_DATE(CONCAT(mq_accounting.year, '-', LPAD(mq_accounting.month, 2, '0'), '-".$toDate->lastOfMonth()->day."'), '%Y-%m-%d') <= DATE('".$toDate->format('Y-m-d')."')
                ");
            });
        }
    }
}
