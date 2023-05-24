<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionController extends Controller
{
    public function __construct(
        private PermissionRepository $permissionRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResource|JsonResponse
    {
        $permissions = PermissionResource::collection($this->permissionRepository->getList($request->query()));
        $permissions->wrap('permissions');

        return $permissions;
    }

    /**
     * Get a listing of the permission by keyword.
     */
    public function search(Request $request): JsonResource|JsonResponse
    {
        $permissions = PermissionResource::collection($this->permissionRepository->search(
            ['name', 'display_name'],
            $request->query(),
            ['id', 'name', 'display_name']
        ));
        $permissions->wrap('permissions');

        return $permissions;
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResource|JsonResponse
    {
        $permission = $this->permissionRepository->update($request->validated(), $permission);

        return new PermissionResource($permission);
    }
}
