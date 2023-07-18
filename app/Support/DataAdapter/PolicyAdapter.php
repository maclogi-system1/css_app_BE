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
            'id' => Arr::get($this->policy, 'policy_r2_id'),
            'store_id' => Arr::get($this->policy, 'store_id'),
            'job_group_id' => null,
            'name' => Arr::get($this->policy, 'policy_name'),
            'category' => Policy::CATEGORIES[Policy::AI_RECOMMENDATION_CATEGORY],
            'kpi' => Arr::get($this->policy, 'kpi_index'),
            'template' => Arr::get($this->policy, 'policy_class'),
            'status' => Policy::CONFIRMED_STATUS,
            'start_date' => Arr::get($this->policy, 'start_date'),
            'end_date' => Arr::get($this->policy, 'end_date'),
            'description' => '',
            'point_rate' => Arr::get($this->policy, 'point_rate'),
            'point_application_period' => Arr::get($this->policy, 'point_application_period'),
            'flat_rate_discount' => Arr::get($this->policy, 'end_date'),
            'created_at' => Arr::get($this->policy, 'created_at'),
            'updated_at' => Arr::get($this->policy, 'updated_at'),
        ];
    }
}
