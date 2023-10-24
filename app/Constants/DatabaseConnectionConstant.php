<?php

namespace App\Constants;

use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PDOException;
use Throwable;

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

    public static function reconnectable(Throwable $e)
    {
        return ($e instanceof QueryException || $e instanceof PDOException)
            && Arr::first($e->errorInfo) == 'HY000'
            && Str::contains($e->getMessage(), 'using password: YES');
    }
}
