<?php

namespace App\Repositories\Contracts;

use App\Models\ShopSettingAwardPoint;

interface ShopSettingAwardPointRepository extends Repository
{
    /**
     * Handle create a new ShopSettingAwardPoint.
     */
    public function create(array $data): ?ShopSettingAwardPoint;

    /**
     * Handle delete all award point setting by storeId.
     */
    public function deleteAllByStoreId(string $storeId): mixed;
}
