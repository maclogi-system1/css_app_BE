<?php

namespace App\Http\Middleware;

use App\Constants\DatabaseConnectionConstant;
use App\WebServices\AWS\SecretsManagerService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DynamicDatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $password = SecretsManagerService::getPasswordCache();

        foreach (DatabaseConnectionConstant::EXTERNAL_CONNECTIONS as $connectionName) {
            Config::set("database.connections.{$connectionName}.password", $password);
            DB::purge($connectionName);
            DB::reconnect($connectionName);
        }

        return $next($request);
    }
}
