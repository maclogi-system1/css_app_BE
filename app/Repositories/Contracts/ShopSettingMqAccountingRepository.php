<?php

namespace App\Repositories\Contracts;

use App\Models\ShopSettingMqAccounting;
use Illuminate\Support\Collection;

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

    /**
     * Handle update multiply by storeId and id.
     */
    public function updateMultiple(string $storeId, array $settings): null|bool;

    /**
     * Get a list of the shop setting mq accounting by storeId.
     */
    public function getListByStore(string $storeId, array $filters = []): Collection;
}
