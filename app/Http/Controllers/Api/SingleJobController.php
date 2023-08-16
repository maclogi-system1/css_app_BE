<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\SingleJobRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SingleJobController extends Controller
{
    public function __construct(
        protected SingleJobRepository $singleJobRepository
    ) {
    }

    /**
     * Get a list of single job by store id.
     */
    public function getListByStore(Request $request, string $storeId)
    {
        $singleJobs = $this->singleJobRepository->getListByStore($storeId, $request->query());

        return response()->json($singleJobs->get('data'), $singleJobs->get('status', Response::HTTP_OK));
    }
}
