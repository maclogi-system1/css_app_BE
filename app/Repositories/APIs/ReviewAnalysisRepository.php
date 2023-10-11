<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\ReviewAnalysisRepository as ReviewAnalysisRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\ReviewAnalysisService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ReviewAnalysisRepository extends Repository implements ReviewAnalysisRepositoryContract
{
    public function __construct(
        protected ReviewAnalysisService $reviewAnalysisService,
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
     * Get review analysis summary by store_id.
     */
    public function getReviewSummary($storeId, array $filters = []): Collection
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
        $result = $this->reviewAnalysisService->getReviewSummary($storeId, $filters, $isMonthQuery);
        $data = $result->get('data');

        // Get compared data product analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->reviewAnalysisService->getReviewSummary($storeId, $filters, $isMonthQuery)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get data chart reviews trends.
     */
    public function getChartReviewsTrends($storeId, array $filters = []): Collection
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
        $result = $this->reviewAnalysisService->getChartReviewsTrends($storeId, $filters, $isMonthQuery);
        $data = $result->get('data');

        // Get compared data product analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->reviewAnalysisService->getChartReviewsTrends($storeId, $filters, $isMonthQuery)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }
}
