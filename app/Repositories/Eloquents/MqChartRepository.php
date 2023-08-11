<?php

namespace App\Repositories\Eloquents;

use App\Models\MqAccounting;
use App\Repositories\Contracts\MqChartRepository as MqChartRepositoryContract;
use App\Repositories\Repository;
use App\Services\AI\MqAccountingService;
use App\Support\Traits\HasMqDateTimeHandler;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MqChartRepository extends Repository implements MqChartRepositoryContract
{
    use HasMqDateTimeHandler;

    public function __construct(
        protected MqAccountingService $mqAccountingService
    ) {
    }

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
    public function financialIndicatorsMonthly($storeId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        return $this->model()
            ->dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->where('store_id', $storeId)
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->select(
                'mq_accounting.store_id',
                'mq_accounting.year',
                'mq_accounting.month',
                'mk.sales_amnt',
                'mc.variable_cost_sum',
                'mc.profit',
                DB::raw('CASE WHEN (mq_accounting.fixed_cost IS NULL)
                    THEN mc.cost_sum + mq_accounting.csv_usage_fee + mq_accounting.store_opening_fee
                    ELSE mq_accounting.fixed_cost
                END as fixed_cost')
            )
            ->get();
    }

    /**
     * Get the cumulative change in revenue and profit.
     */
    public function cumulativeChangeInRevenueAndProfit($storeId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateRange = $this->getDateTimeRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date']);

        $actualMqAccounting = $this->mqAccountingService->getCumulativeChangeInRevenueAndProfit($storeId, $filters);
        $expectedMqAccounting = $this->model()
            ->dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->where('store_id', $storeId)
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->select(
                'mq_accounting.store_id',
                'mq_accounting.year',
                'mq_accounting.month',
                'mk.sales_amnt',
                'mc.profit'
            )
            ->get();

        $profitAchievementRate = [];

        foreach ($dateRange as $yearMonth) {
            [$year, $month] = explode('-', $yearMonth);
            $expected = $expectedMqAccounting->where('year', $year)->where('month', intval($month))->first()?->profit ?? 1;
            $actual = Arr::get($actualMqAccounting->where('year', $year)->where('month', intval($month))->first(), 'profit', 2);
            $result = $expected != 0 ? 100 * $actual / $expected : 0;
            $profitAchievementRate[] = [
                'store_id' => $storeId,
                'year' => intval($year),
                'month' => intval($month),
                'profit_rate' => round($result, 2),
            ];
        }

        return [
            'actual_mq_accounting' => $actualMqAccounting,
            'expected_mq_accounting' => $expectedMqAccounting,
            'profit_achievement_rate' => $profitAchievementRate,
        ];
    }
}
