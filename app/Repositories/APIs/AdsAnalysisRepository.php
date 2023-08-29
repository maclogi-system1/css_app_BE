<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\AdsAnalysisRepository as AdsAnalysisRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\AdsAnalysisService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AdsAnalysisRepository extends Repository implements AdsAnalysisRepositoryContract
{
    public function __construct(
        protected AdsAnalysisService $adsAnalysisService,
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
    public function getAdsAnalysisSummary(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }

        return $this->adsAnalysisService->getAdsAnalysisSummary($storeId, $filters);
    }

    /**
     * Get data Chart and Table Ads conversion from AI.
     */
    public function getListAdsConversion(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }

        return $this->adsAnalysisService->getListAdsConversion($storeId, $filters);
    }

    /**
     * Get list highest and lowest product by ROAS from AI.
     */
    public function getListProductByRoas(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }

        return $this->adsAnalysisService->getListProductByRoas($storeId, $filters);
    }

    /**
     * Get data chart sales and access impact from AI.
     */
    public function getDataChartSalesAndAccess(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }

        return $this->adsAnalysisService->getDataChartSalesAndAccess($storeId, $filters);
    }
}
