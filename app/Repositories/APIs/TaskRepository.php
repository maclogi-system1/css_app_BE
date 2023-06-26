<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\TaskRepository as TaskRepositoryContract;
use App\Repositories\Repository;
use App\Services\OSS\TaskService;

class TaskRepository extends Repository implements TaskRepositoryContract
{
    public function __construct(
        private TaskService $taskService
    ) {}

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return '';
    }

    /**
     * Get the list of the task from oss api.
     */
    public function getList(array $filters = [], array $columns = ['*'])
    {
        return $this->taskService->getList($filters);
    }
}
