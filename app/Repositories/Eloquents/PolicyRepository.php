<?php

namespace App\Repositories\Eloquents;

use App\Jobs\RunPolicySimulation;
use App\Models\Policy;
use App\Models\PolicyAttachment;
use App\Models\PolicyRule;
use App\Repositories\Contracts\PolicyAttachmentRepository;
use App\Repositories\Contracts\PolicyRepository as PolicyRepositoryContract;
use App\Repositories\Repository;
use App\Services\AI\PolicyR2Service;
use App\Services\OSS\JobGroupService;
use App\Services\OSS\SingleJobService;
use App\Support\DataAdapter\PolicyAdapter;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PolicyRepository extends Repository implements PolicyRepositoryContract
{
    public function __construct(
        protected PolicyR2Service $policyR2Service,
        protected JobGroupService $jobGroupService,
        protected SingleJobService $singleJobService,
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
    public function getListByStore($storeId, array $filters = []): Collection|LengthAwarePaginator
    {
        $this->enableUseWith(['attachments', 'rules'], $filters);
        $page = Arr::get($filters, 'page', 1);
        $perPage = Arr::get($filters, 'per_page', 10);
        $category = Arr::has($filters, 'category') ? str(Arr::get($filters, 'category')) : null;

        if (Arr::hasAny($filters, ['keyword', 'from_date', 'to_date'])) {
            return $this->getListBySingleJob($storeId, $filters);
        }

        $query = $this->handleFilterCategory($this->queryBuilder()->where('store_id', $storeId), $category);

        if ($perPage < 0) {
            return $query->get();
        }

        $policies = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        $singleJobIds = Arr::pluck($policies->items(), 'single_job_id');
        $singleJobs = $this->singleJobService->getList([
            'store_id' => $storeId,
            'filters' => [
                'single_jobs.id' => implode(',', $singleJobIds),
            ],
            'with' => ['job_group.jobGroupAssignee'],
            'per_page' => -1,
        ]);

        if ($singleJobs->get('success')) {
            $singleJobData = $singleJobs->get('data')->get('single_jobs');

            foreach ($policies->items() as $item) {
                $singleJobMatches = array_filter(
                    $singleJobData,
                    fn ($sj) => Arr::get($sj, 'id') == $item->single_job_id,
                );
                $item->single_job = reset($singleJobMatches);
            }
        }

        return $policies;
    }

    /**
     * Get category key from filters and handle query.
     */
    private function handleFilterCategory(Builder $query, $category): Builder
    {
        if (! is_null($category)) {
            $constName = $category->upper()
                ->append('_CATEGORY')
                ->prepend(Policy::class.'::')
                ->toString();

            if (defined($constName)) {
                $query->where('category', constant($constName));
            }

            if ($category->toString() == Policy::SIMULATION_CATEGORY) {
                $query->where('processing_status', '!=', Policy::RUNNING_PROCESSING_STATUS);
            }
        }

        return $query;
    }

    /**
     * Get the single_job list and handle the data filtering for the policies.
     */
    private function getListBySingleJob(string $storeId, array $filters): Collection|LengthAwarePaginator
    {
        $result = $this->singleJobService->getList($filters + ['store_id' => $storeId, 'per_page' => -1]);
        $category = Arr::has($filters, 'category') ? str(Arr::get($filters, 'category')) : null;

        if ($result->get('success')) {
            $singleJobs = collect($result->get('data')->get('single_jobs'));
            $singleJobIds = $singleJobs->pluck('id')->unique();

            $policies = $this->handleFilterCategory($this->queryBuilder(), $category)
                ->whereIn('single_job_id', $singleJobIds)
                ->paginate();

            foreach ($policies->items() as $item) {
                $singleJobMatches = $singleJobs->filter(fn ($sj) => Arr::get($sj, 'id') == $item->single_job_id);
                $item->single_job = $singleJobMatches->first();
            }

            return $policies;
        }

        return $result->get('data');
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
     * Find a specified policy.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?Policy
    {
        $this->enableUseWith(['attachments', 'rules'], $filters);
        $category = str(Arr::get($filters, 'category'));

        $constName = $category->upper()
            ->append('_CATEGORY')
            ->prepend(Policy::class.'::')
            ->toString();
        $query = $this->queryBuilder()->where('id', $id);

        if (Arr::has($filters, 'category')) {
            if (defined($constName)) {
                $query->where('category', constant($constName));
            }

            if ($category->toString() == Policy::SIMULATION_CATEGORY) {
                $query->where('processing_status', '!=', Policy::RUNNING_PROCESSING_STATUS);
            }
        }

        $policy = $query->first($columns);

        if (is_null($policy)) {
            return null;
        }

        if (! is_null($policy->single_job_id)) {
            $singleJob = $this->singleJobService->find($policy->single_job_id, ['store_id' => $policy->store_id]);

            if ($singleJob->get('success')) {
                $policy->single_job = $singleJob->get('data')->get('data');
            }
        }

        return $policy;
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
        $textInputConditions = collect(PolicyRule::TEXT_INPUT_CONDITIONS)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();
        $uploadableConditions = collect(PolicyRule::UPLOADABLE_CONDITIONS)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();
        $policyRuleClasses = collect(PolicyRule::CLASSES)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();
        $policyRuleServices = collect(PolicyRule::SERVICES)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();

        return $this->singleJobService->getOptions()->get('data')->merge([
            'control_actions' => $controlActions,
            'categories' => $categories,
            'policy_rule_classes' => $policyRuleClasses,
            'policy_rule_services' => $policyRuleServices,
            'conditions_1' => $textInputConditions,
            'conditions_2' => $uploadableConditions,
            'conditions_3' => $textInputConditions,
        ])->toArray();
    }

    /**
     * Handle delete the specified policy.
     */
    public function delete(Policy $policy): ?Policy
    {
        return $this->handleSafely(function () use ($policy) {
            $policy->rules()->delete();
            app(PolicyAttachmentRepository::class)->deleteMultiple($policy->attachments->pluck('id'));

            if (! is_null($policy->single_job_id)) {
                $this->singleJobService->delete($policy->single_job_id);
            }

            $policy->delete();

            return $policy;
        }, 'Delete policy');
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
            $policies = $this->model()->whereIn('id', $policyIds)->get();

            foreach ($policies as $policy) {
                $this->delete($policy);
            }

            return true;
        }, 'Delete multiple policies');
    }

    /**
     * Handle data validation to update/create policy.
     */
    public function handleValidation(array $data, int $index): array
    {
        $validator = Validator::make($data, $this->getValidationRules($data));

        if (Arr::get($data, 'control_actions') == Policy::EDIT_ACTION) {
            $ossValidation = $this->jobGroupService->validateUpdate($this->getDataForJobGroup($data));
        } else {
            $ossValidation = $this->jobGroupService->validateCreate($this->getDataForJobGroup($data));
        }

        $ossErrorMessages = Arr::get($ossValidation, 'data.errors.messages', []);

        if ($validator->fails() || ! empty($ossErrorMessages)) {
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

    /**
     * Get the policy input validation rules.
     */
    public function getValidationRules(array $data): array
    {
        $rules = [
            'control_actions' => ['required', Rule::in(array_keys(Policy::CONTROL_ACTIONS))],
            'policy_id' => [
                Rule::requiredIf(Arr::get($data, 'control_actions', Policy::CREATE_ACTION) == Policy::EDIT_ACTION),
                Rule::exists('policies', 'id'),
            ],
            'category' => ['required', Rule::in(array_keys(Policy::CATEGORIES))],
            'immediate_reflection' => ['nullable', Rule::in([0, 1])],
            'attachment_key' => ['nullable', 'string', 'size:16'],
        ];

        return $rules;
    }

    /**
     * Get the data and parse it into a data structure for job_group.
     */
    public function getDataForJobGroup(array $data): array
    {
        $executionTime = Arr::get($data, 'execution_date').' '.Arr::get($data, 'execution_time');
        $policy = null;

        if (Arr::get($data, 'control_actions') == Policy::EDIT_ACTION && ($policyId = Arr::get($data, 'policy_id'))) {
            $policy = $this->model()->find($policyId);
        }

        return [
            'job_group_title' => Arr::get($data, 'job_group_title'),
            'job_group_code' => Arr::get($data, 'job_group_code'),
            'job_group_explanation' => Arr::get($data, 'job_group_explanation'),
            'job_group_start_date' => Arr::get($data, 'execution_date'),
            'job_group_start_time' => Arr::get($data, 'execution_time'),
            'job_group_end_date' => Arr::get($data, 'undo_date'),
            'job_group_end_time' => Arr::get($data, 'undo_time'),
            'execute_month' => (new Carbon($executionTime))->format('Y/m/01'),
            'managers' => Arr::get($data, 'managers', []),
            'store_id' => Arr::get($data, 'store_id'),
            'status' => Arr::get($data, 'status'),
            'single_jobs' => [
                [
                    'id' => $policy ? $policy->single_job_id : null,
                    'uuid' => $policy ? null : (string) str()->uuid(),
                    'template_id' => Arr::get($data, 'template_id'),
                    'title' => Arr::get($data, 'job_title'),
                    'immediate_reflection' => Arr::get($data, 'immediate_reflection', 0),
                    'execution_date' => Arr::get($data, 'execution_date'),
                    'execution_time' => Arr::get($data, 'execution_time'),
                    'undo_date' => Arr::get($data, 'undo_date'),
                    'undo_time' => Arr::get($data, 'undo_time'),
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
                    'point_start_date' => Arr::get($data, 'point_start_date'),
                    'point_start_time' => Arr::get($data, 'point_start_time'),
                    'point_end_date' => Arr::get($data, 'point_end_date'),
                    'point_end_time' => Arr::get($data, 'point_end_time'),
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
                    'time_sale_start_date' => Arr::get($data, 'time_sale_start_date'),
                    'time_sale_start_time' => Arr::get($data, 'time_sale_start_time'),
                    'time_sale_end_date' => Arr::get($data, 'time_sale_end_date'),
                    'time_sale_end_time' => Arr::get($data, 'time_sale_end_time'),
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

            $result = $this->jobGroupService->create($data['job_group']);

            if (! $result->get('success')) {
                throw new Exception('Insert job_group failed. '.$result->get('data')->get('message'));
            }

            $singleJobs = $result->get('data')->get('single_jobs');
            $singleJob = reset($singleJobs);
            $jobGroupId = $singleJob['job_group_id'];

            $policy->job_group_id = $jobGroupId;
            $policy->single_job_id = $singleJob['id'];
            $policy->save();

            return [
                'policy' => $policy,
                'job_group_id' => $jobGroupId,
            ];
        }, 'Create policy');
    }

    /**
     * Handle create a new simulation policy.
     */
    public function createSimulation(array $data, string $storeId): ?Policy
    {
        return $this->handleSafely(function () use ($data, $storeId) {
            $simulationStartDate = new Carbon($data['simulation_start_date'].' '.$data['simulation_start_time']);
            $simulationEndDate = new Carbon($data['simulation_end_date'].' '.$data['simulation_end_time']);

            $policySimulation = $this->model()->fill([
                'store_id' => $storeId,
                'name' => $data['name'],
                'category' => Policy::SIMULATION_CATEGORY,
                'simulation_start_date' => $simulationStartDate,
                'simulation_end_date' => $simulationEndDate,
                'simulation_promotional_expenses' => $data['simulation_promotional_expenses'],
                'simulation_store_priority' => $data['simulation_store_priority'],
                'simulation_product_priority' => $data['simulation_product_priority'],
            ]);
            $policySimulation->save();

            if (! empty($policyRules = Arr::get($data, 'policy_rules', []))) {
                foreach ($policyRules as $policyRule) {
                    $this->handleCondition(1, $policyRule);
                    $this->handleCondition(2, $policyRule);
                    $this->handleCondition(3, $policyRule);
                    $policySimulation->rules()->create($policyRule);
                }
            }

            return $policySimulation->withAllRels();
        }, 'Create simulation policy');
    }

    /**
     * Handle update a specified policy.
     */
    public function update(array $data, ?Policy $policy): ?bool
    {
        return $this->handleSafely(function () use ($data, $policy) {
            $policyData = $data['policy'];
            $policy->fill($policyData);
            $policy->save();

            $jobGroupData = $data['job_group'];
            $result = $this->jobGroupService->update($jobGroupData, $jobGroupData['job_group_code']);

            if (! $result->get('success')) {
                throw new Exception('Update job_group failed. '.$result->get('data')->get('message'));
            }

            return true;
        }, 'Update policy');
    }

    /**
     * Handle update a specified policy simulation.
     */
    public function updateSimulation(array $data, Policy $policySimulation): ?Policy
    {
        return $this->handleSafely(function () use ($data, $policySimulation) {
            $policySimulation->fill($data)->save();

            if (! empty($policyRules = Arr::get($data, 'policy_rules', []))) {
                $policySimulation->rules()->delete();
                foreach ($policyRules as $policyRule) {
                    $this->handleCondition(1, $policyRule);
                    $this->handleCondition(2, $policyRule);
                    $this->handleCondition(3, $policyRule);
                    $policySimulation->rules()->create($policyRule);
                }
            }

            return $policySimulation->withAllRels();
        }, 'Update policy simulation');
    }

    /**
     * Handle the condition's data.
     */
    private function handleCondition(int $conditionNumber, array &$policyRule): void
    {
        $conditionName = "condition_{$conditionNumber}";
        $conditionValue = "condition_value_{$conditionNumber}";
        $value = [];

        if ($attachmentKey = Arr::get($policyRule, "attachment_key_{$conditionNumber}")) {
            $policyAttachment = PolicyAttachment::where('attachment_key', $attachmentKey)
                ->where('type', PolicyAttachment::TEXT_TYPE)
                ->where('created_at', '>=', now()->subDay())
                ->whereNull('policy_id')
                ->first();

            if (! is_null($policyAttachment)) {
                $fileContent = file(storage_path('app/public/'.$policyAttachment->path));

                foreach ($fileContent as $content) {
                    $value[] = str_replace([',', ' ', "\n"], '', $content);
                }

                if (Storage::disk($policyAttachment->disk)->exists($policyAttachment->path)) {
                    Storage::disk($policyAttachment->disk)->delete($policyAttachment->path);
                    $policyAttachment->delete();
                }
            }
        }

        if (Arr::get($policyRule, $conditionName) == PolicyRule::SHIPPING_CONDITION) {
            $valueString = str_replace(["\n", ', '], ',', Arr::get($policyRule, $conditionValue, ''));
            $value = array_merge($value, explode(',', $valueString));
            $policyRule[$conditionValue] = implode(',', array_unique($value));
        }
    }

    /**
     * Run policy simulation.
     */
    public function runSimulation(array $data)
    {
        $policy = $this->model()->find($data['policy_id']);
        $policy->processing_status = Policy::RUNNING_PROCESSING_STATUS;
        $policy->save();

        RunPolicySimulation::dispatch($policy);
    }

    /**
     * Get list of work breakdown structure.
     */
    public function workBreakdownStructure(string $storeId, array $filters)
    {
        $jobGroupIds = null;

        if ($policyCategory = Arr::get($filters, 'policy_category')) {
            $jobGroupIds = $this->model()
                ->where('category', $policyCategory)
                ->distinct('job_group_id')
                ->pluck('job_group_id')
                ->join(',');

            if (empty($jobGroupIds)) {
                return collect([
                    'data' => [
                        'work_breakdown_structure' => [],
                    ],
                    'success' => true,
                ]);
            }
        }

        $result = $this->singleJobService->getSchedule($filters + [
            'store_id' => $storeId,
            'job_group_ids' => $jobGroupIds,
        ]);

        if ($result->get('success')) {
            $jobGroupSchedule = [];

            foreach ($result->get('data') as $jobGroupId => $schedules) {
                $jobGroupSchedule[] = [
                    'job_group_id' => $jobGroupId,
                    'job_group_title' => Arr::first($schedules)['job_group_title'],
                    'schedules' => $schedules,
                ];
            }

            $result->put('data', ['work_breakdown_structure' => $jobGroupSchedule]);
        }

        return $result;
    }
}
