<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface UserAccessRepository extends Repository
{
    public function getTotalUserAccess(string $storeId, array $filters = []): Collection;

    /**
     * Get data user access from AI.
     */
    public function getDataChartUserAccess(string $storeId, array $filters = []): Collection;

    /**
     * Get data user access with ads and none ads from AI.
     */
    public function getDataChartUserAccessAds(string $storeId, array $filters = []): Collection;

    /**
     * Get chart data access source from AI.
     */
    public function getDataChartAccessSource(string $storeId, array $filters = []): Collection;

    /**
     * Get table data access source from AI.
     */
    public function getDataTableAccessSource(string $storeId, array $filters = []): Collection;
}
