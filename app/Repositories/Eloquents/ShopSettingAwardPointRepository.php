<?php

namespace App\Repositories\Eloquents;

use App\Models\ShopSettingAwardPoint;
use App\Repositories\Contracts\ShopSettingAwardPointRepository as ShopSettingAwardPointRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ShopSettingAwardPointRepository extends Repository implements ShopSettingAwardPointRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return ShopSettingAwardPoint::class;
    }

    /**
     * Handle create a new ShopSettingRanking.
     */
    public function create(array $data): ?ShopSettingAwardPoint
    {
        return $this->handleSafely(function () use ($data) {
            return $this->model()->create($data);
        }, 'Create Shop Setting Ranking');
    }

    /**
     * Handle delete all award point setting by storeId.
     */
    public function deleteAllByStoreId(string $storeId): mixed
    {
        return $this->model()->newQuery()
            ->where('store_id', $storeId)->delete();
    }

    protected function getWithFilter(Builder $builder, array $filters = []): Builder
    {
        $storeId = Arr::pull($filters, 'store_id');
        if ($storeId) {
            $builder->where('store_id', $storeId);
        }

        return parent::getWithFilter($builder, $filters);
    }
}
