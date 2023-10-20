<?php

namespace App\Repositories\Contracts;

use App\Models\MqKpi;
use Illuminate\Support\Carbon;

interface MqKpiRepository extends Repository
{
    /**
     * Get KPI summary (KPI target achievement rate, KPI performance summary).
     */
    public function getSummary(string $storeId, array $filters = []): array;

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array;

    public function getKPIByDate(string $storeId, Carbon $date): ?MqKpi;
}
