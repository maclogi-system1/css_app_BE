<?php

namespace App\Repositories\Eloquents;

use App\Models\MqSheet;
use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\MqSheetRepository as MqSheetRepositoryContract;
use App\Repositories\Contracts\ShopSettingMqAccountingRepository;
use App\Repositories\Repository;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MqSheetRepository extends Repository implements MqSheetRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return MqSheet::class;
    }

    /**
     * Get mq_accounting details by storeId.
     */
    public function getListByStore(string $storeId, array $filters = []): Collection
    {
        if (! $this->model()->where('store_id', $storeId)->exists()) {
            $this->create([
                'name' => MqSheet::DEFAULT_NAME,
                'store_id' => $storeId,
                'is_default' => true,
            ]);
        }

        $query = $this->getWithFilter($this->queryBuilder(), $filters)
            ->where('store_id', $storeId);

        return $query->get();
    }

    /**
     * Find a default mq sheet by storeId.
     */
    public function getDefaultByStore(string $storeId, array $filters = []): ?MqSheet
    {
        $mqSheet = $this->queryBuilder()
            ->where('store_id', $storeId)
            ->where('is_default', true)
            ->first();

        if (is_null($mqSheet)) {
            return null;
        }

        return $mqSheet;
    }

    /**
     * Handle create a new mq sheet.
     */
    public function create(array $data): ?MqSheet
    {
        return $this->handleSafely(function () use ($data) {
            $mqSheet = $this->model($data);
            $mqSheet->save();
            $storeId = Arr::get($data, 'store_id');

            /** @var \App\Repositories\Contracts\ShopSettingMqAccountingRepository */
            $shopSettingMqAccountingRepository = app(ShopSettingMqAccountingRepository::class);
            $shopSettingMqAccounting = $shopSettingMqAccountingRepository->getListByStore($storeId);

            /** @var \App\Repositories\Contracts\MqAccountingRepository */
            $mqAccountingRepository = app(MqAccountingRepository::class);
            $mqAccountingRepository->makeDefaultData($storeId, $mqSheet->refresh(), $shopSettingMqAccounting->toArray());

            return $mqSheet;
        }, 'Create mq_sheets');
    }

    /**
     * Handle update a specified mq sheet.
     */
    public function update(array $data, MqSheet $mqSheet): ?MqSheet
    {
        return $this->handleSafely(function () use ($data, $mqSheet) {
            $mqSheet->fill($data);
            $mqSheet->save();

            return $mqSheet->refresh();
        }, 'Update mq_sheets');
    }

    /**
     * Handle delete a specified mq sheet.
     */
    public function delete(MqSheet $mqSheet): ?MqSheet
    {
        return $this->handleSafely(function () use ($mqSheet) {
            if ($mqSheet->isDefault()) {
                throw new Exception('Cannot delete a default sheet.');
            }

            $mqSheet->mqAccountings()->delete();
            $mqSheet->delete();

            return $mqSheet;
        }, 'Delete mq_sheets');
    }

    /**
     * Get the total of all sheets in the store.
     */
    public function totalMqSheetInStore(string $storeId): int
    {
        return $this->model()->where('store_id', $storeId)->count();
    }

    /**
     * Hanle cloning a new mq_sheet.
     */
    public function cloneSheet(MqSheet $mqSheet): ?MqSheet
    {
        return $this->handleSafely(function () use ($mqSheet) {
            $newMqSheet = $mqSheet->replicate()->fill([
                'name' => MqSheet::PREFIX_NAME.now()->format('Y/m/d H:i:s'),
                'is_default' => false,
            ]);
            $newMqSheet->save();
            $allMqAccountings = $mqSheet->mqAccountings;

            foreach ($allMqAccountings as $mqAccounting) {
                $newMqKpi = $mqAccounting->mqKpi->replicate();
                $newMqKpi->save();
                $newMqAccessNum = $mqAccounting->mqAccessNum->replicate();
                $newMqAccessNum->save();
                $newMqAdSalesAmnt = $mqAccounting->mqAdSalesAmnt->replicate();
                $newMqAdSalesAmnt->save();
                $newMqUserTrend = $mqAccounting->mqUserTrends->replicate();
                $newMqUserTrend->save();
                $newMqCost = $mqAccounting->mqCost->replicate();
                $newMqCost->save();
                $newMqAccounting = $mqAccounting->replicate()->fill([
                    'mq_kpi_id' => $newMqKpi->id,
                    'mq_access_num_id' => $newMqAccessNum->id,
                    'mq_ad_sales_amnt_id' => $newMqAdSalesAmnt->id,
                    'mq_user_trends_id' => $newMqUserTrend->id,
                    'mq_cost_id' => $newMqCost->id,
                    'mq_sheet_id' => $newMqSheet->id,
                ]);

                $newMqAccounting->save();
            }

            return $newMqSheet;
        }, 'Clone mq_sheets');
    }
}
