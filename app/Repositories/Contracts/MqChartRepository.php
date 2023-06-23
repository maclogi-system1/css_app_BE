<?php

namespace App\Repositories\Contracts;

interface MqChartRepository extends Repository
{
    /**
     * Get monthly changes in financial indicators.
     */
    public function financialIndicatorsMonthly($storeId, array $filter = []);
}
