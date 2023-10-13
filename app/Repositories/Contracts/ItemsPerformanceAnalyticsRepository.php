<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ItemsPerformanceAnalyticsRepository extends Repository
{
    /**
     * Save product's sales performance table.
     */
    public function saveSalesPerformanceTable(string $storeId, array $data): ?array;

    /**
     * Get product's sales performance table.
     */
    public function getPerformanceTable(string $storeId, array $data): Collection;
}
