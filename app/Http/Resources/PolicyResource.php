<?php

namespace App\Http\Resources;

use App\Support\DataAdapter\PolicyAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->resource instanceof PolicyAdapter
            ? $this->resource->toArray()
            : [
                'id' => $this->id,
                'store_id' => $this->store_id,
                'job_group_id' => $this->job_group_id,
                'name' => $this->name,
                'category' => $this->category_for_human,
                'kpi' => $this->kpi_for_human,
                'template' => $this->template_for_human,
                'status' => $this->status_for_human,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'description' => $this->description,
                'point_rate' => $this->point_rate,
                'point_application_period' => $this->point_application_period,
                'flat_rate_discount' => $this->flat_rate_discount,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
    }
}
