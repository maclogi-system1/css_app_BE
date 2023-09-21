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
}
