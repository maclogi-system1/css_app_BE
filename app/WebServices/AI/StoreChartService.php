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
    public function getDataChartComparisonConversionRate(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataChartYearMonthComparisonConversionRate($storeId, $filters);
        }
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $dailyResult = ShopAnalyticsDaily::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
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

        $conversionRateData = collect();
        foreach ($dailyResult as $dailyKey => $dailyItem) {
            $conversionRateData->add([
                'date' => substr($dailyKey, 0, 4).'/'.substr($dailyKey, 4, 2).'/'.substr($dailyKey, 6, 2),
                'conversion_rate' => round(floatval(Arr::get($dailyItem[0], 'conversion_rate', 0)), 2),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'chart_conversion_rate' => $conversionRateData,
            ]),
        ]);
    }

    /**
     * Query summary conversion rate analysis.
     */
    public function getDataTableConversionRateAnalysis(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataYearMonthTableConversionRateAnalysis($storeId, $filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $dailyResult = ShopAnalyticsDaily::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
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
            ->groupBy('date', 'store_id')
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

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'table_conversion_rate' => $data,
            ]),
        ]);
    }

    /**
     * Query PV chart data.
     */
    public function getDataChartRelationPVAndConversionRate(string $storeId, $filters, bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataYearMonthChartRelationPVAndConversionRate($storeId, $filters);
        }
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $dailyResult = ShopAnalyticsDaily::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join(
                'shop_analytics_daily_conversion_rate as daily_conversion_rate',
                'daily_conversion_rate.conversion_rate_id',
                '=',
                'shop_analytics_daily.conversion_rate_id'
            )
            ->join(
                'shop_analytics_daily_access_num as daily_access_num',
                'daily_access_num.access_num_id',
                '=',
                'shop_analytics_daily.access_num_id'
            )
            ->selectRaw('
                AVG(daily_conversion_rate.all_rate) as all_rate,
                SUM(daily_access_num.all_value) as all_access
            ')
            ->groupBy('date', 'store_id')
            ->get();
        $dailyResult = ! is_null($dailyResult) ? $dailyResult->toArray() : [];
        $data = collect();
        foreach ($dailyResult as $item) {
            $data->add([
                'conversion_rate' => round(Arr::get($item, 'all_rate', 0), 2),
                'PV' => intval(Arr::get($item, 'all_access', 0)),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'chart_pv' => $data,
            ]),
        ]);
    }

    /**
     * Query chart stores's conversion rate analysis.
     */
    private function getDataChartYearMonthComparisonConversionRate(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $dailyResult = ShopAnalyticsMonthly::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
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

        $conversionRateData = collect();
        foreach ($dailyResult as $dailyKey => $dailyItem) {
            $conversionRateData->add([
                'date' => substr($dailyKey, 0, 4).'/'.substr($dailyKey, 4, 2),
                'conversion_rate' => round(floatval(Arr::get($dailyItem[0], 'conversion_rate', 0)), 2),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'chart_conversion_rate' => $conversionRateData,
            ]),
        ]);
    }

    /**
     * Query summary conversion rate analysis by year-month.
     */
    private function getDataYearMonthTableConversionRateAnalysis($storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $dailyResult = ShopAnalyticsMonthly::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
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
            ->groupBy('date', 'store_id')
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

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'table_conversion_rate' => $data,
            ]),
        ]);
    }

    /**
     * Query PV chart data by year-month.
     */
    private function getDataYearMonthChartRelationPVAndConversionRate(string $storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $monthlyResult = ShopAnalyticsMonthly::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join(
                'shop_analytics_monthly_conversion_rate as monthly_conversion_rate',
                'monthly_conversion_rate.conversion_rate_id',
                '=',
                'shop_analytics_monthly.conversion_rate_id'
            )
            ->join(
                'shop_analytics_monthly_access_num as monthly_access_num',
                'monthly_access_num.access_num_id',
                '=',
                'shop_analytics_monthly.access_num_id'
            )
            ->selectRaw('
                AVG(monthly_conversion_rate.all_rate) as all_rate,
                SUM(monthly_access_num.all_value) as all_access
            ')
            ->groupBy('date', 'store_id')
            ->get();
        $monthlyResult = ! is_null($monthlyResult) ? $monthlyResult->toArray() : [];
        $data = collect();
        foreach ($monthlyResult as $item) {
            $data->add([
                'conversion_rate' => round(Arr::get($item, 'all_rate', 0), 2),
                'PV' => intval(Arr::get($item, 'all_access', 0)),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'chart_pv' => $data,
            ]),
        ]);
    }
}
