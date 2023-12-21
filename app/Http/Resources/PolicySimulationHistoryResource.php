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
            'manager_id' => $this->resource->user_id,
            'manager' => $this->whenLoaded('manager', fn () => $this->resource->manager->name),
            'title' => $this->resource->title,
            'execution_time' => $this->resource->execution_time,
            'undo_time' => $this->resource->undo_time,
            'creation_date' => $this->resource->creation_date,
            'sale_effect' => $this->resource->sale_effect,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'policy' => $this->whenLoaded('policy', fn () => new PolicyResource($this->resource->policy)),
            'store_pred_2m' => $this->resource?->store_pred_2m,
            'items_pred_2m' => $this->resource?->items_pred_2m,
            'mq_sales_amnt' => $this->resource->mq_sales_amnt,
            'pred_sales_amnt' => $this->resource->pred_sales_amnt,
            'growth_rate_prediction' => $this->resource->growth_rate_prediction,
        ];
    }
}
