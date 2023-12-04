<?php

namespace App\Repositories\Eloquents;

use App\Models\LinkedUserInfo;
use App\Repositories\Contracts\LinkedUserInfoRepository as LinkedUserInfoRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Support\Collection;

class LinkedUserInfoRepository extends Repository implements LinkedUserInfoRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return LinkedUserInfo::class;
    }

    /**
     * Create LinkedUserInfo.
     */
    public function create(array $params): mixed
    {
        return $this->handleSafely(function () use ($params) {
            return LinkedUserInfo::query()->firstOrCreate($params, $params);
        }, 'Create Linked');
    }

    /**
     * Get a list of the linked service user by userIds.
     */
    public function getListByUserIds(array|Collection $userIds): Collection
    {
        return $this->model()->whereIn('user_id', $userIds)->get();
    }

    /**
     * Get a list of oss_user_ids by css_user_ids.
     */
    public function getOssUserIdsByCssUserIds(array|Collection $userIds): array
    {
        return $this->getListByUserIds($userIds)
            ->pluck('linked_service_user_id')
            ->toArray();
    }

    /**
     * Get OSS user id by CSS user id.
     */
    public function getOssUserIdByCssUserId(int $userId): ?int
    {
        return $this->model()->where('user_id', $userId)->first()?->linked_service_user_id;
    }
}
