<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
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
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'company_id' => $this->resource->company_id,
            'profile_photo_path' => $this->profile_photo,
            'email_verified_at' => null,
            'is_admin' => $this->resource->isAdmin(),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'company' => $this->whenLoaded('company', fn () => new CompanyResource($this->resource->company)),
            'role' => $this->whenLoaded('roles', fn () => $this->resource->roles->first()),
            'permissions' => $this->whenLoaded('roles', fn () => PermissionResource::collection($this->resource->getPermissionsViaRoles())),
            'chatwork' => $this->whenLoaded('chatwork', fn () => new ChatworkResource($this->resource->chatwork)),
            'teams' => $this->whenLoaded('teams', fn () => TeamResource::collection($this->resource->teams)),
        ];
    }
}
