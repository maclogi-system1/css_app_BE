<?php

namespace App\Repositories\Contracts;

use App\Models\ShopSettingSearchRanking;

interface ShopSettingSearchRankingRepository extends Repository
{
    /**
     * Handle create a new ShopSettingRanking.
     */
    public function create(array $data): ?ShopSettingSearchRanking;

    /**
     * Handle delete all ranking setting by storeId.
     */
    public function deleteAllByStoreId(string $storeId, bool $isCompetitiveRanking): mixed;
}
