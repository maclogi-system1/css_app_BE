<?php

namespace App\Repositories\Contracts;

interface TaskRepository extends Repository
{
    /**
     * Get the list of the task from oss api.
     */
    public function getList(array $filters = [], array $columns = ['*']);
}
