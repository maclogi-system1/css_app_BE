<?php

namespace App\Repositories\Contracts;

interface MqKpiRepository extends Repository
{
    /**
     * Get KPI summary (KPI target achievement rate, KPI performance summary).
     */
    public function getSummary(string $storeId, array $filters = []): array;
}
