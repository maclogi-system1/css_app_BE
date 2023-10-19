<?php

namespace App\Constants;

class DatabaseConnectionConstant
{
    public const KPI_CONNECTION = 'kpi_real_data';
    public const POLICY_CONNECTION = 'policy_real_data';
    public const INFERENCE_CONNECTION = 'inference_real_data';

    public const EXTERNAL_CONNECTIONS = [
        self::KPI_CONNECTION => 'kpi_real_data',
        self::POLICY_CONNECTION => 'policy_real_data',
        self::INFERENCE_CONNECTION => 'inference_real_data',
    ];

    public static function exceptionRetryable(): array
    {
        return array_map(fn ($connectionName) => "Connection: {$connectionName}", static::EXTERNAL_CONNECTIONS);
    }
}
