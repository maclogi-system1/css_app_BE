<?php

namespace App\WebServices\OSS;

use App\WebServices\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AlertService extends Service
{
    /**
     * Get a listing of the shop using the OSS api.
     */
    public function getList(array $filters = []): Collection
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('alerts.list'), $filters));
    }

    public function markAsRead(int $alertId)
    {
        return $this->toResponse(Http::oss()->put(OSSService::getApiUri('alerts.mark_as_read', $alertId)));
    }

    public function createAlert(array $params)
    {
        return $this->toResponse(Http::oss()->post(OSSService::getApiUri('alerts.create'), $params));
    }
}
