<?php

namespace App\Repositories\Contracts;

use App\Models\PolicySimulationHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PolicySimulationHistoryRepository extends Repository
{
    /**
     * Get a list of the policy by store_id.
     */
    public function getListByStore($storeId, array $filters = []): Collection|LengthAwarePaginator;

    /**
     * Handle create a new policy simulation history.
     */
    public function create(array $data): ?PolicySimulationHistory;
}