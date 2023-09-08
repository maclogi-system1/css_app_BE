<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ShopRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class ShopUserController extends Controller
{
    public function __construct(
        protected ShopRepository $shopRepository,
    ) {
    }

    /**
     * Get a list of the user by store id.
     */
    public function getOptions(Request $request, ?string $storeId = null): JsonResponse
    {
        $storeId ??= Arr::get($request->query('filters'), 'store_id');

        return response()->json(
            $this->shopRepository->getUsers(['store_id' => $storeId, 'per_page' => -1]),
            Response::HTTP_OK
        );
    }
}
