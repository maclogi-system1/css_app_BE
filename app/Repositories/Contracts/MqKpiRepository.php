<?php

namespace App\Repositories\Contracts;

use App\Models\MqKpi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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

    /**
     * Get data for KPI trends (monthly).
     */
    public function getChartKpiTrends(string $storeId, array $filters = []): Collection;
}
