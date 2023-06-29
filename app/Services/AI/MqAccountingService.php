<?php

namespace App\Services\AI;

use App\Services\Service;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class MqAccountingService extends Service
{
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
        $fromDate = Carbon::create(Arr::get($filter, 'from_date'));
        $toDate = Carbon::create(Arr::get($filter, 'to_date'));
        $dateRange = $this->getDateTimeRange($fromDate, $toDate);

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

    public function getDateTimeRange($fromDate, $toDate)
    {
        $period = new CarbonPeriod($fromDate, '1 month', $toDate);

        $result = [];

        foreach ($period as $dateTime) {
            $result[] = $dateTime->format('Y-m');
        }

        return $result;
    }
}
