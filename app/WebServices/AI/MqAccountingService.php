<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\MqAccounting;
use App\Models\KpiRealData\ShopAnalyticsDaily;
use App\Models\KpiRealData\ShopAnalyticsMonthly;
use App\Repositories\Contracts\ShopSettingMqAccountingRepository;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MqAccountingService extends Service
{
    use HasMqDateTimeHandler;

    public function getList(array $filters = [])
    {
        $storeId = Arr::get($filters, 'store_id');
        $yearMonth = Arr::get($filters, 'year_month');

        $mqAccountings = MqAccounting::with(['mqKpi', 'mqAccessNum', 'mqAdSalesAmnt', 'mqUserTrends', 'mqCost'])
            ->when($yearMonth, function ($query, $yearMonth) {
                $yearMonth = Carbon::createFromFormat('Y-m', $yearMonth);
                $query->where([
                    'year' => $yearMonth->year,
                    'month' => $yearMonth->month,
                ]);
            })
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->get()
            ->map(function ($item) {
                $item->store_opening_fee = $item->mqCost->opening_fee;
                $item->csv_usage_fee = $item->mqCost->csv_usage_fee;
                $item->ltv_2y_amnt = $item->mqCost->ltv_2y_amnt;
                $item->lim_cpa = $item->mqCost->lim_cpa;
                $item->cpo_via_ad = $item->mqCost->cpo_via_ad;
                $item->fixed_cost = $item->mqCost?->cost_sum;

                return $item;
            });

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $mqAccountings,
        ]);
    }

    public function getListByStore(string $storeId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        /** @var \App\Repositories\Contracts\ShopSettingMqAccountingRepository */
        $shopSettingMqAccountingRepository = app(ShopSettingMqAccountingRepository::class);
        $settings = $shopSettingMqAccountingRepository->getListByStore($storeId, $filters);

        $mqAccountings = MqAccounting::with(['mqKpi', 'mqAccessNum', 'mqAdSalesAmnt', 'mqUserTrends', 'mqCost'])
            ->where('store_id', $storeId)
            ->dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->get()
            ->map(function ($item) use ($settings) {
                if ($settings->isNotEmpty()) {
                    $setting = $settings->where(function ($shopSetting) use ($item) {
                        return Carbon::create($shopSetting->date)->year == $item->year
                            && Carbon::create($shopSetting->date)->month == $item->month;
                    })->first();

                    if ($setting) {
                        $item->mqCost->opening_fee = $setting->actual_store_opening_fee;
                        $item->mqCost->csv_usage_fee = $setting->actual_csv_usage_fee;
                        $item->mqCost->commision_rate = $setting->actual_commission_rate;
                        $item->mqCost->cost_price_rate = $setting->actual_cost_rate;
                        $item->mqCost->management_agency_fee = $setting->actual_management_agency_expenses;
                    }
                }

                $item->store_opening_fee = $item->mqCost->opening_fee;
                $item->csv_usage_fee = $item->mqCost->csv_usage_fee;
                $item->ltv_2y_amnt = $item->mqCost->ltv_2y_amnt;
                $item->lim_cpa = $item->mqCost->lim_cpa;
                $item->cpo_via_ad = $item->mqCost->cpo_via_ad;
                $item->fixed_cost = $item->mqCost?->cost_sum;

                return $item;
            })
            ->toArray();

        return empty($mqAccountings) ? $settings->map(function ($setting) use ($storeId) {
            $fixedCost = $setting->actual_management_agency_expenses
                + $setting->actual_csv_usage_fee
                + $setting->actual_store_opening_fee;

            return [
                'store_id' => $storeId,
                'year' => Carbon::create($setting->date)->year,
                'month' => Carbon::create($setting->date)->month,
                'csv_usage_fee' => $setting->actual_csv_usage_fee,
                'store_opening_fee' => $setting->actual_store_opening_fee,
                'ltv_2y_amnt'=> 0,
                'lim_cpa'=> 0,
                'cpo_via_ad'=> 0,
                'fixed_cost'=> $fixedCost,
                'mq_kpi' => [
                    'sales_amnt' => 0,
                    'sales_num' => 0,
                    'access_num' => 0,
                    'conversion_rate' => 0,
                    'sales_amnt_per_user' => 0,
                ],
                'mq_access_num' => [
                    'access_flow_sum' => 0,
                    'search_flow_num' => 0,
                    'ranking_flow_num' => 0,
                    'instagram_flow_num' => 0,
                    'google_flow_num' => 0,
                    'cpc_num' => 0,
                    'display_num' => 0,
                ],
                'mq_ad_sales_amnt' => [
                    'sales_amnt_via_ad' => 0,
                    'sales_amnt_seasonal' => 0,
                    'sales_amnt_event' => 0,
                    'tda_access_num' => 0,
                    'tda_v_sales_amnt' => 0,
                    'tda_v_roas' => 0,
                ],
                'mq_user_trends' => [
                    'new_sales_amnt' => 0,
                    'new_sales_num' => 0,
                    'new_price_per_user' => 0,
                    're_sales_amnt' => 0,
                    're_sales_num' => 0,
                    're_price_per_user' => 0,
                ],
                'mq_cost' =>[
                    'coupon_points_cost' => 0,
                    'coupon_points_cost_rate' => 0,
                    'ad_cost' => 0,
                    'ad_cpc_cost' => 0,
                    'ad_season_cost' => 0,
                    'ad_event_cost' => 0,
                    'ad_tda_cost' => 0,
                    'ad_cost_rate' => 0,
                    'cost_price' => 0,
                    'cost_price_rate' => $setting->actual_cost_rate,
                    'postage' => 0,
                    'postage_rate' => 0,
                    'commision' => 0,
                    'commision_rate' => $setting->actual_commission_rate,
                    'variable_cost_sum' => 0,
                    'gross_profit' => 0,
                    'gross_profit_rate' => 0,
                    'management_agency_fee' => $setting->actual_management_agency_expenses,
                    'opening_fee' => $setting->actual_store_opening_fee,
                    'csv_usage_fee' => $setting->actual_csv_usage_fee,
                    'management_agency_fee_rate' => 0,
                    'cost_sum' => $fixedCost,
                    'profit' => 0,
                    'sum_profit' => 0,
                    'ltv_2y_amnt' => 0,
                    'lim_cpa' => 0,
                    'cpo_via_ad' => 0,
                ],
            ];
        })->toArray() : $mqAccountings;
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

        return MqAccounting::dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->where('store_id', $storeId)
            ->join('mq_cost as mc', 'mc.mq_cost_id', '=', 'mq_accounting.mq_cost_id')
            ->join('mq_kpi as mk', 'mk.mq_kpi_id', '=', 'mq_accounting.mq_kpi_id')
            ->select(
                'mq_accounting.store_id',
                DB::raw("CONCAT(mq_accounting.year, '/', LPAD(mq_accounting.month, 2, '0')) as `year_month`"),
                'mk.sales_amnt',
                'mc.profit',
                'mq_accounting.year',
                'mq_accounting.month',
            )
            ->get();
    }

    public function getSalesAmountAndProfit($storeId, array $filters = []): ?MqAccounting
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        return MqAccounting::dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->where('store_id', $storeId)
            ->join('mq_kpi as mk', 'mk.mq_kpi_id', '=', 'mq_accounting.mq_kpi_id')
            ->join('mq_cost as mc', 'mc.mq_cost_id', '=', 'mq_accounting.mq_cost_id')
            ->select(
                DB::raw('SUM(CASE WHEN mk.sales_amnt IS NULL THEN 0 ELSE mk.sales_amnt END) as sales_amnt'),
                DB::raw('SUM(CASE WHEN mc.profit IS NULL THEN 0 ELSE mc.profit END) as profit'),
            )
            ->first();
    }

    /**
     * Query daily analytics KPI summary data from AI DB.
     */
    public function getListMqKpiByStoreId($storeId, array $filters = [], bool $isMonthQuery = false)
    {
        if ($isMonthQuery) {
            return $this->getListYearMonthMqKpiByStoreId($storeId, $filters);
        }
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
                    ->first();
        $result = ! is_null($result) ? $result->toArray() : [];

        $salesAmnt = ! is_null(Arr::get($result, 'sales_amnt', 0)) ? intval(Arr::get($result, 'sales_amnt', 0)) : 0;
        $accessNum = ! is_null(Arr::get($result, 'access_num', 0)) ? intval(Arr::get($result, 'access_num', 0)) : 0;
        $conversionRate = ! is_null(Arr::get($result, 'conversion_rate', 0)) ? floatval(Arr::get($result, 'conversion_rate', 0)) : 0;
        $salesAmntPerUser = ! is_null(Arr::get($result, 'sales_amnt_per_user', 0)) ? floatval(Arr::get($result, 'sales_amnt_per_user', 0)) : 0;

        return collect([
            'sales_amnt' => $salesAmnt,
            'access_num' => $accessNum,
            'conversion_rate' => $conversionRate,
            'sales_amnt_per_user' => $salesAmntPerUser,
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

    /**
     * Query monthly analytics KPI summary data from AI DB.
     */
    private function getListYearMonthMqKpiByStoreId($storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $result = ShopAnalyticsMonthly::where('store_id', $storeId)
                    ->whereRaw('date >= ? AND date <= ?', [$fromDateStr, $toDateStr])
                    ->join('shop_analytics_monthly_sales_amnt as sales', 'sales.sales_amnt_id', '=', 'shop_analytics_monthly.sales_amnt_id')
                    ->join('shop_analytics_monthly_access_num as shop_access', 'shop_access.access_num_id', '=', 'shop_analytics_monthly.access_num_id')
                    ->join('shop_analytics_monthly_conversion_rate as conversion_rate', 'conversion_rate.conversion_rate_id', '=', 'shop_analytics_monthly.conversion_rate_id')
                    ->join('shop_analytics_monthly_sales_amnt_per_user as sales_per_user', 'sales_per_user.sales_amnt_per_user_id', '=', 'shop_analytics_monthly.sales_amnt_per_user_id')
                    ->selectRaw('
                        SUM(sales.all_value) as sales_amnt,
                        SUM(shop_access.all_value) as access_num,
                        AVG(conversion_rate.all_rate) as conversion_rate,
                        AVG(sales_per_user.all_value) as sales_amnt_per_user
                    ')
                    ->first();
        $result = ! is_null($result) ? $result->toArray() : [];

        $salesAmnt = ! is_null(Arr::get($result, 'sales_amnt', 0)) ? intval(Arr::get($result, 'sales_amnt', 0)) : 0;
        $accessNum = ! is_null(Arr::get($result, 'access_num', 0)) ? intval(Arr::get($result, 'access_num', 0)) : 0;
        $conversionRate = ! is_null(Arr::get($result, 'conversion_rate', 0)) ? floatval(Arr::get($result, 'conversion_rate', 0)) : 0;
        $salesAmntPerUser = ! is_null(Arr::get($result, 'sales_amnt_per_user', 0)) ? floatval(Arr::get($result, 'sales_amnt_per_user', 0)) : 0;

        return collect([
            'sales_amnt' => $salesAmnt,
            'access_num' => $accessNum,
            'conversion_rate' => $conversionRate,
            'sales_amnt_per_user' => $salesAmntPerUser,
        ]);
    }

    /**
     * Get total sale amount, cost and profit by store id.
     */
    public function getTotalParamByStore(string $storeId, array $filters = []): ?MqAccounting
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        return MqAccounting::dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->where('mq_accounting.store_id', $storeId)
            ->join('mq_kpi as mk', 'mk.mq_kpi_id', '=', 'mq_accounting.mq_kpi_id')
            ->join('mq_cost as mc', 'mc.mq_cost_id', '=', 'mq_accounting.mq_cost_id')
            ->select(
                'mq_accounting.store_id',
                DB::raw('SUM(mk.sales_amnt) as sales_amnt_total'),
                DB::raw('SUM(mc.cost_sum) as cost_sum_total'),
                DB::raw('SUM(mc.variable_cost_sum) as variable_cost_sum_total'),
                DB::raw('SUM(mc.profit) as profit_total'),
            )
            ->groupBy('mq_accounting.store_id')
            ->first();
    }

    /**
     * Get monthly changes in financial indicators.
     */
    public function financialIndicatorsMonthly($storeId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        return MqAccounting::dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->where('store_id', $storeId)
            ->join('mq_cost as mc', 'mc.mq_cost_id', '=', 'mq_accounting.mq_cost_id')
            ->join('mq_kpi as mk', 'mk.mq_kpi_id', '=', 'mq_accounting.mq_kpi_id')
            ->select(
                'mq_accounting.store_id',
                DB::raw("CONCAT(mq_accounting.year, '/', LPAD(mq_accounting.month, 2, '0')) as `year_month`"),
                'mk.sales_amnt',
                'mc.variable_cost_sum',
                'mc.profit',
                'mc.cost_sum as fixed_cost',
            )
            ->get();
    }

    /**
     * Get total sale amount, cost and profit by store id in a month.
     */
    public function getTotalParamByStoreInAMonth(string $storeId, CarbonInterface $date)
    {
        return MqAccounting::where('mq_accounting.store_id', $storeId)
            ->where('mq_accounting.year', $date->year)
            ->where('mq_accounting.month', $date->month)
            ->join('mq_kpi as mk', 'mk.mq_kpi_id', '=', 'mq_accounting.mq_kpi_id')
            ->join('mq_cost as mc', 'mc.mq_cost_id', '=', 'mq_accounting.mq_cost_id')
            ->select(
                'mq_accounting.store_id',
                'mk.sales_amnt as sales_amnt_total',
                'mc.cost_sum as cost_sum_total',
                'mc.variable_cost_sum as variable_cost_sum_total',
                'mc.profit as profit_total',
            )
            ->first();
    }

    /**
     * Query daily analytics KPI summary data from AI DB.
     */
    public function getChartKpiTrends($storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $result = ShopAnalyticsMonthly::where('store_id', $storeId)
                    ->whereRaw('date >= ? AND date <= ?', [$fromDateStr, $toDateStr])
                    ->join('shop_analytics_monthly_sales_amnt as sales', 'sales.sales_amnt_id', '=', 'shop_analytics_monthly.sales_amnt_id')
                    ->join('shop_analytics_monthly_access_num as shop_access', 'shop_access.access_num_id', '=', 'shop_analytics_monthly.access_num_id')
                    ->join('shop_analytics_monthly_conversion_rate as conversion_rate', 'conversion_rate.conversion_rate_id', '=', 'shop_analytics_monthly.conversion_rate_id')
                    ->join('shop_analytics_monthly_sales_amnt_per_user as sales_per_user', 'sales_per_user.sales_amnt_per_user_id', '=', 'shop_analytics_monthly.sales_amnt_per_user_id')
                    ->selectRaw('
                        store_id,
                        date,
                        SUM(sales.all_value) as sales_amnt,
                        SUM(shop_access.all_value) as access_num,
                        AVG(conversion_rate.all_rate) as conversion_rate,
                        AVG(sales_per_user.all_value) as sales_amnt_per_user
                    ')
                    ->groupBy('store_id', 'date')
                    ->get();
        $result = ! is_null($result) ? $result->toArray() : [];

        $data = collect();
        foreach ($result as $kpiItem) {
            $salesAmnt = ! is_null(Arr::get($kpiItem, 'sales_amnt', 0)) ? intval(Arr::get($kpiItem, 'sales_amnt', 0)) : 0;
            $accessNum = ! is_null(Arr::get($kpiItem, 'access_num', 0)) ? intval(Arr::get($kpiItem, 'access_num', 0)) : 0;
            $conversionRate = ! is_null(Arr::get($kpiItem, 'conversion_rate', 0)) ? floatval(Arr::get($kpiItem, 'conversion_rate', 0)) : 0;
            $salesAmntPerUser = ! is_null(Arr::get($kpiItem, 'sales_amnt_per_user', 0)) ? floatval(Arr::get($kpiItem, 'sales_amnt_per_user', 0)) : 0;
            $date = Arr::get($kpiItem, 'date', '');
            $data->add([
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'sales_amnt' => $salesAmnt,
                'access_num' => $accessNum,
                'conversion_rate' => round($conversionRate, 2),
                'sales_amnt_per_user' => round($salesAmntPerUser, 2),
            ]);
        }

        return $data;
    }

    public function getListReSalesNum(array $filters = [])
    {
        $yearMonth = Arr::get($filters, 'year_month');

        return MqAccounting::join(
            'mq_user_trends as mut',
            'mut.mq_user_trends_id',
            '=',
            'mq_accounting.mq_user_trends_id'
        )
            ->when($yearMonth, function ($query, $yearMonth) {
                [$year, $month] = explode('-', $yearMonth);
                $query->where([
                    'year' => $year,
                    'month' => $month,
                ]);
            })
            ->select(
                'store_id',
                DB::raw('mut.re_sales_num / (mut.re_sales_num + mut.new_sales_num) as re_sales_num_rate'),
            )
            ->get();
    }
}
