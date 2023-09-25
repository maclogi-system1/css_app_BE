<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\TaskRepository as TaskRepositoryContract;
use App\Repositories\Repository;
use App\Rules\DateValid;
use App\WebServices\OSS\TaskService;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($data, $this->getValidationRules($data));

        if ($validator->fails()) {
            return [
                'error' => [
                    'index' => $index,
                    'row' => $index + 1,
                    'messages' => $validator->messages(),
                ],
            ];
        }

        return $validator->validated();
    }

    /**
     * Get the task input validation rules.
     */
    public function getValidationRules(array $data): array
    {
        $rules = [
            'title' => ['required'],
            'issue_type' => ['required'],
            'category' => ['nullable'],
            'job_group_code' => ['required', 'regex:/^jg\-[\d]{5}$/'],
            'status' => ['nullable'],
            'assignees' => ['nullable', 'array'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'due_date' => [
                'nullable',
                'date_format:Y-m-d',
                new DateValid(),
            ],
            'due_time' => ['nullable', 'date_format:H:i'],
            'description' => ['nullable'],
        ];

        return $rules;
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

    public function update(array $data, string $storeId): ?Collection
    {
        $data = $this->handleStartAndEndDateForRequest($data);
        if (! ($id = Arr::get($data, 'id'))) {
            return null;
        }

        $result = $this->taskService->update($id, $data + ['store_id' => $storeId]);

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

    public function delete(string $storeId, int $taskId): ?Collection
    {
        $result = $this->taskService->delete($storeId, $taskId);

        if (in_array($result->get('status'), [Response::HTTP_UNPROCESSABLE_ENTITY, Response::HTTP_NOT_FOUND])) {
            $errors = $result->get('data')->get('message');
            if (! is_array($errors)) {
                $errors = ['message' => $errors];
            }

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
}
