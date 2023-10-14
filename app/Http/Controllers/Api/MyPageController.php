<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfitReferenceRequest;
use App\Repositories\Contracts\MyPageRepository;
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
        $result = $this->myPageRepository->getStoreProfitReference($request->all());
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
        $result = $this->myPageRepository->getStoreProfitTable($request->all());
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
        $result = $this->myPageRepository->getTasks($request->all());
        if (! $result->get('success')) {
            return response()->json([
                'message' => __('Something went wrong!. Please try again'),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json($result->get('data'));
    }
}
