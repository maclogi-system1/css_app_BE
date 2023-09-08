<?php

namespace App\Repositories\Contracts;

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
}
