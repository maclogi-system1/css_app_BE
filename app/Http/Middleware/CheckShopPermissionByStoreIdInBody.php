<?php

namespace App\Http\Middleware;

use App\Support\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckShopPermissionByStoreIdInBody
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->get('store_id')) {
            PermissionHelper::checkViewShopPermission($request->user(), $request->get('store_id'));
        }

        //Continue request
        return $next($request);
    }
}
