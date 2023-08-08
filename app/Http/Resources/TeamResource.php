<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'team';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'company_id' => $this->resource->company_id,
            'name' => $this->resource->name,
            'created_by' => $this->resource->created_by,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'users' => $this->whenLoaded('users', fn () => UserResource::collection($this->users)),
            'owner' => $this->whenLoaded('owner', fn () => new UserResource($this->owner)),
            'company' => $this->whenLoaded('company', fn () => new CompanyResource($this->company)),
        ];
    }
}
