<?php

namespace App\Repositories\Contracts;

interface AlertRepository extends Repository
{
    /**
     * Get the list of the alert from oss api.
     */
    public function getList(array $filters = [], array $columns = ['*']);

    public function markAsRead(int $alertId);

    public function createAlert(array $params);
}
