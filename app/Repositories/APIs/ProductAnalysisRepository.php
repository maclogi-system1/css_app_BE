<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\ProductAnalysisRepository as ProductAnalysisRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\ProductAnalysisService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProductAnalysisRepository extends Repository implements ProductAnalysisRepositoryContract
{
    public function __construct(
        protected ProductAnalysisService $productAnalysisService,
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
     * Get product analysis summary by store_id.
     */
    public function getProductSummary($storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m-d');
        }
        $result = $this->productAnalysisService->getProductSummary($storeId, $filters);

        return collect($result->get('data'));
    }

    /**
     * Get chart selected products sales per month from AI.
     */
    public function getChartSelectedProducts(array $filters = []): Collection
    {
        $result = $this->productAnalysisService->getChartSelectedProducts($filters);

        return collect($result->get('data'));
    }

    /**
     * Get chart products's trends from AI.
     */
    public function getChartProductsTrends(array $filters = []): Collection
    {
        $result = $this->productAnalysisService->getChartProductsTrends($filters);

        return collect($result->get('data'));
    }

    /**
     * Get chart products's stay times from AI.
     */
    public function getChartProductsStayTimes(array $filters = []): Collection
    {
        $result = $this->productAnalysisService->getChartProductsStayTimes($filters);

        return collect($result->get('data'));
    }

    /**
     * Get chart products's rakuten ranking from AI.
     */
    public function getChartProductsRakutenRanking(array $filters = []): Collection
    {
        $result = $this->productAnalysisService->getChartProductsRakutenRanking($filters);

        return collect($result->get('data'));
    }

    /**
     * Get chart products's reviews trends from AI.
     */
    public function getChartProductsReviewsTrends(array $filters = []): Collection
    {
        $result = $this->productAnalysisService->getChartProductsReviewsTrends($filters);

        return collect($result->get('data'));
    }
}
