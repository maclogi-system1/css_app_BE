<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_get_profile_photo(): void
    {
        $user = User::factory()->make([
            'name' => 'User Name',
            'profile_photo_path' => 'images/profile_photo/supreme_administrator_1681980964.png',
        ]);

        $this->assertTrue($user->profile_photo === config('app.url').'/storage/images/profile_photo/supreme_administrator_1681980964.png');
    }
}
