<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface StoreChartRepository extends Repository
{
    /**
     * Get data chart comparison conversionrate from AI.
     */
    public function getDataChartComparisonConversionRate(string $storeId, array $filters = []): Collection;

    /**
     * Get data table conversion rate analysis from AI.
     */
    public function getDataTableConversionRateAnalysis(string $storeId, array $filters = []): Collection;

    /**
     * Get data relation between number of PV and conversion rate from AI.
     */
    public function getDataChartRelationPVAndConversionRate(string $storeId, array $filters = []): Collection;
}
