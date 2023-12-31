<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\SingleJobRepository as SingleJobRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\OSS\SingleJobService;
use Illuminate\Support\Arr;
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
    public function getListByStore(string $storeId, array $filters = []): Collection
    {
        $filters['store_id'] = $storeId;

        if ($status = Arr::pull($filters, 'status')) {
            Arr::set($filters, 'filter.status', $status);
        }

        return $this->singleJobService->getList($filters);
    }

    /**
     * Get a specified single job.
     */
    public function find(int $id, array $columns = ['*'], array $filters = [])
    {
        $result = $this->singleJobService->find($id, $filters);

        if ($result->get('success')) {
            return $result->get('data')->get('data');
        }

        return null;
    }

    /**
     * Delete a specified single job.
     */
    public function delete($id)
    {
        return $this->singleJobService->delete($id);
    }

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): ?Collection
    {
        $result = $this->singleJobService->getOptions();

        if ($result->get('success')) {
            return $this->singleJobService->getOptions()->get('data');
        }

        return collect();
    }

    /**
     * Get schedule of single job and task.
     */
    public function getSchedule(array $filters = [])
    {
        return $this->singleJobService->getSchedule($filters);
    }
}
