<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Collection;

class AccessAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    public function getDataTableAccessAnalysis(string $storeId, array $filters = []): Collection
    {
        $dataFake = collect();

        $dataFake->add([
            'store_id' => $storeId,
            'table_report_search' => collect([
                [
                    'display_name' => 'カテゴリA',
                    'category' => 'category_a',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリB',
                    'category' => 'category_b',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリC',
                    'category' => 'category_c',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリD',
                    'category' => 'category_d',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリE',
                    'category' => 'category_e',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリF',
                    'category' => 'category_f',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリG',
                    'category' => 'category_g',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリH',
                    'category' => 'category_h',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリI',
                    'category' => 'category_i',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリJ',
                    'category' => 'category_j',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリK',
                    'category' => 'category_k',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
                [
                    'display_name' => 'カテゴリL',
                    'category' => 'category_l',
                    'click_num' => rand(1000, 5000),
                    'ctr_rate' => rand(3, 20),
                    'new_user_sales_num' => rand(500, 1000),
                    'new_user_sales_rate' => rand(0, 50),
                    'exist_user_sales_num'  => rand(500, 1000),
                    'exist_user_sales_rate' => rand(0, 50),
                ],
            ]),
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    public function getDataChartNewUserAccess(string $storeId, array $filters = []): Collection
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
                'new_user_sales_num' => rand(5000, 10000),
                'category_a' => rand(0, 500),
                'category_b' => rand(0, 500),
                'category_c' => rand(0, 500),
                'category_d' => rand(0, 500),
                'category_e' => rand(0, 500),
                'category_f' => rand(0, 500),
                'category_g' => rand(0, 500),
                'category_h' => rand(0, 500),
                'category_i' => rand(0, 500),
                'category_j' => rand(0, 500),
                'category_k' => rand(0, 500),
                'category_l' => rand(0, 500),
            ]);
        }

        return $dataFake;
    }

    public function getDataChartExistUserAccess(string $storeId, array $filters = []): Collection
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
                'exist_user_sales_num' => rand(5000, 10000),
                'category_a' => rand(0, 500),
                'category_b' => rand(0, 500),
                'category_c' => rand(0, 500),
                'category_d' => rand(0, 500),
                'category_e' => rand(0, 500),
                'category_f' => rand(0, 500),
                'category_g' => rand(0, 500),
                'category_h' => rand(0, 500),
                'category_i' => rand(0, 500),
                'category_j' => rand(0, 500),
                'category_k' => rand(0, 500),
                'category_l' => rand(0, 500),
            ]);
        }

        return $dataFake;
    }
}
