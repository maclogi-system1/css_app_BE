<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Collection;

class AccessSourceService extends Service
{
    use HasMqDateTimeHandler;

    public function getTotalAccess(string $storeId, array $filters = [])
    {
        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'store_id' => $storeId,
                'date' => now()->format('Y-m-d H:i:s'),
                'total_access' => 4000000,
            ]),
        ]);
    }

    public function getListAccessSource(string $storeId, array $filters = []): Collection
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
                'rakuten_search' => rand(5000, 50000),
                'store_item_page' => rand(5000, 50000),
                'exp_rakuten_service' => rand(10000, 50000),
                'rakuten_market' => rand(5000, 50000),
                'rakuten_gold' => rand(50000, 100000),
                'rakuten_event' => rand(10000, 100000),
                'faivorite' => rand(5000, 50000),
                'basket' => rand(5000, 50000),
                'review' => rand(5000, 50000),
                'view_history' => rand(5000, 50000),
                'purchase_history' => rand(5000, 50000),
                'in_store_search' => rand(5000, 50000),
                'store_category_page' => rand(5000, 50000),
                'store_top' => rand(5000, 50000),
                'discount' => rand(5000, 50000),
                'room' => rand(5000, 50000),
                'ranking_market' => rand(5000, 50000),
                'instagram' => rand(5000, 50000),
                'google' => rand(5000, 50000),
                'yahoo' => rand(5000, 50000),
                'line' => rand(5000, 50000),
                'twitter' => rand(5000, 50000),
                'facebook' => rand(5000, 50000),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_access_source' => $dataFake,
            ]),
        ]);
    }

    public function getTableAccessSource(string $storeId, array $filters = []): Collection
    {
        $dataFake = collect();

        $dataFake->add([
            'store_id' => $storeId,
            'rakuten_search' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'store_item_page' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'exp_rakuten_service'  => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'rakuten_market' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'rakuten_gold'  => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'rakuten_event'  => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'faivorite' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'basket' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'review' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'view_history' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'purchase_history' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'in_store_search' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'store_category_page' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'store_top' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'discount' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'room' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'ranking_market' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'instagram' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'google' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'yahoo' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'line' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'twitter' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
            'facebook' => [
                'value' => rand(5000, 50000),
                'rate' => rand(0, 20),
            ],
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_access_source' => $dataFake,
            ]),
        ]);
    }
}
