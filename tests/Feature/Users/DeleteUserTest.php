<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_speccify_user(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $userDelete = $this->createUser();

        $this->actingAs($user)
            ->deleteJson(route('api.users.destroy', $userDelete))
            ->assertOk()
            ->assertJson([
                'user' => true,
            ]);
        $this->assertNotEmpty($userDelete->refresh()->deleted_at);
    }

    public function test_can_not_delete_yourself(): void
    {
        $user = $this->createUser(['is_admin' => true]);

        $this->actingAs($user)
            ->deleteJson(route('api.users.destroy', $user))
            ->assertBadRequest()
            ->assertJson([
                'message' => 'You can not delete yourself.',
            ]);
        $this->assertEmpty($user->refresh()->deleted_at);
    }

    public function test_can_not_delete_user_without_permission(): void
    {
        $user = $this->createUser();
        $userDelete = $this->createUser();

        $this->actingAs($user)
            ->deleteJson(route('api.users.destroy', $userDelete))
            ->assertForbidden();
        $this->assertEmpty($user->refresh()->deleted_at);
    }

    public function test_can_delete_multiple_users(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $userDelete = User::factory(2)->create();

        $this->actingAs($user)
            ->deleteJson(route('api.users.delete-multiple', $userDelete->mapWithKeys(function ($item, $key) {
                return ["user_ids[{$key}]" => $item->id];
            })->toArray()))
            ->assertOk();
        $this->assertEquals(2, User::whereIn('id', $userDelete->pluck('id'))->onlyTrashed()->count());
    }

    public function test_can_not_delete_multiple_users_without_permission(): void
    {
        $user = $this->createUser();
        $userDelete = User::factory(2)->create();

        $this->actingAs($user)
            ->deleteJson(route('api.users.delete-multiple', $userDelete->mapWithKeys(function ($item, $key) {
                return ["user_ids[{$key}]" => $item->id];
            })->toArray()))
            ->assertForbidden();
        $this->assertNotEquals(2, User::whereIn('id', $userDelete->pluck('id'))->onlyTrashed()->count());
    }

    public function test_can_not_delete_mutiple_users_when_admin_exists_in_the_list()
    {
        $user = $this->createUser(['is_admin' => true]);
        $userDelete = $this->createUser(count: 2);


        $this->actingAs($user)
            ->deleteJson(route('api.users.delete-multiple', $userDelete->mapWithKeys(function ($item, $key) {
                return ["user_ids[{$key}]" => $item->id];
            })->merge(['user_ids[2]' => $user->id])->toArray()))
            ->assertBadRequest();
        $this->assertTrue(User::whereIn('id', $userDelete->pluck('id'))->onlyTrashed()->get()->isEmpty());
    }
}
