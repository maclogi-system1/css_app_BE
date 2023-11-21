<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfitReferenceRequest;
use App\Models\User;
use App\Repositories\Contracts\MyPageRepository;
use App\Support\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class MyPageController extends Controller
{
    public function __construct(
        private MyPageRepository $myPageRepository
    ) {
    }

    /**
     * Get my page options.
     */
    public function options(Request $request)
    {
        return response()->json($this->myPageRepository->getOptions($request->user()));
    }

    /**
     * Get Store Profit Reference.
     */
    public function getStoreProfitReference(StoreProfitReferenceRequest $request)
    {
        $params = $request->all();
        $params = $this->filterDataWithPermission($request->user(), $params);

        $result = $this->myPageRepository->getStoreProfitReference(array_merge(
            $params,
            ['user_id' => $request->user()->id]
        ));
        if (! $result->get('success')) {
            return response()->json([
                'message' => __('Something went wrong!. Please try again'),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json($result->get('data'));
    }

    /**
     * Get Store Profit Reference table.
     */
    public function getStoreProfitTable(StoreProfitReferenceRequest $request)
    {
        $params = $request->all();
        $params = $this->filterDataWithPermission($request->user(), $params);

        $result = $this->myPageRepository->getStoreProfitTable(array_merge(
            $params,
            ['user_id' => $request->user()->id]
        ));
        if (! $result->get('success')) {
            return response()->json([
                'message' => __('Something went wrong!. Please try again'),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json($result->get('data'));
    }

    /**
     * Get tasks, task alerts.
     */
    public function getTasks(StoreProfitReferenceRequest $request)
    {
        $params = $request->all();
        $params = $this->filterDataWithPermission($request->user(), $params);

        $result = $this->myPageRepository->getTasks(array_merge($params, ['user_id' => $request->user()->id]));
        if (! $result->get('success')) {
            return response()->json([
                'message' => __('Something went wrong!. Please try again'),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json($result->get('data'));
    }

    /**
     * Get tasks, task alerts.
     */
    public function getAlerts(StoreProfitReferenceRequest $request)
    {
        $params = $request->all();
        $params = $this->filterDataWithPermission($request->user(), $params);

        $result = $this->myPageRepository->getAlerts(array_merge($params, ['user_id' => $request->user()->id]));
        if (! $result->get('success')) {
            return response()->json([
                'message' => __('Something went wrong!. Please try again'),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json($result->get('data'));
    }

    public function getSales4QuadrantMap(StoreProfitReferenceRequest $request)
    {
        $params = $request->all();
        $params = $this->filterDataWithPermission($request->user(), $params);

        $result = $this->myPageRepository->getSales4QuadrantMap(array_merge(
            $params,
            ['user_id' => $request->user()->id]
        ));
        if (! $result->get('success')) {
            return response()->json([
                'message' => __('Something went wrong!. Please try again'),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json($result->get('data'));
    }

    protected function filterDataWithPermission(User $user, array $params): array
    {
        $params = PermissionHelper::getDataViewShopsWithPermission($user, $params, false);
        if ($user->cannot(['view_all_shops', 'view_all_company_shops', 'view_company_contract_shops'])
            && $user->can('view_shops')) {
            Arr::forget($params, ['store_group']);
        }

        return $params;
    }
}
