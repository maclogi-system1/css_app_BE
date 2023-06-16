<?php

namespace App\Services\OSS;

use App\Services\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ShopService extends Service
{
    /**
     * Get a listing of the shop using the OSS api.
     */
    public function getList(array $filter = []): Collection
    {
        return $this->fakeShops();
        // return $this->toResponse(Http::oss()->get(OSSService::getApiUri('shops.list'), $filter));
    }

    /**
     * Create a fake response list of shops.
     *
     * @deprecated Temporary method to get list of shops while waiting for OSS to provide api.
     * This method is for temporary use only during development and will be removed when OSS provides the api.
     */
    private function fakeShops(): Collection
    {
        return collect([
            'status' => 200,
            'data' => [
                'shops' => [
                    [
                        'id' => 2,
                        'name' => 'shop for test',
                        'description' => '',
                        'status_id' => 1,
                        'status_name' => 'Ongoing',
                        'rms_id_common' => 'testpartner53016',
                        'rms_id_private' => 'testuser_4406',
                        'alerts_count' => 1,
                        'directors' => [
                            [
                                'id' => 1,
                                'name' => 'super admin',
                                'email' => 'admin@oss-maclogi.com',
                                'image_path' => 'https://ui-avatars.com/api/?name=super admin&size=64&rounded=true&color=fff&background=fc6369',
                            ],
                        ],
                        'designers' => [
                            [
                                'id' => 1,
                                'name' => 'super admin',
                                'email' => 'admin@oss-maclogi.com',
                                'image_path' => 'https://ui-avatars.com/api/?name=super admin&size=64&rounded=true&color=fff&background=fc6369',
                            ],
                        ],
                        'consultants' => [
                            [
                                'id' => 1,
                                'name' => 'super admin',
                                'email' => 'admin@oss-maclogi.com',
                                'image_path' => 'https://ui-avatars.com/api/?name=super admin&size=64&rounded=true&color=fff&background=fc6369',
                            ],
                        ],
                    ],
                    [
                        'id' => 3,
                        'name' => 'dsdsd',
                        'description' => '',
                        'status_id' => 1,
                        'status_name' => 'Ongoing',
                        'rms_id_common' => 'we',
                        'rms_id_private' => 'eqwew',
                        'alerts_count' => 0,
                        'directors' => [
                            [
                                'id' => 1,
                                'name' => 'super admin',
                                'email' => 'admin@oss-maclogi.com',
                                'image_path' => 'https://ui-avatars.com/api/?name=super admin&size=64&rounded=true&color=fff&background=fc6369',
                            ],
                        ],
                        'designers' => [
                            [
                                'id' => 1,
                                'name' => 'super admin',
                                'email' => 'admin@oss-maclogi.com',
                                'image_path' => 'https://ui-avatars.com/api/?name=super admin&size=64&rounded=true&color=fff&background=fc6369',
                            ],
                        ],
                        'consultants' => [
                            [
                                'id' => 1,
                                'name' => 'super admin',
                                'email' => 'admin@oss-maclogi.com',
                                'image_path' => 'https://ui-avatars.com/api/?name=super admin&size=64&rounded=true&color=fff&background=fc6369',
                            ],
                        ],
                    ],
                    [
                        'id' => 1,
                        'name' => 'マクロジ',
                        'description' => 'the first company',
                        'status_id' => 1,
                        'status_name' => 'Ongoing',
                        'rms_id_common' => null,
                        'rms_id_private' => null,
                        'alerts_count' => 0,
                        'directors' => [],
                        'designers' => [],
                        'consultants' => [],
                    ]
                ],
                'links' => [
                    'first' => 'http://5e0a-14-241-229-85.ngrok-free.app/api/shops?page=1',
                    'last' => 'http://5e0a-14-241-229-85.ngrok-free.app/api/shops?page=1',
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'links' => [
                        [
                            'url' => null,
                            'label' => '&laquo; Previous',
                            'active' => false,
                        ],
                        [
                            'url' => 'http://5e0a-14-241-229-85.ngrok-free.app/api/shops?page=1',
                            'label' => '1',
                            'active' => true,
                        ],
                        [
                            'url' => null,
                            'label' => 'Next &raquo;',
                            'active' => false,
                        ],
                    ],
                    'path' => 'http://5e0a-14-241-229-85.ngrok-free.app/api/shops',
                    'per_page' => 10,
                    'to' => 3,
                    'total' => 3,
                ],
            ],
        ]);
    }
}
