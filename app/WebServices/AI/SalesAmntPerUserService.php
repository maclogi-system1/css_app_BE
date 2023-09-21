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
            $salesAmntPC = rand(1000, 5000);
            $salesAmntApp = rand(1000, 5000);
            $salesAmntSP = rand(1000, 5000);
            $dataFake->add([
                'store_id' => $storeId,
                'date' => $date,
                'sales_all' => $salesAmntPC + $salesAmntApp + $salesAmntSP,
                'sales_pc' => $salesAmntPC,
                'sales_sd_web' => $salesAmntApp,
                'sales_sd_app' =>  $salesAmntSP,
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
                'store_id' => $storeId,
                'date' => $date,
                'sales_all' => $salesAmntPC + $salesAmntApp + $salesAmntSP,
                'sales_pc' => $salesAmntPC,
                'sales_sd_web' => $salesAmntApp,
                'sales_sd_app' =>  $salesAmntSP,
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
                'PV' => rand(0, 70000),
                'sales_all' =>  rand(1000, 5000),
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
