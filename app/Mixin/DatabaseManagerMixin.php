<?php

namespace App\Mixin;

use App\Constants\DatabaseConnectionConstant;
use Closure;

/**
 * @mixin \Illuminate\Database\DatabaseManager
 *
 * @method static Closure kpiTable()
 */
class DatabaseManagerMixin
{
    public function kpiTable(): Closure
    {
        return fn ($table, $as = null) => $this->connection(DatabaseConnectionConstant::KPI_CONNECTION)
            ->table($table, $as);
    }
}
