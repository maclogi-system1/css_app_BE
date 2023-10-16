<?php

namespace App\Repositories\Eloquents;

use App\Models\CategoriesPerformanceAnalytics;
use App\Repositories\Contracts\CategoriesPerformanceAnalyticsRepository as CategoriesPerformanceAnalyticsRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\CategoryAnalysisService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CategoriesPerformanceAnalyticsRepository extends Repository implements CategoriesPerformanceAnalyticsRepositoryContract
{
    public function __construct(
        protected CategoryAnalysisService $categoryAnalysisService,
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return CategoriesPerformanceAnalytics::class;
    }

    /**
     * Save category's sales performance table.
     */
    public function saveSalesPerformanceTable(string $storeId, array $data): ?array
    {
        return $this->handleSafely(function () use ($storeId, $data) {
            $inputData = [
                'store_id' => $storeId,
                'categories_sales' => json_encode(Arr::get($data, 'categories_sales')),
            ];
            $this->model()->updateOrInsert(['store_id' => $storeId], $inputData);

            return [
                'message' => 'Success.',
                'number_of_failures' => 0,
                'errors' => [],
            ];
        }, 'Save sales performance');
    }

    /**
     * Get category's sales performance table.
     */
    public function getPerformanceTable(string $storeId, array $data): Collection
    {
        $performanceItems = $this->model()->where('store_id', $storeId)->first();
        $performanceItems = ! is_null($performanceItems) ? $performanceItems->toArray() : [];

        $data = collect();
        $itemsSales = Arr::get($performanceItems, 'categories_sales');
        if (! is_null($itemsSales)) {
            $items = json_decode($itemsSales, true);
            $managementNums = implode(',', collect($items)->pluck('catalog_ids')->toArray());

            $filters['catalog_ids'] = $managementNums;
            $actualData = $this->categoryAnalysisService->getCategorySalesInfo($filters)->get('data');

            foreach ($items as $item) {
                $actualItem = collect($actualData)->filter(function ($itemData) use ($item) {
                    return ! is_null(Arr::get($itemData, 'catalog_id'))
                        && Arr::get($itemData, 'catalog_id') == Arr::get($item, 'catalog_id');
                })->first();
                $data->add([
                    'store_id' => $storeId,
                    'catalog_id' => Arr::get($item, 'catalog_id', ''),
                    'catalog_name' => Arr::get($actualItem, 'catalog_name', ''),
                    'target_sales' => Arr::get($item, 'sales_amnt', 0),
                    'current_month_sales' => Arr::get($actualItem, 'current_month_sales', 0),
                    'previous_month_sales' => Arr::get($actualItem, 'previous_month_sales', 0),
                    'month_before_previous_sales' => Arr::get($actualItem, 'month_before_previous_sales', 0),
                ]);
            }
        }

        return $data;
    }
}
