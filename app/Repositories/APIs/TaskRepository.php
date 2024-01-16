<?php

namespace App\Repositories\APIs;

use App\Models\User;
use App\Repositories\Contracts\LinkedUserInfoRepository;
use App\Repositories\Contracts\TaskRepository as TaskRepositoryContract;
use App\Repositories\Contracts\UserRepository;
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
        $result = $this->taskService->getList($filters);
        if ($result->get('success')) {
            $tasks = $this->handleTaskAssignees(collect($result->get('data')->get('tasks')));
            $result->get('data')->put('tasks', $tasks);
        }

        return $result;
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

        if ($assignees = Arr::get($data, 'assignees')) {
            $data['assignees'] = $this->getLinkedUserInfoRepository()
                ->getOssUserIdsByCssUserIds($assignees);
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
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => $errors,
            ]);
        }

        if ($result->get('success')) {
            return $this->handleTaskAssignees(collect($result->get('data')));
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
            return $this->handleTaskAssignees(collect($result->get('data')));
        }

        return null;
    }

    public function handleTaskAssignees(Collection $data): Collection
    {
        return $data->map(function ($task) {
            if (! empty($task['assignees'])) {
                $assigneeIds = collect($task['assignees'])->pluck('id')->toArray();
                $task['assignees'] = $this->getUserRepository()->getListByLinkedUserIds($assigneeIds)->map(function (User $user) {
                    return $user->getFieldForOSS();
                });
            }

            if (! empty($task['created_user'])) {
                $createdUserId = Arr::get($task['created_user'], 'id');
                $task['created_user'] = $this->getUserRepository()->getListByLinkedUserIds([$createdUserId])->map(function (User $user) {
                    return $user->getFieldForOSS();
                })?->first();
            }

            return $task;
        });
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

        if (in_array($result->get('status'), [Response::HTTP_UNPROCESSABLE_ENTITY, Response::HTTP_NOT_FOUND, Response::HTTP_BAD_REQUEST])) {
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

    /**
     * Handle delete multiple tasks.
     */
    public function deleteMultiple(string $storeId, array $taskIds): array
    {
        $failedTasks = [];

        foreach ($taskIds as $taskId) {
            $result = $this->delete($storeId, $taskId);

            if (is_null($result) || $result->get('errors')) {
                $failedTasks[] = $taskId;
            }
        }

        return $failedTasks;
    }

    public function getLinkedUserInfoRepository(): LinkedUserInfoRepository
    {
        return app(LinkedUserInfoRepository::class);
    }

    public function getUserRepository(): UserRepository
    {
        return app(UserRepository::class);
    }

    public function getTask(int $taskId): ?Collection
    {
        $result = $this->taskService->getTask($taskId);
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
            $task = $result->get('data');

            return $this->handleTaskAssignees(collect($task));
        }

        return null;
    }
}
