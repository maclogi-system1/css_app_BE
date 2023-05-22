<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Repositories\Contracts\TeamRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeamController extends Controller
{
    public function __construct(
        private TeamRepository $teamRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $teams = TeamResource::collection($this->teamRepository->getList($request->query()));
        $teams->wrap('teams');

        return $teams;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request): TeamResource|JsonResponse
    {
        $team = $this->teamRepository->create($request->validated(), $request->user());

        return $team ? new TeamResource($team) : response()->json([
            'message' => __('Created failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        $this->authorize('view_team');

        return new TeamResource($team);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team): TeamResource|JsonResponse
    {
        $team = $this->teamRepository->update($request->validated(), $team);

        return $team ? new TeamResource($team) : response()->json([
            'message' => __('Updated failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        $team = $this->teamRepository->delete($team);

        return $team ? new TeamResource($team) : response()->json([
            'message' => __('Deleted failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }
}
