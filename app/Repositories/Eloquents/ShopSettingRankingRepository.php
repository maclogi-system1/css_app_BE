<?php

namespace App\Repositories\Eloquents;

use App\Models\ShopSettingRanking;
use App\Repositories\Contracts\ShopSettingRankingRepository as ShopSettingRankingRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ShopSettingRankingRepository extends Repository implements ShopSettingRankingRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return ShopSettingRanking::class;
    }

    /**
     * Handle create a new ShopSettingRanking.
     */
    public function create(array $data): ?ShopSettingRanking
    {
        return $this->handleSafely(function () use ($data) {
            return $this->model()->create($data);
        }, 'Create Shop Setting Ranking');
    }

    /**
     * Handle delete all ranking setting by storeId.
     */
    public function deleteAllByStoreId(string $storeId, bool $isCompetitiveRanking): mixed
    {
        $query = $this->model()->newQuery()
            ->where('store_id', $storeId);

        if ($isCompetitiveRanking) {
            $query->whereNotNull('store_competitive_id');
        } else {
            $query->whereNull('store_competitive_id');
        }

        return $query->delete();
    }

    protected function getWithFilter(Builder $builder, array $filters = []): Builder
    {
        $storeId = Arr::pull($filters, 'store_id');
        $isCompetitiveRanking = Arr::pull($filters, 'is_competitive_ranking');
        if ($storeId) {
            $builder->where('store_id', $storeId);
        }

        if ($isCompetitiveRanking !== null) {
            if ($isCompetitiveRanking) {
                $builder->whereNotNull('store_competitive_id');
            } else {
                $builder->whereNull('store_competitive_id');
            }
        }

        return parent::getWithFilter($builder, $filters);
    }
}
