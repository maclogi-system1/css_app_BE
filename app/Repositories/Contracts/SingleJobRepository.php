<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface SingleJobRepository extends Repository
{
    /**
     * Get a list of job_groups by store_id from oss.
     */
    public function getListByStore($storeId, array $filters = []): Collection;
}
