<?php

namespace App\Repositories\Eloquents;

use App\Models\MqAccounting;
use App\Repositories\Contracts\MqChartRepository as MqChartRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MqChartRepository extends Repository implements MqChartRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return MqAccounting::class;
    }

    /**
     * Get monthly changes in financial indicators.
     */
    public function financialIndicatorsMonthly($storeId, array $filter = [])
    {
        $fromDate = Carbon::create(Arr::get($filter, 'from_date'));
        $toDate = Carbon::create(Arr::get($filter, 'to_date'));

        return $this->model()->where('store_id', $storeId)
            ->where(function ($query) use ($fromDate) {
                $query->where('year', '>=', $fromDate->year)
                    ->where('month', '>=', $fromDate->month);
            })
            ->where(function ($query) use ($toDate) {
                $query->where('year', '<=', $toDate->year)
                    ->where('month', '<=', $toDate->month);
            })
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->select(
                'mq_accounting.store_id', 'mq_accounting.year', 'mq_accounting.month', 'mk.sales_amnt',
                DB::raw('
                    CASE
                        WHEN (mc.management_agency_fee + mc.reserve1 + mc.reserve2) IS NULL THEN 0
                        ELSE (mc.management_agency_fee + mc.reserve1 + mc.reserve2)
                    END as fixed_cost_sum
                '),
                'mc.variable_cost_sum', 'mc.sum_profit'
            )
            ->get();
    }
}
