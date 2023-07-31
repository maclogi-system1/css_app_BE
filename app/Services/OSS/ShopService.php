<?php

namespace App\Services\OSS;

use App\Services\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ShopService extends Service
{
    /**
     * Get a listing of the shop using the OSS api.
     */
    public function getList(array $filters = []): Collection
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('shops.list'), $filters));
    }
}
