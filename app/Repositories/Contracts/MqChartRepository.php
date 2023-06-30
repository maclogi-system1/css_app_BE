<?php

namespace App\Repositories\Contracts;

interface MqChartRepository extends Repository
{
    /**
     * Get monthly changes in financial indicators.
     */
    public function financialIndicatorsMonthly($storeId, array $filter = []);

    /**
     * Get the cumulative change in revenue and profit.
     */
    public function cumulativeChangeInRevenueAndProfit($storeId, array $filter = []);
}
