<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function __construct(
        private RoleRepository $roleRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $this->authorize('view_role');

        $roles = RoleResource::collection($this->roleRepository->getList($request->query()));
        $roles->wrap('roles');

        return $roles;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RoleResource|JsonResponse
    {
        $role = $this->roleRepository->create($request->validated());

        return $role ? new RoleResource($role) : response()->json([
            'message' => __('Created failure.')
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): RoleResource|JsonResponse
    {
        $this->authorize('view_role');

        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RoleResource|JsonResponse
    {
        $role = $this->roleRepository->update($request->validated(), $role);

        return $role ? new RoleResource($role) : response()->json([
            'message' => __('Updated failure.')
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RoleResource|JsonResponse
    {
        $this->authorize('delete_role');

        $role = $this->roleRepository->delete($role);

        return $role ? new RoleResource($role) : response()->json([
            'message' => __('Deleted failure.')
        ], Response::HTTP_BAD_REQUEST);
    }
}
