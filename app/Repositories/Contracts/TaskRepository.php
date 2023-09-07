<?php

namespace App\Repositories\Contracts;

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
     * Get the task input validation rules.
     */
    public function getValidationRules(array $data): array;
}
