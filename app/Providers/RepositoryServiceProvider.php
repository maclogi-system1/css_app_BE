<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected $repositories = [
        \App\Repositories\Contracts\UserRepository::class => \App\Repositories\Eloquents\UserRepository::class,
        \App\Repositories\Contracts\RoleRepository::class => \App\Repositories\Eloquents\RoleRepository::class,
        \App\Repositories\Contracts\CompanyRepository::class => \App\Repositories\Eloquents\CompanyRepository::class,
        \App\Repositories\Contracts\BookmarkRepository::class => \App\Repositories\Eloquents\BookmarkRepository::class,
        \App\Repositories\Contracts\UserSettingRepository::class => \App\Repositories\Eloquents\UserSettingRepository::class,
        \App\Repositories\Contracts\ChatworkRepository::class => \App\Repositories\Eloquents\ChatworkRepository::class,
        \App\Repositories\Contracts\NotificationRepository::class => \App\Repositories\Eloquents\NotificationRepository::class,
        \App\Repositories\Contracts\TeamRepository::class => \App\Repositories\Eloquents\TeamRepository::class,
        \App\Repositories\Contracts\PermissionRepository::class => \App\Repositories\Eloquents\PermissionRepository::class,
        \App\Repositories\Contracts\ShopRepository::class => \App\Repositories\APIs\ShopRepository::class,
        \App\Repositories\Contracts\AlertRepository::class => \App\Repositories\APIs\AlertRepository::class,
        \App\Repositories\Contracts\TaskRepository::class => \App\Repositories\APIs\TaskRepository::class,
        \App\Repositories\Contracts\MqAccountingRepository::class => \App\Repositories\Eloquents\MqAccountingRepository::class,
        \App\Repositories\Contracts\MqChartRepository::class => \App\Repositories\Eloquents\MqChartRepository::class,
        \App\Repositories\Contracts\PolicyRepository::class => \App\Repositories\Eloquents\PolicyRepository::class,
        \App\Repositories\Contracts\PolicyAttachmentRepository::class => \App\Repositories\Eloquents\PolicyAttachmentRepository::class,
        \App\Repositories\Contracts\JobGroupRepository::class => \App\Repositories\APIs\JobGroupRepository::class,
        \App\Repositories\Contracts\PolicySimulationHistoryRepository::class => \App\Repositories\Eloquents\PolicySimulationHistoryRepository::class,
        \App\Repositories\Contracts\SingleJobRepository::class => \App\Repositories\APIs\SingleJobRepository::class,
        \App\Repositories\Contracts\MqKpiRepository::class => \App\Repositories\Eloquents\MqKpiRepository::class,
        \App\Repositories\Contracts\MqCostRepository::class => \App\Repositories\Eloquents\MqCostRepository::class,
        \App\Repositories\Contracts\MacroConfigurationRepository::class => \App\Repositories\Eloquents\MacroConfigurationRepository::class,
        \App\Repositories\Contracts\UserTrendRepository::class => \App\Repositories\APIs\UserTrendRepository::class,
        \App\Repositories\Contracts\UserAccessRepository::class => \App\Repositories\APIs\UserAccessRepository::class,
        \App\Repositories\Contracts\ReportSearchRepository::class => \App\Repositories\APIs\ReportSearchRepository::class,
        \App\Repositories\Contracts\MacroGraphRepository::class => \App\Repositories\Eloquents\MacroGraphRepository::class,
        \App\Repositories\Contracts\AdsAnalysisRepository::class => \App\Repositories\APIs\AdsAnalysisRepository::class,
        \App\Repositories\Contracts\MacroTemplateRepository::class => \App\Repositories\Eloquents\MacroTemplateRepository::class,
        \App\Repositories\Contracts\AccessAnalysisRepository::class => \App\Repositories\APIs\AccessAnalysisRepository::class,
        \App\Repositories\Contracts\StoreChartRepository::class => \App\Repositories\APIs\StoreChartRepository::class,
        \App\Repositories\Contracts\MqSheetRepository::class => \App\Repositories\Eloquents\MqSheetRepository::class,
        \App\Repositories\Contracts\SalesAmntPerUserAnalysisRepository::class => \App\Repositories\APIs\SalesAmntPerUserAnalysisRepository::class,
        \App\Repositories\Contracts\ProductAnalysisRepository::class => \App\Repositories\APIs\ProductAnalysisRepository::class,
        \App\Repositories\Contracts\ShopSettingMqAccountingRepository::class => \App\Repositories\Eloquents\ShopSettingMqAccountingRepository::class,
        \App\Repositories\Contracts\CategoryAnalysisRepository::class => \App\Repositories\APIs\CategoryAnalysisRepository::class,
        \App\Repositories\Contracts\ShopSettingRankingRepository::class => \App\Repositories\Eloquents\ShopSettingRankingRepository::class,
        \App\Repositories\Contracts\ShopSettingAwardPointRepository::class => \App\Repositories\Eloquents\ShopSettingAwardPointRepository::class,
        \App\Repositories\Contracts\ShopSettingSearchRankingRepository::class => \App\Repositories\Eloquents\ShopSettingSearchRankingRepository::class,
        \App\Repositories\Contracts\ReviewAnalysisRepository::class => \App\Repositories\APIs\ReviewAnalysisRepository::class,
        \App\Repositories\Contracts\LinkedUserInfoRepository::class => \App\Repositories\Eloquents\LinkedUserInfoRepository::class,
        \App\Repositories\Contracts\ItemsPerformanceAnalyticsRepository::class => \App\Repositories\Eloquents\ItemsPerformanceAnalyticsRepository::class,
        \App\Repositories\Contracts\CategoriesPerformanceAnalyticsRepository::class => \App\Repositories\Eloquents\CategoriesPerformanceAnalyticsRepository::class,
        \App\Repositories\Contracts\MyPageRepository::class => \App\Repositories\Eloquents\MyPageRepository::class,
        \App\Repositories\Contracts\ValueChainRepository::class => \App\Repositories\Eloquents\ValueChainRepository::class,
        \App\Repositories\Contracts\StandardDeviationRepository::class => \App\Repositories\Eloquents\StandardDeviationRepository::class,
        \App\Repositories\Contracts\SuggestPolicyRepository::class => \App\Repositories\APIs\SuggestPolicyRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->repositories as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
