<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
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
            $listStoreValues = collect();
            for ($i = 0; $i < 10; $i++) {
                $listStoreValues->add([
                    'display_name' => '店舗'.$i + 1,
                    'store_id' => 'store_'.$i + 1,
                    'conversion_rate' => rand(0, 50) / 10,
                ]);
            }
            $dataFake->add([
                'date' => $date,
                'stores_conversion_rate' => $listStoreValues,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
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

    public function getDataChartRelationPVAndConversionRate($filters): Collection
    {
        $dataFake = collect();
        for ($i = 0; $i < 30; $i++) {
            $dataFake->add([
                'conversion_rate' => rand(0, 5000),
                'PV' => rand(0, 70000),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'chart_pv' => $dataFake,
            ]),
        ]);
    }
}
