<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\UpdateUserProfileInformationController;
use App\Http\Controllers\Api\Auth\UserProfileController;
use App\Http\Controllers\Api\Auth\UsersCompanyController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\ChatworkController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\JobGroupController;
use App\Http\Controllers\Api\KpiController;
use App\Http\Controllers\Api\MacroController;
use App\Http\Controllers\Api\MqAccountingController;
use App\Http\Controllers\Api\MqCostController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PolicyAttachmentController;
use App\Http\Controllers\Api\PolicyController;
use App\Http\Controllers\Api\PolicySimulationHistoryController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ShopUserController;
use App\Http\Controllers\Api\SingleJobController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserSettingController;
use Illuminate\Support\Facades\Route;

Route::any('/test', fn (\Illuminate\Http\Request $request) => ['headers' => $request->header(), 'body' => $request->all()]);
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/send-password-reset-link', [PasswordController::class, 'sendPasswordResetLink'])
    ->name('send-password-reset-link');
Route::post('/password-reset-token', [PasswordController::class, 'getPasswordResetToken'])
    ->name('password-reset-token');
Route::post('/reset-password', [PasswordController::class, 'reset'])
    ->name('reset-password');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/', UserProfileController::class)->name('profile');
        Route::put('/', [UpdateUserProfileInformationController::class, 'update'])->name('update-user-profile-info');
        Route::patch('/update-password', [PasswordController::class, 'update'])->name('update-password');
        Route::post('/upload-photo', [UpdateUserProfileInformationController::class, 'uploadProfilePhoto'])
            ->name('update-profile-photo');
        Route::get('/company', [UsersCompanyController::class, 'show'])->name('company.show');
        Route::put('/company', [UsersCompanyController::class, 'update'])->name('company.update');
    });

    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::delete('/delete-multiple', 'deleteMultiple')->name('delete-multiple');
        Route::get('/search', 'search')->name('search');
        Route::get('/options', 'getOptions')->name('options');
        Route::post('/{user}', 'update')->name('update');
    });
    Route::apiResource('users', UserController::class)->except(['update']);

    Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {
        Route::get('/search', [RoleController::class, 'search'])->name('search');
        Route::delete('/delete-multiple', 'deleteMultiple')->name('delete-multiple');
    });
    Route::apiResource('roles', RoleController::class);

    Route::prefix('permissions')->name('permissions.')->controller(PermissionController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/search', 'search')->name('search');
        Route::patch('/{permission}', 'update')->name('update');
    });

    Route::prefix('bookmarks')->name('bookmarks.')->controller(BookmarkController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/bookmark', 'bookmark')->name('bookmark');
        Route::post('/unbookmark', 'unbookmark')->name('unbookmark');
    });

    Route::prefix('companies')->name('companies.')->controller(CompanyController::class)->group(function () {
        Route::patch('/{id}/restore', 'restore')->name('restore');
        Route::delete('/{id}/force', 'forceDelete')->name('force-delete');
        Route::get('/search', 'search')->name('search');
    });
    Route::apiResource('companies', CompanyController::class);

    Route::get('/user-settings', [UserSettingController::class, 'index'])->name('user-settings.index');
    Route::patch('/user-settings', [UserSettingController::class, 'update'])->name('user-settings.update');

    Route::prefix('chatwork')->name('chatwork.')->controller(ChatworkController::class)->group(function () {
        Route::get('/room/{roomId}', 'roomDetail')->name('room.detail');
        Route::get('/room/{roomId}/members', 'roomMembers')->name('room.members');
        Route::post('/send-message/{roomId}', 'sendMessage')->name('send-message');
    });

    Route::prefix('teams')->name('teams.')->controller(TeamController::class)->group(function () {
        Route::get('/company/{company}', 'getListByCompany')->name('get-list-by-company');
        Route::get('/search', 'search')->name('search');
    });
    Route::apiResource('teams', TeamController::class);

    Route::prefix('mq-accounting')
        ->name('mq-accounting.')
        ->controller(MqAccountingController::class)
        ->group(function () {
            Route::get('/download-template', 'downloadTemplateCsv')
                ->name('download-template')
                ->withoutMiddleware('auth:sanctum');
            Route::get('/download-csv/{storeId}', 'downloadMqAccountingCsv')
                ->name('download-csv');
            Route::get('/download-csv-selection/{storeId}', 'downloadMqAccountingCsvSelection')
                ->name('download-csv-selection');
            Route::post('/upload-csv/{storeId}', 'uploadMqAccountingCsv')->name('upload-csv');
            Route::get('/chart-financial-indicators-monthly/{storeId}', 'financialIndicatorsMonthly');
            Route::get('/chart-cumulative-change-in-revenue-profit/{storeId}', 'cumulativeChangeInRevenueAndProfit');

            Route::get('/{storeId}', 'getListByStore')->name('get-list-by-store');
            Route::put('/{storeId}', 'updateByStore')->name('update-by-store');
            Route::get('/total/{storeId}', 'getTotalParamByStore')->name('get-total-param-by-store');

            Route::get('/get-forecast-vs-actual/{storeId}', 'getForecastVsActual')->name('get-forecast-vs-actual');
            Route::get('/get-comparative-analysis/{storeId}', 'getComparativeAnalysis')->name('get-comparative-analysis');
        });

    Route::prefix('mq-cost')
        ->name('mq-cost.')
        ->controller(MqCostController::class)
        ->group(function () {
            Route::get('/ad-cost/{storeId}', 'getAdCostByStore')->name('get-ad-cost-by-store');
        });

    Route::prefix('policy')
        ->name('policy.')
        ->controller(PolicyController::class)
        ->group(function () {
            Route::get('/download-template/{storeId}', 'downloadTemplateCsv')
                ->name('download-template');

            Route::get('/simulation/{policySimulation}', 'showSimulation')->name('show-simulation');
            Route::put('/simulation/{policySimulation}', 'updateSimulation')->name('update-simulation');

            Route::delete('/delete-multiple', 'deleteMultiple')->name('delete-multiple');
            Route::delete('/{policy}', 'destroy')->name('destroy');
            Route::get('/ai-recommendation/{storeId}', 'getAiRecommendationByStore')
                ->name('ai-recommendation-by-store');
            Route::get('/options', 'getOptions')->name('get-options')->withoutMiddleware('auth:sanctum');
            Route::get('/detail/{policy}', 'show')->name('show');

            Route::post('/simulation/{storeId}', 'storeSimulation')->name('store-simulation');
            Route::post('/run-simulation', 'runSimulation')->name('run-simulation');

            // Work Breakdown Structure
            Route::get('/wbs/{storeId}', 'workBreakdownStructure')->name('wbs');

            Route::get('/{storeId}', 'getListByStore')
                ->name('get-list-by-store');
            Route::post('/{storeId}', 'storeMultiple')->name('store-multiple');
        });

    Route::prefix('policy-simulation-histories')
        ->name('policy-simulation-histories.')
        ->controller(PolicySimulationHistoryController::class)
        ->group(function () {
            Route::get('/{storeId}', 'getListByStore')->name('get-list-by-store');
        });

    Route::prefix('policy-attachments')
        ->name('policy-attachments.')
        ->controller(PolicyAttachmentController::class)
        ->group(function () {
            Route::get('/generate-key', 'generateKey')->name('generate-key');
            Route::post('/upload', 'upload')->name('upload');
            Route::delete('/remove-multiple', 'removeMultiple')->name('remove-multiple');
            Route::delete('/{policyAttachment}', 'remove')->name('remove');
        });

    Route::prefix('job-groups')
        ->name('job-groups.')
        ->controller(JobGroupController::class)
        ->group(function () {
            Route::get('/{storeId}', 'getListByStore')
                ->name('get-list-by-store');
        });

    Route::prefix('single-jobs')
        ->name('single-jobs.')
        ->controller(SingleJobController::class)
        ->group(function () {
            Route::get('/{storeId}', 'getListByStore')
                ->name('get-list-by-store');
        });

    Route::get('/shop-users/options/{storeId?}', [ShopUserController::class, 'getOptions'])->name('shop-users.options');

    // Implement for macros
    Route::prefix('macros')
        ->name('macros.')
        ->controller(MacroController::class)
        ->group(function () {
            Route::get('/list-table', 'getListTable')->name('get-list-table');
            Route::get('/query-results/{macroConfiguration}', 'getQueryResults')->name('query-results');
            Route::post('/run/{macroConfiguration}', 'run')->name('run');
            Route::post('/query-conditions-results', 'getQueryConditionsResults')->name('query-conditions-results');

            Route::prefix('macro-configuration')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'store')->name('configuration.store');
                    Route::get('/options', 'getOptions')->name('configuration.options');
                    Route::get('/{macroConfiguration}', 'show')->name('configuration.show');
                    Route::put('/{macroConfiguration}', 'update')->name('configuration.update');
                    Route::delete('/{macroConfiguration}', 'destroy')->name('configuration.destroy');
                });

            Route::get('/keywords', 'getKeywords')->name('get-keywords');
        });

    Route::prefix('kpi')
        ->name('kpi.')
        ->controller(KpiController::class)
        ->group(function () {
            Route::get('/summary/{storeId}', 'summary')->name('summary');
            Route::get('/chart-user-trends/{storeId}', 'chartUserTrends')->name('chart-user-trends');
            Route::get('/total-user-access/{storeId}', 'totalUserAccess')->name('total-user-access');
            Route::get('/chart-user-access/{storeId}', 'chartUserAccess')->name('chart-user-access');
            Route::get('/chart-user-access-ads/{storeId}', 'chartUserAccessAds')->name('chart-user-access-ads');
            Route::get('/chart-access-source/{storeId}', 'chartAccessSource')->name('chart-access-source');
            Route::get('/table-access-source/{storeId}', 'tableAccessSource')->name('table-access-source');
            Route::get('/chart-report-search/{storeId}', 'chartReportSearch')->name('chart-report-search');
            Route::get('/table-report-search/{storeId}', 'tableReportSearch')->name('table-report-search');
            Route::get('/detail-report-by-product/{storeId}', 'detailReportSearchByProduct')
                ->name('detail-report-by-product');
            Route::get('/chart-macro-graph/{storeId}', 'chartMacroGraph')->name('chart-macro-graph');

            Route::prefix('ads-analysis')
            ->name('ads-analysis.')
            ->group(function () {
                Route::get('/summary/{storeId}', 'adsAnalysisSummary')->name('summary');
                Route::get('/detail-ads-conversion/{storeId}', 'detailAdsConversion')->name('detail-ads-conversion');
                Route::get('/list-product-by-roas/{storeId}', 'getListProductByRoas')->name('list-product-by-roas');
                Route::get('/chart-sales-and-access/{storeId}', 'chartSalesAndAccess')->name('chart-sales-and-access');
            });
        });
});
