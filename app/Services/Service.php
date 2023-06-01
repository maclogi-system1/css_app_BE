<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class Service
{
    protected $baseUrl;

    /**
     * Handle call api as json type.
     */
    public function callApi(?string $url = null): PendingRequest
    {
        return Http::asJson()->acceptJson()->baseUrl($url ?? $this->baseUrl ?? '');
    }
}
