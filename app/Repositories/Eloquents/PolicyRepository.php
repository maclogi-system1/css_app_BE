<?php

namespace App\Repositories\Eloquents;

use App\Models\Policy;
use App\Repositories\Contracts\PolicyRepository as PolicyRepositoryContract;
use App\Repositories\Repository;
use App\Services\AI\PolicyR2Service;
use App\Support\DataAdapter\PolicyAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PolicyRepository extends Repository implements PolicyRepositoryContract
{
    public function __construct(
        protected PolicyR2Service $policyR2Service
    ) {}

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return Policy::class;
    }

    /**
     * Get a list of the policy by store_id.
     */
    public function getListByStore($storeId, array $filters = []): Collection
    {
        $constName = str($filters['category'])->upper()->append('_CATEGORY')->prepend(Policy::class.'::')->toString();
        $query = $this->queryBuilder()->where('store_id', $storeId);

        if (Arr::has($filters, 'category') && defined($constName)) {
            $query->where('category', constant($constName));
        }

        return $query->get();
    }

    /**
     * Get a list of AI recommendations.
     */
    public function getAiRecommendation($storeId, array $filters = []): Collection
    {
        return $this->policyR2Service
            ->getListRecommendByStore($storeId)
            ->map(fn ($policy) => new PolicyAdapter($policy));
    }

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array
    {
        $categories = collect(Policy::CATEGORIES)
            ->only([Policy::MEDIUM_TERM_CATEGORY, Policy::LONG_TERM_CATEGORY])
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();
        $kpis = collect(Policy::KPIS)->map(fn ($label, $value) => compact('value', 'label'))->values();
        $templates = collect(Policy::TEMPLATES)->map(fn ($label, $value) => compact('value', 'label'))->values();
        $statuses = collect(Policy::STATUSES)->map(fn ($label, $value) => compact('value', 'label'))->values();

        return compact('categories', 'kpis', 'templates', 'statuses');
    }

    /**
     * Handle delete the specified policy.
     */
    public function delete(Policy $policy): ?Policy
    {
        $policy->delete();

        return $policy;
    }
}
