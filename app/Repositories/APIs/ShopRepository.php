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
use Illuminate\Support\Facades\Cache;

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
        if (Cache::has('oss_shop_users')) {
            return Cache::get('oss_shop_users');
        }

        $cssUsers = app(UserRepository::class)->getList(['per_page' => -1], ['name', 'email']);
        $result = $this->userService->getShopUsers($filters);
        $data = $result->get('data');

        if ($result->get('success')) {
            $data['users'] = collect(Arr::get($data, 'users'))
                ->whereIn('email', $cssUsers->pluck('email'))
                ->map(function ($user) use ($cssUsers) {
                    $name = $cssUsers->where('email', $user['email'])->first()?->name;

                    return ['label' => $name, 'value' => $user['id']];
                })
                ->values()
                ->toArray();

            if (! empty($data['users'])) {
                Cache::put('oss_shop_users', $data, 300);
            }
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
