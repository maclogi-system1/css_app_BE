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

        return $this->category == Policy::SIMULATION_CATEGORY
            ? $this->simulationAttributes()
            : $this->policyAttributes();
    }

    /**
     * Get common attributes.
     */
    private function commonAttributes(): array
    {
        $singleJob = $this?->single_job;

        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'name' => $this->name,
            'job_group_id' => $this->job_group_id,
            'job_group_title' => Arr::get($singleJob ?? [], 'job_group.title'),
            'job_group_code' => Arr::get($singleJob ?? [], 'job_group.code'),
            'job_group_explanation' => Arr::get($singleJob ?? [], 'job_group.explanation'),
            'job_group_start_time' => Arr::get($singleJob ?? [], 'job_group.start_time'),
            'job_group_end_time' => Arr::get($singleJob ?? [], 'job_group.end_time'),
            'managers' => Arr::get($singleJob ?? [], 'job_group.managers', []),
            'single_job_id' => $this->single_job_id,
            'single_job_title' => Arr::get($singleJob ?? [], 'title'),
            'template' => Arr::get($singleJob ?? [], 'template_id'),
            'execution_time' => Arr::get($singleJob ?? [], 'execution_time'),
            'undo_time' => Arr::get($singleJob ?? [], 'undo_time'),
            'type_item_url' => Arr::get($singleJob ?? [], 'type_item_url'),
            'item_urls' => Arr::get($singleJob ?? [], 'item_urls'),
            'has_banner' => Arr::get($singleJob ?? [], 'has_banner'),
            'remark' => Arr::get($singleJob ?? [], 'remark'),
            'catch_copy_pc_text' => Arr::get($singleJob ?? [], 'catch_copy_pc_text'),
            'catch_copy_pc_error' => Arr::get($singleJob ?? [], 'catch_copy_pc_error'),
            'catch_copy_sp_text' => Arr::get($singleJob ?? [], 'catch_copy_sp_text'),
            'catch_copy_sp_error' => Arr::get($singleJob ?? [], 'catch_copy_sp_error'),
            'item_name_text' => Arr::get($singleJob ?? [], 'item_name_text'),
            'item_name_text_error' => Arr::get($singleJob ?? [], 'item_name_text_error'),
            'point_magnification' => Arr::get($singleJob ?? [], 'point_magnification'),
            'point_start_date' => Arr::get($singleJob ?? [], 'point_start_date'),
            'point_start_time' => Arr::get($singleJob ?? [], 'point_start_time'),
            'point_end_date' => Arr::get($singleJob ?? [], 'point_end_date'),
            'point_end_time' => Arr::get($singleJob ?? [], 'point_end_time'),
            'point_error' => Arr::get($singleJob ?? [], 'point_error'),
            'point_operational' => Arr::get($singleJob ?? [], 'point_operational'),
            'discount_type' => Arr::get($singleJob ?? [], 'discount_type'),
            'discount_rate' => Arr::get($singleJob ?? [], 'discount_rate'),
            'discount_price' => Arr::get($singleJob ?? [], 'discount_price'),
            'discount_undo_type' => Arr::get($singleJob ?? [], 'discount_undo_type'),
            'discount_error' => Arr::get($singleJob ?? [], 'discount_error'),
            'discount_display_price' => Arr::get($singleJob ?? [], 'discount_display_price'),
            'double_price_text' => Arr::get($singleJob ?? [], 'double_price_text'),
            'shipping_fee' => Arr::get($singleJob ?? [], 'shipping_fee'),
            'stock_specify' => Arr::get($singleJob ?? [], 'stock_specify'),
            'time_sale_start_date' => Arr::get($singleJob ?? [], 'time_sale_start_date'),
            'time_sale_start_time' => Arr::get($singleJob ?? [], 'time_sale_start_time'),
            'time_sale_end_date' => Arr::get($singleJob ?? [], 'time_sale_end_date'),
            'time_sale_end_time' => Arr::get($singleJob ?? [], 'time_sale_end_time'),
            'is_unavailable_for_search' => Arr::get($singleJob ?? [], 'is_unavailable_for_search'),
            'description_for_pc' => Arr::get($singleJob ?? [], 'description_for_pc'),
            'description_for_sp' => Arr::get($singleJob ?? [], 'description_for_sp'),
            'description_by_sales_method' => Arr::get($singleJob ?? [], 'description_by_sales_method'),
            'status' => Arr::get($singleJob ?? [], 'job_group.status_id'),
            'status_name' => Arr::get($singleJob ?? [], 'job_group.status_name'),
            'category' => $this->category,
            'category_name' => $this->category_for_human,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get attributes of measures policy and project policy.
     */
    private function policyAttributes(): array
    {
        return $this->commonAttributes() + [
            'immediate_reflection' => $this->immediate_reflection,
            'attachments' => PolicyAttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }

    /**
     * Get simulation attributes.
     */
    private function simulationAttributes(): array
    {
        return $this->commonAttributes() + [
            'simulation_start_date' => $this->simulation_start_date,
            'simulation_end_date' => $this->simulation_end_date,
            'simulation_promotional_expenses' => $this->simulation_promotional_expenses,
            'simulation_store_priority' => $this->simulation_store_priority,
            'simulation_product_priority' => $this->simulation_product_priority,
            'processing_status' => $this->processing_status_for_human,
            'policy_rules' => PolicyRuleResource::collection($this->whenLoaded('rules')),
        ];
    }
}
