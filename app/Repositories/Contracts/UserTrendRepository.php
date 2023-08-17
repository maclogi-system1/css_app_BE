<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface UserTrendRepository extends Repository
{
    /**
     * Get data user trends from AI.
     */
    public function getDataChartUserTrends(string $storeId, array $filters = []): Collection;
}
