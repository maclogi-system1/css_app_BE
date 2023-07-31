<?php

namespace App\Http\Resources;

use App\Support\DataAdapter\PolicyAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'policy';

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
                'single_job_id' => $this->single_job_id,
                'name' => $this->name,
                'category' => $this->category_for_human,
                'kpi' => $this->kpi_for_human,
                'description' => $this->description,
                'immediate_reflection' => $this->immediate_reflection,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'attachments' => PolicyAttachmentResource::collection($this->whenLoaded('attachments')),
            ];
    }
}
