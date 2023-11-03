<?php

namespace App\Repositories\Contracts;

use App\Models\MacroConfiguration;
use Illuminate\Support\Collection;

interface MacroConfigurationRepository extends Repository
{
    /**
     * Get list table.
     */
    public function getListTable(): array;

    /**
     * Handle create a new macro configuration.
     */
    public function create(array $data): ?MacroConfiguration;

    /**
     * Find a specified macroConfiguration with user.
     */
    public function find($id, array $columns = ['*']): ?MacroConfiguration;

    /**
     * Handle update the specified macroConfiguration.
     */
    public function update(array $data, MacroConfiguration $macroConfiguration): ?MacroConfiguration;

    /**
     * Handle delete the specified macroConfiguration.
     */
    public function delete(MacroConfiguration $macroConfiguration): ?MacroConfiguration;

    /**
     * Get a list of the option for select.
     */
    public function getOptions(?string $storeId = null): array;

    /**
     * Build query from conditions of a specified macro configuration.
     */
    public function getQueryResults(MacroConfiguration $macroConfiguration);

    /**
     * Updates the ready state for the specified macro to execute it on schedule.
     */
    public function executeMacro(MacroConfiguration $macroConfiguration): bool;

    /**
     * Get a list of the keywords for select.
     */
    public function getKeywords(string $keyword): array;

    /**
     * Get chart data to display macro graph on kpi screen.
     */
    public function getDataChartMacroGraph(string $storeId): Collection;

    /**
     * Build query from conditions of a specified json conditions.
     */
    public function getQueryConditionsResults(array $conditions);
}
