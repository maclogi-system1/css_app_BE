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
use App\Http\Controllers\Api\MqSheetController;
use App\Http\Controllers\Api\MyPageController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PolicyAttachmentController;
use App\Http\Controllers\Api\PolicyController;
use App\Http\Controllers\Api\PolicySimulationHistoryController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ShopSettingController;
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

Route::middleware(['auth:sanctum', 'dynamic_connection'])->group(function () {
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

    Route::prefix('mq-sheets')
        ->name('mq-sheets.')
        ->controller(MqSheetController::class)
        ->group(function () {
            Route::post('/clone-sheet/{mqSheet}', 'cloneSheet');

            Route::get('/', 'index')->name('index');
            Route::get('/{mqSheet}', 'show')->name('show');
            Route::post('/', 'store')->name('store');
            Route::put('/{mqSheet}', 'update')->name('update');
            Route::delete('/{mqSheet}', 'destroy')->name('destroy');
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

            // deprecated
            Route::post('/simulation/{storeId}', 'storeSimulationByStoreId')->name('store-simulation-by-store-id');

            Route::post('/run-simulation', 'runSimulation')->name('run-simulation');

            // Work Breakdown Structure
            Route::get('/wbs/{storeId}', 'workBreakdownStructure')->name('wbs');

            Route::get('/{storeId}', 'getListByStore')
                ->name('get-list-by-store');

            // deprecated
            Route::post('/{storeId}', 'storeMultipleByStoreId')->name('store-multiple-by-store-id');
        });
    Route::prefix('policies')
        ->name('policies.')
        ->controller(PolicyController::class)
        ->group(function () {
            Route::post('/', 'storeMultiple')->name('store-multiple');
            Route::post('/simulation', 'storeSimulation')->name('store-simulation');
            Route::get('/simulation/{policySimulation}/policy-data', 'getPolicyDataFromSimulation')
                ->name('simulation.policy-data');
            Route::get('/matches-simulation', 'matchesSimulation');
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
            Route::get('/chart-organic-inflows/{storeId}', 'chartOrganicInflows')->name('chart-organic-inflows');
            Route::get('/chart-inflows-via-specific-words/{storeId}', 'chartInflowsViaSpecificWords')
                ->name('chart-inflows-via-specific-words');

            Route::prefix('ads-analysis')
            ->name('ads-analysis.')
            ->group(function () {
                Route::get('/summary/{storeId}', 'adsAnalysisSummary')->name('summary');
                Route::get('/detail-ads-conversion/{storeId}', 'detailAdsConversion')->name('detail-ads-conversion');
                Route::get('/list-product-by-roas/{storeId}', 'getListProductByRoas')->name('list-product-by-roas');
                Route::get('/chart-sales-and-access/{storeId}', 'chartSalesAndAccess')->name('chart-sales-and-access');
                Route::get('/options', 'getOptions')->name('options');
            });

            Route::prefix('access-analysis')
            ->name('access-analysis.')
            ->group(function () {
                Route::get('/summary-table/{storeId}', 'tableAccessAnalysis')->name('summary-table');
                Route::post('/download-csv', 'downloadtableAccessAnalysisCsv')
                ->name('download-csv');
                Route::get('/chart-new-user-access/{storeId}', 'chartNewUserAccess')->name('chart-new-user-access');
                Route::get('/chart-exist-user-access/{storeId}', 'chartExistUserAccess')
                    ->name('chart-exist-user-access');
            });

            Route::prefix('conversion-rate-analysis')
            ->name('conversion-rate-analysis.')
            ->group(function () {
                Route::get('/chart-comparison/{storeId}', 'chartComparisonConversionRate')->name('chart-comparison');
                Route::get('/summary-table/{storeId}', 'tableConversionRateAnalysis')->name('summary-table');
                Route::get('/download-csv/{storeId}', 'downloadtableConversionRateCsv')
                ->name('download-csv');
                Route::get('/chart-relation-PV-and-conversion-rate/{storeId}', 'chartRelationPVAndConversionRate')
                    ->name('chart-relation-PV-and-conversion-rate');
            });

            Route::prefix('sales-amnt-per-user')
            ->name('sales-amnt-per-user.')
            ->group(function () {
                Route::get('/summary-graph/{storeId}', 'chartSummarySaleAmountPerUser')->name('summary');
                Route::get('/table-comparison/{storeId}', 'tableSaleAmountPerUserComparison')->name('table-comparison');
                Route::get('/download-csv/{storeId}', 'downloadtableSalesAmntPerUserCsv')
                ->name('download-csv');
                Route::get('/chart-pv-and-sales/{storeId}', 'chartPVSaleAmountPerUser')->name('chart-pv-and-sales');
            });

            Route::prefix('product-analysis')
            ->name('product-analysis.')
            ->group(function () {
                Route::get('/summary/{storeId}', 'productAnalysisSummary')->name('summary');
                Route::post('/download-csv', 'downloadtableProductsCsv')
                ->name('download-csv');
                Route::post('/chart-selected-products', 'chartSelectedProducts')->name('chart-selected-products');
                Route::post('/chart-products-trends', 'chartProductsTrends')->name('chart-selected-products-trends');
                Route::post('/chart-products-stay-times', 'chartProductsStayTimes')->name('chart-products-stay-times');
                Route::post('/chart-products-rakuten-ranking', 'chartProductsRakutenRanking')->name('chart-products-rakuten-ranking');
                Route::post('/chart-products-reviews-trends', 'chartProductsReviewsTrends')->name('chart-products-reviews-trends');
                Route::get('/get-performance-table/{storeId}', 'getPerformanceTable')->name('get-performance-table');
                Route::post('/save-performance-table/{storeId}', 'saveSalesPerformanceTable')->name('save-performance-table');
                Route::get('/get-product-sales-info', 'getProductSalesInfo')->name('get-product-sales-info');
            });

            Route::prefix('category-analysis')
            ->name('category-analysis.')
            ->group(function () {
                Route::get('/summary/{storeId}', 'categoryAnalysisSummary')->name('summary');
                Route::post('/download-csv', 'downloadtableCategoriesCsv')
                ->name('download-csv');
                Route::post('/chart-selected-categories', 'chartSelectedCategories')->name('chart-selected-categories');
                Route::post('/chart-categories-trends', 'chartCategoriesTrends')->name('chart-categories-trends');
                Route::post('/chart-categories-stay-times', 'chartCategoriesStayTimes')->name('chart-categories-stay-times');
                Route::post('/chart-categories-reviews-trends', 'chartCategoriesReviewsTrends')->name('chart-categories-reviews-trends');
                Route::get('/get-performance-table/{storeId}', 'getCategoryPerformanceTable')->name('get-performance-table');
                Route::post('/save-performance-table/{storeId}', 'saveCategorySalesPerformanceTable')->name('save-performance-table');
                Route::get('/get-category-sales-info', 'getCategorySalesInfo')->name('get-category-sales-info');
            });

            Route::prefix('review-analysis')
            ->name('review-analysis.')
            ->group(function () {
                Route::get('/summary/{storeId}', 'reviewAnalysisSummary')->name('summary');
                Route::get('/chart-reviews-trends/{storeId}', 'chartReviewsTrends')->name('chart-reviews-trends');
            });
        });

    Route::prefix('shop-settings')
        ->name('shop-settings.')
        ->controller(ShopSettingController::class)
        ->group(function () {
            Route::prefix('mq-accounting')
                ->name('mq-accounting.')
                ->group(function () {
                    Route::get('/', 'getMQAccountingSettings')->name('list');
                    Route::get('/download-template', 'downloadTemplateMQAccountingCsv')->name('download-template');
                    Route::post('/upload-csv/{storeId}', 'uploadMQAccountingCsv')->name('upload-csv');
                    Route::put('/update/{storeId}', 'updateMQAccounting')->name('update');
                });

            Route::prefix('rankings')
                ->name('rankings.')
                ->group(function () {
                    Route::get('/', 'getRankingsSettings')->name('list');
                    Route::get('/download-template', 'downloadTemplateRankingCsv')->name('download-template');
                    Route::post('/upload-csv/{storeId}', 'uploadRankingCsv')->name('upload-csv');
                    Route::put('/update/{storeId}', 'updateRankingSettings')->name('update');
                });
            Route::prefix('award-points')
                ->name('award-points.')
                ->group(function () {
                    Route::get('/', 'getAwardPointSettings')->name('list');
                    Route::get('/download-template', 'downloadTemplateAwardPointCsv')->name('download-template');
                    Route::post('/upload-csv/{storeId}', 'uploadAwardPointCsv')->name('upload-csv');
                    Route::put('/update/{storeId}', 'updateAwardPoint')->name('update');
                });
            Route::prefix('search-rankings')
                ->name('search-rankings.')
                ->group(function () {
                    Route::get('/', 'getSearchRankingsSettings')->name('list');
                    Route::get('/download-template', 'downloadTemplateSearchRankingCsv')->name('download-template');
                    Route::post('/upload-csv/{storeId}', 'uploadSearchRankingCsv')->name('upload-csv');
                    Route::put('/update/{storeId}', 'updateSearchRankingSettings')->name('update');
                });
        });

    Route::prefix('my-page')
        ->name('my-page.')
        ->group(function () {
            Route::get('/options', [MyPageController::class, 'options'])->name('options');
            Route::get('/store-profit-reference', [MyPageController::class, 'getStoreProfitReference'])->name('store-profit-reference');
            Route::get('/store-profit-table', [MyPageController::class, 'getStoreProfitTable'])->name('store-profit-table');
            Route::get('/tasks', [MyPageController::class, 'getTasks'])->name('tasks');
    });
});
