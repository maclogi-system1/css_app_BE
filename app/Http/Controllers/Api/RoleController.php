<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function __construct(
        private RoleRepository $roleRepository
    ) {}

    /**
     * Display a listing of the role.
     */
    public function index(Request $request): JsonResource|JsonResponse
    {
        $this->authorize('view_role');

        $roles = RoleResource::collection($this->roleRepository->getList($request->query()));
        $roles->wrap('roles');

        return $roles;
    }

    /**
     * Get a listing of the role by keyword.
     */
    public function search(Request $request): JsonResource|JsonResponse
    {
        $this->authorize('view_role');

        $roles = RoleResource::collection($this->roleRepository->search(
            ['name', 'display_name'],
            $request->query(),
            ['id', 'name', 'display_name']
        ));
        $roles->wrap('roles');

        return $roles;
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request): JsonResource|JsonResponse
    {
        $role = $this->roleRepository->create($request->validated());

        return $role ? new RoleResource($role) : response()->json([
            'message' => __('Created failure.')
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResource|JsonResponse
    {
        $this->authorize('view_role');

        return new RoleResource($role);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResource|JsonResponse
    {
        $role = $this->roleRepository->update($request->validated(), $role);

        return $role ? new RoleResource($role) : response()->json([
            'message' => __('Updated failure.')
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): JsonResource|JsonResponse
    {
        $this->authorize('delete_role');

        $role = $this->roleRepository->delete($role);

        return $role ? new RoleResource($role) : response()->json([
            'message' => __('Deleted failure.')
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove multiple roles at the same time.
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        $this->authorize('delete_role');

        return ! $this->roleRepository->deleteMultiple($request->query('role_ids', []))
            ? response()->json([
                'message' => __('Delete failed. Please check your role ids!'),
                'role_ids' => $request->query('role_ids', []),
            ], Response::HTTP_BAD_REQUEST)
            : response()->json([
                'message' => __('The roles have been deleted successfully.'),
            ]);
    }
}
