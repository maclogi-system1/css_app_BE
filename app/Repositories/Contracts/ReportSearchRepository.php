<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ReportSearchRepository extends Repository
{
    /**
     * Get trending keywords data report search for chart from AI.
     */
    public function getDataChartReportSearch(string $storeId, array $filters = []): Collection;

    /**
     * Get ranking keywords data report search for chart from AI.
     */
    public function getDataTableReportSearch(string $storeId, array $filters = []): Collection;

    /**
     * Get detail keywords data report search  by product from AI.
     */
    public function getDataReportSearchByProduct(string $storeId, array $filters = []): Collection;
}
