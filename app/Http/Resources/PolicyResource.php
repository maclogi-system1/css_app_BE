<?php

namespace App\Http\Resources;

use App\Models\Policy;
use App\Support\DataAdapter\PolicyAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

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
        if ($this->resource instanceof PolicyAdapter) {
            return $this->resource->toArray();
        }

        $singleJob = $this?->single_job;

        return $this->category == Policy::SIMULATION_CATEGORY
            ? [
                'id' => $this->id,
                'store_id' => $this->store_id,
                'name' => $this->name,
                'job_group_id' => $this->job_group_id,
                'job_group_title' => Arr::get($singleJob ?? [], 'job_group.title'),
                'job_group_code' => Arr::get($singleJob ?? [], 'job_group.code'),
                'single_job_id' => $this->single_job_id,
                'single_job_title' => Arr::get($singleJob ?? [], 'title'),
                'status' => Arr::get($singleJob ?? [], 'status_name'),
                'managers' => Arr::get($singleJob ?? [], 'managers'),
                'start_date' => Arr::get($singleJob ?? [], 'execution_time'),
                'end_date' => Arr::get($singleJob ?? [], 'undo_time'),
                'category' => $this->category_for_human,
                'simulation_start_date' => $this->simulation_start_date,
                'simulation_end_date' => $this->simulation_end_date,
                'simulation_promotional_expenses' => $this->simulation_promotional_expenses,
                'simulation_store_priority' => $this->simulation_store_priority,
                'simulation_product_priority' => $this->simulation_product_priority,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'policy_rules' => PolicyRuleResource::collection($this->whenLoaded('rules')),
            ]
            : [
                'id' => $this->id,
                'store_id' => $this->store_id,
                'job_group_id' => $this->job_group_id,
                'job_group_title' => Arr::get($singleJob ?? [], 'job_group.title'),
                'job_group_code' => Arr::get($singleJob ?? [], 'job_group.code'),
                'single_job_id' => $this->single_job_id,
                'single_job_title' => Arr::get($singleJob ?? [], 'title'),
                'status' => Arr::get($singleJob ?? [], 'status_name'),
                'managers' => Arr::get($singleJob ?? [], 'managers'),
                'start_date' => Arr::get($singleJob ?? [], 'execution_time'),
                'end_date' => Arr::get($singleJob ?? [], 'undo_time'),
                'category' => $this->category_for_human,
                'immediate_reflection' => $this->immediate_reflection,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'attachments' => PolicyAttachmentResource::collection($this->whenLoaded('attachments')),
            ];
    }
}
