<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use App\Policies\CompanyPolicy;
use App\Policies\ShopPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        User::class => UserPolicy::class,
        Company::class => CompanyPolicy::class,
    ];

    /**
     * Use for policy without Model.
     *
     * @var array<string, string[]>
     */
    protected array $gates = [
        'view-shop' => [ShopPolicy::class, 'view'],
        'create-shop' => [ShopPolicy::class, 'create'],
        'update-shop' => [ShopPolicy::class, 'update'],
        'delete-shop' => [ShopPolicy::class, 'delete'],
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        $this->defineGates();

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            if ($frontendUrl = config('app.frontend_url')) {
                return $frontendUrl.'/reset-password/'.$token;
            }

            return url('/reset-password/'.$token);
        });
    }

    protected function defineGates(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->isAdmin() ? true : null;
        });

        foreach ($this->gates as $ability => $arguments) {
            Gate::define($ability, $arguments);
        }
    }
}
