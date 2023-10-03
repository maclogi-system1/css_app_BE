<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\ShopAnalyticsDaily;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;

class SalesAmntPerUserService extends Service
{
    use HasMqDateTimeHandler;

    /**
     * Get data ads analysis summary from AI.
     */
    public function getChartSummarySalesAmntPerUser($storeId, array $filters)
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $dailyResult = ShopAnalyticsDaily::where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join(
                'shop_analytics_daily_sales_amnt_per_user as daily_sales',
                'daily_sales.sales_amnt_per_user_id',
                '=',
                'shop_analytics_daily.sales_amnt_per_user_id'
            )
            ->selectRaw('store_id, date, AVG(daily_sales.all_value) as sales_amnt_per_user')
            ->groupBy('store_id', 'date')
            ->orderBy('date')
            ->get()
            ->groupBy('date');
        $dailyResult = ! is_null($dailyResult) ? $dailyResult->toArray() : [];

        $data = collect();
        foreach ($dailyResult as $dailyKey => $dailyItem) {
            $listStoreValues = collect();
            foreach ($dailyItem as $storeVal) {
                $listStoreValues->add([
                    'display_name' => Arr::get($storeVal, 'store_id'),
                    'store_id' => Arr::get($storeVal, 'store_id'),
                    'sales_amnt_per_user' => floatval(Arr::get($storeVal, 'sales_amnt_per_user', 0)),
                ]);
            }
            $data->add([
                'date' => substr($dailyKey, 0, 4).'/'.substr($dailyKey, 4, 2).'/'.substr($dailyKey, 6, 2),
                'stores_sales_amnt_per_user' => $listStoreValues,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get data compare sale amount per user with last year data from AI.
     */
    public function getSalesAmntPerUserComparisonTable($storeId, array $filters)
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $dailyResult = ShopAnalyticsDaily::where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join(
                'shop_analytics_daily_sales_amnt_per_user as daily_sales',
                'daily_sales.sales_amnt_per_user_id',
                '=',
                'shop_analytics_daily.sales_amnt_per_user_id'
            )
            ->selectRaw('
                date, 
                AVG(daily_sales.all_value) as all_value, 
                AVG(daily_sales.pc) as pc,
                AVG(daily_sales.app) as app,
                AVG(daily_sales.device) as device
            ')
            ->orderBy('date')
            ->groupBy('date')
            ->get()
            ->groupBy('date');
        $dailyResult = ! is_null($dailyResult) ? $dailyResult->toArray() : [];

        $data = collect();
        foreach ($dailyResult as $dailyKey => $dailyItem) {
            $salesData = $dailyItem[0];
            $data->add([
                'date' => substr($dailyKey, 0, 4).'/'.substr($dailyKey, 4, 2).'/'.substr($dailyKey, 6, 2),
                'total' => floatval(Arr::get($salesData, 'all_value', 0)),
                'pc' => floatval(Arr::get($salesData, 'pc', 0)),
                'app' => floatval(Arr::get($salesData, 'app', 0)),
                'phone' =>  floatval(Arr::get($salesData, 'device', 0)),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get data chart PV and sales amount per user from AI.
     */
    public function getChartPVSalesAmntPerUser($storeId, array $filters)
    {
        $dataFake = collect();

        for ($i = 0; $i < 30; $i++) {
            $dataFake->add([
                'sales_all' =>  rand(1000, 5000),
                'PV' => rand(0, 70000),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'chart_pv' => $dataFake,
            ]),
        ]);
    }
}
