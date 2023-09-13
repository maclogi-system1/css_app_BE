<?php

namespace App\WebServices\OSS;

use App\WebServices\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class TaskService extends Service
{
    /**
     * Get a listing of the task using the OSS api.
     */
    public function getList(array $filters = []): Collection
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('tasks.list'), $filters));
    }

    /**
     * Handle create a new task.
     */
    public function create(array $data): Collection
    {
        return $this->toResponse(Http::oss()->post(OSSService::getApiUri('tasks.create'), $data));
    }

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): Collection
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('tasks.options')));
    }
}
