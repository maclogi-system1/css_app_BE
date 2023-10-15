<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ShopRepository extends Repository
{
    /**
     * Get the list of the shop from oss api.
     */
    public function getList(array $filters = [], array $columns = ['*']);

    /**
     * Find a specified shop.
     */
    public function find($storeId);

    /**
     * Get a list of the user in a shop.
     */
    public function getUsers(array $filters = []);

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array;

    public function update(string $storeId, array $data): Collection;
}
