<?php

namespace App\Repositories\Eloquents;

use App\Models\MqAccounting;
use App\Repositories\Contracts\MqChartRepository as MqChartRepositoryContract;
use App\Repositories\Repository;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\AI\MqAccountingService;
use App\WebServices\AI\StorePred36mService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MqChartRepository extends Repository implements MqChartRepositoryContract
{
    use HasMqDateTimeHandler;

    public function __construct(
        protected MqAccountingService $mqAccountingService,
        protected StorePred36mService $storePred36mService,
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
        return $this->mqAccountingService->financialIndicatorsMonthly($storeId, $filters);
    }

    /**
     * Get the cumulative change in revenue and profit.
     */
    public function cumulativeChangeInRevenueAndProfit($storeId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateRange = $this->getDateTimeRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date']);

        $actualMqAccounting = $this->mqAccountingService->getCumulativeChangeInRevenueAndProfit($storeId, $filters);
        $query = $this->model()
            ->dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->where('mq_accounting.store_id', $storeId)
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id');

        if ($mqSheetId = Arr::get($filters, 'mq_sheet_id')) {
            $query->join('mq_sheets as ms', 'ms.id', '=', 'mq_accounting.mq_sheet_id')
                ->where('mq_accounting.mq_sheet_id', $mqSheetId);
        } else {
            $query->join('mq_sheets as ms', 'ms.id', '=', 'mq_accounting.mq_sheet_id')
                ->where('ms.is_default', 1);
        }

        $expectedMqAccounting = $query
            ->select(
                'mq_accounting.store_id',
                DB::raw("CONCAT(mq_accounting.year, '/', LPAD(mq_accounting.month, 2, '0')) as `year_month`"),
                'mk.sales_amnt',
                'mc.profit',
                'mq_accounting.mq_sheet_id',
                'ms.name',
                'mq_accounting.year',
                'mq_accounting.month',
            )
            ->get();

        $profitAchievementRate = [];

        foreach ($dateRange as $yearMonth) {
            [$year, $month] = explode('-', $yearMonth);
            $expected = $expectedMqAccounting->where('year', $year)->where('month', intval($month))->first()?->profit ?? 1;
            $actual = $actualMqAccounting->where('year', $year)->where('month', intval($month))->first()?->profit ?? 1;
            $result = $expected != 0 ? 100 * $actual / $expected : 0;
            $profitAchievementRate[] = [
                'store_id' => $storeId,
                'year_month' => $year.'/'.$month,
                'profit_rate' => round($result, 2),
            ];
        }

        return [
            'actual_mq_accounting' => $actualMqAccounting,
            'expected_mq_accounting' => $expectedMqAccounting,
            'profit_achievement_rate' => $profitAchievementRate,
        ];
    }

    /**
     * Calculate and get the break-even point.
     */
    public function getBreakEvenPoint(string $storeId, array $filters = [])
    {
        $actualMqAccounting = $this->mqAccountingService->getTotalParamByStore($storeId, $filters);
        $fixedCost = $actualMqAccounting->cost_sum_total ?? 0;
        $salesAmnt = $actualMqAccounting->sales_amnt_total ?? 0;
        $variableCost = $actualMqAccounting->variable_cost_sum_total ?? 0;

        $breakEvenPoint = $salesAmnt ? round($fixedCost / (($salesAmnt - $variableCost) / $salesAmnt), 2) : 0;
        $breakEvenPointRatio = $salesAmnt ? round(100 * $breakEvenPoint / $salesAmnt, 2) : 0;

        return [
            'sales_amnt' => $salesAmnt,
            'variable_cost_sum' => $variableCost,
            'cost_sum' => $fixedCost,
            'break_even_point' => $breakEvenPoint,
            'break_even_point_ratio' => $breakEvenPointRatio,
        ];
    }

    /**
     * Get expected and expected sales.
     */
    public function getInferredAndExpectedMqSales(string $storeId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $result = $this->storePred36mService->getInferenceSales($storeId, $filters);
        $inferenceSales = [];

        if ($result->get('success')) {
            $inferenceSales = $result->get('data');
        }

        $query = $this->model()->dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->where('mq_accounting.store_id', $storeId)
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id');

        if ($mqSheetId = Arr::get($filters, 'mq_sheet_id')) {
            $query->join('mq_sheets as ms', 'ms.id', '=', 'mq_accounting.mq_sheet_id')
                ->where('mq_accounting.mq_sheet_id', $mqSheetId);
        } else {
            $query->join('mq_sheets as ms', 'ms.id', '=', 'mq_accounting.mq_sheet_id')
                ->where('ms.is_default', 1);
        }

        $expectedSales = $query
            ->select(
                'mq_accounting.store_id',
                DB::raw("CONCAT(mq_accounting.year, '/', LPAD(mq_accounting.month, 2, '0')) as `year_month`"),
                'mk.sales_amnt',
            )
            ->get();

        return [
            'ai_inference' => $inferenceSales,
            'expected_mq' => $expectedSales,
        ];
    }
}
