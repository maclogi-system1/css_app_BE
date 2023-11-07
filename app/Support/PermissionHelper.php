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
    public static function getDataViewShopsWithPermission(User $user, array $params): array
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
                }

                if (! $viewCompanyContractShops && $user->can('view_shops')) {
                    /** @var LinkedUserInfoRepository $linkedUserInfoRepository */
                    $linkedUserInfoRepository = app(LinkedUserInfoRepository::class);
                    $convertUserId = $linkedUserInfoRepository->getOssUserIdsByCssUserIds([$user->id]);
                    if ($convertUserId = Arr::get($convertUserId, 0)) {
                        $params['filters']['projects.created_by'] = $convertUserId;
                    }
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
        $createdById = Arr::get($shop, 'created_by.id');

        $managers = Arr::get($shop, 'managers', []);
        $managerIds = collect($managers)->pluck('id')->toArray();

        $authorizedIds = array_merge([$createdById], $managerIds);

        Gate::forUser($user)->authorize('update-shop', [$shop['company_id'], $authorizedIds]);

        return true;
    }
}
