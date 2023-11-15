<?php

namespace App\Support;

use App\Models\User;
use App\Repositories\Contracts\LinkedUserInfoRepository;
use App\Repositories\Contracts\ShopRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class PermissionHelper
{
    public static function getDataViewShopsWithPermission(User $user, array $params, bool $needConvertUser = true): array
    {
        if (! $user->can('view_all_shops')) {
            $viewAllCompanyShopPermission = $user->can('view_all_company_shops');

            if ($viewAllCompanyShopPermission) {
                $params['filters']['projects.parent_id'] = $user->company_id;
            }

            if (! $viewAllCompanyShopPermission) {
                $viewCompanyContractShops = $user->can('view_company_contract_shops');
                if ($viewCompanyContractShops) {
                    $params['filters']['projects.parent_id'] = $user->company_id;
                    $params['filters']['projects.is_contract'] = 1;

                    if ($user->can('view_shops')) {
                        $params = self::convertManagerUser($user->id, $params, true, true);
                    }
                }

                if (! $viewCompanyContractShops && $user->can('view_shops')) {
                    $params = self::convertManagerUser($user->id, $params, $needConvertUser);
                }
            }
        }

        return $params;
    }

    /**
     * @param  User  $user
     * @param  string  $storeId
     * @return JsonResponse|true
     */
    public static function checkViewShopPermission(User $user, string $storeId): bool|JsonResponse
    {
        /** @var ShopRepository $shopRepository */
        $shopRepository = app(ShopRepository::class);
        $result = $shopRepository->find($storeId);
        if (! $result?->get('success')) {
            return response()->json(['message' => __('Shop not found')], Response::HTTP_NOT_FOUND);
        }

        Gate::forUser($user)->authorize('view-shop', [$result->get('data')->get('data')]);

        return true;
    }

    /**
     * @param  User  $user
     * @param  string  $storeId
     * @return JsonResponse|true
     */
    public static function checkUpdateShopPermission(User $user, string $storeId): bool|JsonResponse
    {
        /** @var ShopRepository $shopRepository */
        $shopRepository = app(ShopRepository::class);
        $shopResult = $shopRepository->find($storeId);
        if (! $shopResult?->get('success')) {
            return response()->json(['message' => __('Shop not found')], Response::HTTP_NOT_FOUND);
        }

        $shop = $shopResult->get('data')->get('data');

        $managers = Arr::get($shop, 'managers', []);
        $managerIds = collect($managers)->pluck('id')->toArray();

        Gate::forUser($user)->authorize('update-shop', [$shop['company_id'], $managerIds]);

        return true;
    }

    protected static function convertManagerUser(int $userId, array $params, bool $needConvertUser, bool $isOwnManager = false): array
    {
        $convertUserIds = [$userId];
        if ($needConvertUser) {
            /** @var LinkedUserInfoRepository $linkedUserInfoRepository */
            $linkedUserInfoRepository = app(LinkedUserInfoRepository::class);
            $convertUserIds = $linkedUserInfoRepository->getOssUserIdsByCssUserIds($convertUserIds);
        }

        if ($convertUserId = Arr::get($convertUserIds, 0)) {
            if ($isOwnManager) {
                $params['own_manager'] = $convertUserId;

                return $params;
            }

            $params['manager'] = $convertUserId;
        }

        return $params;
    }
}
