<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'user';

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
                'company' => new CompanyResource($this->whenLoaded('company')),
                'roles' => RoleResource::collection($this->whenLoaded('roles')),
                'chatwork' => new ChatworkResource($this->whenLoaded('chatwork')),
                'teams' => TeamResource::collection($this->whenLoaded('teams')),
                'profile_photo_path' => $this->profile_photo,
            ]
        );
    }
}
