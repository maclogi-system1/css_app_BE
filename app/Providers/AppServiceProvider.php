<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected array $mixins = [
        \Illuminate\Database\Query\Builder::class => \App\Mixin\BuilderMixin::class,
        \Illuminate\Support\Facades\Http::class => \App\Mixin\HttpMixin::class,
        \Illuminate\Support\Str::class => \App\Mixin\StrMixin::class,
        \Illuminate\Support\Collection::class => \App\Mixin\CollectionMixin::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ($this->mixins as $class => $mixin) {
            $class::mixin(new $mixin());
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
