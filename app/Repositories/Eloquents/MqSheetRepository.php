<?php

namespace App\Repositories\Eloquents;

use App\Models\MqSheet;
use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\MqSheetRepository as MqSheetRepositoryContract;
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
     * Handle create a new mq sheet.
     */
    public function create(array $data): ?MqSheet
    {
        return $this->handleSafely(function () use ($data) {
            $mqSheet = $this->model($data);
            $mqSheet->save();
            $storeId = Arr::get($data, 'store_id');

            /** @var \App\Repositories\Contracts\MqAccountingRepository */
            $mqAccountingRepository = app(MqAccountingRepository::class);
            $mqAccountingRepository->makeEmptyData($storeId, $mqSheet->refresh());

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
}
