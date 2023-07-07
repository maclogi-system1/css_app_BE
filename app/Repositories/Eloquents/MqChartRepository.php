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
        $fromDate = Carbon::create(Arr::get($filter, 'from_date', now()->subYears(2)->month(1)->format('Y-m')));
        $toDate = Carbon::create(Arr::get($filter, 'to_date', now()->addYear()->month(12)->format('Y-m')));

        return $this->model()
            ->dateRange($fromDate, $toDate)
            ->where('store_id', $storeId)
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->select(
                'mq_accounting.store_id', 'mq_accounting.year', 'mq_accounting.month', 'mk.sales_amnt',
                'mc.variable_cost_sum', 'mc.profit', DB::raw('CASE WHEN (mq_accounting.fixed_cost IS NULL)
                    THEN mc.cost_sum + mq_accounting.csv_usage_fee + mq_accounting.store_opening_fee
                    ELSE mq_accounting.fixed_cost
                END as fixed_cost')
            )
            ->get();
    }

    /**
     * Get the cumulative change in revenue and profit.
     */
    public function cumulativeChangeInRevenueAndProfit($storeId, array $filter = [])
    {
        $fromDate = Carbon::create(Arr::get($filter, 'from_date', now()->subYears(2)->month(1)->format('Y-m')));
        $toDate = Carbon::create(Arr::get($filter, 'to_date', now()->addYear()->month(12)->format('Y-m')));
        $dateRange = $this->mqAccountingService->getDateTimeRange($fromDate, $toDate);

        $actualMqAccounting = $this->mqAccountingService->getCumulativeChangeInRevenueAndProfit($storeId, $filter);
        $expectedMqAccounting = $this->model()
            ->dateRange($fromDate, $toDate)
            ->where('store_id', $storeId)
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->select(
                'mq_accounting.store_id', 'mq_accounting.year', 'mq_accounting.month',
                'mk.sales_amnt', 'mc.profit'
            )
            ->get();

        $profitAchievementRate = [];

        foreach ($dateRange as $yearMonth) {
            [$year, $month] = explode('-', $yearMonth);
            $expected = $expectedMqAccounting->where('year', $year)->where('month', intval($month))->first()?->profit ?? 1;
            $actual = Arr::get($actualMqAccounting->where('year', $year)->where('month', intval($month))->first(), 'profit', 2);
            $result = 100 * $actual / $expected;
            $profitAchievementRate[] = [
                'store_id' => $storeId,
                'year' => $year,
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
