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
            'data' => $dataFake,
        ]);
    }

    public function getTableAccessSource(string $storeId, array $filters = []): Collection
    {
        $dataFake = collect();

        $dataFake->add([
            'store_id' => $storeId,
            'chart_access_source' => collect([
                [
                    'display_name' => '楽天サーチ',
                    'name' => 'rakuten_search',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '店舗商品ページ',
                    'name' => 'store_item_page',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '楽天その他サービス',
                    'name' => 'exp_rakuten_service',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '楽天市場トップ',
                    'name' => 'rakuten_market',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '楽天Gold',
                    'name' => 'rakuten_gold',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '楽天イベントページ',
                    'name' => 'rakuten_event',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'お気に入り',
                    'name' => 'faivorite',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '買い物かご',
                    'name' => 'basket',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'みんなのレビュー',
                    'name' => 'review',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '閲覧履歴',
                    'name' => 'view_history',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '購入履歴',
                    'name' => 'purchase_history',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '店舗内サーチ',
                    'name' => 'in_store_search',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '店舗カテゴリページ',
                    'name' => 'store_category_page',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => '店舗トップ',
                    'name' => 'store_top',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'クーポン',
                    'name' => 'discount',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'ROOM',
                    'name' => 'room',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'ランキング市場',
                    'name' => 'ranking_market',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'Instagram',
                    'name' => 'instagram',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'Google',
                    'name' => 'google',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'Yahoo',
                    'name' => 'yahoo',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'Line',
                    'name' => 'line',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'Twitter',
                    'name' => 'twitter',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
                [
                    'display_name' => 'Facebook',
                    'name' => 'facebook',
                    'value' => rand(5000, 50000),
                    'rate' => rand(0, 20),
                ],
            ]),
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }
}
