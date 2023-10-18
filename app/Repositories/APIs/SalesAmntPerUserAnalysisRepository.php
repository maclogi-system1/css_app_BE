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
        $result = $this->salesAmntPerUserService->getChartSummarySalesAmntPerUser($storeId, $filters, $isMonthQuery);
        $data[] = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data[] = $this->salesAmntPerUserService->getChartSummarySalesAmntPerUser($storeId, $filters, $isMonthQuery)->get('data');
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get data table comparison with last year from AI.
     */
    public function getTableSalesAmntPerUserComparison(string $storeId, array $filters = []): Collection
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
        $result = $this->salesAmntPerUserService->getSalesAmntPerUserComparisonTable($storeId, $filters, $isMonthQuery);
        $data[] = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data[] = $this->salesAmntPerUserService->getSalesAmntPerUserComparisonTable($storeId, $filters, $isMonthQuery)->get('data');
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get data table comparison with last year from AI.
     */
    public function getChartPVSalesAmntPerUser(string $storeId, array $filters = []): Collection
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
        $result = $this->salesAmntPerUserService->getChartPVSalesAmntPerUser($storeId, $filters, $isMonthQuery);
        $data[] = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data[] = $this->salesAmntPerUserService->getChartPVSalesAmntPerUser($storeId, $filters, $isMonthQuery)->get('data');
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }
}
