<?php

namespace App\Repositories\Eloquents;

use App\Repositories\Contracts\JobGroupRepository as JobGroupRepositoryContract;
use App\Repositories\Repository;
use App\Services\OSS\JobGroupService;
use Illuminate\Support\Collection;

class JobGroupRepository extends Repository implements JobGroupRepositoryContract
{
    public function __construct(
        protected JobGroupService $jobGroupService,
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

        return $this->jobGroupService->getList($filters);
    }
}
