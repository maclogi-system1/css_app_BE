<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;

class SalesAmntPerUserService extends Service
{
    use HasMqDateTimeHandler;

    /**
     * Get data ads analysis summary from AI.
     */
    public function getChartSummarySalesAmntPerUser($storeId, array $filters)
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
                    'sales_amnt_per_user' => rand(1000, 5000),
                ]);
            }
            $dataFake->add([
                'date' => $date,
                'stores_sales_amnt_per_user' => $listStoreValues,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get data compare sale amount per user with last year data from AI.
     */
    public function getSalesAmntPerUserComparisonTable($storeId, array $filters)
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
            $salesAmntPC = rand(1000, 5000);
            $salesAmntApp = rand(1000, 5000);
            $salesAmntSP = rand(1000, 5000);
            $dataFake->add([
                'date' => $date,
                'total' => $salesAmntPC + $salesAmntApp + $salesAmntSP,
                'pc' => $salesAmntPC,
                'app' => $salesAmntApp,
                'phone' =>  $salesAmntSP,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get data chart PV and sales amount per user from AI.
     */
    public function getChartPVSalesAmntPerUser($storeId, array $filters)
    {
        $dataFake = collect();

        for ($i = 0; $i < 30; $i++) {
            $dataFake->add([
                'sales_all' =>  rand(1000, 5000),
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
