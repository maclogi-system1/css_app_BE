<?php

namespace App\Repositories\Eloquents;

use App\Models\ItemsPerformanceAnalytics;
use App\Repositories\Contracts\ItemsPerformanceAnalyticsRepository as ItemsPerformanceAnalyticsRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\ProductAnalysisService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ItemsPerformanceAnalyticsRepository extends Repository implements ItemsPerformanceAnalyticsRepositoryContract
{
    public function __construct(
        protected ProductAnalysisService $productAnalysisService,
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return ItemsPerformanceAnalytics::class;
    }

    /**
     * Save product's sales performance table.
     */
    public function saveSalesPerformanceTable(string $storeId, array $data): ?array
    {
        return $this->handleSafely(function () use ($storeId, $data) {
            $inputData = [
                'store_id' => $storeId,
                'items_sales' => json_encode(Arr::get($data, 'items_sales')),
                'created_at' => now(),
                'updated_at' => now(),
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
     * Get product's sales performance table.
     */
    public function getPerformanceTable(string $storeId, array $data): Collection
    {
        $performanceItems = $this->model()->where('store_id', $storeId)->first();
        $performanceItems = ! is_null($performanceItems) ? $performanceItems->toArray() : [];

        $data = collect();
        $itemsSales = Arr::get($performanceItems, 'items_sales');
        if (! is_null($itemsSales)) {
            $items = json_decode($itemsSales, true);
            if (! is_null($items)) {
                $managementNums = implode(',', collect($items)->pluck('mng_number')->toArray());

                $filters['management_nums'] = $managementNums;
                $actualData = $this->productAnalysisService->getProductSalesInfo($filters)->get('data');

                foreach ($items as $item) {
                    $actualItem = collect($actualData)->filter(function ($itemData) use ($item) {
                        return ! is_null(Arr::get($itemData, 'management_num'))
                            && Arr::get($itemData, 'management_num') == Arr::get($item, 'mng_number');
                    })->first();
                    $data->add([
                        'store_id' => $storeId,
                        'management_num' => Arr::get($item, 'mng_number'),
                        'item_name' => Arr::get($actualItem, 'item_name'),
                        'target_sales' => Arr::get($item, 'sales_amnt'),
                        'current_month_sales' => Arr::get($actualItem, 'current_month_sales'),
                        'previous_month_sales' => Arr::get($actualItem, 'previous_month_sales'),
                        'month_before_previous_sales' => Arr::get($actualItem, 'month_before_previous_sales'),
                    ]);
                }
            }
        }

        return $data;
    }
}
