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
        \App\Repositories\Contracts\ChatworkRepository::class => \App\Repositories\Eloquents\ChatworkRepository::class,
        \App\Repositories\Contracts\NotificationRepository::class => \App\Repositories\Eloquents\NotificationRepository::class,
        \App\Repositories\Contracts\TeamRepository::class => \App\Repositories\Eloquents\TeamRepository::class,
        \App\Repositories\Contracts\PermissionRepository::class => \App\Repositories\Eloquents\PermissionRepository::class,
        \App\Repositories\Contracts\ShopRepository::class => \App\Repositories\APIs\ShopRepository::class,
        \App\Repositories\Contracts\AlertRepository::class => \App\Repositories\APIs\AlertRepository::class,
        \App\Repositories\Contracts\TaskRepository::class => \App\Repositories\APIs\TaskRepository::class,
        \App\Repositories\Contracts\MqAccountingRepository::class => \App\Repositories\Eloquents\MqAccountingRepository::class,
        \App\Repositories\Contracts\MqChartRepository::class => \App\Repositories\Eloquents\MqChartRepository::class,
        \App\Repositories\Contracts\PolicyRepository::class => \App\Repositories\Eloquents\PolicyRepository::class,
        \App\Repositories\Contracts\PolicyAttachmentRepository::class => \App\Repositories\Eloquents\PolicyAttachmentRepository::class,
        \App\Repositories\Contracts\JobGroupRepository::class => \App\Repositories\APIs\JobGroupRepository::class,
        \App\Repositories\Contracts\PolicySimulationHistoryRepository::class => \App\Repositories\Eloquents\PolicySimulationHistoryRepository::class,
        \App\Repositories\Contracts\SingleJobRepository::class => \App\Repositories\APIs\SingleJobRepository::class,
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
