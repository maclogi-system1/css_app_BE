<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\ShopAnalyticsDaily;
use App\Models\KpiRealData\ShopAnalyticsMonthly;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class StoreChartService extends Service
{
    use HasMqDateTimeHandler;

    /**
     * Query chart stores's conversion rate analysis.
     */
    public function getDataChartComparisonConversionRate(array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataChartYearMonthComparisonConversionRate($filters);
        }
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $dailyResult = ShopAnalyticsDaily::where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join(
                'shop_analytics_daily_conversion_rate as daily_conversion_rate',
                'daily_conversion_rate.conversion_rate_id',
                '=',
                'shop_analytics_daily.conversion_rate_id'
            )
            ->selectRaw('store_id, date, AVG(daily_conversion_rate.all_rate) as conversion_rate')
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
                    'conversion_rate' => round(floatval(Arr::get($storeVal, 'conversion_rate', 0)), 2),
                ]);
            }
            $data->add([
                'date' => substr($dailyKey, 0, 4).'/'.substr($dailyKey, 4, 2).'/'.substr($dailyKey, 6, 2),
                'stores_conversion_rate' => $listStoreValues,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query summary conversion rate analysis.
     */
    public function getDataTableConversionRateAnalysis(array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataYearMonthTableConversionRateAnalysis($filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $dailyResult = ShopAnalyticsDaily::where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join(
                'shop_analytics_daily_conversion_rate as daily_conversion_rate',
                'daily_conversion_rate.conversion_rate_id',
                '=',
                'shop_analytics_daily.conversion_rate_id'
            )
            ->selectRaw('
                date, 
                AVG(daily_conversion_rate.all_rate) as all_rate, 
                AVG(daily_conversion_rate.pc) as pc,
                AVG(daily_conversion_rate.app) as app,
                AVG(daily_conversion_rate.device) as device
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
                'total' => round(floatval(Arr::get($salesData, 'all_rate', 0)), 2),
                'pc' => round(floatval(Arr::get($salesData, 'pc', 0)), 2),
                'app' => round(floatval(Arr::get($salesData, 'app', 0)), 2),
                'phone' =>  round(floatval(Arr::get($salesData, 'device', 0)), 2),
            ]);
        }

        return $data;
    }

    public function getDataChartRelationPVAndConversionRate($filters): Collection
    {
        $dataFake = collect();
        for ($i = 0; $i < 30; $i++) {
            $dataFake->add([
                'conversion_rate' => rand(0, 5000),
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

    /**
     * Query chart stores's conversion rate analysis.
     */
    private function getDataChartYearMonthComparisonConversionRate(array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $dailyResult = ShopAnalyticsMonthly::where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join(
                'shop_analytics_monthly_conversion_rate as monthly_conversion_rate',
                'monthly_conversion_rate.conversion_rate_id',
                '=',
                'shop_analytics_monthly.conversion_rate_id'
            )
            ->selectRaw('store_id, date, AVG(monthly_conversion_rate.all_rate) as conversion_rate')
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
                    'conversion_rate' => round(floatval(Arr::get($storeVal, 'conversion_rate', 0)), 2),
                ]);
            }
            $data->add([
                'date' => substr($dailyKey, 0, 4).'/'.substr($dailyKey, 4, 2),
                'stores_conversion_rate' => $listStoreValues,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query summary conversion rate analysis by year-month.
     */
    private function getDataYearMonthTableConversionRateAnalysis(array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $dailyResult = ShopAnalyticsMonthly::where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join(
                'shop_analytics_monthly_conversion_rate as monthly_conversion_rate',
                'monthly_conversion_rate.conversion_rate_id',
                '=',
                'shop_analytics_monthly.conversion_rate_id'
            )
            ->selectRaw('
                date, 
                AVG(monthly_conversion_rate.all_rate) as all_rate, 
                AVG(monthly_conversion_rate.pc) as pc,
                AVG(monthly_conversion_rate.app) as app,
                AVG(monthly_conversion_rate.device) as device
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
                'date' => substr($dailyKey, 0, 4).'/'.substr($dailyKey, 4, 2),
                'total' => round(floatval(Arr::get($salesData, 'all_rate', 0)), 2),
                'pc' => round(floatval(Arr::get($salesData, 'pc', 0)), 2),
                'app' => round(floatval(Arr::get($salesData, 'app', 0)), 2),
                'phone' =>  round(floatval(Arr::get($salesData, 'device', 0)), 2),
            ]);
        }

        return $data;
    }
}
