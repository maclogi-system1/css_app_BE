<?php

namespace App\Services\OSS;

use App\Services\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AlertService extends Service
{
    /**
     * Get a listing of the shop using the OSS api.
     */
    public function getList(array $filter = []): Collection
    {
        return $this->fakeAlerts();
        // return $this->toResponse(Http::oss()->get(OSSService::getApiUri('alerts.list'), $filter));
    }

    /**
     * Create a fake response list of shops.
     *
     * @deprecated Temporary method to get list of shops while waiting for OSS to provide api.
     * This method is for temporary use only during development and will be removed when OSS provides the api.
     */
    private function fakeAlerts(): Collection
    {
        return collect([
            'status' => 200,
            'data' => [
                'alerts' => [
                    [
                        'id' => 40,
                        'title' => '32323',
                        'content' => '3232',
                        'alert_type_id' => 2,
                        'shop' => [
                            'id' => 2,
                            'name' => 'shop for test',
                            'created_at' => '2023-02-21T08:31:46.000000Z',
                            'updated_at' => '2023-04-10T04:49:26.000000Z'
                        ],
                        'created_at' => '2023-06-06T08:04:08.000000Z',
                        'updated_at' => '2023-06-06T08:04:09.000000Z'
                    ]
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'to' => 1,
                    'total' => 1
                ],
            ],
        ]);
    }
}
