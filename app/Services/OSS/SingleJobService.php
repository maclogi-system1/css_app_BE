<?php

namespace App\Services\OSS;

use App\Services\Service;
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
     * Get a list of the option for select.
     */
    public function getOptions(): Collection
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('single_jobs.options')));
    }
}
