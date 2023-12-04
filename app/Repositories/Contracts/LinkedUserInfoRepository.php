<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface LinkedUserInfoRepository extends Repository
{
    /**
     * Create LinkedUserInfo.
     */
    public function create(array $params): mixed;

    /**
     * Get a list of the linked service user by userIds.
     */
    public function getListByUserIds(array|Collection $userIds): Collection;

    /**
     * Get a list of oss_user_ids by css_user_ids.
     */
    public function getOssUserIdsByCssUserIds(array|Collection $userIds): array;

    /**
     * Get OSS user id by CSS user id.
     */
    public function getOssUserIdByCssUserId(int $userId): ?int;
}
