<?php

namespace App\Repositories\Eloquents;

use App\Models\Policy;
use App\Models\PolicyAttachment;
use App\Repositories\Contracts\PolicyRepository as PolicyRepositoryContract;
use App\Repositories\Repository;
use App\Services\AI\PolicyR2Service;
use App\Support\DataAdapter\PolicyAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

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
        $this->useWith(['attachments']);
        $constName = str(Arr::get($filters, 'category'))
            ->upper()
            ->append('_CATEGORY')
            ->prepend(Policy::class.'::')
            ->toString();
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
        $policy->attachments()->delete();
        $policy->delete();

        return $policy;
    }

    /**
     * Handle data validation to update/create policy.
     */
    public function handleValidation(array $data, int $index): array
    {
        $validator = Validator::make($data, $this->getValidationRules($data));

        if ($validator->fails()) {
            return [
                'error' => [
                    'index' => $index,
                    'row' => $index + 1,
                    'messages' => $validator->getMessageBag(),
                ],
            ];
        }

        return [
            'data' => $validator->validated(),
        ];
    }

    public function getValidationRules(array $data)
    {
        $rules = [
            'name' => ['required', 'max:100'],
            'category' => ['required', Rule::in(array_keys(Policy::CATEGORIES))],
            'kpi' => ['required', Rule::in(array_keys(Policy::KPIS))],
            'template' => ['required', Rule::in(array_keys(Policy::TEMPLATES))],
            'status' => ['required', Rule::in(array_keys(Policy::STATUSES))],
            'start_date' => ['required', 'date', 'after:now'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'description' => ['nullable'],
            'attachment_key' => ['nullable', 'string', 'size:16'],
        ];

        $template = Arr::get($data, 'template');

        if ($template == Policy::POINT_TEMPLATE) {
            $rules['point_rate'] = ['required', 'decimal:0,6', 'between:-999999,999999'];
            $rules['point_application_period'] = [
                'required',
                'date',
                'after_or_equal:start_date',
                'before_or_equal:end_date',
            ];
        } elseif ($template == Policy::TIME_SALE_TEMPLATE) {
            $rules['flat_rate_discount'] = ['required', 'decimal:0,6', 'between:-999999,999999'];
        }

        return $rules;
    }

    /**
     * Handle create a new policy.
     */
    public function create(array $data, string $storeId): ?Policy
    {
        return $this->handleSafely(function () use ($data, $storeId) {
            $policy = $this->model()->fill($data);
            $policy->store_id = $storeId;
            $policy->save();

            if ($attachmentKey = Arr::get($data, 'attachment_key')) {
                PolicyAttachment::where('attachment_key', $attachmentKey)
                    ->whereNull('policy_id')
                    ->update(['policy_id' => $policy->id]);
            }

            return $policy;
        }, 'Create policy');
    }
}
