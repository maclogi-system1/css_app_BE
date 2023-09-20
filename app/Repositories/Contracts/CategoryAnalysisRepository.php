<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface CategoryAnalysisRepository extends Repository
{
    /**
     * Get category analysis summary by store_id.
     */
    public function getCategorySummary($storeId, array $filters = []): Collection;

    /**
     * Get chart selected categories sales per month from AI.
     */
    public function getChartSelectedCategories(array $filters = []): Collection;

    /**
     * Get chart categories's trends from AI.
     */
    public function getChartCategoriesTrends(array $filters = []): Collection;

    /**
     * Get chart categories's stay times from AI.
     */
    public function getChartCategoriesStayTimes(array $filters = []): Collection;

    /**
     * Get chart categories's reviews trends from AI.
     */
    public function chartCategoriesReviewsTrends(array $filters = []): Collection;
}
