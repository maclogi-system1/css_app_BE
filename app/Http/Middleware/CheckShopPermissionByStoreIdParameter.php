<?php

namespace App\Http\Middleware;

use App\Support\PermissionHelper;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckShopPermissionByStoreIdParameter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->route()->hasParameter('storeId') && ($storeId = $request->route()->parameter('storeId'))) {
            if (($response = PermissionHelper::checkViewShopPermission(
                $request->user(),
                $storeId
            )) instanceof JsonResponse) {
                return $response; //Shop Not found
            }
        }

        //Continue request
        return $next($request);
    }
}
