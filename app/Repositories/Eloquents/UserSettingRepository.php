<?php

namespace App\Repositories\Eloquents;

use App\Models\User;
use App\Models\UserSetting;
use App\Repositories\Contracts\UserSettingRepository as UserSettingRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Support\Facades\DB;

class UserSettingRepository extends Repository implements UserSettingRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return UserSetting::class;
    }

    public function getSettings(User $user)
    {
        return $user->settings;
    }

    public function updateSettings(User $user, array $settings)
    {
        return $this->handleSafely(function () use ($user, $settings) {
            foreach ($settings as $key => $value) {
                DB::table('user_settings')->updateOrInsert([
                    'key' => $key,
                    'user_id' => $user->id,
                ], [
                    'value' => $value,
                ]);
            }

            return true;
        }, 'Update user settings');
    }
}
