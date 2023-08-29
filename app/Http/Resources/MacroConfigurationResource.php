<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'store_ids' => explode(',', $this->store_ids),
            'stores' => $this->stores,
            'conditions' => $this->conditions_decode,
            'time_conditions' => $this->time_conditions_decode,
            'macro_type' => $this->macro_type,
            'macro_type_display' => $this->macro_type_for_human,
            'created_by' => $this->whenLoaded('user'),
        ];
    }
}
