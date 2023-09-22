<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ReviewAnalysisRepository extends Repository
{
    /**
     * Get review analysis summary by store_id.
     */
    public function getReviewSummary($storeId, array $filters = []): Collection;

    /**
     * Get data chart reviews trend.
     */
    public function getChartReviewsTrends($storeId, array $filters = []): Collection;
}
