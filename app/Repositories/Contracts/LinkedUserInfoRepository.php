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
    public function getListByUserIds(array $userIds): Collection;
}
