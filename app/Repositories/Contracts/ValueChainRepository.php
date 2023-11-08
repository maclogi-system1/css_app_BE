<?php

namespace App\Repositories\Contracts;

use App\Models\ValueChain;

interface ValueChainRepository extends Repository
{
    /**
     * Get value chain detail by store.
     */
    public function getDetailByStore(string $storeId, array $filters = []): ?ValueChain;

    /**
     * Get data for monthly evaluation chart.
     */
    public function chartMonthlyEvaluation(string $storeId, array $filters = []);
}
