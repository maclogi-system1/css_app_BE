<?php

namespace App\Repositories\Contracts;

interface MqCostRepository extends Repository
{
    /**
     * Get ad cost from mq_cost by store_id.
     */
    public function getAdCostByStore(string $storeId, array $filters = []): int;
}
