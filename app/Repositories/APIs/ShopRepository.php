<?php

namespace App\Repositories\APIs;

use App\Models\User;
use App\Repositories\Contracts\LinkedUserInfoRepository;
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
                'projectPersonInCharges',
                'createdUser',
            ]] + $filters;

        $result = $this->shopService->getList($filters);

        if ($result->get('success')) {
            $shops = $this->convertCssUserByOssUser(collect($result->get('data')->get('shops')));
            $result->get('data')->put('shops', $shops);
        }

        return $result;
    }

    /**
     * Find a specified shop.
     */
    public function find($storeId)
    {
        $result = $this->shopService->find($storeId);
        if ($result->get('success')) {
            $shops = $this->convertCssUserByOssUser(collect($result->get('data')));
            $result->put('data', $shops);
        }

        return $result;
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
        $data = $this->convertOssUserByCssUser($data);

        $result = $this->shopService->update($data + ['store_id' => $storeId]);

        if ($result->get('status') == Response::HTTP_UNPROCESSABLE_ENTITY) {
            $errors = $result->get('data')->get('message');

            return collect([
                'status' => $result->get('status'),
                'errors' => $errors,
            ]);
        }

        return $this->convertCssUserByOssUser($result->get('data'));
    }

    public function create(array $data)
    {
        $data = $this->convertOssUserByCssUser($data);

        $result = $this->shopService->create(array_merge($data, ['is_css' => 1]));

        if ($result->get('status') == Response::HTTP_UNPROCESSABLE_ENTITY) {
            $errors = $result->get('data')->get('message');

            return collect([
                'status' => $result->get('status'),
                'errors' => $errors,
            ]);
        }

        return $this->convertCssUserByOssUser($result->get('data'));
    }

    protected function convertOssUserByCssUser(array $data): array
    {
        $assignees = Arr::get($data, 'assignees', []);
        $convertUser = [
            'consultant',
            'director_contact',
            'designer_contact',
            'person_in_charge_contact',
        ];

        /** @var \App\Repositories\Contracts\LinkedUserInfoRepository */
        $linkedUserInfoRepository = app(LinkedUserInfoRepository::class);
        $ossUserIds = $linkedUserInfoRepository->getOssUserIdsByCssUserIds($assignees);

        Arr::set($data, 'assignees', $ossUserIds);

        foreach ($convertUser as $value) {
            $userId = Arr::get($data, $value);
            if ($userId) {
                $convertUserId = $linkedUserInfoRepository->getOssUserIdsByCssUserIds([$userId]);
                Arr::set($data, $value, $convertUserId[0] ?? 0);
            }
        }

        return $data;
    }

    public function convertCssUserByOssUser(Collection $data): Collection
    {
        return $data->map(function ($shop) {
            $listConvert = ['directors', 'designers', 'consultants', 'managers', 'person_in_charges'];

            if (! empty($shop['is_css'])) {
                foreach ($listConvert as $item) {
                    if (! empty($shop[$item])) {
                        $userIds = collect($shop[$item])->pluck('id')->toArray();
                        $shop[$item] = $this->getUserRepository()->getListByLinkedUserIds($userIds)->map(function (User $user) {
                            return $user->getFieldForOSS();
                        })->toArray();
                    }
                }
            }

            return $shop;
        });
    }

    public function getUserRepository(): UserRepository
    {
        return app(UserRepository::class);
    }
}
