<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface JobGroupRepository extends Repository
{
    /**
     * Get a list of job_groups by store_id from oss.
     */
    public function getListByStore($storeId, array $filters = []): Collection;

    /**
     * Handle getting the start and end timestamps for job_group.
     */
    public function handleStartEndTime($jobGroupId, $data, array &$jobGroups): void;

    /**
     * Handle update start time and end time for a list of the job group.
     */
    public function updateTime(array $jobGroupData): Collection;

    /**
     * Handle create a new job group and single job.
     */
    public function create(array $data);

    /**
     * Handle update a specified job group and single job by job group code.
     */
    public function updateByCode(array $data, string $code);

    /**
     * Handle validation form request.
     */
    public function validateCreate(array $data): array;

    /**
     * Handle validation form request.
     */
    public function validateUpdate(array $data): array;
}
