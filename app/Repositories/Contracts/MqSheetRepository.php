<?php

namespace App\Repositories\Contracts;

use App\Models\MqSheet;
use Illuminate\Support\Collection;

interface MqSheetRepository extends Repository
{
    /**
     * Get mq_accounting details by storeId.
     */
    public function getListByStore(string $storeId, array $filters = []): Collection;

    /**
     * Handle create a new mq sheet.
     */
    public function create(array $data): ?MqSheet;

    /**
     * Handle update a specified mq sheet.
     */
    public function update(array $data, MqSheet $mqSheet): ?MqSheet;

    /**
     * Handle delete a specified mq sheet.
     */
    public function delete(MqSheet $mqSheet): ?MqSheet;
}
