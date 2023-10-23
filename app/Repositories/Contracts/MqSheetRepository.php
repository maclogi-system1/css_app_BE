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
     * Find a default mq sheet by storeId.
     */
    public function getDefaultByStore(string $storeId, array $filters = []): ?MqSheet;

    /**
     * Handle create a new mq sheet.
     */
    public function create(array $data): ?MqSheet;

    /**
     * Handles the creation of new mq_sheet defaults for the store.
     */
    public function createDefault(string $storeId): ?MqSheet;

    /**
     * Handle update a specified mq sheet.
     */
    public function update(array $data, MqSheet $mqSheet): ?MqSheet;

    /**
     * Handle delete a specified mq sheet.
     */
    public function delete(MqSheet $mqSheet): ?MqSheet;

    /**
     * Get the total of all sheets in the store.
     */
    public function totalMqSheetInStore(string $storeId): int;

    /**
     * Hanle cloning a new mq_sheet.
     */
    public function cloneSheet(MqSheet $mqSheet): ?MqSheet;
}
