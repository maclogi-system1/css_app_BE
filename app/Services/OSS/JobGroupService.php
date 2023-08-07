<?php

namespace App\Services\OSS;

use App\Services\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class JobGroupService extends Service
{
    /**
     * Get a listing of the job group.
     */
    public function getList(array $filters = []): Collection
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('job_groups.list'), $filters));
    }

    /**
     * Handle create a new job group and single job.
     */
    public function create(array $data): Collection
    {
        return $this->toResponse(Http::oss()->post(OSSService::getApiUri('job_groups.create'), $data));
    }

    /**
     * Handle validation form request.
     */
    public function validate(array $data): Collection
    {
        return $this->toResponse(Http::oss()->post(OSSService::getApiUri('job_groups.validate'), $data));
    }

    /**
     * Handle update start time and end time for a list of the job group.
     */
    public function updateTime(array $data): Collection
    {
        return $this->toResponse(Http::oss()->patch(OSSService::getApiUri('job_groups.update_time'), $data));
    }
}
