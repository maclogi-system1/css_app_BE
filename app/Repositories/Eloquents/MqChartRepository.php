<?php

namespace App\Repositories\Eloquents;

use App\Models\MqAccounting;
use App\Repositories\Contracts\MqChartRepository as MqChartRepositoryContract;
use App\Repositories\Repository;
use App\Services\AI\MqAccountingService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MqChartRepository extends Repository implements MqChartRepositoryContract
{
    public function __construct(
        protected MqAccountingService $mqAccountingService
    ) {}

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
                'mc.cost_sum', 'mc.variable_cost_sum', 'mc.profit'
            )
            ->get();
    }

    /**
     * Get the cumulative change in revenue and profit.
     */
    public function cumulativeChangeInRevenueAndProfit($storeId, array $filter = [])
    {
        $fromDate = Carbon::create(Arr::get($filter, 'from_date'));
        $toDate = Carbon::create(Arr::get($filter, 'to_date'));

        $actualMqAccounting = $this->mqAccountingService->getCumulativeChangeInRevenueAndProfit($storeId, $filter);
        $expectedMqAccounting = $this->model()->where('store_id', $storeId)
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
                'mq_accounting.store_id', 'mq_accounting.year', 'mq_accounting.month',
                'mk.sales_amnt', 'mc.profit'
            )
            ->get();

        return [
            'actual_mq_accounting' => $actualMqAccounting,
            'expected_mq_accounting' => $expectedMqAccounting,
        ];
    }
}
