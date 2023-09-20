<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\CategoryAnalysisRepository as CategoryAnalysisRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\CategoryAnalysisService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CategoryAnalysisRepository extends Repository implements CategoryAnalysisRepositoryContract
{
    public function __construct(
        protected CategoryAnalysisService $categoryAnalysisService,
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
     * Get category analysis summary by store_id.
     */
    public function getCategorySummary($storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }
        $result[] = $this->categoryAnalysisService->getCategorySummary($storeId, $filters)->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $result[] = $this->categoryAnalysisService->getCategorySummary($storeId, $filters)->get('data');
        }

        return collect($result);
    }

    /**
     * Get chart selected categories sales per month from AI.
     */
    public function getChartSelectedCategories(array $filters = []): Collection
    {
        $result = $this->categoryAnalysisService->getChartSelectedCategories($filters)->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $result = $result->merge($this->categoryAnalysisService->getChartSelectedCategories($filters)->get('data'));
        }

        return collect($result);
    }

    /**
     * Get chart categories's trends from AI.
     */
    public function getChartCategoriesTrends(array $filters = []): Collection
    {
        $result = $this->categoryAnalysisService->getChartCategoriesTrends($filters)->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $result = $result->merge($this->categoryAnalysisService->getChartCategoriesTrends($filters)->get('data'));
        }

        return collect($result);
    }

    /**
     * Get chart categories's stay times from AI.
     */
    public function getChartCategoriesStayTimes(array $filters = []): Collection
    {
        $result = $this->categoryAnalysisService->getChartCategoriesStayTimes($filters)->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $result = $result->merge($this->categoryAnalysisService->getChartCategoriesStayTimes($filters)->get('data'));
        }

        return collect($result);
    }

    /**
     * Get chart categories's reviews trends from AI.
     */
    public function chartCategoriesReviewsTrends(array $filters = []): Collection
    {
        $result = $this->categoryAnalysisService->chartCategoriesReviewsTrends($filters)->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $result = $result->merge($this->categoryAnalysisService->chartCategoriesReviewsTrends($filters)->get('data'));
        }

        return collect($result);
    }
}
