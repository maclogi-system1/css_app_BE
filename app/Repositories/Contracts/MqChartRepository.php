<?php

namespace App\Repositories\Contracts;

interface MqChartRepository extends Repository
{
    /**
     * Get monthly changes in financial indicators.
     */
    public function financialIndicatorsMonthly($storeId, array $filters = []);

    /**
     * Get the cumulative change in revenue and profit.
     */
    public function cumulativeChangeInRevenueAndProfit($storeId, array $filters = []);

    /**
     * Calculate and get the break-even point.
     */
    public function getBreakEvenPoint(string $storeId, array $filters = []);

    /**
     * Get inferred and expected sales.
     */
    public function getInferredAndExpectedMqSales(string $storeId, array $filters = []);
}
