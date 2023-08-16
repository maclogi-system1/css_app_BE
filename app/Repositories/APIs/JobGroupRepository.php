<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\JobGroupRepository as JobGroupRepositoryContract;
use App\Repositories\Repository;
use App\Services\OSS\JobGroupService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class JobGroupRepository extends Repository implements JobGroupRepositoryContract
{
    public function __construct(
        protected JobGroupService $jobGroupService,
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
     * Get a list of job_groups by store_id from oss.
     */
    public function getListByStore($storeId, array $filters = []): Collection
    {
        $filters['store_id'] = $storeId;

        return $this->jobGroupService->getList($filters);
    }

    /**
     * Handle getting the start and end timestamps for job_group.
     */
    public function handleStartEndTime($jobGroupId, $data, array &$jobGroups): void
    {
        $dataStartDateTime = new Carbon(
            Arr::get($data, 'execution_date').' '.Arr::get($data, 'execution_time')
        );
        $dataEndDateTime = new Carbon(
            Arr::get($data, 'undo_date').' '.Arr::get($data, 'undo_time')
        );

        if (isset($jobGroups[$jobGroupId])) {
            $jobGroupStartDateTime = new Carbon(Arr::get($jobGroups, "{$jobGroupId}.start_date"));

            if ($jobGroupStartDateTime->gt($dataStartDateTime)) {
                Arr::set($jobGroups, "{$jobGroupId}.start_date", $dataStartDateTime);
            }

            $jobGroupEndDateTime = new Carbon(Arr::get($jobGroups, "{$jobGroupId}.end_date"));

            if ($jobGroupEndDateTime->lt($dataEndDateTime)) {
                Arr::set($jobGroups, "{$jobGroupId}.end_date", $dataEndDateTime);
            }
        } else {
            $jobGroups[$jobGroupId] = [
                'start_date' => $dataStartDateTime,
                'end_date' => $dataEndDateTime,
            ];
        }
    }

    /**
     * Handle update start time and end time for a list of the job group.
     */
    public function updateTime(array $jobGroupData): Collection
    {
        $data = array_map(function ($value, $id) {
            return [
                'id' => $id,
                'start_time' => $value['start_date']->format('Y-m-d H:i:s'),
                'end_time' => $value['end_date']->format('Y-m-d H:i:s'),
            ];
        }, $jobGroupData, array_keys($jobGroupData));

        return $this->jobGroupService->updateTime([
            'job_groups' => $data,
        ]);
    }
}
