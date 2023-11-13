<?php

namespace App\WebServices\AWS;

use App\Constants\DatabaseConnectionConstant;
use Aws\Exception\AwsException;
use Aws\SecretsManager\SecretsManagerClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SecretsManagerService
{
    public static function getPassword(?string $secretName = null)
    {
        if (in_array(env('APP_ENV'), ['dev', 'local', 'development'])) {
            return env('AI_DB_PASSWORD', '');
        }

        $secretArray = static::getSecretValue($secretName);

        return Arr::get($secretArray, 'password');
    }

    public static function getPasswordCache(?string $secretName = null)
    {
        if (cache()->has('aws_secret_password')) {
            return cache('aws_secret_password');
        }

        $password = static::getPassword($secretName);

        cache(['aws_secret_password' => $password], now()->addDays(7));

        return $password;
    }

    public static function getSecretValue(?string $secretName = null): array
    {
        $client = static::getClient();
        $secretName ??= config('aws.secretsmanager.secret_name');

        try {
            $result = $client->getSecretValue([
                'SecretId' => $secretName,
                'Region' => 'ap-northeast-1',
            ]);
        } catch (AwsException $e) {
            logger()->error('AWS SecretsManagerClient getSecretValue: '.$e->getAwsErrorCode());

            throw $e;
        }

        if (isset($result['SecretString'])) {
            $secret = $result['SecretString'];
        } else {
            $secret = base64_decode($result['SecretBinary']);
        }

        $secretArray = json_decode($secret, true);

        return $secretArray;
    }

    public static function getClient()
    {
        return new SecretsManagerClient(config('aws'));
    }

    public static function tryToConnect()
    {
        static::getAndSetConnectionConfiguration();
        static::checkAndTryToConnect();
    }

    public static function getAndSetConnectionConfiguration()
    {
        $password = static::getPasswordCache();
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

    public static function checkAndTryToConnect()
    {
        try {
            DB::connection(DatabaseConnectionConstant::KPI_CONNECTION)->getPdo();
        } catch (\Throwable $e) {
            if (DatabaseConnectionConstant::reconnectable($e)) {
                cache()->forget('aws_secret_password');
                static::getAndSetConnectionConfiguration();
            } else {
                throw $e;
            }
        }
    }
}
