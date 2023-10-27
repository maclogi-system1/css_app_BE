<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ShopRepository;
use App\Support\PermissionHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class ShopController extends Controller
{
    public function __construct(
        private ShopRepository $shopRepository,
    ) {
    }

    /**
     * Get a listing of the shop from oss api.
     */
    public function index(Request $request): JsonResponse
    {
        $params = $request->query();
        $params = PermissionHelper::getDataViewShopsWithPermission($request->user(), $params);

        $result = $this->shopRepository->getList($params);

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get a specified shop.
     */
    public function show(Request $request, $storeId): JsonResponse
    {
        $result = $this->shopRepository->find($storeId, ['*'], $request->query());
        if (! $result?->get('success')) {
            return response()->json(['message' => __('Shop not found')], Response::HTTP_NOT_FOUND);
        }

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get a list of options for select.
     */
    public function getOptions(): JsonResponse
    {
        $options = $this->shopRepository->getOptions();

        return response()->json($options);
    }

    public function update(string $storeId, Request $request): JsonResponse
    {
        $shopResult = $this->shopRepository->find($storeId);
        if (! $shopResult?->get('success')) {
            return response()->json(['message' => __('Shop not found')], Response::HTTP_NOT_FOUND);
        }

        $shop = $shopResult->get('data')->get('data');
        $createdById = Arr::get($shop, 'created_by.id');

        Gate::forUser($request->user())->authorize('update-shop', [$shop['company_id'], [$createdById]]);

        $result = $this->shopRepository->update($storeId, $request->all());

        if (! empty($result->get('errors'))) {
            return response()->json([
                'message' => 'There are a few failures.',
                'errors' => $result->get('errors'),
            ], $result->get('status'));
        }

        return response()->json($result);
    }

    public function create(Request $request): JsonResponse
    {
        $companyId = $request->get('company');
        $message = 'There are a few failures.';
        if (! $companyId) {
            return response()->json([
                'message' => $message,
                'errors' => [
                    'company' => [
                        __('validation.required', ['attribute' => 'company']),
                    ],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        Gate::forUser($request->user())->authorize('create-shop', [$companyId]);

        $result = $this->shopRepository->create($request->all() + ['created_by' => $request->user()->id]);
        if (! empty($result->get('errors'))) {
            return response()->json([
                'message' => $message,
                'errors' => $result->get('errors'),
            ], $result->get('status'));
        }

        return response()->json($result);
    }

    public function getInfo(string $storeId): JsonResponse
    {
        $result = $this->shopRepository->getInfo($storeId);

        return response()->json($result);
    }
}
