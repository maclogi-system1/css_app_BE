<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicySimulationHistoryResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'policy_simulation_history';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'policy_id' => $this->resource->policy_id,
            'manager' => $this->resource->manager,
            'title' => $this->resource->title,
            'job_title' => $this->resource->job_title,
            'execution_time' => $this->resource->execution_time,
            'undo_time' => $this->resource->undo_time,
            'creation_date' => $this->resource->creation_date,
            'sale_effect' => $this->resource->sale_effect,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'name' => $this->resource->name,
            'simulation' => $this->whenLoaded('policy', fn () => new PolicyResource($this->resource->policy)),
            'store_pred_2m' => $this->resource?->store_pred_2m_data,
            'items_pred_2m' => $this->resource?->items_pred_2m_data,
        ];
    }
}
