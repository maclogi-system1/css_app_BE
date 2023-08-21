<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\JobGroupRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JobGroupController extends Controller
{
    public function __construct(
        private JobGroupRepository $jobGroupRepository
    ) {
    }

    /**
     * Get a list of job group by store id.
     */
    public function getListByStore(Request $request, string $storeId): JsonResponse
    {
        $jobGroupCollection = $this->jobGroupRepository->getListByStore(
            $storeId,
            $request->query(),
        );

        return response()->json(
            $jobGroupCollection->get('data'),
            $jobGroupCollection->get('status', Response::HTTP_OK)
        );
    }
}
