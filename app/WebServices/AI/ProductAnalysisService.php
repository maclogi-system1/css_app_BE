<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProductAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    private $productIds;
    private $products;

    public function __construct()
    {
        $this->productIds = collect();
        for ($i = 0; $i < 30; $i++) {
            $this->productIds->add(1000000 + $i);
        }

        $this->products = collect();
        foreach ($this->productIds as $index => $productId) {
            $this->products->add([
                'rank' => $index + 1,
                'item_id' => $productId,
                'management_number' => 'pakupaku_vege22_'.$index,
                'item_name' => '商品名'.$productId.'テキス',
                'sales_all' => rand(10000000, 99999999),
                'visit_all' => rand(10000, 99999),
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
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'active_product_count_all' => $activeNum,
            'unactive_product_count_all' => $unActiveNum,
            'active_ratio' => round($unActiveNum / $activeNum * 100, 2),
            'zero_inventory_count' => rand(10, 1000),
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
        $productIds = Arr::get($filters, 'product_ids');

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
                'total_sales_all' => rand(10000000, 99999999),
                'total_visit_all' => rand(10000, 99999),
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
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

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

        $products = $this->products;
        if (! empty($productIds)) {
            $products = $products->filter(function ($item) use ($productIdsArr) {
                return in_array(Arr::get($item, 'item_id'), $productIdsArr);
            });
        }

        if (! empty($managementNums)) {
            $products = $products->filter(function ($item) use ($managementNumsArr) {
                return in_array(Arr::get($item, 'management_number'), $managementNumsArr);
            });
        }

        $dailySales = collect();
        $dailyAccess = collect();
        $dailyConvertionRate = collect();
        $dailySalesAmntPerUser = collect();
        foreach ($dateTimeRange as $date) {
            $dailySales->add([
                'date' => $date,
                'products' => $products->map(function ($item) {
                    return [
                        Arr::get($item, 'item_id') => rand(100000, 500000),
                    ];
                }),
            ]);

            $dailyAccess->add([
                'date' => $date,
                'products' => $products->map(function ($item) {
                    return [
                        Arr::get($item, 'item_id') => rand(100, 5000),
                    ];
                }),
            ]);

            $dailyConvertionRate->add([
                'date' => $date,
                'products' => $products->map(function ($item) {
                    return [
                        Arr::get($item, 'item_id') => rand(1, 50),
                    ];
                }),
            ]);

            $dailySalesAmntPerUser->add([
                'date' => $date,
                'products' => $products->map(function ($item) {
                    return [
                        Arr::get($item, 'item_id') => rand(1000, 5000),
                    ];
                }),
            ]);
        }

        $dataFake->add([
            'chart_sales_all' => $dailySales,
            'chart_visit_all' => $dailyAccess,
            'chart_conversion_rate' => $dailyConvertionRate,
            'chart_sales_amnt_per_user' => $dailySalesAmntPerUser,
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
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $products = $this->products;
        if (! empty($productIds)) {
            $products = $products->filter(function ($item) use ($productIdsArr) {
                return in_array(Arr::get($item, 'item_id'), $productIdsArr);
            });
        }

        if (! empty($managementNums)) {
            $products = $products->filter(function ($item) use ($managementNumsArr) {
                return in_array(Arr::get($item, 'management_number'), $managementNumsArr);
            });
        }

        $dataFake = collect();
        foreach ($products as $product) {
            $dataFake->add([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'item_id' => Arr::get($product, 'item_id'),
                'duration_all' => rand(30, 300),
                'exit_rate_all' => rand(10, 50),
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
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            [
                'format' => 'Y/m/d',
                'step' => '1 day',
            ]
        );

        $products = $this->products;
        if (! empty($productIds)) {
            $products = $products->filter(function ($item) use ($productIdsArr) {
                return in_array(Arr::get($item, 'item_id'), $productIdsArr);
            });
        }

        if (! empty($managementNums)) {
            $products = $products->filter(function ($item) use ($managementNumsArr) {
                return in_array(Arr::get($item, 'management_number'), $managementNumsArr);
            });
        }

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
                    Arr::get($product, 'item_id') => $rank,
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
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            'Y/m'
        );

        $products = $this->products;
        if (! empty($productIds)) {
            $products = $products->filter(function ($item) use ($productIdsArr) {
                return in_array(Arr::get($item, 'item_id'), $productIdsArr);
            });
        }

        if (! empty($managementNums)) {
            $products = $products->filter(function ($item) use ($managementNumsArr) {
                return in_array(Arr::get($item, 'management_number'), $managementNumsArr);
            });
        }

        $dataFake = collect();
        foreach ($dateTimeRange as $date) {
            $productsReviewsNum = collect();
            $productsWritingRates = collect();
            foreach ($products as $product) {
                $productsReviewsNum->add([
                    Arr::get($product, 'item_id') => rand(100, 1000),
                ]);

                $productsWritingRates->add([
                    Arr::get($product, 'item_id') => rand(1, 50),
                ]);
            }

            $dataFake->add([
                'date' => $date,
                'review_all' => $productsReviewsNum,
                'review_writing_rate' => $productsWritingRates,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }
}
