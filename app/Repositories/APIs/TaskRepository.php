<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\TaskRepository as TaskRepositoryContract;
use App\Repositories\Repository;
use App\Rules\CompareDateValid;
use App\Rules\DateValid;
use App\WebServices\OSS\TaskService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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
                    'messages' => $validator->getMessageBag()->toArray(),
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
        $startDate = Arr::get($data, 'start_date');
        $startTime = Arr::get($data, 'start_time');
        $startDateTime = Carbon::create($startDate.' '.$startTime);

        $dueDate = Arr::get($data, 'due_date');
        $dueTime = Arr::get($data, 'due_time');
        $dueDateTime = Carbon::create($dueDate.' '.$dueTime);

        $rules = [
            'title' => ['required'],
            'issue_type' => ['required'],
            'category' => ['nullable'],
            'job_group_code' => ['required', 'regex:/^jg\-[\d]{5}$/'],
            'status' => ['nullable'],
            'assignees' => ['nullable', 'array'],
            'start_date' => ['nullable', 'date_format:Y/m/d'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'due_date' => [
                'nullable',
                'date_format:Y/m/d',
                new DateValid(),
                new CompareDateValid($dueDateTime, 'gt', $startDateTime),
            ],
            'due_time' => ['nullable', 'date_format:H:i'],
            'description' => ['nullable'],
        ];

        return $rules;
    }

    public function create(array $data)
    {
        # code...
    }
}
