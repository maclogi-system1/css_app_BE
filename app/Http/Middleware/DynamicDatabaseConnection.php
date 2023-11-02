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
        $this->getAndSetConnectionConfiguration();

        $this->checkAndTryToConnect();

        return $next($request);
    }

    public function getAndSetConnectionConfiguration()
    {
        $password = SecretsManagerService::getPasswordCache();
        $connections = DatabaseConnectionConstant::EXTERNAL_CONNECTIONS;
        if (app()->environment('production')) {
            $connections[config('database.default')] = config('database.default');
        }

        foreach ($connections as $connectionName) {
            Config::set("database.connections.{$connectionName}.password", $password);
            DB::purge($connectionName);
            DB::reconnect($connectionName);
        }
    }

    public function checkAndTryToConnect()
    {
        try {
            DB::connection(DatabaseConnectionConstant::KPI_CONNECTION)->getPdo();
        } catch (\Throwable $e) {
            if (DatabaseConnectionConstant::reconnectable($e)) {
                cache()->forget('aws_secret_password');
                $this->getAndSetConnectionConfiguration();
            } else {
                throw $e;
            }
        }
    }
}
