<?php

namespace App\Services\OSS;

use App\Services\Service;
use Illuminate\Support\Arr;

class ShopService extends Service
{
    use OSSService;

    public function getList(array $filter = [])
    {
        /* $response = $this->callApi()->get('/api/shops');

        if ($response->successful()) {
            return $response->json();
        }

        return [
            'status' => $response->status(),
        ]; */

        $profilePhotoDefault = config('filesystems.profile_photo_default');

        return [
            'shops' => [
                [
                    'id' => 2,
                    'name' => '開発用テスト店舗',
                    'description' => '',
                    'alerts_count' => 3,
                    'status_id' => 2,
                    'status_name' => '処理済み',
                    'rms_id_common' => 'rakuten-rms',
                    'rms_id_private' => 'rakuten-rms',
                    'consultants' => [
                        [
                            'id' => 2,
                            'name' => '小林 楓太',
                            'email' => 'fuuta_kobayashi@example.com',
                            'image_path' => $profilePhotoDefault.'小林 楓太',
                        ],
                    ],
                    'designers' => [
                        [
                            'id' => 2,
                            'name' => '小林 楓太',
                            'email' => 'fuuta_kobayashi@example.com',
                            'image_path' => $profilePhotoDefault.'小林 楓太',
                        ],
                    ],
                    'directors' => [
                        [
                            'id' => 1,
                            'name' => 'Super Admin',
                            'email' => 'admin@example.com',
                            'image_path' => $profilePhotoDefault.'Super Admin',
                        ],
                    ],
                ],
            ],
        ];
    }
}
