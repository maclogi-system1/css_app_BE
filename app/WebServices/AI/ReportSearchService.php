<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Collection;

class ReportSearchService extends Service
{
    use HasMqDateTimeHandler;

    public function getDataReportSearch(string $storeId, array $filters = []): Collection
    {
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

        foreach ($dateTimeRange as $date) {
            $dataFake->add([
                'store_id' => $storeId,
                'date' => $date,
                'keyword_1' => rand(1000, 5000),
                'keyword_2' => rand(1000, 5000),
                'keyword_3' => rand(1000, 5000),
                'keyword_4' => rand(1000, 5000),
                'keyword_5' => rand(1000, 5000),
                'keyword_6' => rand(1000, 5000),
                'keyword_7' => rand(1000, 5000),
                'keyword_8' => rand(1000, 5000),
                'keyword_9' => rand(1000, 5000),
                'keyword_10' => rand(1000, 5000),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_report_search' => $dataFake,
            ]),
        ]);
    }

    public function getRankingReportSearch(string $storeId, array $filters = []): Collection
    {
        $dataFake = collect();

        $dataFake->add([
            'store_id' => $storeId,
            'table_report_search' => collect([
                [
                    'display_name' => 'キーワード1',
                    'keyword' => 'keyword_1',
                    'value' => rand(1000, 5000),
                    'rate' => rand(3, 20),
                    'compare_previous_month' => rand(-30, 30),
                    'compare_previous_year' => rand(-30, 30),
                ],
                [
                    'display_name' => 'キーワード2',
                    'keyword' => 'keyword_2',
                    'value' => rand(1000, 5000),
                    'rate' => rand(3, 20),
                    'compare_previous_month' => rand(-30, 30),
                    'compare_previous_year' => rand(-30, 30),
                ],
                [
                    'display_name' => 'キーワード3',
                    'keyword' => 'keyword_3',
                    'value' => rand(1000, 5000),
                    'rate' => rand(3, 20),
                    'compare_previous_month' => rand(-30, 30),
                    'compare_previous_year' => rand(-30, 30),
                ],
                [
                    'display_name' => 'キーワード4',
                    'keyword' => 'keyword_4',
                    'value' => rand(1000, 5000),
                    'rate' => rand(3, 20),
                    'compare_previous_month' => rand(-30, 30),
                    'compare_previous_year' => rand(-30, 30),
                ],
                [
                    'display_name' => 'キーワード5',
                    'keyword' => 'keyword_5',
                    'value' => rand(1000, 5000),
                    'rate' => rand(3, 20),
                    'compare_previous_month' => rand(-30, 30),
                    'compare_previous_year' => rand(-30, 30),
                ],
                [
                    'display_name' => 'キーワード6',
                    'keyword' => 'keyword_6',
                    'value' => rand(1000, 5000),
                    'rate' => rand(3, 20),
                    'compare_previous_month' => rand(-30, 30),
                    'compare_previous_year' => rand(-30, 30),
                ],
                [
                    'display_name' => 'キーワード7',
                    'keyword' => 'keyword_7',
                    'value' => rand(1000, 5000),
                    'rate' => rand(3, 20),
                    'compare_previous_month' => rand(-30, 30),
                    'compare_previous_year' => rand(-30, 30),
                ],
                [
                    'display_name' => 'キーワード8',
                    'keyword' => 'keyword_8',
                    'value' => rand(1000, 5000),
                    'rate' => rand(3, 20),
                    'compare_previous_month' => rand(-30, 30),
                    'compare_previous_year' => rand(-30, 30),
                ],
                [
                    'display_name' => 'キーワード9',
                    'keyword' => 'keyword_9',
                    'value' => rand(1000, 5000),
                    'rate' => rand(3, 20),
                    'compare_previous_month' => rand(-30, 30),
                    'compare_previous_year' => rand(-30, 30),
                ],
                [
                    'display_name' => 'キーワード10',
                    'keyword' => 'keyword_10',
                    'value' => rand(1000, 5000),
                    'rate' => rand(3, 20),
                    'compare_previous_month' => rand(-30, 30),
                    'compare_previous_year' => rand(-30, 30),
                ],
            ]),
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    public function getDataReportSearchByProduct(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            [
                'format' => 'Y/m/d',
                'step' => '1 day',
            ]
        );

        $chartByProduct = collect();
        $dataFake = collect();

        foreach ($dateTimeRange as $date) {
            $chartByProduct->add([
                'date' => $date,
                'keyword_1' => rand(1000, 5000),
                'keyword_2' => rand(1000, 5000),
                'keyword_3' => rand(1000, 5000),
                'keyword_4' => rand(1000, 5000),
                'keyword_5' => rand(1000, 5000),
                'keyword_6' => rand(1000, 5000),
                'keyword_7' => rand(1000, 5000),
                'keyword_8' => rand(1000, 5000),
                'keyword_9' => rand(1000, 5000),
                'keyword_10' => rand(1000, 5000),
            ]);
        }

        $dataFake->add([
            'store_id' => $storeId,
            'product_1' => collect([
                'total_access' => rand(1000, 5000),
                'item_id' => rand(1000, 5000),
                'item_name' => '商品名1テキス',
                'ranking' => 1,
                'table_report_serach_by_product' => collect([
                    [
                        'display_name' => 'キーワード1',
                        'keyword' => 'keyword_1',
                        'value' => rand(1000, 5000),
                        'rate' => rand(3, 20),
                        'conversion_rate' => rand(3, 20),
                    ],
                    [
                        'display_name' => 'キーワード2',
                        'keyword' => 'keyword_2',
                        'value' => rand(1000, 5000),
                        'rate' => rand(3, 20),
                        'conversion_rate' => rand(3, 20),
                    ],
                    [
                        'display_name' => 'キーワード3',
                        'keyword' => 'keyword_3',
                        'value' => rand(1000, 5000),
                        'rate' => rand(3, 20),
                        'conversion_rate' => rand(3, 20),
                    ],
                    [
                        'display_name' => 'キーワード4',
                        'keyword' => 'keyword_4',
                        'value' => rand(1000, 5000),
                        'rate' => rand(3, 20),
                        'conversion_rate' => rand(3, 20),
                    ],
                    [
                        'display_name' => 'キーワード5',
                        'keyword' => 'keyword_5',
                        'value' => rand(1000, 5000),
                        'rate' => rand(3, 20),
                        'conversion_rate' => rand(3, 20),
                    ],
                ]),
                'chart_report_search_by_product' => $chartByProduct,
            ]),
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'detail_report_search_by_product' => $dataFake,
            ]),
        ]);
    }
}
