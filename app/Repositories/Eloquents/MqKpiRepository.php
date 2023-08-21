<?php

namespace App\Repositories\Eloquents;

use App\Models\MqKpi;
use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\MqKpiRepository as MqKpiRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\MqAccountingService;
use App\Support\Traits\HasMqDateTimeHandler;
use Illuminate\Support\Arr;

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
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $expectedMqKpi = $this->mqAccountingRepository->model()
            ->dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->where('mq_accounting.store_id', $storeId)
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->selectRaw('
                SUM(mk.sales_amnt) as sales_amnt,
                SUM(mk.access_num) as access_num,
                SUM(mk.conversion_rate) as conversion_rate,
                SUM(mk.sales_amnt_per_user) as sales_amnt_per_user
            ')
            ->first()
            ->toArray();
        $actualMqKpi = $this->mqAccountingService->getListMqKpiByStoreId($storeId, $filters)->toArray();

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
            'target_achievement_rate' => $targetAchievementRate,
            'performance_summary' => $actualMqKpi,
        ];
    }
}
