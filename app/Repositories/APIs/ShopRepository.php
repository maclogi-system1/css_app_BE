<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\ShopRepository as ShopRepositoryContract;
use App\Repositories\Repository;
use App\Services\OSS\ShopService;

class ShopRepository extends Repository implements ShopRepositoryContract
{
    public function __construct(
        private ShopService $shopService
    ) {}

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return '';
    }

    /**
     * Get the list of the shop from oss api.
     */
    public function getList(array $filters = [], array $columns = ['*'])
    {
        $filters = ['with' => [
            'shopCredential',
            'projectDirectors',
            'projectDesigners',
            'projectConsultants',
            'createdUser',
        ]] + $filters;

        return $this->shopService->getList($filters);
    }
}
