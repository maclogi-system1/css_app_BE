<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\TaskRepository as TaskRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\OSS\TaskService;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TaskRepository extends Repository implements TaskRepositoryContract
{
    public function __construct(
        private TaskService $taskService
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
     * Get the list of the task from oss api.
     */
    public function getList(array $filters = [], array $columns = ['*'])
    {
        return $this->taskService->getList($filters);
    }

    /**
     * Handle data validation to update/create task.
     */
    public function handleValidation(array $data, int $index): array
    {
        $data = $this->handleStartAndEndDateForRequest($data);
        $validator = $this->taskService->create($data + ['is_draft' => 1]);

        if (! $validator->get('success')) {
            return [
                'error' => [
                    'index' => $index,
                    'row' => $index + 1,
                    'messages' => $validator->get('data')->get('message'),
                ],
            ];
        }

        return $data;
    }

    /**
     * Handles start and end dates for the request.
     */
    public function handleStartAndEndDateForRequest(array $data): array
    {
        if ($startDate = Arr::get($data, 'start_date')) {
            $startTime = Arr::pull($data, 'start_time', '00:00');
            $startDateTime = Carbon::create($startDate.' '.$startTime)->format('Y-m-d H:i:s');
            $data['start_date'] = $startDateTime;
        }

        if ($dueDate = Arr::get($data, 'due_date')) {
            $dueTime = Arr::pull($data, 'due_time', '00:00');
            $dueDateTime = Carbon::create($dueDate.' '.$dueTime)->format('Y-m-d H:i:s');
            $data['due_date'] = $dueDateTime;
        }

        return $data;
    }

    /**
     * Handle create a new task.
     */
    public function create(array $data, string $storeId): ?Collection
    {
        $data = $this->handleStartAndEndDateForRequest($data);
        $result = $this->taskService->create($data + ['store_id' => $storeId, 'is_draft' => 0]);

        if ($result->get('status') == Response::HTTP_UNPROCESSABLE_ENTITY) {
            $errors = $result->get('data')->get('message');

            return collect([
                'status' => $result->get('status'),
                'errors' => $errors,
            ]);
        }

        if ($result->get('success')) {
            return $result->get('data');
        }

        return null;
    }

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array
    {
        $result = $this->taskService->getOptions();

        if (! $result->get('success')) {
            return [];
        }

        return $result->get('data')->toArray();
    }
}
