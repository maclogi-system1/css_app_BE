<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
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

    public function toResponse(Response $response): Collection
    {
        return collect([
            'status' => $response->status(),
            'data' => $response->collect(),
        ]);
    }
}
