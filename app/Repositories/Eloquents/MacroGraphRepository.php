<?php

namespace App\Repositories\Eloquents;

use App\Models\MacroGraph;
use App\Repositories\Contracts\MacroGraphRepository as MacroGraphRepositoryContract;
use App\Repositories\Repository;

class MacroGraphRepository extends Repository implements MacroGraphRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return MacroGraph::class;
    }

    /**
     * Handle create a new macro configuration.
     */
    public function create($macroConfigId, array $data): ?MacroGraph
    {
        return $this->handleSafely(function () use ($data, $macroConfigId) {
            $macroGraph = $this->model()->fill($data);
            $macroGraph->macro_configuration_id = $macroConfigId;
            $macroGraph->save();

            return $macroGraph;
        }, 'Create macroGraph');
    }

    /**
     * Handle update the specified team.
     */
    public function update(array $data, MacroGraph $macroGraph): ?MacroGraph
    {
        return $this->handleSafely(function () use ($data, $macroGraph) {
            $macroGraph->fill($data);
            $macroGraph->save();

            return $macroGraph->refresh();
        }, 'Update macroGraph');
    }

    /**
     * Handle delete the specified macroConfiguration.
     */
    public function delete(MacroGraph $macroGraph): ?bool
    {
        return $macroGraph->delete();
    }
}
