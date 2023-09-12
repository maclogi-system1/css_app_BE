<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\ReportSearchRepository as ReportSearchRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\ReportSearchService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ReportSearchRepository extends Repository implements ReportSearchRepositoryContract
{
    public function __construct(
        private ReportSearchService $reportSearchService
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
     * Get trending keywords data report search for chart from AI.
     */
    public function getDataChartReportSearch(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date') > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }

        return $this->reportSearchService->getDataReportSearch($storeId, $filters);
    }

    /**
     * Get ranking keywords data report search for chart from AI.
     */
    public function getDataTableReportSearch(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }

        return $this->reportSearchService->getRankingReportSearch($storeId, $filters);
    }

    /**
     * Get detail keywords data report search  by product from AI.
     */
    public function getDataReportSearchByProduct(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date') > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }

        return $this->reportSearchService->getDataReportSearchByProduct($storeId, $filters);
    }

    /**
     * Get chart data organic inflows report search keywords from AI.
     */
    public function getDataChartOrganicInflows(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date') > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }

        return $this->reportSearchService->getDataChartOrganicInflows($storeId, $filters);
    }

    /**
     * Get chart data inflows via specific words report search from AI.
     */
    public function getDataChartInflowsViaSpecificWords(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date') > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }

        return $this->reportSearchService->getDataChartInflowsViaSpecificWords($storeId, $filters);
    }
}
