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
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('job_groups.list', $filters)));
    }

    /**
     * Handle create a new job group and single job.
     */
    public function create(array $data): Collection
    {
        // return $this->toResponse(Http::oss()->post(OSSService::getApiUri('job_groups.create'), $data));
        return collect([
            'success' => true,
            'status' => 200,
            'data' => [
                'job_group' => [
                    'id' => rand(1, 100),
                ],
                'single_job' => [
                    'id' => rand(1, 100),
                ],
            ],
        ]);
    }

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): Collection
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('job_groups.options')));
    }

    /**
     * Handle validation form request.
     */
    public function validate(array $data): Collection
    {
        return $this->toResponse(Http::oss()->post(OSSService::getApiUri('job_groups.validate', $data)));
    }
}
