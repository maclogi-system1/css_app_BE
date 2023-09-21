<?php

namespace App\Repositories\Contracts;

use App\Models\ShopSettingMqAccounting;

interface ShopSettingMqAccountingRepository extends Repository
{
    /**
     * Handle create a new ShopSettingMqAccounting.
     */
    public function create(array $data): ?ShopSettingMqAccounting;

    /**
     * Handle delete all mq setting by storeId.
     */
    public function deleteAllByStoreId(string $storeId): mixed;
}
