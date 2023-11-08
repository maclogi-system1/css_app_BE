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
            $table->decimal('number_of_categories_point', 3)->default(1.00);
            $table->decimal('number_of_items_point', 3)->default(1.00);
            $table->decimal('product_utilization_rate_point', 3)->default(1.00);
            $table->decimal('product_cost_rate_point', 3)->default(1.00);
            $table->decimal('low_product_reviews_point', 3)->default(1.00);
            $table->decimal('few_sold_out_items_point', 3)->default(1.00);

            // 仕入れ | Purchase
            $table->decimal('purchase_form_point', 3)->default(1.00);
            $table->decimal('stock_value_point', 3)->default(1.00);

            // 構築・制作 | Construction/Production
            $table->string('top_page', 500)->nullable()
                ->comment('1~3/15: 1, 4~6/15: 2, 7~9/15: 3, 10~12/15: 4, 13~15: 5');
            $table->string('category_page', 500)->nullable()
                ->comment('4 values, each value corresponds to 1.25 points');
            $table->string('header', 500)->nullable()
                ->comment('14 values, each value corresponds to 0.35 points');
            $table->string('product_page', 500)->nullable()
                ->comment('12 values, each value corresponds to 0.41 points');
            $table->decimal('product_page_conversion_rate_point', 3)->default(1.00);
            $table->string('product_thumbnail', 500)->nullable();
            $table->decimal('access_number_point', 3)->default(1.00);
            $table->string('featured_products', 500)->nullable()
                ->comment('4 values, each value corresponds to 1.25 points');
            $table->decimal('left_navigation_point')->default(1.00)
                ->comment('更新している: 5, 更新していない: 1');
            $table->decimal('header_large_banner_small_banner_point')->default(1.00)
                ->comment('更新している: 5, 更新していない: 1');

            // イベント・セール | Event sale
            $table->decimal('event_sales_ratio_point', 3)->default(1.00);
            $table->decimal('sales_ratio_day_endings_0_5_point', 3)->default(1.00)
                ->comment('Sales of days ending in 0 and 5. For example: days 5, 10, 15, 20, 25, 30');
            $table->string('implementation_of_measures', 500)->nullable()
                ->comment('5 values, each value corresponds to 1 points');
            $table->string('coupon_effect')->nullable();
            $table->decimal('rpp_ad_point', 3)->default(1.00);
            $table->string('rpp_ad_operation', 500)->nullable()
                ->comment('Depends on rpp_ad_operation field. 3 values, each value corresponds to 1.66 points');
            $table->decimal('coupon_advance_point', 3)->default(1.00);
            $table->decimal('rgroup_ad_point', 3)->default(1.00);
            $table->decimal('tda_ad_point', 3)->default(1.00);
            $table->decimal('sns_ad_point', 3)->default(1.00);
            $table->decimal('google_access_point', 3)->default(1.00);
            $table->decimal('instagram_access_point', 3)->default(1.00);

            // 広告 | Advertisement
            $table->decimal('compatible_point', 3)->default(1.00);
            $table->decimal('shipping_fee_point', 3)->default(1.00);
            $table->decimal('shipping_ratio_point', 3)->default(1.00);
            $table->decimal('mail_service_point', 3)->default(1.00);
            $table->decimal('bundling_ratio_point', 3)->default(1.00);
            $table->decimal('gift_available_point', 3)->default(1.00);
            $table->decimal('delivery_on_specified_day_point', 3)->default(1.00);
            $table->decimal('delivery_preparation_period_point', 3)->default(1.00);
            $table->decimal('shipping_according_to_the_delivery_date_point', 3)->default(1.00);

            // 受注 | Orders
            $table->decimal('system_introduction_point', 3)->default(1.00)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('order_through_rate_point', 3)->default(1.00);
            $table->decimal('number_of_people_in_charge_of_ordering_point', 3)->default(1.00);

            // 顧客対応 | Customer service
            $table->decimal('thank_you_email_point', 3)->default(1.00)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('what_s_included_point', 3)->default(1.00)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('follow_email_point', 3)->default(1.00)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('order_email_point', 3)->default(1.00)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('shipping_email_point', 3)->default(1.00)->comment('更新している: 5, 更新していない: 1');
            $table->decimal('few_user_complaints_point', 3)->default(1.00);

            // CRM
            $table->decimal('email_newsletter_point', 3)->default(1.00);
            $table->decimal('rpp_cvr_rate_point', 3)->default(1.00); //Repeat rate
            $table->integer('review_writing_rate')->default(0); //Review writing rate
            $table->string('review_measures', 500)->nullable(); //Review measures
            $table->decimal('line_official_point', 3)->default(1.00); //LINE official
            $table->integer('instagram_followers')->default(0); //Number of Instagram followers
            $table->decimal('ltv_point', 3)->default(1.00); //LTV

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
