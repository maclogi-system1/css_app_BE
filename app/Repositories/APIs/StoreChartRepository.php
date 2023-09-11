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
    public function getDataChartComparisonConversionRate(array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }

        return $this->storeChartService->getDataChartComparisonConversionRate($filters);
    }

    /**
     * Get data table conversion rate analysis from AI.
     */
    public function getDataTableConversionRateAnalysis(array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }

        $data = collect($this->storeChartService->getDataTableConversionRateAnalysis($filters));

        // Get compared data table conversion rate analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->storeChartService->getDataTableConversionRateAnalysis($filters));
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => ['table_conversion_rate_analysis' => $data],
        ]);
    }

    /**
     * Get data relation between number of PV and conversion rate from AI.
     */
    public function getDataChartRelationPVAndConversionRate(array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }

        return $this->storeChartService->getDataChartRelationPVAndConversionRate($filters);
    }
}