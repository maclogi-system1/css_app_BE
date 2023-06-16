<?php

namespace Tests;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, WithFaker;

    protected function createUser(array $attributes = [], int $count = 1): User|Collection
    {
        $users = User::factory($count)->for(Company::factory())->create($attributes);

        return $count > 1 ? $users : $users->first();
    }
}
