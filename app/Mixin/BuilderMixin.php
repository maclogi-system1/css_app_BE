<?php

namespace App\Mixin;

use Closure;

/**
 * @mixin \Illuminate\Database\Query\Builder
 *
 * @method static Closure search()
 * @method static Closure orSearch()
 * @method static Closure searches()
 * @method static Closure orSearches()
 */
class BuilderMixin
{
    public function search(): Closure
    {
        return fn ($field, $value) => $value ? $this->where($field, 'like', "%{$value}%") : $this;
    }

    public function orSearch(): Closure
    {
        return fn ($field, $value) => $value ? $this->orWhere($field, 'like', "%{$value}%") : $this;
    }

    public function searches(): Closure
    {
        return function (array $values) {
            if (empty($values)) {
                return $this;
            }

            return $this->where(function ($query) use ($values) {
                foreach ($values as $field => $value) {
                    $query->where($field, 'like', "%{$value}%");
                }
            });
        };
    }

    public function orSearches(): Closure
    {
        return function (array $values) {
            if (empty($values)) {
                return $this;
            }

            return $this->where(function ($query) use ($values) {
                foreach ($values as $field => $value) {
                    $query->orWhere($field, 'like', "%{$value}%");
                }
            });
        };
    }
}
