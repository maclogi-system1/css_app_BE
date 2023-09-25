<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface AccessAnalysisRepository extends Repository
{
    /**
     * Get detail data table access analysis from AI.
     */
    public function getDataTableAccessAnalysis(string $storeId, array $filters = []): Collection;

    /**
     * Get chart data new user access for access analysis screen from AI.
     */
    public function getDataChartNewUserAccess(string $storeId, array $filters = []): Collection;

    /**
     * Get chart data exist user access for access analysis screen from AI.
     */
    public function getDataChartExistUserAccess(string $storeId, array $filters = []): Collection;
}
