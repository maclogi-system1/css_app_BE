<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('value_chains', function (Blueprint $table) {
            $table->id();
            $table->string('store_id');
            $table->date('date');

            // 商品 | Merchandise
            $table->decimal('number_of_categories_point', 3)->default(0);
            $table->decimal('number_of_items_point', 3)->default(0);
            $table->decimal('product_utilization_rate_point', 3)->default(0);
            $table->decimal('product_cost_rate_point', 3)->default(0);
            $table->decimal('low_product_reviews_point', 3)->default(0);
            $table->decimal('few_sold_out_items_point', 3)->default(0);

            // 仕入れ | Purchase
            $table->decimal('purchase_form_point', 3)->default(0)
                ->comment('5: 自社製造, 3: OEM, 1: 仕入れ');
            $table->decimal('stock_value_point', 3)->default(0)
                ->comment('5: 売上1ヶ月分, 4: 売上2ヶ月～3ヶ月分, 3: 売上4ヶ月分～5ヶ月分, 2: 売上6か月分～11ヶ月分, 1: 売上1年分以上');

            // 構築・制作 | Construction/Production
            $table->string('top_page')->nullable()
                ->comment('1~3/15: 1, 4~6/15: 2, 7~9/15: 3, 10~12/15: 4, 13~15: 5');
            $table->string('category_page')->nullable()
                ->comment('4 values, each value corresponds to 1.25 points');
            $table->string('header')->nullable()
                ->comment('14 values, each value corresponds to 0.35 points');
            $table->string('product_page')->nullable()
                ->comment('12 values, each value corresponds to 0.41 points');
            $table->decimal('product_page_conversion_rate_point', 3)->default(0);
            $table->string('product_thumbnail')->nullable();
            $table->decimal('access_number_point', 3)->default(0);
            $table->string('featured_products')->nullable()
                ->comment('4 values, each value corresponds to 1.25 points');
            $table->decimal('left_navigation_point')->default(0)
                ->comment('更新している: 5, 更新していない: 1');
            $table->decimal('header_large_banner_small_banner_point')->default(0)
                ->comment('更新している: 5, 更新していない: 1');

            // イベント・セール | Event sale
            $table->decimal('event_sales_ratio_point', 3)->default(0);
            $table->decimal('sales_ratio_day_endings_0_5_point', 3)->default(0)
                ->comment('Sales of days ending in 0 and 5. For example: days 5, 10, 15, 20, 25, 30');
            $table->string('implementation_of_measures')->nullable()
                ->comment('5 values, each value corresponds to 1 points');
            $table->decimal('coupon_effect_point', 3)->default(0);

            // 広告 | Advertisement
            $table->decimal('rpp_ad_point', 3)->default(0);
            $table->string('rpp_ad_operation')->nullable()
                ->comment('Depends on rpp_ad_operation field. 3 values, each value corresponds to 1.66 points');
            $table->decimal('coupon_advance_point', 3)->default(0);
            $table->decimal('rgroup_ad_point', 3)->default(0);
            $table->decimal('tda_ad_point', 3)->default(0);
            $table->decimal('sns_ad_point', 3)->default(0);
            $table->decimal('google_access_point', 3)->default(0);
            $table->decimal('instagram_access_point', 3)->default(0);

            // 物流 | Logistics
            $table->decimal('compatible_point', 3)->default(0);
            $table->decimal('shipping_fee_point', 3)->default(0);
            $table->decimal('shipping_ratio_point', 3)->default(0);
            $table->decimal('mail_service_point', 3)->default(0);
            $table->decimal('bundling_ratio_point', 3)->default(0);
            $table->string('gift_available')->nullable();
            $table->decimal('delivery_on_specified_day_point', 3)->default(0);
            $table->decimal('delivery_preparation_period_point', 3)->default(0);
            $table->decimal('shipping_according_to_the_delivery_date_point', 3)->default(0);

            // 受注 | Orders
            $table->decimal('system_introduction_point', 3)->default(0)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('order_through_rate_point', 3)->default(0);
            $table->decimal('number_of_people_in_charge_of_ordering_point', 3)->default(0);

            // 顧客対応 | Customer service
            $table->decimal('thank_you_email_point', 3)->default(0)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('what_s_included_point', 3)->default(0)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('follow_email_point', 3)->default(0)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('order_email_point', 3)->default(0)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('shipping_email_point', 3)->default(0)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('few_user_complaints_point', 3)->default(0);

            // CRM
            $table->decimal('email_newsletter_point', 3)->default(0);
            $table->decimal('rpp_cvr_rate_point', 3)->default(0); //Repeat rate
            $table->integer('review_writing_rate')->default(0); //Review writing rate
            $table->string('review_measures')->nullable(); //Review measures
            $table->decimal('line_official_point', 3)->default(0); //LINE official
            $table->decimal('instagram_followers', 3)->default(0); //Number of Instagram followers
            $table->decimal('ltv_point', 3)->default(0); //LTV

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('value_chains');
    }
};
