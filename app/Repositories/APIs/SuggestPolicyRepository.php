<?php

namespace App\Repositories\APIs;

use App\Models\InferenceRealData\SuggestPolicy;
use App\Repositories\Contracts\SuggestPolicyRepository as SuggestPolicyRepositoryContract;
use App\Repositories\Repository;

class SuggestPolicyRepository extends Repository implements SuggestPolicyRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return SuggestPolicy::class;
    }

    /**
     * Get suggested policies by predId.
     */
    public function getSuggestedPoliciesByPredId(?string $predId = null): ?SuggestPolicy
    {
        if (is_null($predId)) {
            return null;
        }

        return $this->model()->with('suggestedPolicies')->where('pred_id', $predId)->first();
    }
}
