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
        $data[] = $result->get('data');

        // Get compared data product analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data[] = $this->productAnalysisService->getProductSummary($storeId, $filters)->get('data');
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get chart selected products sales per month from AI.
     */
    public function getChartSelectedProducts(array $filters = []): Collection
    {
        $result = $this->productAnalysisService->getChartSelectedProducts($filters);
        $data = $result->get('data');

        // Get compared data product analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->productAnalysisService->getChartSelectedProducts($filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get chart products's trends from AI.
     */
    public function getChartProductsTrends(array $filters = []): Collection
    {
        $result = $this->productAnalysisService->getChartProductsTrends($filters);
        $data = $result->get('data');

        // Get compared data product analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data = $data->merge($this->productAnalysisService->getChartProductsTrends($filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get chart products's stay times from AI.
     */
    public function getChartProductsStayTimes(array $filters = []): Collection
    {
        $result = $this->productAnalysisService->getChartProductsStayTimes($filters);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data = $data->merge($this->productAnalysisService->getChartProductsStayTimes($filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get chart products's rakuten ranking from AI.
     */
    public function getChartProductsRakutenRanking(array $filters = []): Collection
    {
        $result = $this->productAnalysisService->getChartProductsRakutenRanking($filters);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $data = $data->merge($this->productAnalysisService->getChartProductsRakutenRanking($filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get chart products's reviews trends from AI.
     */
    public function getChartProductsReviewsTrends(array $filters = []): Collection
    {
        $result = $this->productAnalysisService->getChartProductsReviewsTrends($filters);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->productAnalysisService->getChartProductsReviewsTrends($filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }
}
