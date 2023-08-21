<?php

namespace App\Mixin;

use App\WebServices\OSS\OSSService;
use Closure;
use Illuminate\Support\Facades\Http;

/**
 * @mixin \Illuminate\Database\Query\Builder
 *
 * @method static Closure oss()
 */
class HttpMixin
{
    public function oss(): Closure
    {
        return fn () => Http::asJson()->acceptJson()->withHeaders([
            'Origin' => config('app.url'),
            'X-Api-Key' => OSSService::getApiKey(),
        ])->baseUrl(config('services.maclogi_oss.url'));
    }
}
