<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ShopRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShopController extends Controller
{
    public function __construct(
        private ShopRepository $shopRepository
    ) {
    }

    /**
     * Get a listing of the shop from oss api.
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->shopRepository->getList($request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get a specified shop.
     */
    public function show($storeId): JsonResponse
    {
        $result = $this->shopRepository->find($storeId);

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }
}
