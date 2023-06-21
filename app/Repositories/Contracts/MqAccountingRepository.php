<?php

namespace App\Repositories\Contracts;

use App\Models\MqAccounting;
use Closure;
use Illuminate\Database\Eloquent\Collection;

interface MqAccountingRepository extends Repository
{
    /**
     * Get mq_accounting details by storeId.
     */
    public function getListByStore(string $storeId, array $filter = []): ?Collection;

    /**
     * Get mq_accounting details from AI by storeId.
     */
    public function getListFromAIByStore(string $storeId, array $filter = []): ?array;

    /**
     * Read and parse csv file contents.
     */
    public function readAndParseCsvFileContents(array $rows);

    /**
     * Return a callback handle stream csv file.
     */
    public function streamCsvFile(array $filter = [], ?string $storeId = ''): Closure;

    /**
     * Update an existing model or create a new model.
     */
    public function updateOrCreate(array $rows, $storeId);
}
