<?php

namespace App\Services\OSS;

class OSSService
{
    /**
     * Get api key.
     */
    public static function getApiKey(): string
    {
        $salt = config('services.maclogi_oss.key');
        $appUrl = config('app.url');

        return sha1($appUrl.$salt);
    }

    /**
     * Get api uri.
     */
    public static function getApiUri($key): string
    {
        return config("services.maclogi_oss.api_uri.{$key}");
    }
}
