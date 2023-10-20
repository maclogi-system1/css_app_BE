<?php

namespace App\Repositories\Eloquents;

use App\Constants\KpiConstant;
use App\Models\MqKpi;
use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\MqKpiRepository as MqKpiRepositoryContract;
use App\Repositories\Repository;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\AI\MqAccountingService;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class MqKpiRepository extends Repository implements MqKpiRepositoryContract
{
    use HasMqDateTimeHandler;

    public function __construct(
        protected MqAccountingRepository $mqAccountingRepository,
        protected MqAccountingService $mqAccountingService
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return MqKpi::class;
    }

    /**
     * Get KPI summary (KPI target achievement rate, KPI performance summary).
     */
    public function getSummary(string $storeId, array $filters = []): array
    {
        $result[] = $this->getSummaryData($storeId, $filters);

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $result[] = $this->getSummaryData($storeId, $filters);
        }

        return $result;
    }

    /**
     * Get kpi summary dạta.
     */
    private function getSummaryData(string $storeId, array $filters = []): array
    {
        // Check if the input matches the 'yyyy-MM' format
        $isMonthQuery = false;
        if (Arr::has($filters, ['from_date', 'to_date'])) {
            if (
                preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'from_date'))
                && preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'to_date'))
            ) {
                $isMonthQuery = true;
            }
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $expectedMqKpi = $this->mqAccountingRepository->model()
            ->dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->where('mq_accounting.store_id', $storeId)
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->selectRaw('
                SUM(mk.sales_amnt) as sales_amnt,
                SUM(mk.access_num) as access_num,
                AVG(mk.conversion_rate) as conversion_rate,
                AVG(mk.sales_amnt_per_user) as sales_amnt_per_user
            ')
            ->first();
        $expectedMqKpi = ! is_null($expectedMqKpi) ? $expectedMqKpi->toArray() : [];
        $actualMqKpi = $this->mqAccountingService->getListMqKpiByStoreId($storeId, $filters, $isMonthQuery)->toArray();

        $expectedSalesAmnt = Arr::get($expectedMqKpi, 'sales_amnt', 0);
        $actualSalesAmnt = Arr::get($actualMqKpi, 'sales_amnt', 0);
        $expectedAccessNum = Arr::get($expectedMqKpi, 'access_num', 0);
        $actualAccessNum = Arr::get($actualMqKpi, 'access_num', 0);
        $expectedConversionRate = Arr::get($expectedMqKpi, 'conversion_rate', 0);
        $actualConversionRate = Arr::get($actualMqKpi, 'conversion_rate', 0);
        $expectedSalesAmntPerUser = Arr::get($expectedMqKpi, 'sales_amnt_per_user', 0);
        $actualSalesAmntPerUser = Arr::get($actualMqKpi, 'sales_amnt_per_user', 0);

        $targetAchievementRate = [
            'sales_amnt' => round($expectedSalesAmnt ? $actualSalesAmnt / $expectedSalesAmnt * 100 : 0, 2),
            'access_num' => round($expectedAccessNum ? $actualAccessNum / $expectedAccessNum * 100 : 0, 2),
            'conversion_rate' => round(
                $expectedConversionRate ? $actualConversionRate / $expectedConversionRate * 100 : 0,
                2
            ),
            'sales_amnt_per_user' => round(
                $expectedSalesAmntPerUser ? $actualSalesAmntPerUser / $expectedSalesAmntPerUser * 100 : 0,
                2
            ),
        ];

        return [
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date'  => Arr::get($filters, 'to_date'),
            'target_achievement_rate' => $targetAchievementRate,
            'performance_summary' => $actualMqKpi,
        ];
    }

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array
    {
        $adsTypes = collect(KpiConstant::ADS_TYPES)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();

        return [
            'ads_types' => $adsTypes,
        ];
    }

    public function getKPIByDate(string $storeId, Carbon $date): ?MqKpi
    {
        /** @var MqKpi $mqKPI */
        $mqKPI = $this->model()->newQuery()
            ->join('mq_accounting as ma', function (JoinClause $join) use ($storeId, $date) {
                $join->on('ma.mq_kpi_id', '=', 'mq_kpi.id')
                    ->where('ma.store_id', $storeId)
                    ->where('ma.year', $date->year)
                    ->where('ma.month', $date->month);
            })->first();

        return $mqKPI;
    }
}
