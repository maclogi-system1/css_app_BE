<?php

namespace App\Repositories\Contracts;

use App\Models\MacroConfiguration;

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
    public function getOptions(): array;

    /**
     * Build query from conditions of a specified macro configuration.
     */
    public function getQueryResults(MacroConfiguration $macroConfiguration);
}
