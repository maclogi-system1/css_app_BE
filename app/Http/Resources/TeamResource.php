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
        return array_merge(
            parent::toArray($request),
            [
                'users' => UserResource::collection($this->whenLoaded('users')),
                'owner' => new UserResource($this->whenLoaded('owner')),
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]
        );
    }
}
