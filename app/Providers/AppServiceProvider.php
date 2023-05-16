<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Builder::macro('searches', function ($values) {
            if (empty($values)) {
                return $this;
            }

            return $this->where(function ($query) use ($values) {
                foreach ($values as $field => $value) {
                    $query->where($field, 'like', "%{$value}%");
                }
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
