<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface TaskRepository extends Repository
{
    /**
     * Get the list of the task from oss api.
     */
    public function getList(array $filters = [], array $columns = ['*']);

    /**
     * Handle data validation to update/create task.
     */
    public function handleValidation(array $data, int $index): array;

    /**
     * Handle create a new task.
     */
    public function create(array $data, string $storeId): ?Collection;

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array;
}
