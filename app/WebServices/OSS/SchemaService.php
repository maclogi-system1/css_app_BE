<?php

namespace App\WebServices\OSS;

use App\WebServices\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SchemaService extends Service
{
    /**
     * Get macro query condition result from OSS api.
     */
    public function getQueryConditionsResult(array $filters = []): Collection
    {
        return $this->toResponse(Http::oss()->post(OSSService::getApiUri('schema.query_condition_result'), $filters));
    }
}
