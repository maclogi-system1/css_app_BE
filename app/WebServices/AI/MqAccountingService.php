<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\ShopAnalyticsDaily;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MqAccountingService extends Service
{
    use HasMqDateTimeHandler;

    public function getListByStore(string $storeId, array $filters = [])
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
                'ltv_2y_amnt' => rand(1000, 20000000),
                'lim_cpa' => rand(1000, 20000000),
                'cpo_via_ad' => rand(1000, 20000000),
                'csv_usage_fee' => rand(1000, 20000000),
                'store_opening_fee' => rand(1000, 20000000),
                'fixed_cost' => rand(1000, 100000),
                'mq_kpi' => [
                    'sales_amnt' => rand(1000, 20000000),
                    'sales_num' => rand(1000, 20000000),
                    'access_num' => rand(1000, 20000000),
                    'conversion_rate' => rand(1000, 999999),
                    'sales_amnt_per_user' => rand(1000, 20000000),
                ],
                'mq_access_num' => [
                    'access_flow_sum' => rand(1000, 20000000),
                    'search_flow_num' => rand(1000, 20000000),
                    'ranking_flow_num' => rand(1000, 20000000),
                    'instagram_flow_num' => rand(1000, 20000000),
                    'google_flow_num' => rand(1000, 20000000),
                    'cpc_num' => rand(1000, 20000000),
                    'display_num' => rand(1000, 20000000),
                ],
                'mq_ad_sales_amnt' => [
                    'sales_amnt_via_ad' => rand(1000, 20000000),
                    'sales_amnt_seasonal' => rand(1000, 20000000),
                    'sales_amnt_event' => rand(1000, 20000000),
                    'tda_access_num' => rand(1000, 20000000),
                    'tda_v_sales_amnt' => rand(1000, 20000000),
                    'tda_v_roas' => rand(1000, 999999),
                ],
                'mq_user_trends' => [
                    'new_sales_amnt' => rand(1000, 20000000),
                    'new_sales_num' => rand(1000, 20000000),
                    'new_price_per_user' => rand(1000, 20000000),
                    're_sales_amnt' => rand(1000, 20000000),
                    're_sales_num' => rand(1000, 20000000),
                    're_price_per_user' => rand(1000, 20000000),
                ],
                'mq_cost' => [
                    'coupon_points_cost' => rand(1000, 20000000),
                    'coupon_points_cost_rate' => rand(1000, 999999),
                    'ad_cost' => rand(1000, 20000000),
                    'ad_cpc_cost' => rand(1000, 20000000),
                    'ad_season_cost' => rand(1000, 20000000),
                    'ad_event_cost' => rand(1000, 20000000),
                    'ad_tda_cost' => rand(1000, 20000000),
                    'ad_cost_rate' => rand(1000, 999999),
                    'cost_price' => rand(1000, 20000000),
                    'cost_price_rate' => rand(1000, 999999),
                    'postage' => rand(1000, 20000000),
                    'postage_rate' => rand(1000, 999999),
                    'commision' => rand(1000, 20000000),
                    'commision_rate' => rand(1000, 999999),
                    'variable_cost_sum' => rand(1000, 20000000),
                    'gross_profit' => rand(1000, 20000000),
                    'gross_profit_rate' => rand(1000, 999999),
                    'management_agency_fee' => rand(1000, 20000000),
                    'reserve1' => rand(1000, 20000000),
                    'reserve2' => rand(1000, 20000000),
                    'management_agency_fee_rate' => rand(1000, 999999),
                    'cost_sum' => rand(1000, 20000000),
                    'profit' => rand(1000, 20000000),
                    'sum_profit' => rand(1000, 20000000),
                ],
            ];
        }

        return $result;
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
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $result = ShopAnalyticsDaily::where('store_id', $storeId)
                    ->whereRaw('date >= ? AND date <= ?', [$fromDateStr, $toDateStr])
                    ->join('shop_analytics_daily_sales_amnt as sales', 'sales.sales_amnt_id', '=', 'shop_analytics_daily.sales_amnt_id')
                    ->join('shop_analytics_daily_access_num as shop_access', 'shop_access.access_num_id', '=', 'shop_analytics_daily.access_num_id')
                    ->join('shop_analytics_daily_conversion_rate as conversion_rate', 'conversion_rate.conversion_rate_id', '=', 'shop_analytics_daily.conversion_rate_id')
                    ->join('shop_analytics_daily_sales_amnt_per_user as sales_per_user', 'sales_per_user.sales_amnt_per_user_id', '=', 'shop_analytics_daily.sales_amnt_per_user_id')
                    ->selectRaw('
                        SUM(sales.all_value) as sales_amnt,
                        SUM(shop_access.all_value) as access_num,
                        AVG(conversion_rate.all_rate) as conversion_rate,
                        AVG(sales_per_user.all_value) as sales_amnt_per_user
                    ')
                    ->first()->toArray();

        return collect([
            'sales_amnt' => Arr::get($result, 'sales_amnt', 0),
            'access_num' => Arr::get($result, 'access_num', 0),
            'conversion_rate' => Arr::get($result, 'conversion_rate', 0),
            'sales_amnt_per_user' => Arr::get($result, 'sales_amnt_per_user', 0),
        ]);
    }

    public function getMacroQueryResult(array $filters): Collection
    {
        $dataFake = collect();
        foreach ($filters as $filter) {
            $dataFake->add([
                'store_id' => Arr::get($filter, 'store_id'),
                'year' => Arr::get($filter, 'year'),
                'month' => Arr::get($filter, 'month'),
                'ltv_2y_amnt' => rand(1000, 20000000),
                'lim_cpa' => rand(1000, 20000000),
                'cpo_via_ad' => rand(1000, 20000000),
                'csv_usage_fee' => rand(1000, 20000000),
                'store_opening_fee' => rand(1000, 20000000),
                'fixed_cost' => rand(1000, 100000),
                'year_month' => Arr::get($filter, 'year').'-'.Arr::get($filter, 'month').'-01',
                'access_flow_sum' => rand(1000, 20000000),
                'search_flow_num' => rand(1000, 20000000),
                'ranking_flow_num' => rand(1000, 20000000),
                'instagram_flow_num' => rand(1000, 20000000),
                'google_flow_num' => rand(1000, 20000000),
                'cpc_num' => rand(1000, 20000000),
                'display_num' => rand(1000, 20000000),
                'sales_amnt_via_ad' => rand(1000, 20000000),
                'sales_amnt_seasonal' => rand(1000, 20000000),
                'sales_amnt_event' => rand(1000, 20000000),
                'tda_access_num' => rand(1000, 20000000),
                'tda_v_sales_amnt' => rand(1000, 20000000),
                'tda_v_roas' => rand(1000, 999999),
                'new_sales_amnt' => rand(1000, 20000000),
                'new_sales_num' => rand(1000, 20000000),
                'new_price_per_user' => rand(1000, 20000000),
                're_sales_amnt' => rand(1000, 20000000),
                're_sales_num' => rand(1000, 20000000),
                're_price_per_user' => rand(1000, 20000000),
                'coupon_points_cost' => rand(1000, 20000000),
                'coupon_points_cost_rate' => rand(1000, 999999),
                'ad_cost' => rand(1000, 20000000),
                'ad_cpc_cost' => rand(1000, 20000000),
                'ad_season_cost' => rand(1000, 20000000),
                'ad_event_cost' => rand(1000, 20000000),
                'ad_tda_cost' => rand(1000, 20000000),
                'ad_cost_rate' => rand(1000, 999999),
                'cost_price' => rand(1000, 20000000),
                'cost_price_rate' => rand(1000, 999999),
                'postage' => rand(1000, 20000000),
                'postage_rate' => rand(1000, 999999),
                'commision' => rand(1000, 20000000),
                'commision_rate' => rand(1000, 999999),
                'variable_cost_sum' => rand(1000, 20000000),
                'gross_profit' => rand(1000, 20000000),
                'gross_profit_rate' => rand(1000, 999999),
                'management_agency_fee' => rand(1000, 20000000),
                'reserve1' => rand(1000, 20000000),
                'reserve2' => rand(1000, 20000000),
                'management_agency_fee_rate' => rand(1000, 999999),
                'cost_sum' => rand(1000, 20000000),
                'profit' => rand(1000, 20000000),
                'sum_profit' => rand(1000, 20000000),
                'sales_amnt' => rand(1000, 20000000),
                'sales_num' => rand(1000, 20000000),
                'access_num' => rand(1000, 20000000),
                'conversion_rate' => rand(1000, 999999),
                'sales_amnt_per_user' => rand(1000, 20000000),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }
}
