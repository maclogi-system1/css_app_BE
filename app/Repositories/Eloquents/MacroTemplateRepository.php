<?php

namespace App\Repositories\Eloquents;

use App\Models\MacroTemplate;
use App\Repositories\Contracts\MacroTemplateRepository as MacroTemplateRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Support\Arr;

class MacroTemplateRepository extends Repository implements MacroTemplateRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return MacroTemplate::class;
    }

    /**
     * Handle create a new macro template.
     */
    public function create(int $macroConfigId, array $data): ?MacroTemplate
    {
        return $this->handleSafely(function () use ($macroConfigId, $data) {
            $data['payload'] = json_encode(Arr::get($data, 'payload'));
            $data['macro_configuration_id'] = $macroConfigId;

            $macroTemplate = $this->model()->fill($data);
            $macroTemplate->save();

            return $macroTemplate;
        }, 'Create macro template');
    }

    /**
     * Update an existing model or create a new model.
     */
    public function updateOrCreate(array $attributes, array $values = []): ?MacroTemplate
    {
        return $this->handleSafely(function () use ($attributes, $values) {
            $values['payload'] = json_encode(Arr::get($values, 'payload'));
            $macroTemplate = $this->model()->updateOrCreate($attributes, $values);

            return $macroTemplate;
        }, 'Update or create macro template');
    }

    /**
     * Handle delete the specified macro template by macroConfigId.
     */
    public function deleteByMacroConfigId(int $macroConfigId): int
    {
        return $this->model()->where('macro_configuration_id', $macroConfigId)->delete();
    }
}
