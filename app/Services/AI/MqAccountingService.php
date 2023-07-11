<?php

namespace App\Services\AI;

use App\Services\Service;
use App\Support\Traits\HasMqDateTimeHandler;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class MqAccountingService extends Service
{
    use HasMqDateTimeHandler;

    public function getListByStore(string $storeId, array $filter = [])
    {
        return [];
    }

    public function getMonthlyChangesInFinancialIndicators(string $storeId, array $filter = [])
    {
        return [];
    }

    /**
     * Get the cumulative change in revenue and profit.
     */
    public function getCumulativeChangeInRevenueAndProfit($storeId, array $filter = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filter);
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

    public function getForecastVsActual($storeId, array $filter = []): Collection
    {
        return collect([
            'sales_amnt' => rand(7000000, 20000000),
            'profit' => rand(7000000, 20000000),
        ]);
    }
}
