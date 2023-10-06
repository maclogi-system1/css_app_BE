<?php

namespace App\Repositories\Contracts;

interface LinkedUserInfoRepository extends Repository
{
    /**
     * Create LinkedUserInfo.
     */
    public function create(array $params): mixed;
}
