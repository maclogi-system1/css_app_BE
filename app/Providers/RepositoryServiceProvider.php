<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected $repositories = [
        \App\Repositories\Contracts\UserRepository::class => \App\Repositories\Eloquents\UserRepository::class,
        \App\Repositories\Contracts\RoleRepository::class => \App\Repositories\Eloquents\RoleRepository::class,
        \App\Repositories\Contracts\CompanyRepository::class => \App\Repositories\Eloquents\CompanyRepository::class,
        \App\Repositories\Contracts\BookmarkRepository::class => \App\Repositories\Eloquents\BookmarkRepository::class,
        \App\Repositories\Contracts\UserSettingRepository::class => \App\Repositories\Eloquents\UserSettingRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->repositories as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
