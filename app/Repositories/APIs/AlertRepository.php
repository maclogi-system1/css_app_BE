<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\AlertRepository as AlertRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\OSS\AlertService;

class AlertRepository extends Repository implements AlertRepositoryContract
{
    public function __construct(
        private AlertService $alertService
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
     * Get the list of the alert from oss api.
     */
    public function getList(array $filters = [], array $columns = ['*'])
    {
        return $this->alertService->getList($filters);
    }
}
