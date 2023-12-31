<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValueChain extends Model
{
    use HasFactory;

    public const PURCHASE_FORM_VALUES = [
        5 => '自社製造',
        3 => 'OEM',
        1 => '仕入れ',
    ];

    public const STOCK_VALUE_VALUES = [
        5 => '売上1ヶ月分',
        4 => '売上2ヶ月～3ヶ月分',
        3 => '売上4ヶ月分～5ヶ月分',
        2 => '売上6か月分～11ヶ月分',
        1 => '売上1年分以上',
    ];

    public const TOP_PAGE_VALUES = [
        'SNSバナー',
        '商品バナー (SALE訴求)',
        '商品バナー (季節限定)',
        '新商品バナー',
        'セールバナー (全体)',
        '定番〇〇選 (商品おすすめアイテム)',
        'カテゴリ画像',
        'イベントバナー (母の日など)',
        'カテゴリボタン設置',
        'レフトナビ',
        '横帯が入っている',
        'フローティングバナーが入っている',
        'レビュー総数の表示(レビュー数やレビュー内容が多い場合)',
        'よくあるお問い合わせ',
        '店舗内検索がある',
    ];

    public const CATEGORY_PAGE_VALUES = [
        'おすすめ商品等がカテゴリページ上部に設置されている',
        'ジャンル分け（第3階層目まで）ができている',
        '商品の並び替え（価格別・種類別・サイズ別・容量別など）ができている',
        '在庫切れの商品は表示されないようにできている',
    ];

    public const HEADER_VALUES = [
        '特集が設置されている（季節感、イベント感、売れてる感）',
        '自社イベントのバナーがある',
        '楽天市場イベントバナーが設置されている(お買い物マラソン・スーパーSALEなど)',
        '店舗内検索がある',
        '付帯サービスのバナーが設置されている(開墾設置など)',
        '商品バナーが設置されている(価格や送料無料などあり)',
        '新商品バナーが設置されている',
        '季節商材のバナーが設置されている',
        'カテゴリボタン設置',
        'フローティングバナーが入っている',
        '横帯が入っている',
        'よくあるご質問がある',
        'お問い合わせができる',
        'レビュー総数の表示(レビュー数やレビュー内容が多い場合)',
    ];

    public const PRODUCT_PAGE_VALUES = [
        'FVでポイントがまとまってる',
        '煽りクーポン画像がある',
        '回遊が設置されてある（セール価格入り）',
        'ランキング、売れている実績',
        'お客様の声、レビュー',
        '特集誘導がある',
        'キャッチコピーがある',
        'ベネフィットが伝わる画像がある',
        '販売実績  (累計いくら、何万枚突破！など)',
        '調査結果（満足度）',
        'カテゴリ / 容量 / サイズ別画像がある',
        '作り方 / レシピ (食品)',
    ];

    public const PRODUCT_THUMBNAIL_VALUES = [
        '商品名（画像で何の商品か分かる場合は不要）がある',
        '権威性がある (ランキング / 賞のエンブレム)',
        '特徴 (強みとなる)が伝わる',
        '商品パッケージ / 使用状況が乗っている',
        'カラバリなどある際は載っている',
        '施策内容が載っている (期限 / いくら安くなるか / g単価など安さの表現 / 限定感)',
        'テキスト占有率20%をクリアしている',
    ];

    public const FEATURED_PRODUCTS_VALUES = [
        '季節商品の目玉商品が設置されている',
        '季節外の目玉商品が残っていない',
        '注力したい商品の目玉商品が設置されている',
        '在庫切れの商品が設置されていない',
    ];

    public const LEFT_NAVIGATION_VALUES = [
        5 => '更新している',
        1 => '更新していない',
    ];

    public const HEADER_LARGE_BANNER_SMALL_BANNER_VALUES = [
        5 => '更新している',
        1 => '更新していない',
    ];

    public const IMPLEMENTATION_OF_MEASURES_VALUES = [
        'ポイント',
        'クーポン',
        'DEAL',
        'ＳＳサーチ',
        '値引き',
    ];

    public const RPP_AD_OPERATION_VALUES = [
        'キーワード入札',
        '週1以上のチューニング',
        '商品別効果測定と、商品除外などの対応ができているか',
    ];

    public const MAIL_SERVICE_VALUES = [
        5 => '対応している',
        1 => '対応していない',
    ];

    public const GIFT_AVAILABLE_VALUES = [
        'のし',
        'ラッピング',
        'カード',
    ];

    public const DELIVERY_ON_SPECIFIED_DAY_VALUES = [
        5 => '対応している',
        1 => '対応していない',
    ];

    public const SYSTEM_INTRODUCTION_VALUES = [
        5 => '対応している',
        1 => '対応していない',
    ];

    public const ORDER_THROUGH_RATE_VALUES = [
        5 => '95%以上',
        4 => '94%～80％',
        3 => '79％～50％',
        2 => '49％～30％',
        1 => '29％以下',
    ];

    public const NUMBER_OF_PEOPLE_IN_CHARGE_OF_ORDERING_VALUES = [
        5 => '300件/人',
        4 => '200件/人',
        3 => '100件/人',
        2 => '50件/人',
        1 => '49件/人以下',
    ];

    public const THANK_YOU_EMAIL_VALUES = [
        5 => '対応している',
        1 => '対応していない',
    ];
    public const WHAT_S_INCLUDED_VALUES = [
        5 => '対応している',
        1 => '対応していない',
    ];
    public const FOLLOW_EMAIL_VALUES = [
        5 => '対応している',
        1 => '対応していない',
    ];
    public const ORDER_EMAIL_VALUES = [
        5 => '対応している',
        1 => '対応していない',
    ];
    public const SHIPPING_EMAIL_VALUES = [
        5 => '対応している',
        1 => '対応していない',
    ];

    public const REVIEW_MEASURES_VALUES = [
        'なし',
        'クーポン',
        'プレゼント',
    ];

    protected $fillable = [
        'store_id', 'date', 'number_of_categories_point', 'number_of_items_point', 'product_utilization_rate_point',
        'product_cost_rate_point', 'low_product_reviews_point', 'few_sold_out_items_point', 'purchase_form_point',
        'stock_value_point', 'top_page', 'category_page', 'header', 'product_page',
        'product_page_conversion_rate_point', 'product_thumbnail', 'access_number_point', 'featured_products',
        'left_navigation_point', 'header_large_banner_small_banner_point', 'event_sales_ratio_point',
        'sales_ratio_day_endings_0_5_point', 'implementation_of_measures', 'coupon_effect', 'rpp_ad_point',
        'rpp_ad_operation', 'coupon_advance_point', 'rgroup_ad_point', 'tda_ad_point', 'sns_ad_point',
        'google_access_point', 'instagram_access_point', 'next_day_delivery_point', 'shipping_fee_point',
        'shipping_ratio_point', 'mail_service_point', 'bundling_ratio_point', 'gift_available_point',
        'delivery_on_specified_day_point', 'delivery_preparation_period_point',
        'shipping_on_the_specified_date_point', 'shipping_according_to_the_delivery_date_point',
        'system_introduction_point', 'order_through_rate_point', 'number_of_people_in_charge_of_ordering_point',
        'thank_you_email_point', 'what_s_included_point', 'follow_email_point', 'order_email_point',
        'shipping_email_point', 'few_user_complaints_point', 'email_newsletter_point', 're_sales_num_rate_point',
        'review_writing_rate_point', 'review_measures', 'line_official_point', 'instagram_followers', 'ltv_point',
    ];

    public function getTopPagePointAttribute()
    {
        $items = array_filter(explode(',', $this->top_page));
        $itemsCount = count($items);

        return match (true) {
            $itemsCount >= 0 && $itemsCount <= 3 => 1,
            $itemsCount >= 4 && $itemsCount <= 6 => 2,
            $itemsCount >= 7 && $itemsCount <= 9 => 3,
            $itemsCount >= 10 && $itemsCount <= 12 => 4,
            $itemsCount >= 13 && $itemsCount <= 15 => 5,
            default => 0,
        };
    }

    public function getCategoryPagePointAttribute()
    {
        $items = array_filter(explode(',', $this->category_page));
        $totalItems = count(static::CATEGORY_PAGE_VALUES);
        $multiplier = 5 / $totalItems;

        return round($multiplier * count($items), 2);
    }

    public function getHeaderPointAttribute()
    {
        $items = array_filter(explode(',', $this->header));
        $totalItems = count(static::HEADER_VALUES);
        $multiplier = 5 / $totalItems;

        return round($multiplier * count($items), 2);
    }

    public function getProductPagePointAttribute()
    {
        $items = array_filter(explode(',', $this->product_page));
        $totalItems = count(static::PRODUCT_PAGE_VALUES);
        $multiplier = 5 / $totalItems;

        return round($multiplier * count($items), 2);
    }

    public function getProductThumbnailPointAttribute()
    {
        $items = array_filter(explode(',', $this->product_thumbnail));
        $totalItems = count(static::PRODUCT_THUMBNAIL_VALUES);
        $multiplier = 5 / $totalItems;

        return round($multiplier * count($items), 2);
    }

    public function getFeaturedProductsPointAttribute()
    {
        $items = array_filter(explode(',', $this->featured_products));
        $totalItems = count(static::FEATURED_PRODUCTS_VALUES);
        $multiplier = 5 / $totalItems;

        return round($multiplier * count($items), 2);
    }

    public function getImplementationOfMeasuresPointAttribute()
    {
        $items = array_filter(explode(',', $this->implementation_of_measures));
        $totalItems = count(static::IMPLEMENTATION_OF_MEASURES_VALUES);
        $multiplier = 5 / $totalItems;

        return round($multiplier * count($items), 2);
    }

    public function getGiftAvailablePointAttribute()
    {
        $items = array_filter(explode(',', $this->gift_available));
        $totalItems = count(static::GIFT_AVAILABLE_VALUES);
        $multiplier = 5 / $totalItems;

        return round($multiplier * count($items), 2);
    }

    public function getRppAdOperationPointAttribute()
    {
        $items = array_filter(explode(',', $this->rpp_ad_operation));
        $totalItems = count(static::RPP_AD_OPERATION_VALUES);
        $multiplier = 5 / $totalItems;

        return round($multiplier * count($items), 2);
    }

    public function getReviewMeasuresPointAttribute()
    {
        $items = array_filter(explode(',', $this->review_measures));
        $totalItems = count(static::REVIEW_MEASURES_VALUES);
        $multiplier = 5 / $totalItems;

        return round($multiplier * count($items), 2);
    }
}
