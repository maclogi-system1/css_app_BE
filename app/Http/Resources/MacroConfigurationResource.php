<?php

namespace App\Http\Resources;

use App\Constants\MacroConstant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MacroConfigurationResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'macro_configuration';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $additionalField = [];

        if ($this->resource->macro_type == MacroConstant::MACRO_TYPE_GRAPH_DISPLAY) {
            $additionalField['graph'] = $this->whenLoaded('graph');
        } elseif (in_array($this->resource->macro_type, [
            MacroConstant::MACRO_TYPE_AI_POLICY_RECOMMENDATION,
            MacroConstant::MACRO_TYPE_POLICY_REGISTRATION,
            MacroConstant::MACRO_TYPE_TASK_ISSUE,
        ])) {
            $templateKey = match ($this->resource->macro_type) {
                MacroConstant::MACRO_TYPE_AI_POLICY_RECOMMENDATION => 'simulation',
                MacroConstant::MACRO_TYPE_POLICY_REGISTRATION => 'policies',
                MacroConstant::MACRO_TYPE_TASK_ISSUE => 'tasks',
            };
            $additionalField[$templateKey] = $this->whenLoaded(
                'templates',
                function () {
                    return MacroTemplateResource::collection($this->resource->templates);
                }
            );
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'store_ids' => explode(',', $this->store_ids),
            'stores' => $this?->stores ?? [],
            'conditions' => $this->conditions_decode,
            'time_conditions' => $this->time_conditions_decode,
            'macro_type' => $this->macro_type,
            'macro_type_display' => $this->macro_type_for_human,
            'created_by' => $this->whenLoaded('user'),
        ] + $additionalField;
    }
}
