<?php

namespace App\Services\OSS;

trait OSSService
{
    /**
     * Get base url api of maclogi oss.
     */
    public function getBaseUrl()
    {
        if (empty($this->baseUrl)) {
            $this->baseUrl = config('services.maclogi_oss.url');
        }

        return $this->baseUrl;
    }

    /**
     * Get api key.
     */
    public function getApiKey()
    {
        $salt = config('services.maclogi_oss.key');
        $appUrl = config('app.url');

        return sha1($salt.$appUrl);
    }

    /**
     * Handle call oss api.
     */
    public function ossClient()
    {
        return $this->callApi()->withHeaders(['X-Api-Key' => $this->getApiKey()]);
    }
}
