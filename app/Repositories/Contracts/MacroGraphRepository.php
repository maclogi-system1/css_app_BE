<?php

namespace App\Repositories\Contracts;

use App\Models\MacroGraph;

interface MacroGraphRepository extends Repository
{
    /**
     * Handle create a new macro graph.
     */
    public function create($macroConfigId, array $data): ?MacroGraph;

    /**
     * Handle update the specified macro graph.
     */
    public function update(array $data, MacroGraph $macroGraph): ?MacroGraph;

    /**
     * Handle delete the specified macro graph.
     */
    public function delete(MacroGraph $macroGraph): ?bool;
}
