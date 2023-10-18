<?php

namespace App\WebServices\AWS;

use Aws\Exception\AwsException;
use Illuminate\Support\Arr;

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
        return app('aws')->createClient('secretsManager');
    }
}
