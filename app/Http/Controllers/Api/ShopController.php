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

        if (! $result?->get('success')) {
            return response()->json(['message' => __('Shop not found')], Response::HTTP_NOT_FOUND);
        }

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get a list of options for select.
     */
    public function getOptions()
    {
        $options = $this->shopRepository->getOptions();

        return response()->json($options);
    }

    public function update(string $storeId, Request $request)
    {
        $result = $this->shopRepository->update($storeId, $request->all());

        if (! empty($result->get('errors'))) {
            return response()->json([
                'message' => 'There are a few failures.',
                'errors' => $result->get('errors'),
            ], $result->get('status'));
        }

        return response()->json($result);
    }
}
