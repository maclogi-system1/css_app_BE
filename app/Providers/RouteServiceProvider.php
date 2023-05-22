<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    protected $bindingModels = [
        'user' => \App\Repositories\Contracts\UserRepository::class,
        'role' => \App\Repositories\Contracts\RoleRepository::class,
        'company' => \App\Repositories\Contracts\CompanyRepository::class,
        'team' => \App\Repositories\Contracts\TeamRepository::class,
    ];

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->modelBinding();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->name('api.')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function modelBinding()
    {
        foreach ($this->bindingModels as $key => $repository) {
            Route::bind($key, function ($value) use ($key, $repository) {
                return app($repository)->find($value, ['*'], request()->query())
                    ?? abort(404, str($key)->title()->append(' not found.')->toString());
            });
        }
    }
}
