<?php

namespace App\WebServices\AI;

use App\Constants\KpiConstant;
use App\Models\KpiRealData\CouponAdviceAd;
use App\Models\KpiRealData\RgroupAd;
use App\Models\KpiRealData\RppAd;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdsAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    /**
     * Query Ads summary data by date.
     */
    public function getAdsAnalysisSummary($storeId, $filters, bool $isMonthQuery = false)
    {
        $strategyId = Arr::get($filters, 'strategy_id');
        switch ($strategyId) {
            case KpiConstant::ADS_TYPE_RPP:
                if ($isMonthQuery) {
                    return $this->getYearMonthRppAdsSummary($storeId, $filters);
                }

                return $this->getRppAdsSummary($storeId, $filters);
                break;

            case KpiConstant::ADS_TYPE_COUPON_ADVANCE:
                if ($isMonthQuery) {
                    return $this->getYearMonthCouponAdvanceAdsSummary($storeId, $filters);
                }

                return $this->getCouponAdvanceAdsSummary($storeId, $filters);
                break;

            case KpiConstant::ADS_TYPE_RAKUTEN_GROUP_ADS:
                if ($isMonthQuery) {
                    return $this->getYearMonthRakutenGroupAdsSummarry($storeId, $filters);
                }

                return $this->getRakutenGroupAdsSummarry($storeId, $filters);
                break;

            default:
                return collect([
                    'success' => true,
                    'status' => 200,
                    'data' => collect(),
                ]);
                break;
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect(),
        ]);
    }

    /**
     * Query detail ads conversion with strategy id.
     */
    public function getListAdsConversion($storeId, $filters, bool $isMonthQuery = false)
    {
        $strategyId = Arr::get($filters, 'strategy_id');
        switch ($strategyId) {
            case KpiConstant::ADS_TYPE_RPP:
                if ($isMonthQuery) {
                    return $this->getYearMonthRppAdsDetail($storeId, $filters);
                }

                return $this->getRppAdsDetail($storeId, $filters);
                break;

            case KpiConstant::ADS_TYPE_COUPON_ADVANCE:
                if ($isMonthQuery) {
                    return $this->getYearMonthCouponAdvanceAdsDetail($storeId, $filters);
                }

                return $this->getCouponAdvanceAdsDetail($storeId, $filters);
                break;

            case KpiConstant::ADS_TYPE_RAKUTEN_GROUP_ADS:
                if ($isMonthQuery) {
                    return $this->getYearMonthRakutenGroupAdsDetail($storeId, $filters);
                }

                return $this->getRakutenGroupAdsDetail($storeId, $filters);
                break;

            default:
                return collect([
                    'success' => true,
                    'status' => 200,
                    'data' => collect(),
                ]);
                break;
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect(),
        ]);
    }

    public function getListProductByRoas($storeId, $filters): Collection
    {
        $dataFake = collect();

        $dataFake->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'high_roas_products' => collect([
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
            ]),
            'low_roas_products' => collect([
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
            ]),
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Query data chart sales and access.
     */
    public function getDataChartSalesAndAccess($storeId, $filters, bool $isMonthQuery = false)
    {
        $strategyId = Arr::get($filters, 'strategy_id');
        switch ($strategyId) {
            case KpiConstant::ADS_TYPE_RPP:
                if ($isMonthQuery) {
                    return $this->getYearMonthRppSalesAndAccess($storeId, $filters);
                }

                return $this->getRppSalesAndAccess($storeId, $filters);
                break;

            case KpiConstant::ADS_TYPE_COUPON_ADVANCE:
                if ($isMonthQuery) {
                    return $this->getYearMonthCouponSalesAndAccess($storeId, $filters);
                }

                return $this->getCouponSalesAndAccess($storeId, $filters);
                break;

            case KpiConstant::ADS_TYPE_RAKUTEN_GROUP_ADS:
                if ($isMonthQuery) {
                    return $this->getYearMonthRakutenGroupSalesAndAccess($storeId, $filters);
                }

                return $this->getRakutenGroupSalesAndAccess($storeId, $filters);
                break;

            default:
                return collect([
                    'success' => true,
                    'status' => 200,
                    'data' => collect(),
                ]);
                break;
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect(),
        ]);

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            'Y/m'
        );

        $dataFake = collect();
        $dataFake2 = collect();

        foreach ($dateTimeRange as $date) {
            $dataFake->add([
                'store_id' => $storeId,
                'date' => $date,
                'ads_revenue' => rand(60000, 100000),
                'total_revenue' => rand(60000, 100000),
                'increase_rate' =>  rand(10, 50),
            ]);
            $dataFake2->add([
                'store_id' => $storeId,
                'date' => $date,
                'sales_amnt' => [
                    'whole' => rand(60000, 100000),
                    'rpp_ad' => rand(10000, 20000),
                    'coupon_advice_ad' =>rand(10000, 20000),
                    'rgroup_ad' => rand(10000, 20000),
                ],
                'click_num' => [
                    'whole' => rand(50, 100),
                    'rpp_ad' => rand(10, 30),
                    'coupon_advice_ad' =>rand(10, 30),
                    'rgroup_ad' => rand(10, 30),
                ],
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_sales_and_acess_total' => $dataFake,
                'chart_sales_and_acess_detail' => $dataFake2,
            ]),
        ]);
    }

    /**
     * Query Ads summary of Rpp strategy id.
     */
    private function getRppAdsSummary($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $adsSummary = RppAd::where('store_id', $storeId)
        ->where('date', '>=', $fromDateStr)
        ->where('date', '<=', $toDateStr)
        ->join('rpp_sales_amnt as sales_amnt', 'sales_amnt.rpp_sales_amnt_id', '=', 'rpp_ad.rpp_sales_amnt_id')
        ->join('rpp_actual_amnt as actual_amnt', 'actual_amnt.rpp_actual_amnt_id', '=', 'rpp_ad.rpp_actual_amnt_id')
        ->join('rpp_cvr_rate as cvr_rate', 'cvr_rate.rpp_cvr_rate_id', '=', 'rpp_ad.rpp_cvr_rate_id')
        ->join('rpp_roas as roas_rate', 'roas_rate.rpp_roas_id', '=', 'rpp_ad.rpp_roas_id')
        ->join('rpp_sales_num as sales_num', 'sales_num.rpp_sales_num_id', '=', 'rpp_ad.rpp_sales_num_id')
        ->select(
            DB::raw('SUM(sales_amnt.sum_12h) AS sales_amnt_total'),
            DB::raw('AVG(consumption_rate) AS consumption_rate'),
            DB::raw('SUM(actual_amnt.acutual_amnt_sum) AS ad_cost_total'),
            DB::raw('SUM(click_num_sum) AS click_num_total'),
            DB::raw('AVG(actual_cpc_sum) AS cpc'),
            DB::raw('AVG(cvr_rate.sum_12h) AS cvr'),
            DB::raw('AVG(roas_rate.sum_12h) AS roas'),
            DB::raw('SUM(sales_num.sum_12h) AS sales_num_total')
        )
        ->groupBy('store_id')
        ->first();

        $adsSummary = ! is_null($adsSummary) ? $adsSummary->toArray() : [];
        $salesAmnt = Arr::get($adsSummary, 'sales_amnt_total', 0);
        $consumptionRate = Arr::get($adsSummary, 'consumption_rate', 0);
        $adsCostTotal = Arr::get($adsSummary, 'ad_cost_total', 0);
        $clickNumTotal = Arr::get($adsSummary, 'click_num_total', 0);
        $cpcRate = Arr::get($adsSummary, 'cpc', 0);
        $crvRate = Arr::get($adsSummary, 'cvr', 0);
        $roasRate = Arr::get($adsSummary, 'roas', 0);
        $salesNum = Arr::get($adsSummary, 'sales_num_total', 0);

        $data = collect();
        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'sales_amnt_total' => intval($salesAmnt),
            'consumption_rate' => round(floatval($consumptionRate), 2),
            'ad_cost_total' => intval($adsCostTotal),
            'click_num_total' =>  intval($clickNumTotal),
            'cpc' => round(floatval($cpcRate), 2),
            'cvr' => round(floatval($crvRate), 2),
            'roas' => round(floatval($roasRate), 2),
            'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads summary of Rpp strategy id by year-month.
     */
    private function getYearMonthRppAdsSummary($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $adsSummary = RppAd::where('store_id', $storeId)
        ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
        ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
        ->join('rpp_sales_amnt as sales_amnt', 'sales_amnt.rpp_sales_amnt_id', '=', 'rpp_ad.rpp_sales_amnt_id')
        ->join('rpp_actual_amnt as actual_amnt', 'actual_amnt.rpp_actual_amnt_id', '=', 'rpp_ad.rpp_actual_amnt_id')
        ->join('rpp_cvr_rate as cvr_rate', 'cvr_rate.rpp_cvr_rate_id', '=', 'rpp_ad.rpp_cvr_rate_id')
        ->join('rpp_roas as roas_rate', 'roas_rate.rpp_roas_id', '=', 'rpp_ad.rpp_roas_id')
        ->join('rpp_sales_num as sales_num', 'sales_num.rpp_sales_num_id', '=', 'rpp_ad.rpp_sales_num_id')
        ->select(
            DB::raw('SUM(sales_amnt.sum_12h) AS sales_amnt_total'),
            DB::raw('AVG(consumption_rate) AS consumption_rate'),
            DB::raw('SUM(actual_amnt.acutual_amnt_sum) AS ad_cost_total'),
            DB::raw('SUM(click_num_sum) AS click_num_total'),
            DB::raw('AVG(actual_cpc_sum) AS cpc'),
            DB::raw('AVG(cvr_rate.sum_12h) AS cvr'),
            DB::raw('AVG(roas_rate.sum_12h) AS roas'),
            DB::raw('SUM(sales_num.sum_12h) AS sales_num_total')
        )
        ->groupBy('store_id')
        ->first();

        $adsSummary = ! is_null($adsSummary) ? $adsSummary->toArray() : [];
        $salesAmnt = Arr::get($adsSummary, 'sales_amnt_total', 0);
        $consumptionRate = Arr::get($adsSummary, 'consumption_rate', 0);
        $adsCostTotal = Arr::get($adsSummary, 'ad_cost_total', 0);
        $clickNumTotal = Arr::get($adsSummary, 'click_num_total', 0);
        $cpcRate = Arr::get($adsSummary, 'cpc', 0);
        $crvRate = Arr::get($adsSummary, 'cvr', 0);
        $roasRate = Arr::get($adsSummary, 'roas', 0);
        $salesNum = Arr::get($adsSummary, 'sales_num_total', 0);

        $data = collect();
        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'sales_amnt_total' => intval($salesAmnt),
            'consumption_rate' => round(floatval($consumptionRate), 2),
            'ad_cost_total' => intval($adsCostTotal),
            'click_num_total' =>  intval($clickNumTotal),
            'cpc' => round(floatval($cpcRate), 2),
            'cvr' => round(floatval($crvRate), 2),
            'roas' => round(floatval($roasRate), 2),
            'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads summary of coupon_advance strategy id.
     */
    private function getCouponAdvanceAdsSummary($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $adsSummary = CouponAdviceAd::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->select(
                DB::raw('SUM(sales_amnt) AS sales_amnt_total'),
                DB::raw('AVG(consumption_rate) AS consumption_rate'),
                DB::raw('SUM(actual_amnt) AS ad_cost_total'),
                DB::raw('AVG(roas_rate) AS roas'),
                DB::raw('SUM(sales_num) AS sales_num_total')
            )
            ->groupBy('store_id')
            ->first();
        $adsSummary = ! is_null($adsSummary) ? $adsSummary->toArray() : [];

        $salesAmnt = Arr::get($adsSummary, 'sales_amnt_total', 0);
        $consumptionRate = Arr::get($adsSummary, 'consumption_rate', 0);
        $adsCostTotal = Arr::get($adsSummary, 'ad_cost_total', 0);
        $clickNumTotal = Arr::get($adsSummary, 'click_num_total', 0);
        $cpcRate = Arr::get($adsSummary, 'cpc', 0);
        $crvRate = Arr::get($adsSummary, 'cvr', 0);
        $roasRate = Arr::get($adsSummary, 'roas', 0);
        $salesNum = Arr::get($adsSummary, 'sales_num_total', 0);

        $data = collect();
        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'sales_amnt_total' => intval($salesAmnt),
            'consumption_rate' => round(floatval($consumptionRate), 2),
            'ad_cost_total' => intval($adsCostTotal),
            'click_num_total' =>  intval($clickNumTotal),
            'cpc' => round(floatval($cpcRate), 2),
            'cvr' => round(floatval($crvRate), 2),
            'roas' => round(floatval($roasRate), 2),
            'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads summary of coupon_advance strategy id year-month.
     */
    private function getYearMonthCouponAdvanceAdsSummary($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $adsSummary = CouponAdviceAd::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->select(
                DB::raw('SUM(sales_amnt) AS sales_amnt_total'),
                DB::raw('AVG(consumption_rate) AS consumption_rate'),
                DB::raw('SUM(actual_amnt) AS ad_cost_total'),
                DB::raw('AVG(roas_rate) AS roas'),
                DB::raw('SUM(sales_num) AS sales_num_total')
            )
            ->groupBy('store_id')
            ->first();
        $adsSummary = ! is_null($adsSummary) ? $adsSummary->toArray() : [];

        $salesAmnt = Arr::get($adsSummary, 'sales_amnt_total', 0);
        $consumptionRate = Arr::get($adsSummary, 'consumption_rate', 0);
        $adsCostTotal = Arr::get($adsSummary, 'ad_cost_total', 0);
        $clickNumTotal = Arr::get($adsSummary, 'click_num_total', 0);
        $cpcRate = Arr::get($adsSummary, 'cpc', 0);
        $crvRate = Arr::get($adsSummary, 'cvr', 0);
        $roasRate = Arr::get($adsSummary, 'roas', 0);
        $salesNum = Arr::get($adsSummary, 'sales_num_total', 0);

        $data = collect();
        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'sales_amnt_total' => intval($salesAmnt),
            'consumption_rate' => round(floatval($consumptionRate), 2),
            'ad_cost_total' => intval($adsCostTotal),
            'click_num_total' =>  intval($clickNumTotal),
            'cpc' => round(floatval($cpcRate), 2),
            'cvr' => round(floatval($crvRate), 2),
            'roas' => round(floatval($roasRate), 2),
            'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads summary of rakuten group strategy id.
     */
    private function getRakutenGroupAdsSummarry($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $adsSummary = RgroupAd::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('rgroup_ad_sales_amnt as rgroup_sales_amnt', 'rgroup_sales_amnt.sales_amnt_id', '=', 'rgroup_ad.sales_amnt_id')
            ->select(
                DB::raw('SUM(rgroup_sales_amnt.sales_amnt) AS sales_amnt_total'),
                DB::raw('SUM(ad_cost) AS ad_cost_total'),
                DB::raw('SUM(click_num) AS click_num_total'),
                DB::raw('AVG(cpc) AS cpc'),
                DB::raw('AVG(rgroup_sales_amnt.cvr) AS cvr'),
                DB::raw('AVG(rgroup_sales_amnt.roas) AS roas'),
                DB::raw('SUM(rgroup_sales_amnt.sales_num) AS sales_num_total')
            )
            ->groupBy('store_id')
            ->first();
        $adsSummary = ! is_null($adsSummary) ? $adsSummary->toArray() : [];

        $salesAmnt = Arr::get($adsSummary, 'sales_amnt_total', 0);
        $consumptionRate = Arr::get($adsSummary, 'consumption_rate', 0);
        $adsCostTotal = Arr::get($adsSummary, 'ad_cost_total', 0);
        $clickNumTotal = Arr::get($adsSummary, 'click_num_total', 0);
        $cpcRate = Arr::get($adsSummary, 'cpc', 0);
        $crvRate = Arr::get($adsSummary, 'cvr', 0);
        $roasRate = Arr::get($adsSummary, 'roas', 0);
        $salesNum = Arr::get($adsSummary, 'sales_num_total', 0);

        $data = collect();
        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'sales_amnt_total' => intval($salesAmnt),
            'consumption_rate' => round(floatval($consumptionRate), 2),
            'ad_cost_total' => intval($adsCostTotal),
            'click_num_total' =>  intval($clickNumTotal),
            'cpc' => round(floatval($cpcRate), 2),
            'cvr' => round(floatval($crvRate), 2),
            'roas' => round(floatval($roasRate), 2),
            'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads summary of rakuten group strategy id year-month.
     */
    private function getYearMonthRakutenGroupAdsSummarry($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $adsSummary = RgroupAd::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('rgroup_ad_sales_amnt as rgroup_sales_amnt', 'rgroup_sales_amnt.sales_amnt_id', '=', 'rgroup_ad.sales_amnt_id')
            ->select(
                DB::raw('SUM(rgroup_sales_amnt.sales_amnt) AS sales_amnt_total'),
                DB::raw('SUM(ad_cost) AS ad_cost_total'),
                DB::raw('SUM(click_num) AS click_num_total'),
                DB::raw('AVG(cpc) AS cpc'),
                DB::raw('AVG(rgroup_sales_amnt.cvr) AS cvr'),
                DB::raw('AVG(rgroup_sales_amnt.roas) AS roas'),
                DB::raw('SUM(rgroup_sales_amnt.sales_num) AS sales_num_total')
            )
            ->groupBy('store_id')
            ->first();
        $adsSummary = ! is_null($adsSummary) ? $adsSummary->toArray() : [];

        $salesAmnt = Arr::get($adsSummary, 'sales_amnt_total', 0);
        $consumptionRate = Arr::get($adsSummary, 'consumption_rate', 0);
        $adsCostTotal = Arr::get($adsSummary, 'ad_cost_total', 0);
        $clickNumTotal = Arr::get($adsSummary, 'click_num_total', 0);
        $cpcRate = Arr::get($adsSummary, 'cpc', 0);
        $crvRate = Arr::get($adsSummary, 'cvr', 0);
        $roasRate = Arr::get($adsSummary, 'roas', 0);
        $salesNum = Arr::get($adsSummary, 'sales_num_total', 0);

        $data = collect();
        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'sales_amnt_total' => intval($salesAmnt),
            'consumption_rate' => round(floatval($consumptionRate), 2),
            'ad_cost_total' => intval($adsCostTotal),
            'click_num_total' =>  intval($clickNumTotal),
            'cpc' => round(floatval($cpcRate), 2),
            'cvr' => round(floatval($crvRate), 2),
            'roas' => round(floatval($roasRate), 2),
            'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads detail conversion of Rpp strategy id.
     */
    private function getRppAdsDetail($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $adsResult = RppAd::where('store_id', $storeId)
        ->where('date', '>=', $fromDateStr)
        ->where('date', '<=', $toDateStr)
        ->join('rpp_sales_amnt as sales_amnt', 'sales_amnt.rpp_sales_amnt_id', '=', 'rpp_ad.rpp_sales_amnt_id')
        ->join('rpp_actual_amnt as actual_amnt', 'actual_amnt.rpp_actual_amnt_id', '=', 'rpp_ad.rpp_actual_amnt_id')
        ->join('rpp_cvr_rate as cvr_rate', 'cvr_rate.rpp_cvr_rate_id', '=', 'rpp_ad.rpp_cvr_rate_id')
        ->join('rpp_roas as roas_rate', 'roas_rate.rpp_roas_id', '=', 'rpp_ad.rpp_roas_id')
        ->join('rpp_sales_num as sales_num', 'sales_num.rpp_sales_num_id', '=', 'rpp_ad.rpp_sales_num_id')
        ->select(
            'date',
            DB::raw('SUM(sales_amnt.sum_12h) AS sales_amnt_total'),
            DB::raw('AVG(consumption_rate) AS consumption_rate'),
            DB::raw('SUM(actual_amnt.acutual_amnt_sum) AS ad_cost_total'),
            DB::raw('SUM(click_num_sum) AS click_num_total'),
            DB::raw('AVG(actual_cpc_sum) AS cpc'),
            DB::raw('AVG(cvr_rate.sum_12h) AS cvr'),
            DB::raw('AVG(roas_rate.sum_12h) AS roas'),
            DB::raw('SUM(sales_num.sum_12h) AS sales_num_total')
        )
        ->groupBy('date', 'store_id')
        ->get();

        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmnt = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $consumptionRate = Arr::get($dailyItem[0], 'consumption_rate', 0);
            $adsCostTotal = Arr::get($dailyItem[0], 'ad_cost_total', 0);
            $clickNumTotal = Arr::get($dailyItem[0], 'click_num_total', 0);
            $cpcRate = Arr::get($dailyItem[0], 'cpc', 0);
            $crvRate = Arr::get($dailyItem[0], 'cvr', 0);
            $roasRate = Arr::get($dailyItem[0], 'roas', 0);
            $salesNum = Arr::get($dailyItem[0], 'sales_num_total', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'sales_amnt' => intval($salesAmnt),
                'consumption_rate' => round(floatval($consumptionRate), 2),
                'ad_cost' => intval($adsCostTotal),
                'click_num' =>  intval($clickNumTotal),
                'cpc' => round(floatval($cpcRate), 2),
                'cvr' => round(floatval($crvRate), 2),
                'roas' => round(floatval($roasRate), 2),
                'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads detail conversion of Rpp strategy id by year-month.
     */
    private function getYearMonthRppAdsDetail($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $adsResult = RppAd::where('store_id', $storeId)
        ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
        ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
        ->join('rpp_sales_amnt as sales_amnt', 'sales_amnt.rpp_sales_amnt_id', '=', 'rpp_ad.rpp_sales_amnt_id')
        ->join('rpp_actual_amnt as actual_amnt', 'actual_amnt.rpp_actual_amnt_id', '=', 'rpp_ad.rpp_actual_amnt_id')
        ->join('rpp_cvr_rate as cvr_rate', 'cvr_rate.rpp_cvr_rate_id', '=', 'rpp_ad.rpp_cvr_rate_id')
        ->join('rpp_roas as roas_rate', 'roas_rate.rpp_roas_id', '=', 'rpp_ad.rpp_roas_id')
        ->join('rpp_sales_num as sales_num', 'sales_num.rpp_sales_num_id', '=', 'rpp_ad.rpp_sales_num_id')
        ->select(
            DB::raw('SUBSTRING(date, 1, 6) as date'),
            DB::raw('SUM(sales_amnt.sum_12h) AS sales_amnt_total'),
            DB::raw('AVG(consumption_rate) AS consumption_rate'),
            DB::raw('SUM(actual_amnt.acutual_amnt_sum) AS ad_cost_total'),
            DB::raw('SUM(click_num_sum) AS click_num_total'),
            DB::raw('AVG(actual_cpc_sum) AS cpc'),
            DB::raw('AVG(cvr_rate.sum_12h) AS cvr'),
            DB::raw('AVG(roas_rate.sum_12h) AS roas'),
            DB::raw('SUM(sales_num.sum_12h) AS sales_num_total')
        )
        ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'store_id')
        ->get();

        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmnt = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $consumptionRate = Arr::get($dailyItem[0], 'consumption_rate', 0);
            $adsCostTotal = Arr::get($dailyItem[0], 'ad_cost_total', 0);
            $clickNumTotal = Arr::get($dailyItem[0], 'click_num_total', 0);
            $cpcRate = Arr::get($dailyItem[0], 'cpc', 0);
            $crvRate = Arr::get($dailyItem[0], 'cvr', 0);
            $roasRate = Arr::get($dailyItem[0], 'roas', 0);
            $salesNum = Arr::get($dailyItem[0], 'sales_num_total', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'sales_amnt' => intval($salesAmnt),
                'consumption_rate' => round(floatval($consumptionRate), 2),
                'ad_cost' => intval($adsCostTotal),
                'click_num' =>  intval($clickNumTotal),
                'cpc' => round(floatval($cpcRate), 2),
                'cvr' => round(floatval($crvRate), 2),
                'roas' => round(floatval($roasRate), 2),
                'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads detail of coupon_advance strategy id.
     */
    private function getCouponAdvanceAdsDetail($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $adsResult = CouponAdviceAd::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->select(
                'date',
                DB::raw('SUM(sales_amnt) AS sales_amnt_total'),
                DB::raw('AVG(consumption_rate) AS consumption_rate'),
                DB::raw('SUM(actual_amnt) AS ad_cost_total'),
                DB::raw('AVG(roas_rate) AS roas'),
                DB::raw('SUM(sales_num) AS sales_num_total')
            )
            ->groupBy('date', 'store_id')
            ->get();
        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];

        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmnt = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $consumptionRate = Arr::get($dailyItem[0], 'consumption_rate', 0);
            $adsCostTotal = Arr::get($dailyItem[0], 'ad_cost_total', 0);
            $clickNumTotal = Arr::get($dailyItem[0], 'click_num_total', 0);
            $cpcRate = Arr::get($dailyItem[0], 'cpc', 0);
            $crvRate = Arr::get($dailyItem[0], 'cvr', 0);
            $roasRate = Arr::get($dailyItem[0], 'roas', 0);
            $salesNum = Arr::get($dailyItem[0], 'sales_num_total', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'sales_amnt' => intval($salesAmnt),
                'consumption_rate' => round(floatval($consumptionRate), 2),
                'ad_cost' => intval($adsCostTotal),
                'click_num' =>  intval($clickNumTotal),
                'cpc' => round(floatval($cpcRate), 2),
                'cvr' => round(floatval($crvRate), 2),
                'roas' => round(floatval($roasRate), 2),
                'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads detail of coupon_advance strategy id by year-month.
     */
    private function getYearMonthCouponAdvanceAdsDetail($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $adsResult = CouponAdviceAd::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->select(
                DB::raw('SUBSTRING(date, 1, 6) as date'),
                DB::raw('SUM(sales_amnt) AS sales_amnt_total'),
                DB::raw('AVG(consumption_rate) AS consumption_rate'),
                DB::raw('SUM(actual_amnt) AS ad_cost_total'),
                DB::raw('AVG(roas_rate) AS roas'),
                DB::raw('SUM(sales_num) AS sales_num_total')
            )
            ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'store_id')
            ->get();
        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];

        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmnt = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $consumptionRate = Arr::get($dailyItem[0], 'consumption_rate', 0);
            $adsCostTotal = Arr::get($dailyItem[0], 'ad_cost_total', 0);
            $clickNumTotal = Arr::get($dailyItem[0], 'click_num_total', 0);
            $cpcRate = Arr::get($dailyItem[0], 'cpc', 0);
            $crvRate = Arr::get($dailyItem[0], 'cvr', 0);
            $roasRate = Arr::get($dailyItem[0], 'roas', 0);
            $salesNum = Arr::get($dailyItem[0], 'sales_num_total', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'sales_amnt' => intval($salesAmnt),
                'consumption_rate' => round(floatval($consumptionRate), 2),
                'ad_cost' => intval($adsCostTotal),
                'click_num' =>  intval($clickNumTotal),
                'cpc' => round(floatval($cpcRate), 2),
                'cvr' => round(floatval($crvRate), 2),
                'roas' => round(floatval($roasRate), 2),
                'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads summary of rakuten group strategy id.
     */
    private function getRakutenGroupAdsDetail($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $adsResult = RgroupAd::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('rgroup_ad_sales_amnt as rgroup_sales_amnt', 'rgroup_sales_amnt.sales_amnt_id', '=', 'rgroup_ad.sales_amnt_id')
            ->select(
                'date',
                DB::raw('SUM(rgroup_sales_amnt.sales_amnt) AS sales_amnt_total'),
                DB::raw('SUM(ad_cost) AS ad_cost_total'),
                DB::raw('SUM(click_num) AS click_num_total'),
                DB::raw('AVG(cpc) AS cpc'),
                DB::raw('AVG(rgroup_sales_amnt.cvr) AS cvr'),
                DB::raw('AVG(rgroup_sales_amnt.roas) AS roas'),
                DB::raw('SUM(rgroup_sales_amnt.sales_num) AS sales_num_total')
            )
            ->groupBy('date', 'store_id')
            ->get();
        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];

        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmnt = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $consumptionRate = Arr::get($dailyItem[0], 'consumption_rate', 0);
            $adsCostTotal = Arr::get($dailyItem[0], 'ad_cost_total', 0);
            $clickNumTotal = Arr::get($dailyItem[0], 'click_num_total', 0);
            $cpcRate = Arr::get($dailyItem[0], 'cpc', 0);
            $crvRate = Arr::get($dailyItem[0], 'cvr', 0);
            $roasRate = Arr::get($dailyItem[0], 'roas', 0);
            $salesNum = Arr::get($dailyItem[0], 'sales_num_total', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'sales_amnt' => intval($salesAmnt),
                'consumption_rate' => round(floatval($consumptionRate), 2),
                'ad_cost' => intval($adsCostTotal),
                'click_num' =>  intval($clickNumTotal),
                'cpc' => round(floatval($cpcRate), 2),
                'cvr' => round(floatval($crvRate), 2),
                'roas' => round(floatval($roasRate), 2),
                'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads summary of rakuten group strategy id.
     */
    private function getYearMonthRakutenGroupAdsDetail($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $adsResult = RgroupAd::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('rgroup_ad_sales_amnt as rgroup_sales_amnt', 'rgroup_sales_amnt.sales_amnt_id', '=', 'rgroup_ad.sales_amnt_id')
            ->select(
                DB::raw('SUBSTRING(date, 1, 6) as date'),
                DB::raw('SUM(rgroup_sales_amnt.sales_amnt) AS sales_amnt_total'),
                DB::raw('SUM(ad_cost) AS ad_cost_total'),
                DB::raw('SUM(click_num) AS click_num_total'),
                DB::raw('AVG(cpc) AS cpc'),
                DB::raw('AVG(rgroup_sales_amnt.cvr) AS cvr'),
                DB::raw('AVG(rgroup_sales_amnt.roas) AS roas'),
                DB::raw('SUM(rgroup_sales_amnt.sales_num) AS sales_num_total')
            )
            ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'store_id')
            ->get();
        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];

        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmnt = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $consumptionRate = Arr::get($dailyItem[0], 'consumption_rate', 0);
            $adsCostTotal = Arr::get($dailyItem[0], 'ad_cost_total', 0);
            $clickNumTotal = Arr::get($dailyItem[0], 'click_num_total', 0);
            $cpcRate = Arr::get($dailyItem[0], 'cpc', 0);
            $crvRate = Arr::get($dailyItem[0], 'cvr', 0);
            $roasRate = Arr::get($dailyItem[0], 'roas', 0);
            $salesNum = Arr::get($dailyItem[0], 'sales_num_total', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'sales_amnt' => intval($salesAmnt),
                'consumption_rate' => round(floatval($consumptionRate), 2),
                'ad_cost' => intval($adsCostTotal),
                'click_num' =>  intval($clickNumTotal),
                'cpc' => round(floatval($cpcRate), 2),
                'cvr' => round(floatval($crvRate), 2),
                'roas' => round(floatval($roasRate), 2),
                'cpa' => $salesNum > 0 ? round(($adsCostTotal / $salesNum) * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query Ads detail conversion of Rpp strategy id.
     */
    private function getRppSalesAndAccess($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $adsResult = RppAd::where('store_id', $storeId)
        ->where('date', '>=', $fromDateStr)
        ->where('date', '<=', $toDateStr)
        ->join('rpp_sales_amnt as sales_amnt', 'sales_amnt.rpp_sales_amnt_id', '=', 'rpp_ad.rpp_sales_amnt_id')
        ->join('rpp_actual_amnt as actual_amnt', 'actual_amnt.rpp_actual_amnt_id', '=', 'rpp_ad.rpp_actual_amnt_id')
        ->select(
            'date',
            DB::raw('SUM(sales_amnt.sum_12h) AS sales_amnt_total'),
            DB::raw('SUM(sales_amnt.within_12h) AS ads_sales_amnt'),
            DB::raw('SUM(actual_amnt.acutual_amnt_sum) AS act_sum'),
            DB::raw('SUM(actual_amnt.actual_amnt_new) AS act_new')
        )
        ->groupBy('date', 'store_id')
        ->get();

        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmntTotal = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $adsSalesAmnt = Arr::get($dailyItem[0], 'ads_sales_amnt', 0);
            $actualSumAmnt = Arr::get($dailyItem[0], 'act_sum', 0);
            $actualNewAmnt = Arr::get($dailyItem[0], 'act_new', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'ads_revenue' => intval($adsSalesAmnt),
                'total_revenue' => round(floatval($salesAmntTotal), 2),
                'increase_rate' => $actualSumAmnt > 0 ? round($actualNewAmnt / $actualSumAmnt * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_sales_and_acess_total' => $data,
                'chart_sales_and_acess_detail' => $this->getSalesAndAccessDetail($storeId, $filters),
            ]),
        ]);
    }

    /**
     * Query Ads detail conversion of Rpp strategy id by year-month.
     */
    private function getYearMonthRppSalesAndAccess($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $adsResult = RppAd::where('store_id', $storeId)
        ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
        ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
        ->join('rpp_sales_amnt as sales_amnt', 'sales_amnt.rpp_sales_amnt_id', '=', 'rpp_ad.rpp_sales_amnt_id')
        ->join('rpp_actual_amnt as actual_amnt', 'actual_amnt.rpp_actual_amnt_id', '=', 'rpp_ad.rpp_actual_amnt_id')
        ->select(
            DB::raw('SUBSTRING(date, 1, 6) as date'),
            DB::raw('SUM(sales_amnt.sum_12h) AS sales_amnt_total'),
            DB::raw('SUM(sales_amnt.within_12h) AS ads_sales_amnt'),
            DB::raw('SUM(actual_amnt.acutual_amnt_sum) AS act_sum'),
            DB::raw('SUM(actual_amnt.actual_amnt_new) AS act_new')
        )
        ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'store_id')
        ->get();

        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmntTotal = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $adsSalesAmnt = Arr::get($dailyItem[0], 'ads_sales_amnt', 0);
            $actualSumAmnt = Arr::get($dailyItem[0], 'act_sum', 0);
            $actualNewAmnt = Arr::get($dailyItem[0], 'act_new', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'ads_revenue' => intval($adsSalesAmnt),
                'total_revenue' => round(floatval($salesAmntTotal), 2),
                'increase_rate' => $actualSumAmnt > 0 ? round($actualNewAmnt / $actualSumAmnt * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_sales_and_acess_total' => $data,
                'chart_sales_and_acess_detail' => $this->getYearMonthSalesAndAccessDetail($storeId, $filters),
            ]),
        ]);
    }

    /**
     * Query Ads detail conversion of coupon strategy id.
     */
    private function getCouponSalesAndAccess($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $adsResult = CouponAdviceAd::where('store_id', $storeId)
        ->where('date', '>=', $fromDateStr)
        ->where('date', '<=', $toDateStr)
        ->select(
            'date',
            DB::raw('SUM(sales_amnt) AS sales_amnt_total'),
            DB::raw('SUM(sales_amnt_with_dscnt) AS ads_sales_amnt')
        )
        ->groupBy('date', 'store_id')
        ->get();

        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmntTotal = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $adsSalesAmnt = Arr::get($dailyItem[0], 'ads_sales_amnt', 0);
            $actualSumAmnt = Arr::get($dailyItem[0], 'act_sum', 0);
            $actualNewAmnt = Arr::get($dailyItem[0], 'act_new', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'ads_revenue' => intval($adsSalesAmnt),
                'total_revenue' => round(floatval($salesAmntTotal), 2),
                'increase_rate' => $actualSumAmnt > 0 ? round($actualNewAmnt / $actualSumAmnt * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_sales_and_acess_total' => $data,
                'chart_sales_and_acess_detail' => $this->getSalesAndAccessDetail($storeId, $filters),
            ]),
        ]);
    }

    /**
     * Query Ads detail conversion of coupon strategy id by year-month.
     */
    private function getYearMonthCouponSalesAndAccess($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $adsResult = CouponAdviceAd::where('store_id', $storeId)
        ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
        ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
        ->select(
            DB::raw('SUBSTRING(date, 1, 6) as date'),
            DB::raw('SUM(sales_amnt) AS sales_amnt_total'),
            DB::raw('SUM(sales_amnt_with_dscnt) AS ads_sales_amnt')
        )
        ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'store_id')
        ->get();

        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmntTotal = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $adsSalesAmnt = Arr::get($dailyItem[0], 'ads_sales_amnt', 0);
            $actualSumAmnt = Arr::get($dailyItem[0], 'act_sum', 0);
            $actualNewAmnt = Arr::get($dailyItem[0], 'act_new', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'ads_revenue' => intval($adsSalesAmnt),
                'total_revenue' => round(floatval($salesAmntTotal), 2),
                'increase_rate' => $actualSumAmnt > 0 ? round($actualNewAmnt / $actualSumAmnt * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_sales_and_acess_total' => $data,
                'chart_sales_and_acess_detail' => $this->getYearMonthSalesAndAccessDetail($storeId, $filters),
            ]),
        ]);
    }

    /**
     * Query Ads detail conversion of rakuten group strategy id.
     */
    private function getRakutenGroupSalesAndAccess($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $adsResult = RgroupAd::where('store_id', $storeId)
        ->where('date', '>=', $fromDateStr)
        ->where('date', '<=', $toDateStr)
        ->join('rgroup_ad_sales_amnt as rgroup_sales_amnt', 'rgroup_sales_amnt.sales_amnt_id', '=', 'rgroup_ad.sales_amnt_id')
        ->select(
            'date',
            DB::raw('SUM(rgroup_sales_amnt.sales_amnt) AS sales_amnt_total'),
            DB::raw('SUM(rgroup_sales_amnt.new_user_sales_amnt) AS ads_sales_amnt')
        )
        ->groupBy('date', 'store_id')
        ->get();

        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmntTotal = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $adsSalesAmnt = Arr::get($dailyItem[0], 'ads_sales_amnt', 0);
            $actualSumAmnt = Arr::get($dailyItem[0], 'act_sum', 0);
            $actualNewAmnt = Arr::get($dailyItem[0], 'act_new', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'ads_revenue' => intval($adsSalesAmnt),
                'total_revenue' => round(floatval($salesAmntTotal), 2),
                'increase_rate' => $actualSumAmnt > 0 ? round($actualNewAmnt / $actualSumAmnt * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_sales_and_acess_total' => $data,
                'chart_sales_and_acess_detail' => $this->getSalesAndAccessDetail($storeId, $filters),
            ]),
        ]);
    }

    /**
     * Query Ads detail conversion of rakuten group strategy id by year-month.
     */
    private function getYearMonthRakutenGroupSalesAndAccess($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $adsResult = RgroupAd::where('store_id', $storeId)
        ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
        ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
        ->join('rgroup_ad_sales_amnt as rgroup_sales_amnt', 'rgroup_sales_amnt.sales_amnt_id', '=', 'rgroup_ad.sales_amnt_id')
        ->select(
            DB::raw('SUBSTRING(date, 1, 6) as date'),
            DB::raw('SUM(rgroup_sales_amnt.sales_amnt) AS sales_amnt_total'),
            DB::raw('SUM(rgroup_sales_amnt.new_user_sales_amnt) AS ads_sales_amnt')
        )
        ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'store_id')
        ->get();

        $adsResult = ! is_null($adsResult) ? $adsResult->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($adsResult as $date => $dailyItem) {
            $salesAmntTotal = Arr::get($dailyItem[0], 'sales_amnt_total', 0);
            $adsSalesAmnt = Arr::get($dailyItem[0], 'ads_sales_amnt', 0);
            $actualSumAmnt = Arr::get($dailyItem[0], 'act_sum', 0);
            $actualNewAmnt = Arr::get($dailyItem[0], 'act_new', 0);

            $data->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'ads_revenue' => intval($adsSalesAmnt),
                'total_revenue' => round(floatval($salesAmntTotal), 2),
                'increase_rate' => $actualSumAmnt > 0 ? round($actualNewAmnt / $actualSumAmnt * 100, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_sales_and_acess_total' => $data,
                'chart_sales_and_acess_detail' => $this->getYearMonthSalesAndAccessDetail($storeId, $filters),
            ]),
        ]);
    }

    /**
     * Query ads sales and access detail.
     */
    private function getSalesAndAccessDetail($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $rppResults = RppAd::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('rpp_sales_amnt as sales_amnt', 'sales_amnt.rpp_sales_amnt_id', '=', 'rpp_ad.rpp_sales_amnt_id')
            ->select(
                'date',
                DB::raw('SUM(sales_amnt.sum_12h) AS rpp_sales_amnt'),
                DB::raw('SUM(click_num_sum) AS rpp_click_num')
            )
            ->groupBy('date', 'store_id')
            ->orderBy('date')
            ->get();
        $rppResults = ! is_null($rppResults) ? $rppResults->groupBy('date')->toArray() : [];

        $couponResults = CouponAdviceAd::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->select(
                'date',
                DB::raw('SUM(sales_amnt) AS coupon_sales_amnt')
            )
            ->groupBy('date', 'store_id')
            ->orderBy('date')
            ->get();
        $couponResults = ! is_null($couponResults) ? $couponResults->groupBy('date')->toArray() : [];

        $rgroupResults = RgroupAd::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('rgroup_ad_sales_amnt as rgroup_sales_amnt', 'rgroup_sales_amnt.sales_amnt_id', '=', 'rgroup_ad.sales_amnt_id')
            ->select(
                'date',
                DB::raw('SUM(rgroup_sales_amnt.sales_amnt) AS rgroup_sales'),
                DB::raw('SUM(click_num) AS rgroup_click_num')
            )
            ->groupBy('date', 'store_id')
            ->orderBy('date')
            ->get();
        $rgroupResults = ! is_null($rgroupResults) ? $rgroupResults->groupBy('date')->toArray() : [];

        $result = collect();
        foreach ($rppResults as $date => $adsItems) {
            $rppSales = Arr::get($adsItems[0] ?? [], 'rpp_sales_amnt', 0);
            $rppClickNum = Arr::get($adsItems[0] ?? [], 'rpp_click_num', 0);

            $couponItem = collect($couponResults)->filter(function ($item) use ($date) {
                return $date == Arr::get($item[0], 'date');
            })->first();
            $couponSales = Arr::get($couponItem[0] ?? [], 'coupon_sales_amnt', 0);

            $rgroupItem = collect($rgroupResults)->filter(function ($item) use ($date) {
                return $date == Arr::get($item[0], 'date');
            })->first();
            $rgroupSales = Arr::get($rgroupItem[0] ?? [], 'rgroup_sales', 0);
            $rgroupClickNum = Arr::get($rgroupItem[0] ?? [], 'rgroup_click_num', 0);

            $result->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'sales_amnt' => [
                    'whole' => $rppSales + $couponSales + $rgroupSales,
                    'rpp_ad' => intval($rppSales),
                    'coupon_advice_ad' => intval($couponSales),
                    'rgroup_ad' => intval($rgroupSales),
                ],
                'click_num' => [
                    'whole' => $rppClickNum + $rgroupClickNum,
                    'rpp_ad' => intval($rppClickNum),
                    'coupon_advice_ad' => 0,
                    'rgroup_ad' => intval($rgroupClickNum),
                ],
            ]);
        }

        return $result;
    }

    /**
     * Query ads sales and access detail by year-month.
     */
    private function getYearMonthSalesAndAccessDetail($storeId, $filters): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $rppResults = RppAd::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('rpp_sales_amnt as sales_amnt', 'sales_amnt.rpp_sales_amnt_id', '=', 'rpp_ad.rpp_sales_amnt_id')
            ->select(
                DB::raw('SUBSTRING(date, 1, 6) as date'),
                DB::raw('SUM(sales_amnt.sum_12h) AS rpp_sales_amnt'),
                DB::raw('SUM(click_num_sum) AS rpp_click_num')
            )
            ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'store_id')
            ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'))
            ->get();
        $rppResults = ! is_null($rppResults) ? $rppResults->groupBy('date')->toArray() : [];

        $couponResults = CouponAdviceAd::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->select(
                DB::raw('SUBSTRING(date, 1, 6) as date'),
                DB::raw('SUM(sales_amnt) AS coupon_sales_amnt')
            )
            ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'store_id')
            ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'))
            ->get();
        $couponResults = ! is_null($couponResults) ? $couponResults->groupBy('date')->toArray() : [];

        $rgroupResults = RgroupAd::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('rgroup_ad_sales_amnt as rgroup_sales_amnt', 'rgroup_sales_amnt.sales_amnt_id', '=', 'rgroup_ad.sales_amnt_id')
            ->select(
                DB::raw('SUBSTRING(date, 1, 6) as date'),
                DB::raw('SUM(rgroup_sales_amnt.sales_amnt) AS rgroup_sales'),
                DB::raw('SUM(click_num) AS rgroup_click_num')
            )
            ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'store_id')
            ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'))
            ->get();
        $rgroupResults = ! is_null($rgroupResults) ? $rgroupResults->groupBy('date')->toArray() : [];

        $result = collect();
        foreach ($rppResults as $date => $adsItems) {
            $rppSales = Arr::get($adsItems[0] ?? [], 'rpp_sales_amnt', 0);
            $rppClickNum = Arr::get($adsItems[0] ?? [], 'rpp_click_num', 0);

            $couponItem = collect($couponResults)->filter(function ($item) use ($date) {
                return $date == Arr::get($item[0], 'date');
            })->first();
            $couponSales = Arr::get($couponItem[0] ?? [], 'coupon_sales_amnt', 0);

            $rgroupItem = collect($rgroupResults)->filter(function ($item) use ($date) {
                return $date == Arr::get($item[0], 'date');
            })->first();
            $rgroupSales = Arr::get($rgroupItem[0] ?? [], 'rgroup_sales', 0);
            $rgroupClickNum = Arr::get($rgroupItem[0] ?? [], 'rgroup_click_num', 0);

            $result->add([
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'sales_amnt' => [
                    'whole' => $rppSales + $couponSales + $rgroupSales,
                    'rpp_ad' => intval($rppSales),
                    'coupon_advice_ad' => intval($couponSales),
                    'rgroup_ad' => intval($rgroupSales),
                ],
                'click_num' => [
                    'whole' => $rppClickNum + $rgroupClickNum,
                    'rpp_ad' => intval($rppClickNum),
                    'coupon_advice_ad' => 0,
                    'rgroup_ad' => intval($rgroupClickNum),
                ],
            ]);
        }

        return $result;
    }
}
