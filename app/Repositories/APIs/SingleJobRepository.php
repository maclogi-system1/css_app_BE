<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\SingleJobRepository as SingleJobRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\OSS\SingleJobService;
use Illuminate\Support\Collection;

class SingleJobRepository extends Repository implements SingleJobRepositoryContract
{
    public function __construct(
        protected SingleJobService $singleJobService
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return '';
    }

    /**
     * Get a list of job_groups by store_id from oss.
     */
    public function getListByStore($storeId, array $filters = []): Collection
    {
        $filters['store_id'] = $storeId;

        return $this->singleJobService->getList($filters);
    }
}
