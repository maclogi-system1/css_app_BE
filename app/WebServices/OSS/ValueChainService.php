<?php

namespace App\WebServices\OSS;

use App\WebServices\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ValueChainService extends Service
{
    public function monthlyEvaluation(array $filters = []): Collection
    {
        return $this->toResponse(Http::oss()->get(OSSService::getApiUri('value_chain.monthly_evaluation'), $filters));
    }
}
