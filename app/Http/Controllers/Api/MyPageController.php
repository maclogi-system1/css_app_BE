<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfitReferenceRequest;
use App\Repositories\Contracts\MyPageRepository;
use App\Support\PermissionHelper;
use Illuminate\Http\Response;

class MyPageController extends Controller
{
    public function __construct(
        private MyPageRepository $myPageRepository
    ) {
    }

    /**
     * Get my page options.
     */
    public function options()
    {
        return response()->json($this->myPageRepository->getOptions());
    }

    /**
     * Get Store Profit Reference.
     */
    public function getStoreProfitReference(StoreProfitReferenceRequest $request)
    {
        $params = $request->all();
        $params = PermissionHelper::getDataViewShopsWithPermission($request->user(), $params);

        $result = $this->myPageRepository->getStoreProfitReference($params);
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
        $params = PermissionHelper::getDataViewShopsWithPermission($request->user(), $params);

        $result = $this->myPageRepository->getStoreProfitTable($params);
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
        $params = PermissionHelper::getDataViewShopsWithPermission($request->user(), $params);

        $result = $this->myPageRepository->getTasks($params);
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
        $params = PermissionHelper::getDataViewShopsWithPermission($request->user(), $params);

        $result = $this->myPageRepository->getAlerts($params);
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
        $params = PermissionHelper::getDataViewShopsWithPermission($request->user(), $params);

        $result = $this->myPageRepository->getSales4QuadrantMap($params);
        if (! $result->get('success')) {
            return response()->json([
                'message' => __('Something went wrong!. Please try again'),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json($result->get('data'));
    }
}
