<?php

namespace App\Repositories\Contracts;

use App\Models\InferenceRealData\SuggestPolicy;

interface SuggestPolicyRepository extends Repository
{
    /**
     * Get suggested policies by predId.
     */
    public function getSuggestedPoliciesByPredId(?string $predId = null): ?SuggestPolicy;
}
