<?php

namespace App\Services\OSS;

use App\Services\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class UserService extends Service
{
    /**
     * Get a list of the user in a shop.
     */
    public function getShopUsers(array $filters = []): Collection
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('users.shop_users'), $filters));
    }
}
