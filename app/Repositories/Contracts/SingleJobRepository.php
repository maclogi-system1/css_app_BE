<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface SingleJobRepository extends Repository
{
    /**
     * Get a list of job_groups by store_id from oss.
     */
    public function getListByStore(string $storeId, array $filters = []): Collection;

    /**
     * Get a specified single job.
     */
    public function find(int $id, array $columns = ['*'], array $filters = []);

    /**
     * Delete a specified single job.
     */
    public function delete($id);

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): ?Collection;

    /**
     * Get schedule of single job and task.
     */
    public function getSchedule(array $filters = []);
}
