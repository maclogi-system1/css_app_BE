<?php

namespace App\Http\Resources;

use App\Models\PolicyRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyRuleResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'policy_rule';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class' => $this->class,
            'class_name' => PolicyRule::CLASSES[$this->class],
            'service' => $this->service,
            'service_name' => PolicyRule::SERVICES[$this->service],
            'value' => $this->value,
            'condition_1' => $this->condition_1,
            'condition_name_1' => PolicyRule::TEXT_INPUT_CONDITIONS[$this->condition_1],
            'condition_value_1' => $this->condition_value_1,
            'condition_2' => $this->condition_2,
            'condition_name_2' => PolicyRule::UPLOADABLE_CONDITIONS[$this->condition_2],
            'condition_value_2' => $this->condition_value_2,
            'condition_3' => $this->condition_3,
            'condition_name_3' => PolicyRule::TEXT_INPUT_CONDITIONS[$this->condition_3],
            'condition_value_3' => $this->condition_value_3,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
