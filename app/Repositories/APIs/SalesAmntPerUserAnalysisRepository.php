<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\SalesAmntPerUserAnalysisRepository as SalesAmntPerUserAnalysisRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\SalesAmntPerUserService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SalesAmntPerUserAnalysisRepository extends Repository implements SalesAmntPerUserAnalysisRepositoryContract
{
    public function __construct(
        protected SalesAmntPerUserService $salesAmntPerUserService,
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
     * Get data ads analysis summary from AI.
     */
    public function getChartSummarySalesAmntPerUser(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }
        $result = $this->salesAmntPerUserService->getChartSummarySalesAmntPerUser($storeId, $filters);

        return $result->get('data');
    }

    /**
     * Get data table comparison with last year from AI.
     */
    public function getTableSalesAmntPerUserComparison(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }
        $result = $this->salesAmntPerUserService->getSalesAmntPerUserComparisonTable($storeId, $filters);

        return $result->get('data');
    }

    /**
     * Get data table comparison with last year from AI.
     */
    public function getChartPVSalesAmntPerUser(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }
        $result = $this->salesAmntPerUserService->getChartPVSalesAmntPerUser($storeId, $filters);

        return $result->get('data');
    }
}
