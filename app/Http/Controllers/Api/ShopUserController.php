<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OSS\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ShopUserController extends Controller
{
    public function __construct(
        protected UserService $userService,
    ) {
    }

    /**
     * Get a list of the user by store id.
     */
    public function getOptions(string $storeId): JsonResponse
    {
        $result = $this->userService->getShopUsers(['store_id' => $storeId]);
        $data = $result->get('data');
        $data['users'] = array_map(fn ($user) => ['label' => $user['name'], 'value' => $user['id']], $data['users']);

        return response()->json(
            $data,
            $result->get('status', Response::HTTP_OK)
        );
    }
}
