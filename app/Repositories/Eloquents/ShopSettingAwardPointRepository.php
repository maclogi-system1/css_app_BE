<?php

namespace App\Repositories\Eloquents;

use App\Models\ShopSettingAwardPoint;
use App\Repositories\Contracts\ShopSettingAwardPointRepository as ShopSettingAwardPointRepositoryContract;
use App\Repositories\Repository;
use App\Support\Traits\ShopSettingUpdateRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ShopSettingAwardPointRepository extends Repository implements ShopSettingAwardPointRepositoryContract
{
    use ShopSettingUpdateRepository;

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
        }, 'Create Shop Setting Award Point');
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
        $fromDate = Arr::pull($filters, 'from_date');
        $toDate = Arr::pull($filters, 'to_date');
        if ($storeId) {
            $builder->where('store_id', $storeId);
        }

        if ($fromDate && $toDate) {
            $fromDate = Carbon::createFromFormat('Y-m', $fromDate)->firstOfMonth()->toDateString();
            $toDate = Carbon::createFromFormat('Y-m', $toDate)->endOfMonth()->toDateString();
            $builder->whereBetween('purchase_date', [$fromDate, $toDate]);
        }

        $builder->whereNotNull('purchase_date');

        return parent::getWithFilter($builder, $filters);
    }
}
