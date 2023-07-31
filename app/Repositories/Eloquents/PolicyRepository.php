<?php

namespace App\Repositories\Eloquents;

use App\Models\Policy;
use App\Models\PolicyAttachment;
use App\Repositories\Contracts\PolicyRepository as PolicyRepositoryContract;
use App\Repositories\Repository;
use App\Services\AI\PolicyR2Service;
use App\Services\OSS\JobGroupService;
use App\Support\DataAdapter\PolicyAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class PolicyRepository extends Repository implements PolicyRepositoryContract
{
    public function __construct(
        protected PolicyR2Service $policyR2Service,
        protected JobGroupService $jobGroupService,
    ) {
    }

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
            ->prepend(Policy::class . '::')
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
            ->only([Policy::MEASURES_CATEGORY, Policy::PROJECT_CATEGORY])
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();
        $controlActions = collect(Policy::CONTROL_ACTIONS)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();

        return [
            'categories' => $categories,
            'control_actions' => $controlActions,
        ];
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
        $ossValidation = $this->jobGroupService->validate($this->getDataForJobGroup($data));
        $ossErrorMessages = Arr::get($ossValidation, 'data.errors.messages', []);

        if ($validator->fails() || !empty($ossErrorMessages)) {
            return [
                'error' => [
                    'index' => $index,
                    'row' => $index + 1,
                    'messages' => array_merge(
                        $validator->getMessageBag()->toArray(),
                        $ossErrorMessages,
                    ),
                ],
            ];
        }

        return [
            'policy' => $validator->validated(),
            'job_group' => $this->getDataForJobGroup($data),
        ];
    }

    public function getValidationRules(array $data)
    {
        $rules = [
            'control_actions' => ['required', Rule::in(array_keys(Policy::CONTROL_ACTIONS))],
            'category' => ['required', Rule::in(array_keys(Policy::CATEGORIES))],
            'immediate_reflection' => ['nullable', Rule::in([0, 1])],
            'attachment_key' => ['nullable', 'string', 'size:16'],
        ];

        return $rules;
    }

    public function getDataForJobGroup(array $data): array
    {
        $executionTime = Arr::get($data, 'execution_date') . ' ' . Arr::get($data, 'execution_time');

        return [
            'title' => Arr::get($data, 'job_group_title'),
            'job_group_code' => Arr::get($data, 'job_group_code'),
            'explanation' => Arr::get($data, 'job_group_explanation'),
            'job_group_start_date' => Arr::get($data, 'execution_date'),
            'job_group_start_time' => Arr::get($data, 'execution_time'),
            'job_group_end_date' => Arr::get($data, 'undo_date'),
            'job_group_end_time' => Arr::get($data, 'undo_time'),
            'execute_month' => (new Carbon($executionTime))->format('Y/m/01'),
            'managers' => preg_replace('/ *\, */', ',', Arr::get($data, 'managers', '')),
            'single_jobs' => [
                [
                    'status' => Arr::get($data, 'status'),
                    'template_id' => Arr::get($data, 'template_id'),
                    'title' => Arr::get($data, 'job_title'),
                    'immediate_reflection' => Arr::get($data, 'immediate_reflection', 0),
                    'execution_time' => $executionTime,
                    'undo_time' => Arr::get($data, 'undo_date') . ' ' . Arr::get($data, 'undo_time'),
                    'type_item_url' => Arr::get($data, 'type_item_url'),
                    'item_urls' => preg_replace('/ *\, */', ',', Arr::get($data, 'item_urls', '')),
                    'has_banner' => Arr::get($data, 'has_banner', 2),
                    'remark' => Arr::get($data, 'remark'),
                    'catch_copy_pc_text' => Arr::get($data, 'catch_copy_pc_text'),
                    'catch_copy_pc_error' => Arr::get($data, 'catch_copy_pc_error'),
                    'catch_copy_sp_text' => Arr::get($data, 'catch_copy_sp_text'),
                    'catch_copy_sp_error' => Arr::get($data, 'catch_copy_sp_error'),
                    'item_name_text' => Arr::get($data, 'item_name_text'),
                    'item_name_text_error' => Arr::get($data, 'item_name_text_error'),
                    'point_magnification' => Arr::get($data, 'point_magnification'),
                    'point_start' => Arr::get($data, 'point_start_date') . ' ' . Arr::get($data, 'point_start_time'),
                    'point_end_date' => Arr::get($data, 'point_end_date') . ' ' . Arr::get($data, 'point_end_time'),
                    'point_error' => Arr::get($data, 'point_error'),
                    'point_operational' => Arr::get($data, 'point_operational'),
                    'discount_type' => Arr::get($data, 'discount_type'),
                    'discount_rate' => Arr::get($data, 'discount_rate'),
                    'discount_price' => Arr::get($data, 'discount_price'),
                    'discount_undo_type' => Arr::get($data, 'discount_undo_type'),
                    'discount_error' => Arr::get($data, 'discount_error'),
                    'discount_display_price' => Arr::get($data, 'discount_display_price'),
                    'double_price_text' => Arr::get($data, 'double_price_text'),
                    'shipping_fee' => Arr::get($data, 'shipping_fee'),
                    'stock_specify' => Arr::get($data, 'stock_specify'),
                    'time_sale_start_date_time' => Arr::get($data, 'time_sale_start_date')
                        . ' '
                        . Arr::get($data, 'time_sale_start_time'),
                    'time_sale_end_date_time' => Arr::get($data, 'time_sale_end_date')
                        . ' '
                        . Arr::get($data, 'time_sale_end_time'),
                    'is_unavailable_for_search' => Arr::get($data, 'is_unavailable_for_search'),
                    'description_for_pc' => Arr::get($data, 'description_for_pc'),
                    'description_for_sp' => Arr::get($data, 'description_for_sp'),
                    'description_by_sales_method' => Arr::get($data, 'description_by_sales_method'),
                ],
            ],
        ];
    }

    /**
     * Handle create a new policy.
     */
    public function create(array $data, string $storeId): ?array
    {
        return $this->handleSafely(function () use ($data, $storeId) {
            $policyData = $data['policy'] + ['store_id' => $storeId];
            $policy = $this->model()->fill($policyData);
            $policy->store_id = $storeId;
            $policy->save();

            if ($attachmentKey = Arr::get($policyData, 'attachment_key')) {
                PolicyAttachment::where('attachment_key', $attachmentKey)
                    ->whereNull('policy_id')
                    ->update(['policy_id' => $policy->id]);
            }

            $result = $this->jobGroupService->create($data['job_group'] + ['store_id' => $storeId]);

            if (!$result['success']) {
                throw new Exception('Insert job_group failed.');
            }

            $jobGroup = $result['data']['job_group'];
            $singleJob = $result['data']['single_job'];

            $policy->job_group_id = $jobGroup['id'];
            $policy->single_job_id = $singleJob['id'];
            $policy->save();

            return [
                'policy' => $policy,
                'job_group' => $result['data']['job_group'],
            ];
        }, 'Create policy');
    }

    /**
     * Handle getting the start and end timestamps for job_group
     */
    public function handleStartEndTimeForJobGroup($jobGroupId, $data, array &$jobGroups): void
    {
        $dataStartDateTime = new Carbon(
            Arr::get($data, 'execution_date') . ' ' . Arr::get($data, 'execution_time')
        );
        $dataEndDateTime = new Carbon(
            Arr::get($data, 'undo_date') . ' ' . Arr::get($data, 'undo_time')
        );

        if (isset($jobGroups[$jobGroupId])) {
            $jobGroupStartDateTime = new Carbon(Arr::get($jobGroups, "{$jobGroupId}.start_date"));

            if ($jobGroupStartDateTime->gt($dataStartDateTime)) {
                Arr::set($jobGroups, "{$jobGroupId}.start_date", $dataStartDateTime);
            }

            $jobGroupEndDateTime = new Carbon(Arr::get($jobGroups, "{$jobGroupId}.end_date"));

            if ($jobGroupEndDateTime->lt($dataEndDateTime)) {
                Arr::set($jobGroups, "{$jobGroupId}.end_date", $dataEndDateTime);
            }
        } else {
            $jobGroups[$jobGroupId] = [
                'start_date' => $dataStartDateTime,
                'end_date' => $dataEndDateTime,
            ];
        }
    }

    /**
     * Handle delete multiple policies at the same time.
     */
    public function deleteMultiple(array $policyIds): ?bool
    {
        if (empty($policyIds)) {
            return null;
        }

        return $this->handleSafely(function () use ($policyIds) {
            $result = $this->model()->whereIn('id', $policyIds)->delete();

            return $result;
        }, 'Delete multiple policies');
    }
}
