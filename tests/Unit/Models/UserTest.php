<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_get_full_name_user(): void
    {
        $user = User::factory()->make([
            'first_name' => 'First',
            'last_name' => 'Last',
        ]);

        $this->assertTrue($user->full_name === 'First Last');
    }

    public function test_get_profile_photo(): void
    {
        $user = User::factory()->make([
            'first_name' => 'First',
            'last_name' => 'Last',
            'profile_photo_path' => 'images/profile_photo/supreme_administrator_1681980964.png',
        ]);

        $this->assertTrue($user->profile_photo === config('app.url').'/storage/images/profile_photo/supreme_administrator_1681980964.png');
    }
}
