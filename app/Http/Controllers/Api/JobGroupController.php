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
     * Get a list of policies by store id in this site.
     */
    public function getListByStore(Request $request, string $storeId): JsonResponse
    {
        $policyCollection = $this->jobGroupRepository->getListByStore(
            $storeId,
            $request->query(),
        );

        return response()->json($policyCollection->get('data'), $policyCollection->get('status', Response::HTTP_OK));
    }
}
