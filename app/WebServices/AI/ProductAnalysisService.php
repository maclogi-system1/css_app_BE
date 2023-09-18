<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProductAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    private $productCodes;
    private $products;

    public function __construct()
    {
        $this->productCodes = collect();
        for ($i = 0; $i < 30; $i++) {
            $this->productCodes->add(1000000 + $i);
        }

        $this->products = collect();
        foreach ($this->productCodes as $productCode) {
            $this->products->add([
                'code' => $productCode,
                'name' => '商品名'.$productCode.'テキス',
                'total_sales' => rand(10000000, 99999999),
                'total_access' => rand(10000, 99999),
                'conversion_rate' => rand(1, 50),
                'sales_amnt_per_user' => rand(1000, 5000),
            ]);
        }
    }

    /**
     * Get product analysis summary by store_id.
     */
    public function getProductSummary($storeId, array $filters = []): Collection
    {
        $products = $this->products;

        $activeNum = rand(1000, 5000);
        $unActiveNum = rand(1000, $activeNum);
        $dataFake = [
            'active_product_num' => $activeNum,
            'unactive_product_num' => $unActiveNum,
            'active_ratio' => round($unActiveNum / $activeNum * 100, 2),
            'empty_product_num' => rand(10, 1000),
            'products' => $products,
        ];

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get chart selected products sales per month from AI.
     */
    public function getChartSelectedProducts(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $productCodes = Arr::get($filters, 'product_codes');

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            'Y/m'
        );

        $dataFake = collect();

        foreach ($dateTimeRange as $date) {
            $dataFake->add([
                'date' => $date,
                'total_sales' => rand(10000000, 99999999),
                'total_access' => rand(10000, 99999),
                'conversion_rate' => rand(1, 50),
                'sales_amnt_per_user' => rand(1000, 5000),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get chart products's trends from AI.
     */
    public function getChartProductsTrends(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $productCodes = Arr::get($filters, 'product_codes');
        $productCodesArr = explode(',', $productCodes);

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            [
                'format' => 'Y/m/d',
                'step' => '1 day',
            ]
        );

        $dataFake = collect();

        $products = $this->products->filter(function ($item) use ($productCodesArr) {
            return in_array(Arr::get($item, 'code'), $productCodesArr);
        });

        $dailySales = collect();
        $dailyAccess = collect();
        $dailyConvertionRate = collect();
        $dailySalesAmntPerUser = collect();
        foreach ($dateTimeRange as $date) {
            $dailySales->add([
                'date' => $date,
                'products' => $products->map(function ($item) {
                    return [
                        Arr::get($item, 'code') => rand(100000, 500000),
                    ];
                }),
            ]);

            $dailyAccess->add([
                'date' => $date,
                'products' => $products->map(function ($item) {
                    return [
                        Arr::get($item, 'code') => rand(100, 5000),
                    ];
                }),
            ]);

            $dailyConvertionRate->add([
                'date' => $date,
                'products' => $products->map(function ($item) {
                    return [
                        Arr::get($item, 'code') => rand(1, 50),
                    ];
                }),
            ]);

            $dailySalesAmntPerUser->add([
                'date' => $date,
                'products' => $products->map(function ($item) {
                    return [
                        Arr::get($item, 'code') => rand(1000, 5000),
                    ];
                }),
            ]);
        }

        $dataFake->add([
            'daily_sales' => $dailySales,
            'daily_access' => $dailyAccess,
            'daily_conversion_rate' => $dailyConvertionRate,
            'daily_sales_amnt_per_user' => $dailySalesAmntPerUser,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get chart products's stay times from AI.
     */
    public function getChartProductsStayTimes(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $productCodes = Arr::get($filters, 'product_codes');
        $productCodesArr = explode(',', $productCodes);

        $products = $this->products->filter(function ($item) use ($productCodesArr) {
            return in_array(Arr::get($item, 'code'), $productCodesArr);
        });

        $dataFake = collect();
        foreach ($products as $product) {
            $dataFake->add([
                'product_code' => Arr::get($product, 'code'),
                'stay_times' => rand(30, 300),
                'exit_rate' => rand(10, 50),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get chart products's rakuten ranking from AI.
     */
    public function getChartProductsRakutenRanking(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $productCodes = Arr::get($filters, 'product_codes');
        $productCodesArr = explode(',', $productCodes);
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            [
                'format' => 'Y/m/d',
                'step' => '1 day',
            ]
        );

        $products = $this->products->filter(function ($item) use ($productCodesArr) {
            return in_array(Arr::get($item, 'code'), $productCodesArr);
        });

        $dataFake = collect();
        foreach ($dateTimeRange as $date) {
            $productRank = collect();
            $existedRank = [];
            foreach ($products as $product) {
                $rank = rand(1, 50);
                while (in_array($rank, $existedRank)) {
                    $rank = rand(1, 50);
                }
                $existedRank[] = $rank;
                $productRank->add([
                    Arr::get($product, 'code') => $rank,
                ]);
            }

            $dataFake->add([
                'date' => $date,
                'products_rank' => $productRank,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get chart products's reviews trends from AI.
     */
    public function getChartProductsReviewsTrends(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $productCodes = Arr::get($filters, 'product_codes');
        $productCodesArr = explode(',', $productCodes);
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            'Y/m'
        );

        $products = $this->products->filter(function ($item) use ($productCodesArr) {
            return in_array(Arr::get($item, 'code'), $productCodesArr);
        });

        $dataFake = collect();
        foreach ($dateTimeRange as $date) {
            $productsReviewsNum = collect();
            $productsWritingRates = collect();
            foreach ($products as $product) {
                $productsReviewsNum->add([
                    Arr::get($product, 'code') => rand(100, 1000),
                ]);

                $productsWritingRates->add([
                    Arr::get($product, 'code') => rand(1, 50),
                ]);
            }

            $dataFake->add([
                'date' => $date,
                'products_reviews_num' => $productsReviewsNum,
                'products_writing_rates' => $productsWritingRates,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }
}
