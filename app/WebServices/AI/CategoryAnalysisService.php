<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CategoryAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    private $categoryIds;
    private $categories;

    public function __construct()
    {
        $this->categoryIds = collect();
        for ($i = 0; $i < 30; $i++) {
            $this->categoryIds->add(1000000 + $i);
        }

        $this->categories = collect();
        foreach ($this->categoryIds as $index => $categoryId) {
            $this->categories->add([
                'rank' => $index + 1,
                'item_id' => $categoryId,
                'item_name' => 'カテゴリ名'.$categoryId.'テキス',
                'sales_all' => rand(10000000, 99999999),
                'visit_all' => rand(10000, 99999),
                'conversion_rate' => rand(1, 99),
                'sales_amnt_per_user' => rand(1000, 5000),
            ]);
        }
    }

    /**
     * Get categories analysis summary by store_id.
     */
    public function getCategorySummary($storeId, array $filters = []): Collection
    {
        $activeNum = rand(1000, 5000);
        $unActiveNum = rand(1000, $activeNum);
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dataFake = [
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'active_category_count_all' => $activeNum,
            'unactive_category_count_all' => $unActiveNum,
            'active_ratio' => round($activeNum / ($activeNum + $unActiveNum), 2) * 100,
            'zero_inventory_num' => rand(10, 1000),
            'categories' => $this->categories,
        ];

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get chart selected categories sales per month from AI.
     */
    public function getChartSelectedCategories(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $categoryIds = Arr::get($filters, 'category_ids');

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
                'conversion_rate' => rand(1, 99),
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
     * Get chart categories's trends from AI.
     */
    public function getChartCategoriesTrends(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $categoryIds = Arr::get($filters, 'category_ids');
        $categoryIdsArr = explode(',', $categoryIds);

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

        $categories = $this->categories->filter(function ($item) use ($categoryIdsArr) {
            return in_array(Arr::get($item, 'item_id'), $categoryIdsArr);
        });

        $dailySales = collect();
        $dailyAccess = collect();
        $dailyConvertionRate = collect();
        $dailySalesAmntPerUser = collect();
        foreach ($dateTimeRange as $date) {
            $dailySales->add([
                'date' => $date,
                'categories' => $categories->map(function ($item) {
                    return [
                        Arr::get($item, 'item_id') => rand(100000, 500000),
                    ];
                }),
            ]);

            $dailyAccess->add([
                'date' => $date,
                'categories' => $categories->map(function ($item) {
                    return [
                        Arr::get($item, 'item_id') => rand(100, 5000),
                    ];
                }),
            ]);

            $dailyConvertionRate->add([
                'date' => $date,
                'categories' => $categories->map(function ($item) {
                    return [
                        Arr::get($item, 'item_id') => rand(1, 99),
                    ];
                }),
            ]);

            $dailySalesAmntPerUser->add([
                'date' => $date,
                'categories' => $categories->map(function ($item) {
                    return [
                        Arr::get($item, 'item_id') => rand(1000, 5000),
                    ];
                }),
            ]);
        }

        $dataFake->add([
            'sales_all' => $dailySales,
            'visit_all' => $dailyAccess,
            'conversion_rate' => $dailyConvertionRate,
            'sales_amnt_per_user' => $dailySalesAmntPerUser,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get chart categories's stay times from AI.
     */
    public function getChartCategoriesStayTimes(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $categoryIds = Arr::get($filters, 'category_ids');
        $categoryIdsArr = explode(',', $categoryIds);

        $categories = $this->categories->filter(function ($item) use ($categoryIdsArr) {
            return in_array(Arr::get($item, 'item_id'), $categoryIdsArr);
        });

        $dataFake = collect();
        foreach ($categories as $category) {
            $dataFake->add([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'category_id' => Arr::get($category, 'item_id'),
                'duration_all' => rand(30, 300),
                'exit_rate_all' => rand(10, 90),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get chart categories's reviews trends from AI.
     */
    public function chartCategoriesReviewsTrends(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $categoryIds = Arr::get($filters, 'category_ids');
        $categoryIdsArr = explode(',', $categoryIds);
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            'Y/m'
        );

        $categories = $this->categories->filter(function ($item) use ($categoryIdsArr) {
            return in_array(Arr::get($item, 'item_id'), $categoryIdsArr);
        });

        $dataFake = collect();
        foreach ($dateTimeRange as $date) {
            $categoriesReviewAll = collect();
            $categoriesReviewWritingRate = collect();
            foreach ($categories as $category) {
                $allCategoryReview = rand(100, 1000);
                $categoriesReviewAll->add([
                    Arr::get($category, 'item_id') => $allCategoryReview,
                ]);

                $categoriesReviewWritingRate->add([
                    Arr::get($category, 'item_id') => round(rand(10, $allCategoryReview) / $allCategoryReview, 2) * 100,
                ]);
            }

            $dataFake->add([
                'date' => $date,
                'review_all' => $categoriesReviewAll,
                'review_writing_rate' => $categoriesReviewWritingRate,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }
}
