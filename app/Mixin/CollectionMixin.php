<?php

namespace App\Mixin;

use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * @mixin \Illuminate\Support\Collection
 *
 * @method static Closure paginate()
 */
class CollectionMixin
{
    public function paginate(): Closure
    {
        return function ($perPage = 10, $page = null, $options = []) {
            $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

            return (new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $this->count(),
                $perPage,
                $page,
                $options
            ))->withPath('');
        };
    }
}
