<?php

namespace App\Http\Resources;

use App\Constants\MacroConstant;
use App\Models\MacroTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MacroTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'payload' => $this->resource->payload_decode,
            'type' => $this->resource->type,
            'type_display' => MacroConstant::MACRO_TYPES[$this->resource->type],
            'status' => $this->resource->status,
            'status_display' => MacroTemplate::STATES[$this->resource->status],
        ];
    }
}
