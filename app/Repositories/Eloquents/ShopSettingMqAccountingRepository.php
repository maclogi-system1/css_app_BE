<?php

namespace App\Repositories\Eloquents;

use App\Models\ShopSettingMqAccounting;
use App\Repositories\Contracts\ShopSettingMqAccountingRepository as ShopSettingMqAccountingRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ShopSettingMqAccountingRepository extends Repository implements ShopSettingMqAccountingRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return ShopSettingMqAccounting::class;
    }

    /**
     * Handle create a new ShopSettingMqAccounting.
     */
    public function create(array $data): ?ShopSettingMqAccounting
    {
        return $this->handleSafely(function () use ($data) {
            return $this->model()->create($data);
        }, 'Create Shop Setting Mq Accounting');
    }

    /**
     * Handle delete all mq setting by storeId.
     */
    public function deleteAllByStoreId(string $storeId): mixed
    {
        return $this->model()->newQuery()->where('store_id', $storeId)->delete();
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
