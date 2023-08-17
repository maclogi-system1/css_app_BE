<?php

namespace App\Repositories\Eloquents;

use App\Models\MacroConfiguration;
use App\Repositories\Contracts\MacroConfigurationRepository as MacroConfigurationRepositoryContract;
use App\Repositories\Repository;
use Auth;

class MacroConfigurationRepository extends Repository implements MacroConfigurationRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return MacroConfiguration::class;
    }

    /**
     * Find a specified macro configuration.
     */
    public function find($id, array $columns = ['*']): ?MacroConfiguration
    {
        return $this->queryBuilder()->where('id', $id)->first($columns);
    }

    /**
     * Handle create a new macro configuration.
     */
    public function create(array $data): ?MacroConfiguration
    {
        return $this->handleSafely(function () use ($data) {
            $macroConfiguration = $this->model()->fill($data);
            $macroConfiguration->save();
            return $macroConfiguration;
        }, 'Create macroConfiguration');
    }

    /**
     * Handle update the specified team.
     */
    public function update(array $data, MacroConfiguration $macroConfiguration): ?MacroConfiguration
    {
        return $this->handleSafely(function () use ($data, $macroConfiguration) {
            $macroConfiguration->fill($data);
            $macroConfiguration->save();
            return $macroConfiguration->refresh();
        }, 'Update macroConfiguration');
    }

    /**
     * Handle delete the specified macroConfiguration.
     */
    public function delete(MacroConfiguration $macroConfiguration): bool
    {
        $macroConfiguration->deleted_by = Auth::id();
        $macroConfiguration->save();
        return $macroConfiguration->delete();
    }
}
