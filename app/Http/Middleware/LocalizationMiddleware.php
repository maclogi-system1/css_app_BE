<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //Check header request and set language defaut
        $lang = ($request->hasHeader('X-localization')) ? $request->header('X-localization') : 'ja';
        //Set laravel localization
        app()->setLocale($lang);

        //Continue request
        return $next($request);
    }
}
