<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ProductAnalysisRepository extends Repository
{
    /**
     * Get product analysis summary by store_id.
     */
    public function getProductSummary($storeId, array $filters = []): Collection;

    /**
     * Get chart selected products sales per month from AI.
     */
    public function getChartSelectedProducts(array $filters = []): Collection;

    /**
     * Get chart products's trends from AI.
     */
    public function getChartProductsTrends(array $filters = []): Collection;

    /**
     * Get chart products's stay times from AI.
     */
    public function getChartProductsStayTimes(array $filters = []): Collection;

    /**
     * Get chart products's rakuten ranking from AI.
     */
    public function getChartProductsRakutenRanking(array $filters = []): Collection;

    /**
     * Get chart products's reviews trends from AI.
     */
    public function getChartProductsReviewsTrends(array $filters = []): Collection;
}
