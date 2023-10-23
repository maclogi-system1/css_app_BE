<?php

namespace App\Repositories\Contracts;

use App\Models\MqSheet;
use Closure;
use Illuminate\Database\Eloquent\Collection;

interface MqAccountingRepository extends Repository
{
    /**
     * Get a list of items that can be shown.
     */
    public function getShowableRows(): array;

    /**
     * Get mq_accounting details by storeId.
     */
    public function getListByStore(string $storeId, array $filters = []): ?Collection;

    /**
     * Get mq_accounting details from AI by storeId.
     */
    public function getListFromAIByStore(string $storeId, array $filters = []): ?array;

    /**
     * Get a list comparing the actual values with the expected values.
     */
    public function getListCompareActualsWithExpectedValues(string $storeId, array $filters = []): array;

    /**
     * Read and parse csv file contents.
     */
    public function readAndParseCsvFileContents(array $rows);

    /**
     * Return a callback handle stream csv file.
     */
    public function streamCsvFile(array $filters = [], ?string $storeId = ''): Closure;

    /**
     * Update an existing model or create a new model.
     */
    public function updateOrCreate(array $rows, $storeId);

    /**
     * Read and parse data for update.
     */
    public function getDataForUpdate(array $data): array;

    /**
     * Get total sale amount, cost and profit by store id.
     */
    public function getTotalParamByStore(string $storeId, array $filters = []);

    /**
     * Get forecast vs actual.
     */
    public function getForecastVsActual(string $storeId, array $filters = []): array;

    /**
     * Get comparative analysis.
     */
    public function getComparativeAnalysis(string $storeId, array $filters = []);

    /**
     * Get a list of validation rules for validator.
     */
    public function getValidationRules(string $storeId): array;

    /**
     * Handle data validation to update mq_accounting.
     */
    public function handleValidationUpdate($data, $storeId): array;

    /**
     * Handle creating default mq_accounting.
     */
    public function makeDefaultData(string $storeId, MqSheet $mqSheet, array $dataFromSetting = []): void;

    /**
     * Handles the creation of new mq_accounting along with relationships from AI data.
     */
    public function makeDataFromAI(string $storeId, MqSheet $mqSheet): void;
}
