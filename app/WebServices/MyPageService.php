<?php

namespace App\WebServices;

use App\WebServices\OSS\OSSService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class MyPageService extends Service
{
    /**
     * Get total task, alerts from OSS.
     */
    public function getStoreProfitReference(array $params): Collection
    {
        return $this->toResponse(Http::oss()->get(
            OSSService::getApiUri('my_page.get_store_profit_reference'),
            $params
        ));
    }

    /**
     * Get total task, alerts from OSS.
     */
    public function getStoreProfitTable(array $params): Collection
    {
        return $this->toResponse(Http::oss()->get(
            OSSService::getApiUri('my_page.get_store_profit_table'),
            $params
        ));
    }

    /**
     * Get tasks.
     */
    public function getTasks(array $params): Collection
    {
        return $this->toResponse(Http::oss()->get(
            OSSService::getApiUri('my_page.get_tasks'),
            $params
        ));
    }

    /**
     * Get alerts.
     */
    public function getAlerts(array $params)
    {
        return $this->toResponse(Http::oss()->get(
            OSSService::getApiUri('my_page.get_alerts'),
            $params
        ));
    }
}
