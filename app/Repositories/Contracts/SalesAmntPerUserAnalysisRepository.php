<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface SalesAmntPerUserAnalysisRepository extends Repository
{
    /**
     * Get data ads analysis summary from AI.
     */
    public function getChartSummarySalesAmntPerUser(string $storeId, array $filters = []): Collection;

    /**
     * Get data compare sale amount per user with last year data from AI.
     */
    public function getTableSalesAmntPerUserComparison(string $storeId, array $filters = []): Collection;

    /**
     * Get data chart PV and sales amount per user from AI.
     */
    public function getChartPVSalesAmntPerUser(string $storeId, array $filters = []): Collection;
}
