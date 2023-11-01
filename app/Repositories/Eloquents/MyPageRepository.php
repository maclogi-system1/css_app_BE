<?php

namespace App\Repositories\Eloquents;

use App\Constants\DatabaseConnectionConstant;
use App\Repositories\Contracts;
use App\Repositories\Contracts\LinkedUserInfoRepository;
use App\Repositories\Contracts\MyPageRepository as MyPageRepositoryContract;
use App\Repositories\Contracts\ShopRepository;
use App\Repositories\Contracts\TaskRepository;
use App\Repositories\Repository;
use App\WebServices\MyPageService;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MyPageRepository extends Repository implements MyPageRepositoryContract
{
    public function __construct(
        protected MyPageService $myPageService,
        protected Contracts\MqKpiRepository $mqKpiRepository
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return '';
    }

    public function getOptions(): array
    {
        $genres = [
            'レディースファッション',
            'メンズファッション',
            '靴',
            'バッグ・小物・ブランド雑貨',
            '下着・ナイトウェア',
            'ジュエリー・アクセサリー',
            '腕時計',
            '食品',
            'スイーツ・お菓子',
            '水・ソフトドリンク',
            'ビール・洋酒',
            '日本酒・焼酎',
            'スマートフォン・タブレット',
            'パソコン・周辺機器',
            'TV・オーディオ・カメラ',
            '家電',
            '光回線・モバイル通信',
            'インテリア・寝具・収納',
            '日用品雑貨・文房具・手芸',
            'キッチン用品・食器',
            'ダイエット・健康',
            '医薬品・コンタクト・介護',
            '美容・コスメ・香水',
            'スポーツ・アウトドア',
            '花・ガーデン・DIY',
            'おもちゃ',
            '楽器・音響機器',
            'ホビー',
            'テレビゲーム',
            'CD・DVD',
            '車用品・バイク用品',
            'ペット・ペットグッズ',
            'キッズ・ベビー・マタニティ',
            'ゴルフ',
            '本・雑誌・コミック',
            'サービス・リフォーム',
            'カタログギフト・チケット',
        ];

        return [
            'store_group' => [
                [
                    'label' => 'すべて',
                    'value' => 'all',
                ],
                [
                    'label' => 'チーム',
                    'value' => 'team',
                ],
                [
                    'label' => '担当店舗',
                    'value' => 'store_in_charge',
                ],
            ],
            'store_genres' => array_map(function ($genre) {
                return [
                    'label' => $genre,
                    'value' => $genre,
                ];
            }, $genres),
        ];
    }

    public function getStoreProfitReference(array $params): Collection
    {
        $params = $this->prepareDataStoreProfit($params);

        $result = $this->myPageService->getStoreProfitReference($params);
        if (! $result->get('success')) {
            return $result;
        }

        $storeProfit = $result->get('data')->toArray();
        $storeIds = $result->get('data')->get('store_ids');
        unset($storeProfit['store_ids']);

        $shopAnalyticsYesterday = $this->getShopAnalyticsByDate($storeIds, now()->subDays());

        $shopAnalyticsCurrentMonth = $this->getShopAnalyticsCurrentMonth($storeIds, now());

        $shopAnalyticsCurrentMonthMQ = $this->getShopAnalyticsCurrentMonthMQ($storeIds, now());

        $totalSalesCurrentTheMonth = (int) ($shopAnalyticsCurrentMonth->total_sales ?? 0);
        $totalSalesCurrentTheMonthMQ = (int) ($shopAnalyticsCurrentMonthMQ->total_sales ?? 0);

        return $result->put('data', array_merge($storeProfit, [
            'total_store' => count($storeIds),
            'total_sales_the_previous_day' => (int) ($shopAnalyticsYesterday->total_sales ?? 0),
            'total_sales_the_month' => $totalSalesCurrentTheMonth,
            'total_sales_achievement_rate' => $totalSalesCurrentTheMonthMQ
                ? round(100 * $totalSalesCurrentTheMonth / $totalSalesCurrentTheMonthMQ, 2)
                : 0,
        ]));
    }

    public function getStoreProfitTable(array $params): Collection
    {
        $params = $this->prepareDataStoreProfit($params);

        $result = $this->myPageService->getStoreProfitTable($params);
        if (! $result->get('success')) {
            return $result;
        }

        $shops = collect($result->get('data')->get('shops'))->map(function ($shop) {
            $storeIds = [$shop['store_id']];
            $totalSalesThePreviousDay = (int) ($this->getShopAnalyticsByDate(
                $storeIds,
                now()->subDays()
            )?->total_sales ?? 0);

            $totalSalesToday = (int) ($this->getShopAnalyticsByDate(
                $storeIds,
                now()
            )?->total_sales ?? 0);
            $totalSalesSameDayLastYear = (int) ($this->getShopAnalyticsByDate(
                $storeIds,
                now()->subYear()
            )?->total_sales ?? 0);

            $totalSalesTheMonth = (int) ($this->getShopAnalyticsCurrentMonth($storeIds, now())?->total_sales ?? 0);
            $totalSalesTheLastYear = (int) ($this->getShopAnalyticsCurrentMonth($storeIds, now()->subYears(), true)?->total_sales ?? 0);
            $totalSalesCurrentTheMonthMQ = (int) ($this->getShopAnalyticsCurrentMonthMQ($storeIds, now())?->total_sales ?? 0);
            $shopCost = $this->getShopCostByDate($storeIds, now())->first();
            $salesForecast = (int) ($shop['sales_forecast'] ?? 0);

            $extraData = [
                'total_sales_the_previous_day' => $totalSalesThePreviousDay,
                'total_sales_the_month' => $totalSalesTheMonth,
                'total_sales_the_last_year' => $totalSalesTheLastYear,
                'total_sales_achievement_rate' => $totalSalesCurrentTheMonthMQ
                    ? round(100 * $totalSalesTheMonth / $totalSalesCurrentTheMonthMQ, 2)
                    : 0,
                'variable_cost_sum' => $shopCost->variable_cost_sum ?? 0,
                'cost_sum' => $shopCost->cost_sum ?? 0,
                'sum_profit' => $shopCost->sum_profit ?? 0,
                'same_day_last_year_rate' => $totalSalesToday - $totalSalesSameDayLastYear,
                'sales_target' => $this->mqKpiRepository->getKPIByDate($shop['store_id'], now())?->sales_amnt ?? 0,
                'sales_forecast' => $salesForecast,
                'sales_consumption_rate' => ($salesForecast * $totalSalesTheMonth)
                    ? round(100 * $totalSalesToday / $salesForecast * $totalSalesTheMonth, 2)
                    : 0,
            ];

            return array_merge($shop, $extraData);
        });

        $result->get('data')->put(
            'shops',
            $this->getShopRepository()->convertCssUserByOssUser($shops)
        );

        return $result;
    }

    public function getLinkedUserInfoRepository(): LinkedUserInfoRepository
    {
        return app(LinkedUserInfoRepository::class);
    }

    public function getShopRepository(): ShopRepository
    {
        return app(ShopRepository::class);
    }

    public function getTaskRepository(): TaskRepository
    {
        return app(TaskRepository::class);
    }

    public function getUserRepository() : Contracts\UserRepository
    {
        return app(Contracts\UserRepository::class);
    }

    public function getShopAnalyticsByDate(Collection|array $storeIds, Carbon $date): object
    {
        return DB::connection(DatabaseConnectionConstant::KPI_CONNECTION)
            ->table('shop_analytics_daily as sad')
            ->join('shop_analytics_daily_sales_amnt as sadsa', function (JoinClause $join) use ($storeIds, $date) {
                $join->on('sad.sales_amnt_id', '=', 'sadsa.sales_amnt_id')
                    ->whereIn('sad.store_id', $storeIds)
                    ->whereRaw("STR_TO_DATE(`sad`.`date`, '%Y%m%d') = ?", [$date->format('Y-m-d')]);
            })
            ->selectRaw('SUM(sadsa.all_value) as total_sales')
            ->first();
    }

    public function getShopAnalyticsCurrentMonth($storeIds, Carbon $monthYear, bool $isYearly = false): object
    {
        return DB::connection(DatabaseConnectionConstant::KPI_CONNECTION)
            ->table('shop_analytics_monthly as sam')
            ->join('shop_analytics_monthly_sales_amnt as samsa', function (JoinClause $join) use ($storeIds, $monthYear, $isYearly) {
                $join->on('sam.sales_amnt_id', '=', 'samsa.sales_amnt_id')
                    ->whereIn('sam.store_id', $storeIds);

                $date = $monthYear->format('Y-m');
                $format = '%Y-%m';
                if ($isYearly) {
                    $date = $monthYear->format('Y');
                    $format = '%Y';
                }

                $join->whereRaw("DATE_FORMAT(CONCAT(`sam`.`date`, '01'), '{$format}') = ?", [$date]);
            })->selectRaw('SUM(samsa.all_value) as total_sales')
            ->first();
    }

    public function getShopAnalyticsCurrentMonthMQ(array $storeIds, Carbon $date): object
    {
        return DB::connection(DatabaseConnectionConstant::KPI_CONNECTION)
            ->table('mq_accounting as ma')
            ->join('mq_kpi as mk', function (JoinClause $join) use ($storeIds, $date) {
                $join->on('ma.mq_kpi_id', '=', 'mk.mq_kpi_id')
                    ->whereIn('ma.store_id', $storeIds)
                    ->where('ma.year', $date->year)
                    ->where('ma.month', $date->month);
            })->selectRaw('SUM(mk.sales_amnt) as total_sales')
            ->first();
    }

    public function getShopCostByDate(array $storeIds, Carbon $date): Collection
    {
        return DB::connection(DatabaseConnectionConstant::KPI_CONNECTION)
            ->table('mq_accounting as ma')
            ->join('mq_cost as mc', function (JoinClause $join) use ($storeIds, $date) {
                $join->on('ma.mq_cost_id', '=', 'mc.mq_cost_id')
                    ->whereIn('ma.store_id', $storeIds)
                    ->where('ma.year', $date->year)
                    ->where('ma.month', $date->month);
            })->get();
    }

    public function prepareDataStoreProfit(array $params): array
    {
        $manager = array_filter(explode(',', Arr::get($params, 'manager', '')));
        $storeGroup = Arr::get($params, 'store_group');
        $userId = Arr::pull($params, 'user_id');
        if (in_array($storeGroup, ['team', 'store_in_charge']) && $userId) {
            $user = $this->getUserRepository()->find($userId);
            if ($user) { //user login
                if ('team' == $storeGroup) {
                    $teamIds = $user->teams->pluck('id')->toArray();
                    /** @var \App\Repositories\Contracts\TeamRepository $teamRepository */
                    $teamRepository = app(\App\Repositories\Contracts\TeamRepository::class);
                    $teamUserIds = $teamRepository->getTeamUserIdsWithTeamIds($teamIds);
                    $manager = array_merge($manager, $teamUserIds);
                }

                if ('store_in_charge' == $storeGroup) {
                    $manager = [$userId];
                }
            }
        }

        $ossManager = $this->getLinkedUserInfoRepository()->getOssUserIdsByCssUserIds($manager);

        return array_merge($params, ['manager' => implode(',', $ossManager)]);
    }

    public function getTasks(array $params): Collection
    {
        $params = $this->prepareDataStoreProfit($params);

        $result = $this->myPageService->getTasks($params);
        if (! $result->get('success')) {
            return $result;
        }

        $tasks = $this->getTaskRepository()->handleTaskAssignees(collect($result->get('data')->get('tasks')));
        $result->get('data')->put('tasks', $tasks);

        return $result;
    }

    public function getAlerts(array $params): Collection
    {
        $params = $this->prepareDataStoreProfit($params);

        return $this->myPageService->getAlerts($params);
    }

    public function getSales4QuadrantMap(array $params): Collection
    {
        $params = $this->prepareDataStoreProfit($params);
        $result = $this->myPageService->getStoreProfitTable($params);

        if (! $result->get('success')) {
            return $result;
        }

        $shops = collect($result->get('data')->get('shops'))->map(function ($shop) {
            $storeIds = [Arr::get($shop, 'store_id', '')];
            $totalSalesThisMonth = (int) ($this->getShopAnalyticsCurrentMonth($storeIds, now())?->total_sales ?? 0);
            $totalSalesLastMonth = (int) ($this->getShopAnalyticsCurrentMonth($storeIds, now()->subMonth())?->total_sales ?? 0);

            return [
                'id' => Arr::get($shop, 'id'),
                'store_id' => Arr::get($shop, 'store_id'),
                'name' => Arr::get($shop, 'name'),
                'total_sales_this_month' => $totalSalesThisMonth,
                'total_sales_last_month' => $totalSalesLastMonth,
                'sales_target' => $this->mqKpiRepository->getKPIByDate(Arr::get($shop, 'store_id', ''), now())?->sales_amnt ?? 0,
            ];
        });

        $result->get('data')->put('shops', $shops);

        return $result;
    }
}
