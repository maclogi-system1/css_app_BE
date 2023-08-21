<?php

namespace App\WebServices\AI;

use App\WebServices\Service;
use App\Support\Traits\HasMqDateTimeHandler;
use Illuminate\Support\Collection;

class MqAccountingService extends Service
{
    use HasMqDateTimeHandler;

    public function getListByStore(string $storeId, array $filters = [])
    {
        return [];
    }

    public function getMonthlyChangesInFinancialIndicators(string $storeId, array $filters = [])
    {
        return [];
    }

    /**
     * Get the cumulative change in revenue and profit.
     */
    public function getCumulativeChangeInRevenueAndProfit($storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateRange = $this->getDateTimeRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date']);

        $result = [];

        foreach ($dateRange as $yearMonth) {
            [$year, $month] = explode('-', $yearMonth);
            $result[] = [
                'store_id' => $storeId,
                'year' => intval($year),
                'month' => intval($month),
                'sales_amnt' => rand(100000, 999999),
                'profit' => rand(100000, 999999),
            ];
        }

        return collect($result);
    }

    public function getForecastVsActual($storeId, array $filters = []): Collection
    {
        return collect([
            'sales_amnt' => rand(7000000, 20000000),
            'profit' => rand(7000000, 20000000),
        ]);
    }

    public function getListMqKpiByStoreId($storeId, array $filters = [])
    {
        return collect([
            'sales_amnt' => rand(10000000, 20000000),
            'access_num' => rand(10000, 100000),
            'conversion_rate' => rand(10, 50),
            'sales_amnt_per_user' => rand(10000000, 20000000),
        ]);
    }
}
