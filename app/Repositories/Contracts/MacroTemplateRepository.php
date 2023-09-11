<?php

namespace App\Repositories\Contracts;

use App\Models\MacroTemplate;

interface MacroTemplateRepository extends Repository
{
    /**
     * Handle create a new macro template.
     */
    public function create(int $macroConfigId, array $data): ?MacroTemplate;

    /**
     * Update an existing model or create a new model.
     */
    public function updateOrCreate(array $attributes, array $values = []): ?MacroTemplate;

    /**
     * Handle delete the specified macro template by macroConfigId.
     */
    public function deleteByMacroConfigId(int $macroConfigId): int;
}
