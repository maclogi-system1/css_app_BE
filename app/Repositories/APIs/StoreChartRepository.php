<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\StoreChartRepository as StoreChartRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\StoreChartService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class StoreChartRepository extends Repository implements StoreChartRepositoryContract
{
    public function __construct(
        private  StoreChartService $storeChartService,
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return '';
    }

    /**
     * Get data chart comparison conversionrate from AI.
     */
    public function getDataChartComparisonConversionRate(string $storeId, array $filters = []): Collection
    {
        // Check if the input matches the 'yyyy-MM' format
        $isMonthQuery = false;
        if (Arr::has($filters, ['from_date', 'to_date'])) {
            if (
                preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'from_date'))
                && preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'to_date'))
            ) {
                $isMonthQuery = true;
            }
        }

        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }
        $result = $this->storeChartService->getDataChartComparisonConversionRate($storeId, $filters, $isMonthQuery);
        $data[] = $result->get('data');

        // Get compared data product analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data[] = $this->storeChartService->getDataChartComparisonConversionRate($storeId, $filters, $isMonthQuery)->get('data');
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get data table conversion rate analysis from AI.
     */
    public function getDataTableConversionRateAnalysis(string $storeId, array $filters = []): Collection
    {
        // Check if the input matches the 'yyyy-MM' format
        $isMonthQuery = false;
        if (Arr::has($filters, ['from_date', 'to_date'])) {
            if (
                preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'from_date'))
                && preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'to_date'))
            ) {
                $isMonthQuery = true;
            }
        }

        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }

        $result = collect($this->storeChartService->getDataTableConversionRateAnalysis($storeId, $filters, $isMonthQuery));
        $data[] = $result->get('data');

        // Get compared data table conversion rate analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data[] = $this->storeChartService->getDataTableConversionRateAnalysis($storeId, $filters, $isMonthQuery)->get('data');
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get data relation between number of PV and conversion rate from AI.
     */
    public function getDataChartRelationPVAndConversionRate(string $storeId, array $filters = []): Collection
    {
        // Check if the input matches the 'yyyy-MM' format
        $isMonthQuery = false;
        if (Arr::has($filters, ['from_date', 'to_date'])) {
            if (
                preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'from_date'))
                && preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'to_date'))
            ) {
                $isMonthQuery = true;
            }
        }
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }
        $result = $this->storeChartService->getDataChartRelationPVAndConversionRate($storeId, $filters, $isMonthQuery);
        $data[] = $result->get('data');

        // Get compared data product analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data[] = $this->storeChartService->getDataChartRelationPVAndConversionRate($storeId, $filters, $isMonthQuery)->get('data');
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }
}
