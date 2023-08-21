<?php

namespace App\WebServices\OSS;

use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SingleJobService extends Service
{
    /**
     * Get a list of the single job.
     */
    public function getList(array $filters = []): Collection
    {
        if (Arr::has($filters, 'to_date')) {
            $filters['end_date'] = Arr::pull($filters, 'to_date');
        }

        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('single_jobs.list'), $filters));
    }

    /**
     * Get a specified single job.
     */
    public function find($id, array $filters = [])
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('single_jobs.detail', $id), $filters));
    }

    /**
     * Delete a specified single job.
     */
    public function delete($id)
    {
        return $this->toResponse(Http::oss()->delete(OSSService::getApiUri('single_jobs.delete', $id)));
    }

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): Collection
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('single_jobs.options')));
    }

    /**
     * Get schedule of single job and task.
     */
    public function getSchedule(array $filters = [])
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('single_jobs.schedule'), $filters));
    }
}
