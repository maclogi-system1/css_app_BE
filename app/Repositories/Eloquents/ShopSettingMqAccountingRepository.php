<?php

namespace App\Repositories\Eloquents;

use App\Models\ShopSettingMqAccounting;
use App\Repositories\Contracts\MqSheetRepository;
use App\Repositories\Contracts\ShopSettingMqAccountingRepository as ShopSettingMqAccountingRepositoryContract;
use App\Repositories\Repository;
use App\Support\Traits\HasMqDateTimeHandler;
use App\Support\Traits\ShopSettingUpdateRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ShopSettingMqAccountingRepository extends Repository implements ShopSettingMqAccountingRepositoryContract
{
    use HasMqDateTimeHandler, ShopSettingUpdateRepository;

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
            $shopSetting = $this->model()->create($data);

            /** @var \App\Repositories\Contracts\MqSheetRepository */
            $mqSheetRepository = app(MqSheetRepository::class);
            $mqSheetRepository->createDefault($shopSetting->store_id);

            return $shopSetting;
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
        $fromDate = Arr::pull($filters, 'from_date');
        $toDate = Arr::pull($filters, 'to_date');
        if ($storeId) {
            $builder->where('store_id', $storeId);
        }

        if ($fromDate && $toDate) {
            $fromDate = Carbon::createFromFormat('Y-m', $fromDate)->firstOfMonth()->toDateString();
            $toDate = Carbon::createFromFormat('Y-m', $toDate)->endOfMonth()->toDateString();
            $builder->whereBetween('date', [$fromDate, $toDate]);
        }

        $builder->whereNotNull('date');

        return parent::getWithFilter($builder, $filters);
    }

    /**
     * Get a list of the shop setting mq accounting by storeId.
     */
    public function getListByStore(string $storeId, array $filters = []): Collection
    {
        if (app()->environment('staging')) {
            $storeId = $storeId == 'ariat' ? '_partner_53016' : $storeId;
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date'];
        $toDate = $dateRangeFilter['to_date'];

        return $this->model()
            ->where('store_id', $storeId)
            ->when(! empty($filters), function ($query) use ($fromDate, $toDate) {
                $query->where('date', '>=', $fromDate)
                    ->where('date', '<=', $toDate);
            })
            ->get();
    }
}
