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
                $this->model()->newQuery()
                    ->where('id', '=', $setting['id'])
                    ->where('store_id', $storeId)
                    ->update($setting);
            }

            return true;
        }, 'Update'.str_replace('App\Models\\', '', get_class($this->model())));
    }
}
