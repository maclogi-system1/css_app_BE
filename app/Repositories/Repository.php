<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

abstract class Repository
{
    /**
     * List of scopes to be attached when querying.
     *
     * @var array
     */
    protected array $scopes = [];

    /**
     * @var array
     */
    protected array $withs = [];

    /**
     * @var array
     */
    protected array $has = [];

    /**
     * @var array
     */
    protected array $doesntHave = [];

    /**
     * Get full name of model.
     */
    abstract public function getModelName(): string;

    /**
     * Get a new Eloquent model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function model()
    {
        return app($this->getModelName());
    }

    /**
     * Get the list of the resource with pagination and handle filter.
     *
     * @param  array  $filters
     * @param  array  $columns
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function getList(array $filters = [], array $columns = ['*']): LengthAwarePaginator|Collection
    {
        $page = Arr::get($filters, 'page', 1);
        $perPage = Arr::get($filters, 'per_page', config('coreapp.per_page_default', 10));

        $query = $this->getWithFilter($this->queryBuilder(), $filters);

        if ($perPage < 0) {
            return $query->get();
        }

        return $query->paginate($perPage, $columns, 'page', $page)->withQueryString();
    }

    /**
     * Get a builder to query data.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function queryBuilder(): Builder
    {
        $query = $this->model()->query();

        if (! empty($this->withs)) {
            $query->with($this->withs);
        }

        if (! empty($this->scopes)) {
            foreach ($this->scopes as $key => $scope) {
                if (is_array($scope)) {
                    $query->{$key}(...$scope);
                } else {
                    $query->{$scope}();
                }
            }
        }

        if (! empty($this->has)) {
            foreach ($this->has as $relation => $callback) {
                if (is_numeric($relation)) {
                    $query->has($callback);
                    continue;
                }

                $query->whereHas($relation, $callback);
            }
        }

        if (! empty($this->doesntHave)) {
            foreach ($this->doesntHave as $relation => $callback) {
                if (is_numeric($relation)) {
                    $query->doesntHave($callback);
                    continue;
                }

                $query->whereDoesntHave($relation, $callback);
            }
        }

        return $query;
    }

    /**
     * Set filter for builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getWithFilter(Builder $builder, array $filters = []): Builder
    {
        $search = Arr::get($filters, 'search');
        $filter = Arr::get($filters, 'filter');
        $sort = Arr::get($filters, 'sort');

        if (! empty($search)) {
            $builder->searches($search);
        }

        if (! empty($filter)) {
            foreach ($filter as $field => $value) {
                $builder->where($field, $value);
            }
        }

        if (! empty($sort)) {
            $builder->orderBy($sort['field'], $sort['direction'] ?? 'asc');
        }

        return $builder;
    }

    /**
     * Set a list of model scopes to query.
     *
     * @param  array|string  $scope
     * @return $this
     */
    public function useScope(array|string $scope): static
    {
        if (is_string($scope)) {
            $scope = [$scope];
        }

        $this->scopes = array_merge($this->scopes, $scope);

        return $this;
    }

    /**
     * Set a list of model relationships to query.
     *
     * @param  array|string  $scope
     * @return $this
     */
    public function useWith(array|string $with): static
    {
        if (is_string($with)) {
            $with = [$with];
        }

        $this->withs = array_merge($this->withs, $with);

        return $this;
    }

    /**
     * Set a list of model has/doesn't have relationships to query.
     *
     * @param  array|string  $has
     * @param  bool  $boolean
     * @return $this
     */
    public function useHas(array|string $has, bool $boolean = true): static
    {
        if (is_string($has)) {
            $has = [$has];
        }

        if ($boolean) {
            $this->has = array_merge($this->has, $has);
        } else {
            $this->doesntHave = array_merge($this->doesntHave, $has);
        }

        return $this;
    }

    /**
     * Set a list of model doesn't have relationships to query.
     *
     * @param  array|string  $doesntHave
     * @return $this
     */
    public function useDoesntHave(array|string $doesntHave)
    {
        return $this->useHas($doesntHave, false);
    }

    protected function handleSafely(\Closure $callback, $titleError = 'Process')
    {
        DB::beginTransaction();

        try {
            $result = call_user_func($callback);

            DB::commit();

            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();

            logger()->error("{$titleError}: {$e->getMessage()}");

            chatwork_log($e->getMessage(), 'error');

            return null;
        }
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard($name = null)
    {
        return auth()->guard($name);
    }

    /**
     * Get user authenticated.
     *
     * @param  string  $guard
     * @param  \App\Models\User|null  $user
     * @return \App\Models\User|null
     */
    protected function auth($guard = null, $user = null)
    {
        if (! is_null($user)) {
            return $user;
        }

        if (! auth()->check()) {
            return null;
        }

        return $this->guard($guard)->user();
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @throws \InvalidArgumentException
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->model()->{$method}(...$parameters);
    }
}
