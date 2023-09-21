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
        $result = $this->reportSearchService->getDataReportSearch($storeId, $filters);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data = $data->merge($this->reportSearchService->getDataReportSearch($storeId, $filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get ranking keywords data report search for chart from AI.
     */
    public function getDataTableReportSearch(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }
        $result = $this->reportSearchService->getRankingReportSearch($storeId, $filters);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data = $data->merge($this->reportSearchService->getRankingReportSearch($storeId, $filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get detail keywords data report search  by product from AI.
     */
    public function getDataReportSearchByProduct(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date') > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }
        $result = $this->reportSearchService->getDataReportSearchByProduct($storeId, $filters);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data = $data->merge($this->reportSearchService->getDataReportSearchByProduct($storeId, $filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
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
