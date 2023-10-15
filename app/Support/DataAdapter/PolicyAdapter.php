<?php

namespace App\Support\DataAdapter;

use App\Models\Policy;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class PolicyAdapter implements Arrayable
{
    protected $policy;

    public function __construct(array|Arrayable $policy)
    {
        $this->policy = $policy instanceof Arrayable ? $policy->toArray() : $policy;
    }

    public function toArray()
    {
        return [
            'id' => Arr::get($this->policy, 'policy_id'),
            'store_id' => Arr::get($this->policy, 'store_id'),
            'job_group_id' => null,
            'job_group_title' => Arr::get($this->policy, 'policy_class'),
            'single_job_title' => Arr::get($this->policy, 'policy_name'),
            'category' => Policy::AI_RECOMMENDATION_CATEGORY,
            'category_name' => Policy::CATEGORIES[Policy::AI_RECOMMENDATION_CATEGORY],
            'status' => -10,
            'execution_time' => Arr::get($this->policy, 'start_date'),
            'undo_time' => Arr::get($this->policy, 'end_date'),
            'point_rate' => Arr::get($this->policy, 'point_rate'),
            'point_application_period' => Arr::get($this->policy, 'point_application_period'),
            'flat_rate_discount' => Arr::get($this->policy, 'end_date'),
            'created_at' => Arr::get($this->policy, 'created_at'),
            'updated_at' => Arr::get($this->policy, 'updated_at'),
        ];
    }
}
