<?php

namespace App\WebServices\OSS;

use App\WebServices\Service;
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

    /**
     * Handle create a new user.
     */
    public function create(array $data): Collection
    {
        return $this->toResponse(Http::oss()->post(OSSService::getApiUri('users.create'), $data));
    }
}
