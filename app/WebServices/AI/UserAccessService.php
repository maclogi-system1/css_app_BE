<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\MqAccounting;
use App\Models\KpiRealData\ShopAnalyticsDaily;
use App\Models\KpiRealData\ShopAnalyticsDailyAccessNum;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserAccessService extends Service
{
    use HasMqDateTimeHandler;

    /**
     * Get User Access chart data.
     */
    public function getListUserAccess(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getListYearMonthUserAccess($storeId, $filters);
        }
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $shopDailyAnalytics = ShopAnalyticsDaily::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->select('date', 'store_id', 'access_num_id');
        $dailyAccessNum = ShopAnalyticsDailyAccessNum::query()
            ->joinSub($shopDailyAnalytics, 'shop_analytics_daily', function ($join) {
                $join->on('shop_analytics_daily_access_num.access_num_id', '=', 'shop_analytics_daily.access_num_id');
            })
            ->select(
                'shop_analytics_daily.date',
                DB::raw('SUM(shop_analytics_daily_access_num.all_value) as daily_access')
            )
            ->groupBy('shop_analytics_daily.store_id', 'shop_analytics_daily.date')
            ->orderBy('shop_analytics_daily.date')
            ->get();
        $dailyAccessNum = ! is_null($dailyAccessNum) ? $dailyAccessNum->toArray() : [];

        $data = collect();
        foreach ($dailyAccessNum as $accessItem) {
            $date = Arr::get($accessItem, 'date');
            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'access_flow_sum' => Arr::get($accessItem, 'daily_access', 0),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    public function getListUserAccessAds(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $data = collect();
        $result = MqAccounting::where('store_id', $storeId)
            ->where('year', '>=', $dateRangeFilter['from_date']->year)
            ->where('month', '>=', $dateRangeFilter['from_date']->month)
            ->where('year', '<=', $dateRangeFilter['to_date']->year)
            ->where('month', '<=', $dateRangeFilter['to_date']->month)
            ->join('mq_access_num as ma', 'ma.mq_access_num_id', '=', 'mq_accounting.mq_access_num_id')
            ->selectRaw('
                store_id,
                CONCAT(year,"/",LPAD(month, 2, "0")) as date,
                ma.access_flow_sum,
                ma.cpc_num
            ')
            ->get();
        $result = ! is_null($result) ? $result->toArray() : [];
        foreach ($result as $item) {
            $data->add([
                'store_id' => $storeId,
                'date' => Arr::get($item, 'date'),
                'access_flow_sum' => Arr::get($item, 'access_flow_sum'),
                'cpc_num' => Arr::get($item, 'cpc_num'),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get user access chart data by year-month.
     */
    private function getListYearMonthUserAccess(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $realAccessData = MqAccounting::where('store_id', $storeId)
            ->where('year', '>=', $dateRangeFilter['from_date']->year)
            ->where('month', '>=', $dateRangeFilter['from_date']->month)
            ->where('year', '<=', $dateRangeFilter['to_date']->year)
            ->where('month', '<=', $dateRangeFilter['to_date']->month)
            ->join('mq_access_num as ma', 'ma.mq_access_num_id', '=', 'mq_accounting.mq_access_num_id')
            ->selectRaw('
                store_id,
                CONCAT(year,"/",LPAD(month, 2, "0")) as date,
                ma.access_flow_sum
            ')
            ->get();
        $realAccessData = ! is_null($realAccessData) ? $realAccessData->toArray() : [];

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $realAccessData,
        ]);
    }
}
