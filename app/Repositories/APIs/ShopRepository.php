<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\ShopRepository as ShopRepositoryContract;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Repository;
use App\WebServices\OSS\ShopService;
use App\WebServices\OSS\UserService;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ShopRepository extends Repository implements ShopRepositoryContract
{
    public function __construct(
        private ShopService $shopService,
        private UserService $userService
    ) {
    }

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
            'projectManagers',
            'createdUser',
        ]] + $filters;

        return $this->shopService->getList($filters);
    }

    /**
     * Find a specified shop.
     */
    public function find($storeId)
    {
        return $this->shopService->find($storeId);
    }

    /**
     * Get a list of the user in a shop.
     */
    public function getUsers(array $filters = [])
    {
        $data = ['users' => []];

        $result = $this->userService->getShopUsers($filters);
        $ossShopUser = collect(Arr::get($result->get('data'), 'users'));

        if (! empty($ossShopUser)) {
            $ossShopUserEmail = Arr::pluck($ossShopUser, 'email');

            $data['users'] = app(UserRepository::class)
                ->getList(['per_page' => -1], ['name', 'email', 'id'])
                ->filter(function ($user) use ($ossShopUserEmail) {
                    return in_array($user->email, $ossShopUserEmail);
                })
                ->map(function ($user) {
                    return ['value' => $user->id, 'label' => $user->name];
                })
                ->values()
                ->toArray();
        }

        return $data;
    }

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array
    {
        $result = $this->shopService->getOptions();

        if (! $result->get('success')) {
            return [];
        }

        return $result->get('data')->toArray();
    }

    /**
     * update shop.
     */
    public function update(string $storeId, array $data): Collection
    {
        $result = $this->shopService->update($data + ['store_id' => $storeId]);

        if ($result->get('status') == Response::HTTP_UNPROCESSABLE_ENTITY) {
            $errors = $result->get('data')->get('message');

            return collect([
                'status' => $result->get('status'),
                'errors' => $errors,
            ]);
        }

        return $result->get('data');
    }
}
