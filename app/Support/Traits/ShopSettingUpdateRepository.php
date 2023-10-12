<?php

namespace App\Support\Traits;

use App\Repositories\Repository;

/**
 * @mixin Repository
 */
trait ShopSettingUpdateRepository
{
    /**
     * Handle update multiply by storeId and id.
     */
    public function updateMultiple(string $storeId, array $settings): null|bool
    {
        return $this->handleSafely(function () use ($storeId, $settings) {
            foreach ($settings as $setting) {
                $id = $setting['id'] ?? null;
                if (is_null($id)) {
                    $setting = array_merge($setting, ['store_id' => $storeId]);
                }

                $this->model()->newQuery()
                    ->where('store_id', $storeId)
                    ->updateOrCreate(['id' => $id], $setting);
            }

            return true;
        }, 'Update'.str_replace('App\Models\\', '', get_class($this->model())));
    }
}
