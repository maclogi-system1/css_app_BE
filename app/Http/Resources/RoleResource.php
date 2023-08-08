<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'role';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'display_name' => $this->resource->display_name,
            'name' => $this->resource->name,
            'guard_name' => $this->resource->guard_name,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'users' => $this->whenLoaded('users', fn () => UserResource::collection($this->resource->users)),
            'permissions' => $this->whenLoaded(
                'permissions',
                fn () => PermissionResource::collection($this->resource->permissions)
            ),
        ];
    }
}
