<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Collection;

class StoreChartService extends Service
{
    use HasMqDateTimeHandler;

    public function getDataChartComparisonConversionRate(array $filters = []): Collection
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
                'date' => $date,
                [
                    'display_name' => '店舗1',
                    'store_id' => 'store_1',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
                [
                    'display_name' => '店舗2',
                    'store_id' => 'store_2',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
                [
                    'display_name' => '店舗3',
                    'store_id' => 'store_3',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
                [
                    'display_name' => '店舗4',
                    'store_id' => 'store_4',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
                [
                    'display_name' => '店舗5',
                    'store_id' => 'store_5',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
                [
                    'display_name' => '店舗5',
                    'store_id' => 'store_5',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
                [
                    'display_name' => '店舗6',
                    'store_id' => 'store_6',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
                [
                    'display_name' => '店舗7',
                    'store_id' => 'store_7',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
                [
                    'display_name' => '店舗8',
                    'store_id' => 'store_8',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
                [
                    'display_name' => '店舗9',
                    'store_id' => 'store_9',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
                [
                    'display_name' => '店舗10',
                    'store_id' => 'store_10',
                    'conversion_rate' => rand(0, 50) / 10,
                ],
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => ['chart_conversion_rate' => $dataFake],
        ]);
    }

    public function getDataTableConversionRateAnalysis(array $filters = []): Collection
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
                'date' => $date,
                'total'=> rand(50, 100),
                'pc' => rand(0, 50),
                'app' => rand(0, 50),
                'phone' => rand(0, 50),
            ]);
        }

        return $dataFake;
    }

    public function getDataChartRelationPVAndConversionRate(): Collection
    {
        $dataFake['data'] = [];
        for ($i = 0; $i < 30; $i++) {
            array_push(
                $dataFake['data'],
                [
                    'conversion_rate' => rand(0, 5000),
                    'PV' => rand(0, 70000),
                ]
            );
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }
}
