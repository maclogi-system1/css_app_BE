<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Collection;

class AdsAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    public function getAdsAnalysisSummary($storeId, $filters)
    {
        $dataFake = collect();

        $dataFake->add([
            'store_id' => $storeId,
            'sales_amnt_total' => 1000000,
            'consumption_rate' => rand(10, 100),
            'ad_cost_total' => 1000000,
            'click_num_total' =>  10000,
            'cpc' => rand(10, 100),
            'cvr' => rand(10, 100),
            'roas' => rand(10, 100),
            'cpa' => rand(10, 100),
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    public function getListAdsConversion($storeId, $filters)
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            'Y/m'
        );

        $dataFake = collect();

        foreach ($dateTimeRange as $date) {
            $dataFake->add([
                'store_id' => $storeId,
                'date' => $date,
                'sales_amnt' => rand(20000, 500000),
                'consumption_rate' => rand(10, 100),
                'ad_cost' => rand(20000, 50000),
                'click_num' => rand(10000, 25000),
                'cpc' => rand(10, 100),
                'cvr' => rand(10, 100),
                'roas' => rand(10, 100),
                'cpa' => rand(10, 100),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'detail_ads_conversion' => $dataFake,
            ]),
        ]);
    }

    public function getListProductByRoas($storeId, $filters): Collection
    {
        $dataFake = collect();

        $dataFake->add([
            'store_id' => $storeId,
            'high_roas_products' => [
                '1' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                '2' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                '3' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                '4' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                '5' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                '6' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                '7' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                '8' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                '9' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
                '10' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(5000, 50000),
                    'rpp_sales_num_720h' => rand(5000, 50000),
                    'rpp_roas' => rand(5000, 50000),
                ],
            ],
            'low_roas_products' => [
                '1' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                '2' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                '3' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                '4' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                '5' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                '6' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                '7' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                '8' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                '9' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
                '10' => [
                    'product_control_number' => 1,
                    'rpp_sales_amnt_720h' => rand(500, 5000),
                    'rpp_sales_num_720h' => rand(500, 5000),
                    'rpp_roas' => rand(500, 5000),
                ],
            ],
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'list_product_by_roas' => $dataFake,
            ]),
        ]);
    }

    public function getDataChartSalesAndAccess($storeId, $filters)
    {
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
                    'ad_tda' => rand(60000, 100000),
                    'rpp_ad' => rand(10000, 20000),
                    'coupon_advice_ad' =>rand(10000, 20000),
                    'rgroup_ad' => rand(10000, 20000),
                ],
                'click_num' => [
                    'ad_tda' => rand(50, 100),
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
}