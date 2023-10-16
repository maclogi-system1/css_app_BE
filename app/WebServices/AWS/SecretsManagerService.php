<?php

namespace App\WebServices\AWS;

use Aws\Exception\AwsException;
use Illuminate\Support\Arr;

class SecretsManagerService
{
    public static function getPassword(?string $secretName = null)
    {
        $secretArray = static::getSecretValue($secretName);

        return Arr::get($secretArray, 'password');
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
