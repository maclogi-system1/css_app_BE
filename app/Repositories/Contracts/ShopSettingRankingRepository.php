<?php

namespace App\Repositories\Contracts;

use App\Models\ShopSettingRanking;

interface ShopSettingRankingRepository extends Repository
{
    /**
     * Handle create a new ShopSettingRanking.
     */
    public function create(array $data): ?ShopSettingRanking;

    /**
     * Handle delete all ranking setting by storeId.
     */
    public function deleteAllByStoreId(string $storeId, bool $isCompetitiveRanking): mixed;

    /**
     * Handle update multiply by storeId and id.
     */
    public function updateMultiple(string $storeId, array $settings, bool $isCompetitiveRanking): null|bool;
}
