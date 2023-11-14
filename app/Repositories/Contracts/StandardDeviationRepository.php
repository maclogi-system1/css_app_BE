<?php

namespace App\Repositories\Contracts;

use App\Models\StandardDeviation;

interface StandardDeviationRepository extends Repository
{
    /**
     * Get the first record matching the attributes. If the record is not found, create it.
     */
    public function firstOrCreate(array $data): ?StandardDeviation;
}
