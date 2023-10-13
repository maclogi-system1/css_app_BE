<?php

namespace App\Repositories\Eloquents;

use App\Models\ShopSettingSearchRanking;
use App\Repositories\Contracts\ShopSettingSearchRankingRepository as ShopSettingSearchRankingRepositoryContract;
use App\Repositories\Repository;
use App\Support\Traits\ShopSettingUpdateRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ShopSettingSearchRankingRepository extends Repository implements ShopSettingSearchRankingRepositoryContract
{
    use ShopSettingUpdateRepository {
        updateMultiple as updateMultipleTrait;
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return ShopSettingSearchRanking::class;
    }

    /**
     * Handle create a new ShopSettingRanking.
     */
    public function create(array $data): ?ShopSettingSearchRanking
    {
        return $this->handleSafely(function () use ($data) {
            return $this->model()->create($data);
        }, 'Create Shop Setting Search Ranking');
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
            $builder->where('is_competitive', (int) $isCompetitiveRanking);
        }

        return parent::getWithFilter($builder, $filters);
    }

    public function updateMultiple(string $storeId, array $settings, bool $isCompetitiveRanking): null|bool
    {
        foreach ($settings as $key => $setting) {
            if (! $isCompetitiveRanking) {
                unset($setting[$key]['store_competitive_id']);
            }

            $settings[$key]['is_competitive'] = (int) $isCompetitiveRanking;
        }

        return $this->updateMultipleTrait($storeId, $settings);
    }
}
