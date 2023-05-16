<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserSettingRepository extends Repository
{
    public function getSettings(User $user);
    public function updateSettings(User $user, array $settings);
}
