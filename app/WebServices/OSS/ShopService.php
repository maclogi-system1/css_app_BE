<?php

namespace App\WebServices\OSS;

use App\WebServices\Service;
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

    /**
     * Find a specified shop.
     */
    public function find($storeId, array $filters = [])
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('shops.detail', $storeId), $filters));
    }

    /**
     * Get a list of the option for select.
     */
    public function getOptions()
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('shops.options')));
    }

    /**
     * update shop.
     */
    public function update(array $data)
    {
        return $this->toResponse(Http::oss()->put(OSSService::getApiUri('shops.update'), $data));
    }

    /**
     * create shop.
     */
    public function create(array $data)
    {
        return $this->toResponse(Http::oss()->post(OSSService::getApiUri('shops.create'), $data));
    }

    public function getAlertCount(string $storeId)
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('alerts.get_alert_count', $storeId)));
    }

    public function delete(string $storeId)
    {
        return $this->toResponse(Http::oss()->delete(OSSService::getApiUri('shops.delete', $storeId)));
    }
}
