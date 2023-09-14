<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
    public function model(array $attributes = [])
    {
        return app($this->getModelName())->fill($attributes);
    }

    /**
     * Get the list of the resource with pagination and handle filter.
     *
     * @param  array  $filters
     * @param  array  $columns
     * @return mixed
     */
    public function getList(array $filters = [], array $columns = ['*'])
    {
        $page = Arr::get($filters, 'page', 1);
        $perPage = Arr::get($filters, 'per_page', 10);

        $query = $this->getWithFilter($this->queryBuilder(), $filters);

        if ($perPage < 0) {
            return $query->get($columns);
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
        $searchList = Arr::get($filters, 'searches');
        $filter = Arr::get($filters, 'filter');
        $filterList = Arr::get($filters, 'filters');
        $sort = Arr::get($filters, 'sort');

        if (! empty($search)) {
            $builder->searches($search);
        }

        if (! empty($searchList)) {
            foreach ($searchList as $field => $value) {
                if (str($value)->contains(',')) {
                    $values = explode(',', $value);
                    foreach ($values as $val) {
                        $builder->orSearch($field, $val);
                    }
                } else {
                    $builder->search($field, $value);
                }
            }
        }

        if (! empty($filter)) {
            foreach ($filter as $field => $value) {
                $builder->where($field, $value);
            }
        }

        if (! empty($filterList)) {
            foreach ($filterList as $field => $value) {
                if (str($value)->contains(',')) {
                    $values = explode(',', $value);
                    $builder->whereIn($field, $values);
                } else {
                    $builder->where($field, $value);
                }
            }
        }

        if (! empty($sort)) {
            $builder->orderBy($sort['field'], $sort['direction'] ?? 'asc');
        }

        return $builder;
    }

    /**
     * Get a listing of the resource bt keyword.
     *
     * @param  array  $queries
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(array $fields, array $queries = [], $columns = ['*']): Collection
    {
        if (empty($queries)) {
            return $this->model()->all($columns);
        }

        $builder = $this->model()->select($columns);

        if ($limit = Arr::get($queries, 'limit')) {
            $builder->limit($limit);
        }

        if ($keyword = Arr::get($queries, 'keyword')) {
            $builder->orSearches(array_combine($fields, array_fill(0, count($fields), $keyword)));
        }

        return $builder->get();
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

    /**
     * Enable "filters" to use "useWith".
     *
     * @param  array  $relationValid
     * @param  array  $filters
     * @return $this
     */
    public function enableUseWith(array $relationValid, array $filters = []): static
    {
        if (Arr::has($filters, 'with') && ! array_diff($filters['with'], $relationValid)) {
            $this->useWith($filters['with']);
        }

        return $this;
    }

    /**
     * Safely execute database interactions using transaction.
     *
     * @param  \Closure  $callback
     * @param  string  $titleError
     * @return mixed
     */
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
