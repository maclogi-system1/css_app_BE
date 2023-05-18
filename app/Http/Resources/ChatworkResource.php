<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatworkResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'chatwork';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'account_id' => $this->account_id,
            'role' => $this->role,
            'name' => $this->name,
            'chatwork_id' => $this->chatwork_id,
            'organization_id' => $this->organization_id,
            'organization_name' => $this->organization_name,
            'url' => $this->url,
            'department' => $this->department,
            'avatar_image_url' => $this->avatar_image_url,
        ];
    }
}
