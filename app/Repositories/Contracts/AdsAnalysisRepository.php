<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface AdsAnalysisRepository extends Repository
{
    /**
     * Get data ads analysis summary from AI.
     */
    public function getAdsAnalysisSummary(string $storeId, array $filters = []): Collection;

    /**
     * Get data chart Ads conversion from AI.
     */
    public function getListAdsConversion(string $storeId, array $filters = []): Collection;

    /**
     * Get list highest and lowest product by ROAS from AI.
     */
    public function getListProductByRoas(string $storeId, array $filters = []): Collection;

    /**
     * Get data chart sales and access impact from AI.
     */
    public function getDataChartSalesAndAccess(string $storeId, array $filters = []): Collection;
}
