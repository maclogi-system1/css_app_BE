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
     * Handles create the task for multiple shops.
     */
    public function createForMultipleShops(array $data, string $storeIds): ?Collection;

    /**
     * Handle update a task.
     */
    public function update(array $data, string $storeId): ?Collection;

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array;

    /**
     * Handle delete a task.
     */
    public function delete(string $storeId, int $taskId): ?Collection;

    /**
     * Handle delete multiple tasks.
     */
    public function deleteMultiple(string $storeId, array $taskIds): array;

    /**
     * Convert user oss to css.
     */
    public function handleTaskAssignees(Collection $data): Collection;

    public function getTask(int $taskId): ?Collection;
}
